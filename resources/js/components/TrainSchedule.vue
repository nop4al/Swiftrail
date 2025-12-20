<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { getSchedules } from '@/utils/api'
import { saveSelectedSchedule, savePassengerData } from '@/utils/storage'
import { formatPrice, formatDate, showError, showSuccess } from '@/utils/helpers'
import { dummyTrains, getTrainsByRoute } from '@/utils/dummyData'
import AlertModal from './AlertModal.vue'

const router = useRouter()
const route = useRoute()

const selectedClass = ref(null)
const sortBy = ref('departure')
const showSoldOut = ref(false)
const filterPanel = ref(false)
const alertModal = ref(null)
const authModal = ref(null)

// Track selected class for each train (train_id -> class_type)
const selectedClassPerTrain = ref({})

// Passenger state
const showPassengerModal = ref(false)
const adultCount = ref(1)
const childCount = ref(0)
const infantCount = ref(0)

const fromStation = ref(route.query.from || 'Gambir (GMR)')
const toStation = ref(route.query.to || 'Bandung (BD)')
const travelDate = ref(route.query.date || new Date().toISOString().split('T')[0])

// Loading and error states
const isLoading = ref(false)
const hasError = ref(false)
const errorMessage = ref('')

// Format date for display (e.g., YYYY-MM-DD => 03 Des)
const formatDateDisplay = () => {
  if (!travelDate.value) return '03 Des'
  const d = new Date(travelDate.value + 'T00:00:00')
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Des']
  return `${d.getDate()} ${months[d.getMonth()]}`
}

const trains = ref([])

const filteredTrains = computed(() => {
  let result = trains.value

  if (!showSoldOut.value) {
    result = result.filter(t => t.status !== 'sold-out')
  }

  if (selectedClass.value) {
    result = result.filter(t => 
      t.classes.some(c => c.type === selectedClass.value)
    )
  }

  if (sortBy.value === 'departure') {
    result.sort((a, b) => a.departure.localeCompare(b.departure))
  } else if (sortBy.value === 'price') {
    result.sort((a, b) => {
      const priceA = Math.min(...a.classes.map(c => c.price))
      const priceB = Math.min(...b.classes.map(c => c.price))
      return priceA - priceB
    })
  } else if (sortBy.value === 'duration') {
    result.sort((a, b) => {
      const durationA = parseInt(a.duration)
      const durationB = parseInt(b.duration)
      return durationA - durationB
    })
  }

  return result
})

const getMinPrice = (train) => {
  return Math.min(...train.classes.map(c => c.price))
}

const getClassBadgeColor = (className) => {
  const classLower = className.toLowerCase()
  if (classLower.includes('economy')) return 'economy'
  if (classLower.includes('executive')) return 'executive'
  if (classLower.includes('compartment')) return 'compartment'
  return 'default'
}

const passengerDisplayText = computed(() => {
  const parts = []
  if (adultCount.value > 0) {
    parts.push(`${String(adultCount.value).padStart(2, '0')} ${adultCount.value === 1 ? 'Dewasa' : 'Dewasa'}`)
  }
  if (childCount.value > 0) {
    parts.push(`${String(childCount.value).padStart(2, '0')} ${childCount.value === 1 ? 'Anak' : 'Anak'}`)
  }
  if (infantCount.value > 0) {
    parts.push(`${String(infantCount.value).padStart(2, '0')} Bayi`)
  }
  return parts.length > 0 ? parts.join(' • ') : '01 Dewasa 00 Bayi'
})

const openPassengerModal = () => {
  showPassengerModal.value = true
}

const closePassengerModal = () => {
  showPassengerModal.value = false
}

const selectClass = (trainId, trainClass) => {
  // Store only the class type (economy, business, executive)
  selectedClassPerTrain.value[trainId] = trainClass.type
}

const savePassengers = () => {
  showPassengerModal.value = false
}

const goToSelectSeat = (train) => {
  // Check if user is logged in
  const authToken = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token')
  if (!authToken) {
    // Show auth warning before trying to select seat
    authModal.value?.show()
    return
  }

  // Check if class is selected
  const selectedClass = selectedClassPerTrain.value[train.id]
  if (!selectedClass) {
    alertModal.value?.show()
    return
  }

  // Save selected schedule to storage for next component
  saveSelectedSchedule({
    id: train.id,
    trainName: train.name,
    trainNumber: train.number,
    departure: train.departure,
    arrival: train.arrival,
    duration: train.duration,
    fromStation: fromStation.value,
    toStation: toStation.value,
    date: travelDate.value,
    classes: train.classes || [],
    selectedClass: selectedClass // Add selected class info
  })
  
  // Save passenger information
  savePassengerData({
    adults: adultCount.value,
    children: childCount.value,
    infants: infantCount.value,
    totalPassengers: adultCount.value + childCount.value + infantCount.value
  })
  
  // Navigate to seat selection with ALL required data
  router.push({
    path: '/select-seat',
    query: {
      trainId: train.id,
      trainName: train.name,
      trainNumber: train.number,
      departure: train.departure,
      arrival: train.arrival,
      fromStation: fromStation.value,
      toStation: toStation.value,
      date: travelDate.value,
      trainClass: selectedClass,
      passengers: `${adultCount.value}${childCount.value}${infantCount.value}`
    }
  })
}

const handleAuthLoginClick = () => {
  router.push('/login')
}

const selectTrain = (train) => {
  console.log('Selected train:', train)
}

const incrementAdult = () => {
  if (adultCount.value < 9) adultCount.value++
}

const decrementAdult = () => {
  if (adultCount.value > 1) adultCount.value--
}

const incrementChild = () => {
  if (childCount.value < 9) childCount.value++
}

const decrementChild = () => {
  if (childCount.value > 0) childCount.value--
}

const incrementInfant = () => {
  if (infantCount.value < 9 && infantCount.value < adultCount.value) infantCount.value++
}

const decrementInfant = () => {
  if (infantCount.value > 0) infantCount.value--
}

// Fetch schedules from sessionStorage or API
const fetchSchedules = async () => {
  try {
    isLoading.value = true
    hasError.value = false
    errorMessage.value = ''
    
    // Check if search results are in sessionStorage
    const searchResultsStr = sessionStorage.getItem('searchResults')
    
    if (searchResultsStr) {
      // Use search results from sessionStorage
      const searchData = JSON.parse(searchResultsStr)
      
      // Map API response format to component format
      const mappedTrains = (searchData.trains || []).map(train => ({
        id: train.train_id,
        name: train.train_name,
        number: train.train_code,
        departure: train.departure,
        arrival: train.arrival,
        duration: train.duration,
        fromStation: searchData.fromStation,
        toStation: searchData.toStation,
        status: (train.seats_available || 0) > 0 ? 'available' : 'sold-out',
        classes: train.classes || [],
        seats_available: train.seats_available || 0,
        capacity: train.capacity || 0,
        min_price: train.min_price || 0,
        max_price: train.max_price || 0,
        original_data: train // Keep original API response
      }))
      
      trains.value = mappedTrains
      fromStation.value = searchData.fromStation
      toStation.value = searchData.toStation
      travelDate.value = searchData.travelDate
      // Do NOT clear searchResults - keep it for back button navigation
      // sessionStorage.removeItem('searchResults')
      
      if (trains.value.length === 0) {
        hasError.value = true
        errorMessage.value = 'Tidak ada jadwal kereta yang tersedia untuk rute ini'
      }
    } else {
      // If no sessionStorage data, show error
      hasError.value = true
      errorMessage.value = 'Data kereta tidak ditemukan. Silakan cari ulang dari halaman utama.'
      trains.value = []
    }
    
  } catch (error) {
    hasError.value = true
    errorMessage.value = error.message || 'Gagal memuat jadwal kereta. Silakan coba lagi.'
    console.error('Error fetching schedules:', error)
    trains.value = []
  } finally {
    isLoading.value = false
  }
}

// Search function with new parameters
const searchSchedules = async () => {
  await fetchSchedules()
  filterPanel.value = false
}

// Load schedules when component mounts
onMounted(async () => {
  await fetchSchedules()
})

const backToHome = () => {
  router.push('/home')
}

const searchAgain = () => {
  filterPanel.value = false
}
</script>

<template>
  <div class="train-schedule-page">
    <!-- Header -->
    <header class="schedule-header">
      <div class="header-top">
        <button class="back-btn" @click="backToHome">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <div class="header-title">
          <div class="route-info">
            <span>{{ fromStation }}</span>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M5 12H19M12 5L19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>{{ toStation }}</span>
          </div>
          <div class="route-date">{{ new Date(travelDate).toLocaleDateString('id-ID', { weekday: 'short', year: 'numeric', month: 'numeric', day: 'numeric' }) }}</div>
        </div>
      </div>

      <!-- Travel Info Bar -->
      <div class="travel-info">
        <div class="info-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
            <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <span>{{ passengerDisplayText }}</span>
        </div>
        <button class="edit-btn" @click="openPassengerModal">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M11 4H4C2.89543 4 2 4.89543 2 6V20C2 21.1046 2.89543 22 4 22H18C19.1046 22 20 21.1046 20 20V13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M18.5 2.5C19.3284 1.67157 20.6716 1.67157 21.5 2.5C22.3284 3.32843 22.3284 4.67157 21.5 5.5L12 15H9V12L18.5 2.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>
    </header>

    <!-- Passenger Selection Modal -->
    <div v-if="showPassengerModal" class="modal-overlay" @click.self="closePassengerModal">
      <div class="modal-content passenger-modal">
        <!-- Modal Header -->
        <div class="modal-header">
          <button class="modal-close" @click="closePassengerModal">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
          <h2 class="modal-title">Pilih Penumpang</h2>
          <div style="width: 24px"></div>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
          <!-- Adult Passenger -->
          <div class="passenger-option">
            <div class="passenger-info">
              <h3 class="passenger-type">Dewasa</h3>
              <p class="passenger-description">Berusia 3 tahun ke atas</p>
            </div>
            <div class="passenger-counter">
              <button class="counter-btn" @click="decrementAdult" :disabled="adultCount <= 1">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </button>
              <span class="counter-value">{{ String(adultCount).padStart(2, '0') }}</span>
              <button class="counter-btn" @click="incrementAdult" :disabled="adultCount >= 9">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="separator"></div>

          <!-- Child Passenger -->
          <div class="passenger-option">
            <div class="passenger-info">
              <h3 class="passenger-type">Anak</h3>
              <p class="passenger-description">Berusia 3-11 tahun</p>
            </div>
            <div class="passenger-counter">
              <button class="counter-btn" @click="decrementChild" :disabled="childCount <= 0">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </button>
              <span class="counter-value">{{ String(childCount).padStart(2, '0') }}</span>
              <button class="counter-btn" @click="incrementChild" :disabled="childCount >= 9">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="separator"></div>

          <!-- Infant Passenger -->
          <div class="passenger-option">
            <div class="passenger-info">
              <h3 class="passenger-type">Bayi</h3>
              <p class="passenger-description">Berusia di bawah 3 tahun</p>
            </div>
            <div class="passenger-counter">
              <button class="counter-btn" @click="decrementInfant" :disabled="infantCount <= 0">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </button>
              <span class="counter-value">{{ String(infantCount).padStart(2, '0') }}</span>
              <button class="counter-btn" @click="incrementInfant" :disabled="infantCount >= 9 || infantCount >= adultCount">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Info Box -->
          <div class="info-box">
            <div class="info-box-header">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <path d="M12 16V12M12 8H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <span>Syarat Penumpang Bayi</span>
            </div>
            <ul class="info-list">
              <li>Tidak ada biaya tiket</li>
              <li>Tidak memiliki kursi sendiri</li>
              <li>Tidak boleh melebihi jumlah penumpang dewasa</li>
            </ul>
            <a href="#" class="info-link">Lihat Informasi Lengkap</a>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
          <button class="save-btn" @click="savePassengers">Simpan</button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="schedule-container">
      <!-- Filter Sidebar -->
      <aside class="filter-sidebar">
        <div class="filter-header">
          <h3>Filter</h3>
          <button class="close-filter" @click="filterPanel = false">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
        </div>

        <!-- Class Filter -->
        <div class="filter-group">
          <h4 class="filter-title">Kelas Kereta</h4>
          <div class="filter-options">
            <button 
              class="filter-option"
              :class="{ active: selectedClass === null }"
              @click="selectedClass = null"
            >
              Semua
            </button>
            <button 
              class="filter-option"
              :class="{ active: selectedClass === 'executive' }"
              @click="selectedClass = 'executive'"
            >
              <span class="class-badge executive"></span> Executive
            </button>
            <button 
              class="filter-option"
              :class="{ active: selectedClass === 'economy' }"
              @click="selectedClass = 'economy'"
            >
              <span class="class-badge economy"></span> Economy
            </button>
            <button 
              class="filter-option"
              :class="{ active: selectedClass === 'compartment' }"
              @click="selectedClass = 'compartment'"
            >
              <span class="class-badge compartment"></span> Compartment
            </button>
          </div>
        </div>

        <!-- Sort Options -->
        <div class="filter-group">
          <h4 class="filter-title">Urutkan Berdasarkan</h4>
          <div class="filter-options">
            <button 
              class="filter-option"
              :class="{ active: sortBy === 'departure' }"
              @click="sortBy = 'departure'"
            >
              Waktu Keberangkatan
            </button>
            <button 
              class="filter-option"
              :class="{ active: sortBy === 'price' }"
              @click="sortBy = 'price'"
            >
              Harga (Termurah)
            </button>
            <button 
              class="filter-option"
              :class="{ active: sortBy === 'duration' }"
              @click="sortBy = 'duration'"
            >
              Durasi Perjalanan
            </button>
          </div>
        </div>

        <!-- Show Sold Out -->
        <div class="filter-group">
          <label class="checkbox-label">
            <input type="checkbox" v-model="showSoldOut" />
            <span>Tampilkan Terjual Habis</span>
          </label>
        </div>
      </aside>

      <!-- Train List -->
      <main class="train-list">
        <!-- Loading State -->
        <div v-if="isLoading" class="loading-state">
          <div class="spinner"></div>
          <h3>Mencari Jadwal Kereta...</h3>
          <p>Tunggu sebentar, kami sedang mencari jadwal terbaik untuk Anda</p>
        </div>

        <!-- Error State -->
        <div v-else-if="hasError" class="error-state">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
            <path d="M12 7V13M12 17H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <h3>Terjadi Kesalahan</h3>
          <p>{{ errorMessage }}</p>
          <button class="retry-btn" @click="fetchSchedules">Coba Lagi</button>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredTrains.length === 0" class="empty-state">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
            <path d="M8 12H16M12 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <h3>Tidak Ada Kereta Tersedia</h3>
          <p>Silakan ubah kriteria pencarian Anda</p>
        </div>

        <!-- Train Cards -->
        <div v-else class="trains-wrapper">
          <div 
            v-for="train in filteredTrains" 
            :key="train.id" 
            class="train-card"
            :class="{ 'sold-out': train.status === 'sold-out' }"
          >
            <div class="train-card-content">
              <!-- Train Header -->
              <div class="train-header">
                <div class="train-name-section">
                  <div class="train-name">{{ train.name }}</div>
                </div>
                <div class="train-status">
                  <span v-if="train.status === 'available'" class="status-badge available">
                    Tersedia
                  </span>
                  <span v-else class="status-badge sold-out-badge">
                    Terjual Habis
                  </span>
                </div>
              </div>

              <!-- Time & Route -->
              <div class="time-section">
                <div class="time-item">
                  <div class="time">{{ train.departure }}</div>
                  <div class="station">{{ train.fromStation }}</div>
                </div>

                <div class="duration-section">
                  <div class="duration-line">
                    <div class="duration-dot departure"></div>
                    <div class="duration-bar"></div>
                    <div class="duration-dot arrival"></div>
                  </div>
                  <div class="duration-text">{{ train.duration }}</div>
                </div>

                <div class="time-item">
                  <div class="time">{{ train.arrival }}</div>
                  <div class="station">{{ train.toStation }}</div>
                </div>
              </div>

              <!-- Train Info Row -->
              <div class="train-info-row">
                <div class="info-item">
                  <span class="info-label">Kereta</span>
                  <span class="info-value">{{ train.name }} No. {{ train.number }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Tanggal</span>
                  <span class="info-value">{{ train.date }}</span>
                </div>
              </div>

              <!-- Classes Available -->
              <div class="classes-section">
                <div class="classes-label">Pilihan Kelas Tersedia:</div>
                <div class="classes-list">
                  <div 
                    v-for="trainClass in train.classes" 
                    :key="trainClass.type"
                    class="class-option"
                    :class="{ 'selected': selectedClassPerTrain[train.id] === trainClass.type }"
                    @click="selectClass(train.id, trainClass)"
                  >
                    <span class="class-name">{{ trainClass.name }}</span>
                    <span class="class-price">Rp {{ trainClass.price.toLocaleString('id-ID') }}</span>
                    <span class="class-available">{{ trainClass.available }} kursi</span>
                  </div>
                </div>
              </div>

              <!-- Action Button -->
              <div class="action-section">
                <button 
                  v-if="train.status === 'available'"
                  class="select-btn"
                  @click="goToSelectSeat(train)"
                >
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 17L5 12M5 12L10 7M5 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  Pilih Kursi
                </button>
                <button v-else class="select-btn disabled" disabled>
                  Terjual Habis
                </button>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- Bottom Action Bar (Mobile) -->
    <div class="bottom-actions">
      <button class="sort-btn" @click="filterPanel = !filterPanel">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M4 6H20M10 12H14M6 18H18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        Sort
      </button>
      <button class="filter-btn" @click="filterPanel = !filterPanel">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M3 6H21V6.01M6 12H18M9 18H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        Filter
      </button>
    </div>

    <!-- Alert Modal for Class Selection -->
    <AlertModal
      ref="alertModal"
      type="info"
      message="Silakan pilih kelas terlebih dahulu"
      confirmButtonText="Oke"
    />

    <!-- Auth Modal for Login Required -->
    <AlertModal
      ref="authModal"
      type="warning"
      title="Login Diperlukan"
      message="Anda harus login terlebih dahulu untuk memilih kursi. Silakan login dengan akun Anda atau daftar jika belum memiliki akun."
      confirmButtonText="Lanjut ke Login"
      @confirm="handleAuthLoginClick"
    />
  </div>
</template>

<style scoped>
.train-schedule-page {
  width: 100%;
  min-height: 100vh;
  background: var(--color-bg-light);
  display: flex;
  flex-direction: column;
}

/* Header */
.schedule-header {
  background: white;
  border-bottom: 1px solid #e0e4ea;
  padding: 16px 20px;
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-top {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.back-btn {
  width: 40px;
  height: 40px;
  border: none;
  background: #f0f2f5;
  border-radius: 8px;
  color: var(--color-text-dark);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.back-btn:hover {
  background: #e8ebf0;
}

.header-title {
  flex: 1;
}

.route-info {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 16px;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 4px;
}

.route-info svg {
  color: var(--color-secondary);
}

.route-date {
  font-size: 13px;
  color: var(--color-text-light);
}

.travel-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #f8f9fa;
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 13px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 6px;
  color: var(--color-text-light);
}

.info-item svg {
  color: var(--color-primary);
}

.edit-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: white;
  border: 1px solid #e0e4ea;
  border-radius: 6px;
  color: var(--color-primary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.edit-btn:hover {
  background: #f0f8ff;
  border-color: var(--color-primary);
}

/* Main Container */
.schedule-container {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 20px;
  padding: 20px;
  flex: 1;
  max-width: 1400px;
  margin: 0 auto;
  width: 100%;
}

/* Filter Sidebar */
.filter-sidebar {
  background: white;
  border-radius: 8px;
  padding: 16px;
  height: fit-content;
  border: 1px solid #e0e4ea;
  position: sticky;
  top: 140px;
}

.filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.filter-header h3 {
  font-size: 16px;
  font-weight: 600;
  color: var(--color-text-dark);
  margin: 0;
}

.close-filter {
  display: none;
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  color: var(--color-text-light);
  cursor: pointer;
}

.filter-group {
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f0f0f0;
}

.filter-group:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.filter-title {
  font-size: 13px;
  font-weight: 600;
  text-transform: uppercase;
  color: var(--color-text-light);
  margin: 0 0 12px 0;
  letter-spacing: 0.5px;
}

.filter-options {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.filter-option {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border: 1px solid #e0e4ea;
  background: white;
  border-radius: 6px;
  font-size: 13px;
  color: var(--color-text-dark);
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: left;
}

.filter-option:hover {
  background: #f8f9fa;
  border-color: var(--color-primary);
}

.filter-option.active {
  background: var(--color-primary);
  color: white;
  border-color: var(--color-primary);
}

.class-badge {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.class-badge.executive {
  background: #7c3aed;
}

.class-badge.economy {
  background: #ec4899;
}

.class-badge.compartment {
  background: #f59e0b;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 13px;
  color: var(--color-text-dark);
}

.checkbox-label input {
  cursor: pointer;
}

/* Train List */
.train-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.trains-wrapper {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 8px;
  color: var(--color-text-light);
}

.empty-state svg {
  margin-bottom: 16px;
  color: #cbd5e1;
}

.empty-state h3 {
  font-size: 18px;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 8px;
}

.empty-state p {
  margin: 0;
}

/* Train Card */
.train-card {
  background: white;
  border: 1px solid #e0e4ea;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s ease;
  animation: slideInUp 0.4s ease-out;
}

.train-card:hover:not(.sold-out) {
  box-shadow: 0 4px 16px rgba(22, 117, 231, 0.12);
  border-color: var(--color-primary);
}

.train-card.sold-out {
  opacity: 0.7;
  background: #fafafa;
}

.train-card-content {
  padding: 16px;
}

.train-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.train-name-section {
  display: flex;
  align-items: center;
  gap: 12px;
}

.train-class-badge {
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  color: white;
}

.train-class-badge.executive {
  background: #7c3aed;
}

.train-class-badge.economy {
  background: #ec4899;
}

.train-class-badge.compartment {
  background: #f59e0b;
}

.train-class-badge.default {
  background: #6b7280;
}

.train-name {
  font-size: 16px;
  font-weight: 600;
  color: var(--color-text-dark);
}

.train-status {
  text-align: right;
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.available {
  background: #f0fdf4;
  color: #15803d;
  border: 1px solid #86efac;
}

.status-badge.sold-out-badge {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fca5a5;
}

.time-section {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.time-item {
  flex: 1;
}

.time {
  font-size: 18px;
  font-weight: 700;
  color: var(--color-text-dark);
  margin-bottom: 4px;
}

.station {
  font-size: 13px;
  color: var(--color-text-light);
}

.duration-section {
  flex: 0.8;
  text-align: center;
}

.duration-line {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-bottom: 4px;
}

.duration-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--color-primary);
}

.duration-bar {
  flex: 1;
  height: 2px;
  background: #e0e4ea;
}

.duration-text {
  font-size: 12px;
  color: var(--color-text-light);
  font-weight: 500;
}

.train-info-row {
  display: flex;
  gap: 24px;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.info-label {
  font-size: 12px;
  color: var(--color-text-light);
  font-weight: 500;
}

.info-value {
  font-size: 14px;
  color: var(--color-text-dark);
  font-weight: 600;
}

.classes-section {
  margin-bottom: 16px;
}

.classes-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--color-text-light);
  text-transform: uppercase;
  margin-bottom: 8px;
}

.classes-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 12px;
}

.class-option {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 8px 12px;
  background: #f8f9fa;
  border: 1px solid #e0e4ea;
  border-radius: 6px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: fit-content;
  flex: 0 1 auto;
}

.class-option:hover {
  border-color: var(--color-primary);
  background: #f0f7ff;
}

.class-option.selected {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
}

.class-option.selected .class-name,
.class-option.selected .class-price,
.class-option.selected .class-available {
  color: white;
}

.class-name {
  font-weight: 600;
  color: var(--color-text-dark);
}

.class-price {
  color: var(--color-primary);
  font-weight: 600;
}

.class-available {
  color: var(--color-text-light);
  font-size: 11px;
}

.action-section {
  display: flex;
  gap: 8px;
}

.select-btn {
  flex: 1;
  padding: 12px 16px;
  background: linear-gradient(135deg, #1675E7 0%, #275CDE 100%);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.select-btn:hover:not(.disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

.select-btn.disabled {
  background: #cbd5e1;
  cursor: not-allowed;
}

/* Bottom Action Bar */
.bottom-actions {
  display: none;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: white;
  border-top: 1px solid #e0e4ea;
  padding: 12px 16px;
  gap: 8px;
  z-index: 50;
}

.sort-btn,
.filter-btn {
  flex: 1;
  padding: 12px;
  border: 1px solid #e0e4ea;
  background: white;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text-dark);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: all 0.3s ease;
}

.sort-btn:hover,
.filter-btn:hover {
  background: #f8f9fa;
  border-color: var(--color-primary);
  color: var(--color-primary);
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: flex-end;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease;
}

.modal-content {
  background: white;
  border-radius: 16px 16px 0 0;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  animation: slideUp 0.3s ease;
}

.passenger-modal {
  border-radius: 16px 16px 0 0;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 20px 16px;
  border-bottom: 1px solid #e0e4ea;
}

.modal-close {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  color: var(--color-text-dark);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.modal-close:hover {
  color: var(--color-primary);
}

.modal-title {
  font-size: 18px;
  font-weight: 700;
  color: var(--color-text-dark);
  margin: 0;
  flex: 1;
  text-align: center;
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
}

.passenger-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0;
  margin-bottom: 0;
}

.passenger-info {
  flex: 1;
}

.passenger-type {
  font-size: 15px;
  font-weight: 600;
  color: var(--color-text-dark);
  margin: 0 0 4px 0;
}

.passenger-description {
  font-size: 13px;
  color: var(--color-text-light);
  margin: 0;
}

.passenger-counter {
  display: flex;
  align-items: center;
  gap: 12px;
  background: white;
  border: 1px solid #e0e4ea;
  border-radius: 8px;
  padding: 8px;
}

.counter-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: white;
  border-radius: 6px;
  color: var(--color-primary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  border: 1px solid #e0e4ea;
}

.counter-btn:hover:not(:disabled) {
  background: #f0f8ff;
  border-color: var(--color-primary);
}

.counter-btn:disabled {
  color: #cbd5e1;
  cursor: not-allowed;
  border-color: #f0f0f0;
}

.counter-value {
  font-size: 16px;
  font-weight: 700;
  color: var(--color-text-dark);
  min-width: 28px;
  text-align: center;
}

.separator {
  height: 1px;
  background: #e0e4ea;
  margin: 16px 0;
}

.info-box {
  background: #f0f8ff;
  border: 1px solid #dbeafe;
  border-radius: 8px;
  padding: 16px;
  margin-top: 16px;
}

.info-box-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
  font-size: 14px;
  font-weight: 600;
  color: var(--color-primary);
}

.info-list {
  list-style: none;
  padding: 0;
  margin: 0 0 12px 0;
}

.info-list li {
  font-size: 13px;
  color: var(--color-text-dark);
  margin-bottom: 8px;
  padding-left: 20px;
  position: relative;
}

.info-list li:before {
  content: '•';
  position: absolute;
  left: 0;
  color: var(--color-primary);
  font-weight: bold;
}

.info-list li:last-child {
  margin-bottom: 0;
}

.info-link {
  color: var(--color-primary);
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  display: inline-block;
}

.info-link:hover {
  text-decoration: underline;
}

.modal-footer {
  padding: 16px 20px 20px;
  border-top: 1px solid #e0e4ea;
  background: white;
}

.save-btn {
  width: 100%;
  padding: 14px 20px;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

.save-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(22, 117, 231, 0.4);
}

.save-btn:active {
  transform: translateY(0);
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 1024px) {
  .schedule-container {
    grid-template-columns: 1fr;
  }

  .filter-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    max-width: 280px;
    height: 100%;
    z-index: 200;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    overflow-y: auto;
  }

  .filter-sidebar.active {
    transform: translateX(0);
  }

  .close-filter {
    display: flex;
  }

  .bottom-actions {
    display: flex;
  }
}

@media (max-width: 640px) {
  .schedule-header {
    padding: 12px 16px;
  }

  .header-top {
    margin-bottom: 12px;
  }

  .train-card-content {
    padding: 12px;
  }

  .time-section {
    flex-wrap: wrap;
    gap: 12px;
  }

  .train-header {
    flex-direction: column;
    gap: 8px;
  }

  .classes-list {
    flex-direction: column;
  }

  .class-option {
    width: 100%;
  }

  .travel-info {
    flex-wrap: wrap;
    gap: 8px;
  }

  .route-info {
    font-size: 14px;
  }

  .modal-content {
    max-width: 100%;
    border-radius: 20px 20px 0 0;
  }

  .modal-header {
    padding: 16px 16px 12px;
  }

  .modal-body {
    padding: 16px;
  }

  .modal-footer {
    padding: 12px 16px 16px;
  }

  .passenger-counter {
    gap: 10px;
  }

  .counter-btn {
    width: 28px;
    height: 28px;
  }

  .counter-value {
    font-size: 14px;
    min-width: 24px;
  }

  .passenger-type {
    font-size: 14px;
  }

  .passenger-description {
    font-size: 12px;
  }

  .info-box {
    padding: 12px;
  }

  .info-box-header {
    font-size: 13px;
    margin-bottom: 10px;
  }

  .info-list li {
    font-size: 12px;
    margin-bottom: 6px;
  }

  .save-btn {
    padding: 12px 16px;
    font-size: 14px;
  }
}

@media (max-width: 480px) {
  .modal-overlay {
    align-items: stretch;
  }

  .modal-content {
    border-radius: 12px 12px 0 0;
  }

  .modal-header {
    padding: 12px 12px 10px;
  }

  .modal-title {
    font-size: 16px;
  }

  .modal-body {
    padding: 12px;
  }

  .modal-footer {
    padding: 10px 12px 12px;
  }

  .passenger-option {
    gap: 12px;
  }

  .passenger-counter {
    gap: 8px;
  }

  .counter-btn {
    width: 26px;
    height: 26px;
    font-size: 12px;
  }

  .counter-value {
    font-size: 12px;
    min-width: 20px;
  }

  .passenger-type {
    font-size: 13px;
  }

  .passenger-description {
    font-size: 11px;
  }

  .info-box {
    padding: 10px;
  }

  .info-box-header {
    font-size: 12px;
    gap: 6px;
    margin-bottom: 8px;
  }

  .info-list li {
    font-size: 11px;
    margin-bottom: 4px;
    padding-left: 18px;
  }

  .info-link {
    font-size: 12px;
  }

  .save-btn {
    padding: 10px 14px;
    font-size: 13px;
  }
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 80px 20px 60px;
  background: white;
  border-radius: 8px;
  color: var(--color-text-light);
}

.spinner {
  width: 48px;
  height: 48px;
  margin: 0 auto 24px;
  border: 4px solid #f0f0f0;
  border-top: 4px solid var(--color-primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.loading-state h3 {
  font-size: 18px;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 8px;
}

.loading-state p {
  margin: 0;
  font-size: 14px;
}

/* Error State */
.error-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 8px;
  color: var(--color-text-light);
}

.error-state svg {
  margin-bottom: 16px;
  color: #ef4444;
  width: 64px;
  height: 64px;
}

.error-state h3 {
  font-size: 18px;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 8px;
}

.error-state p {
  margin: 0 0 24px 0;
  font-size: 14px;
}

.retry-btn {
  padding: 12px 32px;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.retry-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
