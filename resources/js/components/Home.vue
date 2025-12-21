<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import SwiftPayCard from './SwiftPayCard.vue'
import { tabs } from '../data/tabs.js'
import { 
  formatDate,
  formatPrice,
  fetchPromos,
  fetchDestinations,
  fetchStations,
  searchTrains as apiSearchTrains
} from '../utils/api.js'
import { dummyTrains, getTrainsByRoute } from '@/utils/dummyData'

// Router
const router = useRouter()

// User data
const userToken = ref(null)
const userData = ref(null)
const showUserMenu = ref(false)

// Search form data
const activeTab = ref('antar-kota')

// Data stasiun - diambil dari API
const stationList = ref([])

const fromStation = ref('')
const toStation = ref('')
const travelDate = ref(new Date().toISOString().split('T')[0])

// Data dari API
const promos = ref([])
const destinations = ref([])
const stations = ref([])
const searchResults = ref([])

// Loading dan error states
const isLoading = ref(false)
const isSearching = ref(false)
const errorMessage = ref(null)
const successMessage = ref(null)

// Modal untuk promo
const showPromoModal = ref(false)
const selectedPromo = ref(null)

// Modal untuk destinasi
const showDestinationModal = ref(false)
const selectedDestination = ref(null)

// Modal untuk hasil pencarian
const showSearchModal = ref(false)

// Modal untuk notifikasi
const showNotificationModal = ref(false)
const notificationMessage = ref('')
const notificationType = ref('info') // 'info', 'warning', 'error', 'success'

// Fetch data saat component mounted
onMounted(async () => {
  try {
    isLoading.value = true
    errorMessage.value = null
    
    // Check if user is logged in
    userToken.value = localStorage.getItem('userToken')
    const userDataStr = localStorage.getItem('userData')
    if (userDataStr) {
      userData.value = JSON.parse(userDataStr)
    }
    
    // Fetch semua data secara parallel
    const [promosData, destinationsData, stationsData] = await Promise.all([
      fetchPromos(),
      fetchDestinations(),
      fetchStations()
    ])
    
    promos.value = promosData || []
    destinations.value = destinationsData || []
    stations.value = stationsData || []
    
    // Transform stations API data untuk stationList
    console.log('stationsData:', stationsData, 'type:', typeof stationsData, 'isArray:', Array.isArray(stationsData))
    
    if (stationsData && Array.isArray(stationsData) && stationsData.length > 0) {
      // Convert to plain array to avoid Vue reactivity issues
      const plainArray = JSON.parse(JSON.stringify(stationsData))
      stationList.value = plainArray.map(station => ({
        name: station.name,
        code: station.code,
        fullName: `${station.name} (${station.code})`
      }))
      
      console.log('stationList.value:', stationList.value)
      
      // Set default stations: Gambir → Surabaya Pasar Turi
      const gambir = stationList.value.find(s => s.name === 'Gambir')
      const surabaya = stationList.value.find(s => s.name === 'Surabaya Pasar Turi')
      
      fromStation.value = gambir?.fullName || stationList.value[0]?.fullName || ''
      toStation.value = surabaya?.fullName || stationList.value[stationList.value.length - 1]?.fullName || ''
      
      console.log('Default fromStation:', fromStation.value)
      console.log('Default toStation:', toStation.value)
    }
    
  } catch (error) {
    errorMessage.value = 'Gagal memuat data. Silakan coba lagi.'
    console.error('Error loading data:', error)
  } finally {
    isLoading.value = false
  }
})

const handleLogout = () => {
  localStorage.removeItem('userToken')
  localStorage.removeItem('userData')
  showUserMenu.value = false
  router.push('/login')
}

const openPromoModal = (promo) => {
  selectedPromo.value = promo
  showPromoModal.value = true
  document.body.classList.add('modal-open')
  document.body.style.overflow = 'hidden'
}

const closePromoModal = () => {
  showPromoModal.value = false
  document.body.classList.remove('modal-open')
  document.body.style.overflow = 'auto'
}

const openDestinationModal = (destination) => {
  selectedDestination.value = destination
  showDestinationModal.value = true
  document.body.classList.add('modal-open')
  document.body.style.overflow = 'hidden'
}

const closeDestinationModal = () => {
  showDestinationModal.value = false
  document.body.classList.remove('modal-open')
  document.body.style.overflow = 'auto'
}

const closeSearchModal = () => {
  showSearchModal.value = false
  document.body.classList.remove('modal-open')
  document.body.style.overflow = 'auto'
}

const formattedDate = computed(() => {
  return formatDate(travelDate.value)
})

const swapStations = () => {
  const temp = fromStation.value
  fromStation.value = toStation.value
  toStation.value = temp
}

const handleDateChange = () => {
  // Date change event - ready for next search
  console.log('Date changed to:', travelDate.value)
}

const handleTabClick = (tabId) => {
  if (tabId !== 'antar-kota') {
    notificationMessage.value = 'Fitur ini masih dalam pengembangan'
    notificationType.value = 'info'
    showNotificationModal.value = true
    return
  }
  activeTab.value = tabId
}

const closeNotificationModal = () => {
  showNotificationModal.value = false
}

const handleBookTrain = (train) => {
  // Extract station names from fullName (e.g., 'Gambir (GMB)' => 'Gambir')
  const fromStationName = fromStation.value.split(' (')[0]
  const toStationName = toStation.value.split(' (')[0]
  
  // Save search results to sessionStorage for TrainSchedule component
  sessionStorage.setItem('searchResults', JSON.stringify({
    trains: searchResults.value,
    fromStation: fromStationName,
    toStation: toStationName,
    travelDate: travelDate.value
  }))
  
  // Navigate to TrainSchedule component with query params
  router.push({
    name: 'TrainSchedule',
    query: {
      from: fromStationName,
      to: toStationName,
      date: travelDate.value,
      trainId: train.train_id,
      trainName: train.train_name
    }
  })
  
  // Close search modal
  showSearchModal.value = false
  document.body.classList.remove('modal-open')
  document.body.style.overflow = 'auto'
}

const searchTrains = async () => {
  try {
    isSearching.value = true
    errorMessage.value = null
    successMessage.value = null
    
    const result = await apiSearchTrains({
      fromStation: fromStation.value,
      toStation: toStation.value,
      travelDate: travelDate.value,
      activeTab: activeTab.value
    })
    
    if (result.success) {
      searchResults.value = result.results || []
      showSearchModal.value = true
      document.body.classList.add('modal-open')
      document.body.style.overflow = 'hidden'
      successMessage.value = `Ditemukan ${searchResults.value.length} kereta tersedia`
    } else {
      errorMessage.value = result.error || 'Gagal mencari kereta'
    }
  } catch (error) {
    errorMessage.value = 'Terjadi kesalahan saat mencari kereta'
    console.error('Error searching trains:', error)
  } finally {
    isSearching.value = false
  }
}

</script>

<template>
  <div class="homepage">
    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-bg">
        <div class="hero-gradient"></div>
        <div class="hero-pattern"></div>
      </div>
      
      <div class="hero-content">
        <h1 class="hero-title">Jelajahi Indonesia</h1>
        <p class="hero-subtitle">
          Akses layanan Kereta Api Antar Kota, Lokal, KRL, LRT, hingga Bandara dalam satu aplikasi.
        </p>
      </div>

      <!-- SwiftPay Card -->
      <SwiftPayCard />

      <!-- Booking Card -->
      <div class="booking-card">
        <!-- Tabs -->
        <div class="booking-tabs">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            :class="['tab-btn', { active: activeTab === tab.id }]"
            @click="handleTabClick(tab.id)"
          >
            <span class="tab-icon">
              <!-- Train Icon -->
              <svg v-if="tab.icon === 'train'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 11V8C4 5.79086 5.79086 4 8 4H16C18.2091 4 20 5.79086 20 8V11M4 11V15C4 16.1046 4.89543 17 6 17H7M4 11H20M20 11V15C20 16.1046 19.1046 17 18 17H17M7 17V19L5 21M7 17H17M17 17V19L19 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <rect x="7" y="7" width="3" height="2" rx="0.5" fill="currentColor"/>
                <rect x="14" y="7" width="3" height="2" rx="0.5" fill="currentColor"/>
              </svg>
              <!-- Tram Icon -->
              <svg v-else-if="tab.icon === 'tram'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5 10V16C5 17.1046 5.89543 18 7 18H17C18.1046 18 19 17.1046 19 16V10C19 7.79086 17.2091 6 15 6H9C6.79086 6 5 7.79086 5 10Z" stroke="currentColor" stroke-width="2"/>
                <path d="M8 18V21M16 18V21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M12 3V6M9 3H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <circle cx="8" cy="14" r="1.5" fill="currentColor"/>
                <circle cx="16" cy="14" r="1.5" fill="currentColor"/>
              </svg>
              <!-- Commuter Icon -->
              <svg v-else-if="tab.icon === 'commuter'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="5" y="4" width="14" height="14" rx="3" stroke="currentColor" stroke-width="2"/>
                <path d="M8 18V21M16 18V21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <rect x="8" y="7" width="3" height="3" rx="0.5" fill="currentColor"/>
                <rect x="13" y="7" width="3" height="3" rx="0.5" fill="currentColor"/>
                <circle cx="9" cy="14" r="1" fill="currentColor"/>
                <circle cx="15" cy="14" r="1" fill="currentColor"/>
              </svg>
              <!-- Bolt Icon -->
              <svg v-else-if="tab.icon === 'bolt'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M13 2L4 14H11L10 22L20 10H13L13 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <!-- Plane Icon -->
              <svg v-else-if="tab.icon === 'plane'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 17L9 12L3 7V17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9 12H21M21 12L17 8M21 12L17 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            <span class="tab-label">{{ tab.label }}</span>
          </button>
        </div>

        <!-- Booking Form -->
        <div class="booking-form">
          <div class="form-group">
            <label class="form-label">DARI STASIUN</label>
            <div class="input-wrapper">
              <span class="input-icon from">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M4 11V8C4 5.79086 5.79086 4 8 4H16C18.2091 4 20 5.79086 20 8V11M4 11V15C4 16.1046 4.89543 17 6 17H18C19.1046 17 20 16.1046 20 15V11M4 11H20" stroke="currentColor" stroke-width="2"/>
                  <circle cx="8" cy="14" r="1" fill="currentColor"/>
                  <circle cx="16" cy="14" r="1" fill="currentColor"/>
                </svg>
              </span>
              <select 
                v-model="fromStation"
                class="form-input"
              >
                <option v-for="station in stationList" :key="station.code" :value="station.fullName">
                  {{ station.fullName }}
                </option>
              </select>
            </div>
          </div>

          <button class="swap-btn" @click="swapStations" title="Tukar stasiun">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M7 16L3 12M3 12L7 8M3 12H21M17 8L21 12M21 12L17 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>

          <div class="form-group">
            <label class="form-label">KE STASIUN</label>
            <div class="input-wrapper">
              <span class="input-icon to">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12 21C12 21 19 15.5 19 10C19 6.13401 15.866 3 12 3C8.13401 3 5 6.13401 5 10C5 15.5 12 21 12 21Z" stroke="currentColor" stroke-width="2"/>
                  <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
                </svg>
              </span>
              <select 
                v-model="toStation"
                class="form-input"
              >
                <option v-for="station in stationList" :key="station.code" :value="station.fullName">
                  {{ station.fullName }}
                </option>
              </select>
            </div>
          </div>

          <div class="form-group date-group">
            <label class="form-label">TANGGAL</label>
            <div class="input-wrapper">
              <span class="input-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect x="3" y="6" width="18" height="15" rx="2" stroke="currentColor" stroke-width="2"/>
                  <path d="M3 10H21" stroke="currentColor" stroke-width="2"/>
                  <path d="M8 3V6M16 3V6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </span>
              <input 
                type="date" 
                v-model="travelDate"
                class="form-input date-input"
                @change="handleDateChange"
              />
            </div>
          </div>

          <button class="search-btn" @click="searchTrains" :disabled="isSearching">
            {{ isSearching ? 'Mencari...' : 'Cari' }}
          </button>
        </div>

        <!-- Loading Indicator -->
        <div v-if="isLoading" class="loading-indicator">
          <div class="spinner"></div>
          <p>Memuat data...</p>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="error-alert">
          <p>{{ errorMessage }}</p>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="success-alert">
          <p>{{ successMessage }}</p>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features">
      <div class="features-container">
        <div class="feature-card">
          <div class="feature-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2"/>
              <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <h3>Pemesanan Cepat</h3>
          <p>Pesan tiket dalam hitungan detik dengan proses yang mudah dan efisien.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M20.618 5.984C17.456 2.844 12.518 2.122 8.618 4.192C5.018 6.104 2.748 9.944 3.058 14.024C3.268 16.934 4.678 19.614 6.988 21.394" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              <path d="M17.012 21.394C19.322 19.614 20.732 16.934 20.942 14.024" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <h3>Pembayaran Aman</h3>
          <p>Transaksi terjamin dengan berbagai metode pembayaran yang aman.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M17 8H19C20.1046 8 21 8.89543 21 10V18C21 19.1046 20.1046 20 19 20H5C3.89543 20 3 19.1046 3 18V10C3 8.89543 3.89543 8 5 8H7" stroke="currentColor" stroke-width="2"/>
              <path d="M12 4V14M12 14L8 10M12 14L16 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3>E-Tiket Instan</h3>
          <p>Dapatkan tiket elektronik langsung di aplikasi tanpa perlu cetak.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M18 18.72C18.93 18.46 19.77 17.96 20.4 17.28C22.31 15.16 22.03 11.98 19.78 10.2C19.4 9.92 18.98 9.7 18.53 9.54" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              <path d="M5.47 9.54C5.02 9.7 4.6 9.92 4.22 10.2C1.97 11.98 1.69 15.16 3.6 17.28C4.23 17.96 5.07 18.46 6 18.72" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              <path d="M8.53 13.02C7.22 14.33 7.22 16.47 8.53 17.78C9.84 19.09 11.98 19.09 13.29 17.78C14.6 16.47 14.6 14.33 13.29 13.02C11.98 11.71 9.84 11.71 8.53 13.02Z" stroke="currentColor" stroke-width="2"/>
              <path d="M12 5C12 5 14.5 7.5 14.5 9.5C14.5 10.88 13.38 12 12 12C10.62 12 9.5 10.88 9.5 9.5C9.5 7.5 12 5 12 5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3>Layanan 24/7</h3>
          <p>Dukungan pelanggan siap membantu kapan saja Anda butuhkan.</p>
        </div>
      </div>
    </section>

    <!-- Promo Section -->
    <section class="promo">
      <div class="promo-container">
        <div class="section-header">
          <h2 class="section-title">Promo & Penawaran Spesial</h2>
          <p class="section-subtitle">Dapatkan keuntungan maksimal dengan penawaran eksklusif untuk member SwiftRail</p>
        </div>
        <div class="promo-grid">
          <div class="promo-card" v-for="promo in promos" :key="promo.id" style="animation-delay: 0s;">
            <div class="promo-image" style="background: linear-gradient(135deg, #DC143C 0%, #8B0000 100%); background-size: cover;" v-if="promo.id === 1">
              <div class="promo-building">Laboratorium Klinik<br>PRAMITA</div>
            </div>
            <div class="promo-image" style="background: linear-gradient(135deg, #2C3E50 0%, #1A252F 100%); background-size: cover;" v-else-if="promo.id === 2">
              <div class="promo-hotel">IBIS YIA KULON<br>PROGO</div>
            </div>
            <div class="promo-image" style="background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); background-size: cover;" v-else-if="promo.id === 3">
              <div class="promo-food">Makanan di<br>TERMINAL</div>
            </div>
            <div class="promo-image" style="background: linear-gradient(135deg, #1ABC9C 0%, #16A085 100%); background-size: cover;" v-else>
              <div class="promo-travel">PERJALANAN<br>ANDA</div>
            </div>
            <div class="promo-content">
              <div class="promo-label">
                {{ promo.label }}
              </div>
              <div class="promo-discount">{{ promo.discount }}</div>
              <p class="promo-desc">{{ promo.shortDesc }}</p>
              <button @click="openPromoModal(promo)" class="promo-cta">Lihat Penawaran →</button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Popular Destinations Section -->
    <section class="destinations">
      <div class="destinations-container">
        <div class="section-header">
          <h2 class="section-title">Jelajahi Destinasi Populer</h2>
          <p class="section-subtitle">Nikmati pengalaman perjalanan ke kota-kota impian di Indonesia</p>
        </div>
        <div class="destinations-grid">
          <button v-for="destination in destinations" :key="destination.id" 
            :class="['destination-card', destination.name.toLowerCase().replace('yogyakarta', 'yogya').replace('bandung', 'bandung').replace('jakarta', 'jakarta').replace('surabaya', 'surabaya')]"
            @click="openDestinationModal(destination)">
            <div class="destination-overlay"></div>
            <h3>{{ destination.name }}</h3>
            <p class="destination-subtitle">{{ destination.subtitle }}</p>
            <span class="destination-arrow">→</span>
          </button>
        </div>
      </div>
    </section>

    <!-- Rewards Section -->
    <section class="rewards">
      <div class="rewards-container">
        <div class="section-header">
          <h2 class="section-title">Tukarkan Poin Anda Sekarang</h2>
          <p class="section-subtitle">Kumpulkan RailPoin dari setiap pembelian dan tukarkan dengan hadiah menarik</p>
        </div>
        <div class="rewards-header">
          <a href="#" class="view-all-link">Lihat Semua Reward →</a>
        </div>
        
        <div class="rewards-grid">
          <div class="reward-card gold" style="animation-delay: 0s;">
            <div class="reward-top">
              <div class="reward-badge">RAILPOIN</div>
            </div>
            <div class="reward-amount">25.000</div>
            <p class="reward-label">Poin Perjalanan</p>
            <a href="#" class="reward-btn">Tukar Sekarang</a>
          </div>
          <div class="reward-card platinum" style="animation-delay: 0.1s;">
            <div class="reward-top">
              <div class="reward-badge">RAILPOIN</div>
            </div>
            <div class="reward-amount">50.000</div>
            <p class="reward-label">Poin Premium</p>
            <a href="#" class="reward-btn">Tukar Sekarang</a>
          </div>
          <div class="reward-card silver" style="animation-delay: 0.2s;">
            <div class="reward-top">
              <div class="reward-badge">VOUCHER</div>
            </div>
            <div class="reward-amount">100K</div>
            <p class="reward-label">Voucher Makanan</p>
            <a href="#" class="reward-btn">Tukar Sekarang</a>
          </div>
          <div class="reward-card bronze" style="animation-delay: 0.3s;">
            <div class="reward-top">
              <div class="reward-badge">BONUS</div>
            </div>
            <div class="reward-amount">15.000</div>
            <p class="reward-label">Diskon Tiket</p>
            <a href="#" class="reward-btn">Tukar Sekarang</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Promo Modal -->
    <div v-if="showPromoModal" class="modal-overlay" @click="closePromoModal">
      <div class="modal-content" @click.stop>
        <button class="modal-close" @click="closePromoModal">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>
        
        <div v-if="selectedPromo" class="modal-body">
          <div class="modal-image-container">
            <img :src="selectedPromo.image" :alt="selectedPromo.name" class="modal-image">
          </div>
          
          <div class="modal-header">
            <h2 class="modal-title">{{ selectedPromo.name }}</h2>
            <p class="modal-label">{{ selectedPromo.label }}</p>
          </div>

          <div class="modal-discount-section">
            <div class="discount-value">{{ selectedPromo.discount }}</div>
          </div>

          <div class="modal-section">
            <h3 class="section-heading">Deskripsi Penawaran</h3>
            <p class="section-text">{{ selectedPromo.description }}</p>
          </div>

          <div class="modal-section">
            <h3 class="section-heading">Berlaku Hingga</h3>
            <p class="duration-text">{{ selectedPromo.duration }}</p>
          </div>

          <div class="modal-section">
            <h3 class="section-heading">Syarat & Ketentuan</h3>
            <ul class="terms-list">
              <li v-for="(term, index) in selectedPromo.terms" :key="index">
                <span class="term-number">{{ index + 1 }}.</span>
                <span class="term-text">{{ term }}</span>
              </li>
            </ul>
          </div>

          <button class="modal-action-btn" @click="closePromoModal">Tutup</button>
        </div>
      </div>
    </div>

    <!-- Destination Modal -->
    <div v-if="showDestinationModal" class="modal-overlay" @click="closeDestinationModal">
      <div class="modal-content" @click.stop>
        <button class="modal-close" @click="closeDestinationModal">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>
        
        <div v-if="selectedDestination" class="modal-body">
          <div class="modal-image-container">
            <img :src="selectedDestination.image" :alt="selectedDestination.name" class="modal-image">
          </div>
          
          <div class="modal-header">
            <h2 class="modal-title">{{ selectedDestination.name }}</h2>
            <p class="modal-label">{{ selectedDestination.subtitle }}</p>
          </div>

          <div class="modal-section">
            <h3 class="section-heading">Tentang Destinasi</h3>
            <p class="section-text">{{ selectedDestination.description }}</p>
          </div>

          <div class="modal-section">
            <h3 class="section-heading">Daya Tarik Utama</h3>
            <ul class="terms-list">
              <li v-for="(highlight, index) in selectedDestination.highlights" :key="index">
                <span class="term-number">✓</span>
                <span class="term-text">{{ highlight }}</span>
              </li>
            </ul>
          </div>

          <div class="modal-info-grid">
            <div class="modal-info-card">
              <h4 class="info-label">Waktu Terbaik Berkunjung</h4>
              <p class="info-value">{{ selectedDestination.bestTime }}</p>
            </div>
            <div class="modal-info-card">
              <h4 class="info-label">Stasiun Terdekat</h4>
              <p class="info-value">{{ selectedDestination.transport }}</p>
            </div>
          </div>

          <button class="modal-action-btn" @click="closeDestinationModal">Tutup</button>
        </div>
      </div>
    </div>

    <!-- Search Results Modal -->
    <div v-if="showSearchModal" class="modal-overlay" @click="closeSearchModal">
      <div class="modal-content search-modal" @click.stop>
        <button class="modal-close" @click="closeSearchModal">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>
        
        <div class="search-modal-header">
          <h2>Hasil Pencarian Kereta</h2>
          <p class="search-info">
            {{ fromStation }} → {{ toStation }} | {{ formattedDate }}
          </p>
        </div>

        <div v-if="searchResults.length > 0" class="trains-list">
          <div v-for="train in searchResults" :key="train.train_id" class="train-card">
            <div class="train-header">
              <div class="train-name">
                <h3>{{ train.train_name }}</h3>
                <span class="train-number">{{ train.train_code }}</span>
              </div>
            </div>

            <div class="train-timing">
              <div class="time-block">
                <div class="time">{{ train.departure }}</div>
                <div class="label">Keberangkatan</div>
              </div>
              <div class="duration">
                <div class="line"></div>
                <div class="duration-text">{{ train.duration }}</div>
              </div>
              <div class="time-block">
                <div class="time">{{ train.arrival }}</div>
                <div class="label">Kedatangan</div>
              </div>
            </div>

            <div class="train-classes" v-if="train.classes && train.classes.length > 0">
              <span v-for="cls in train.classes" :key="cls.type" class="class-badge">
                {{ cls.name }} ({{ cls.available }})
              </span>
            </div>

            <div class="train-details">
              <div class="detail-item">
                <span class="label">Kursi Tersedia</span>
                <span class="value">{{ train.seats_available }} kursi</span>
              </div>
              <div class="detail-item">
                <span class="label">Harga</span>
                <span class="value price">{{ formatPrice(train.min_price) }} - {{ formatPrice(train.max_price) }}</span>
              </div>
            </div>

            <button class="train-action-btn" @click="handleBookTrain(train)">Pesan Sekarang</button>
          </div>
        </div>

        <div v-else class="no-results">
          <p>Tidak ada kereta tersedia untuk rute dan tanggal yang dipilih.</p>
        </div>

        <button class="modal-action-btn" @click="closeSearchModal">Tutup</button>
      </div>
    </div>

    <!-- Notification Modal -->
    <div v-if="showNotificationModal" class="modal-overlay" @click="closeNotificationModal">
      <div class="modal-content notification-modal" @click.stop>
        <button class="modal-close" @click="closeNotificationModal">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>
        <div class="notification-body">
          <div :class="['notification-icon', notificationType]">
            <svg v-if="notificationType === 'info'" width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
              <path d="M12 16V12M12 8H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <svg v-else-if="notificationType === 'warning'" width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2L2 20H22L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M12 9V13M12 17H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <svg v-else-if="notificationType === 'success'" width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
              <path d="M8 12L11 15L16 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <svg v-else width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
              <path d="M12 8V12M12 16H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <p class="notification-message">{{ notificationMessage }}</p>
          <button class="notification-btn" @click="closeNotificationModal">Oke</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.homepage {
  width: 100%;
  min-height: 100vh;
  background: var(--color-bg-light);
}

/* Hero */
.hero {
  position: relative;
  padding-top: 0;
  min-height: 600px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.hero-bg {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 420px;
  overflow: hidden;
}

.hero-gradient {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary) 50%, var(--color-secondary) 100%);
}

.hero-pattern {
  position: absolute;
  inset: 0;
  background-image: 
    radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(9, 216, 227, 0.15) 0%, transparent 40%),
    radial-gradient(circle at 40% 40%, rgba(255,255,255,0.05) 0%, transparent 60%);
}

.hero-content {
  position: relative;
  z-index: 1;
  text-align: center;
  padding: 3rem 1.5rem 2rem;
  max-width: 700px;
}

.hero-title {
  font-size: 3rem;
  font-weight: 700;
  color: var(--color-white);
  margin-bottom: 1rem;
  font-style: italic;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.hero-subtitle {
  font-size: 1.125rem;
  color: rgba(255, 255, 255, 0.9);
  line-height: 1.7;
  max-width: 600px;
  margin: 0 auto;
}

/* Booking Card */
.booking-card {
  position: relative;
  z-index: 10;
  background: var(--color-white);
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1), 0 2px 10px rgba(0, 0, 0, 0.05);
  margin: 0 1.5rem;
  max-width: 900px;
  width: calc(100% - 3rem);
  overflow: hidden;
}

.booking-tabs {
  display: flex;
  border-bottom: 1px solid #e5e7eb;
  overflow-x: auto;
}

.tab-btn {
  flex: 1;
  min-width: 100px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.375rem;
  padding: 1rem 0.75rem;
  background: transparent;
  border: none;
  border-bottom: 3px solid transparent;
  color: var(--color-text-light);
}

.tab-btn:hover {
  color: var(--color-primary);
  background: rgba(22, 117, 231, 0.03);
}

.tab-btn.active {
  color: var(--color-primary);
  border-bottom-color: var(--color-primary);
}

.tab-icon {
  display: flex;
  align-items: center;
  justify-content: center;
}

.tab-label {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.02em;
}

.booking-form {
  display: flex;
  align-items: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  flex-wrap: wrap;
}

.form-group {
  flex: 1;
  min-width: 180px;
}

.date-group {
  flex: 0.8;
  min-width: 160px;
}

.form-label {
  display: block;
  font-size: 0.6875rem;
  font-weight: 600;
  color: var(--color-text-light);
  letter-spacing: 0.05em;
  margin-bottom: 0.5rem;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 0.875rem;
  color: var(--color-text-light);
  display: flex;
  align-items: center;
  justify-content: center;
}

.input-icon.from {
  color: var(--color-primary);
}

.input-icon.to {
  color: var(--color-secondary);
}

.form-input {
  width: 100%;
  padding: 0.875rem 0.875rem 0.875rem 2.75rem;
  font-size: 0.9375rem;
  font-weight: 500;
  color: var(--color-text-dark);
  background: var(--color-bg-light);
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
}

.form-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(22, 117, 231, 0.1);
}

.form-input::placeholder {
  color: #9ca3af;
}

.date-input {
  cursor: pointer;
}

.swap-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: var(--color-bg-light);
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  color: var(--color-text-light);
  flex-shrink: 0;
  margin-bottom: 0.25rem;
}

.swap-btn:hover {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: var(--color-white);
  transform: rotate(180deg);
}

.search-btn {
  padding: 0.875rem 2.5rem;
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-white);
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  border: none;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(22, 117, 231, 0.3);
  flex-shrink: 0;
}

.search-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(22, 117, 231, 0.4);
}

.search-btn:active {
  transform: translateY(0);
}

.tracking-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 2rem;
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-primary);
  background: linear-gradient(135deg, rgba(22, 117, 231, 0.1) 0%, rgba(34, 197, 94, 0.1) 100%);
  border: 2px solid var(--color-primary);
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(22, 117, 231, 0.15);
  flex-shrink: 0;
  cursor: pointer;
  transition: all 0.3s ease;
}

.tracking-btn:hover {
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  color: var(--color-white);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(22, 117, 231, 0.3);
}

.tracking-btn:active {
  transform: translateY(0);
}

/* Features */
.features {
  padding: 6rem 1.5rem;
  background: var(--color-white);
  margin-top: 3rem;
}

.features-container {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
}

.feature-card {
  text-align: center;
  padding: 2rem 1.5rem;
  border-radius: 16px;
}

.feature-card:hover {
  background: var(--color-bg-light);
  transform: translateY(-4px);
}

.feature-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, rgba(22, 117, 231, 0.1) 0%, rgba(9, 216, 227, 0.1) 100%);
  border-radius: 16px;
  color: var(--color-primary);
  margin-bottom: 1.25rem;
}

.feature-card h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 0.75rem;
}

.feature-card p {
  font-size: 0.9375rem;
  color: var(--color-text-light);
  line-height: 1.6;
}

/* Promo Section */
.promo {
  padding: 5rem 1.5rem;
  background: var(--color-white);
}

.promo-container {
  max-width: 1200px;
  margin: 0 auto;
}

.section-header {
  margin-bottom: 3rem;
  text-align: left;
}

.section-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin-bottom: 0.75rem;
  letter-spacing: -0.5px;
}

.section-subtitle {
  font-size: 1rem;
  color: var(--color-text-light);
  line-height: 1.6;
  max-width: 600px;
}

.promo-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
}

.promo-card {
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  animation: fadeInUp 0.6s ease-out forwards;
  opacity: 0;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.promo-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 16px 32px rgba(0, 0, 0, 0.15);
}

.promo-badge {
  position: absolute;
  top: 1rem;
  left: 1rem;
  background: rgba(255, 255, 255, 0.95);
  color: var(--color-text-dark);
  padding: 0.5rem 1rem;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 700;
  z-index: 2;
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.badge-icon {
  font-size: 1.25rem;
}

.promo-image {
  position: relative;
  height: 160px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  text-align: center;
  padding: 1rem;
}

.promo-building,
.promo-hotel,
.promo-food,
.promo-travel {
  font-size: 1.125rem;
  line-height: 1.4;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.promo-content {
  padding: 1.5rem;
  background: white;
}

.promo-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin-bottom: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.promo-discount {
  font-size: 2rem;
  font-weight: 700;
  color: var(--color-primary);
  margin-bottom: 0.5rem;
}

.promo-desc {
  font-size: 0.875rem;
  color: var(--color-text-light);
  line-height: 1.4;
  margin-bottom: 1rem;
}

.promo-cta {
  display: inline-block;
  background: var(--color-primary);
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.promo-cta:hover {
  background: #0c5bb5;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

/* Destinations Section */
.destinations {
  padding: 5rem 1.5rem;
  background: var(--color-bg-light);
}

.destinations-container {
  max-width: 1200px;
  margin: 0 auto;
}

.destinations-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
}

.destination-card {
  height: 220px;
  border-radius: 16px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.75rem;
  font-weight: 700;
  cursor: pointer;
  position: relative;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
  border: none;
  padding: 0;
}

.destination-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.3);
  z-index: 1;
  transition: all 0.3s ease;
}

.destination-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.25);
}

.destination-card:hover .destination-overlay {
  background: rgba(0, 0, 0, 0.4);
}

.destination-card h3 {
  position: relative;
  z-index: 2;
  margin: 0;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.destination-subtitle {
  font-size: 0.875rem;
  font-weight: 500;
  margin-top: 0.5rem;
  opacity: 0.95;
  position: relative;
  z-index: 2;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
}

.destination-arrow {
  position: absolute;
  bottom: 1.5rem;
  font-size: 1.5rem;
  opacity: 0;
  transition: all 0.3s ease;
  z-index: 2;
}

.destination-card:hover .destination-arrow {
  opacity: 1;
  transform: translateX(4px);
}

.destination-card.jakarta {
  background: linear-gradient(135deg, rgba(22, 117, 231, 0.85), rgba(12, 157, 229, 0.85)), url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 250"><rect fill="%231675E7" width="400" height="250"/></svg>');
  background-size: cover;
  background-position: center;
}

.destination-card.bandung {
  background: linear-gradient(135deg, rgba(255, 100, 100, 0.85), rgba(220, 20, 60, 0.85)), url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 250"><rect fill="%23FF6464" width="400" height="250"/></svg>');
  background-size: cover;
  background-position: center;
}

.destination-card.yogya {
  background: linear-gradient(135deg, rgba(255, 165, 0, 0.85), rgba(255, 140, 0, 0.85)), url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 250"><rect fill="%23FFA500" width="400" height="250"/></svg>');
  background-size: cover;
  background-position: center;
}

.destination-card.surabaya {
  background: linear-gradient(135deg, rgba(52, 152, 219, 0.85), rgba(44, 62, 80, 0.85)), url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 250"><rect fill="%233498DB" width="400" height="250"/></svg>');
  background-size: cover;
  background-position: center;
}

/* Rewards Section */
.rewards {
  padding: 5rem 1.5rem;
  background: white;
}

.rewards-container {
  max-width: 1200px;
  margin: 0 auto;
}

.rewards-header {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 2rem;
}

.view-all-link {
  color: var(--color-primary);
  font-weight: 600;
  font-size: 0.95rem;
  text-decoration: none;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.view-all-link:hover {
  color: var(--color-primary-dark);
  gap: 0.75rem;
}

.rewards-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
}

.reward-card {
  padding: 2rem 1.5rem;
  border-radius: 16px;
  text-align: center;
  position: relative;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  animation: fadeInUp 0.6s ease-out forwards;
  opacity: 0;
}

.reward-card::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 200px;
  height: 200px;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 70%);
  border-radius: 50%;
}

.reward-top {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.reward-card.gold {
  background: linear-gradient(135deg, #FFE5B4 0%, #FFD699 100%);
}

.reward-card.platinum {
  background: linear-gradient(135deg, #E0E0E0 0%, #B0C4DE 100%);
}

.reward-card.silver {
  background: linear-gradient(135deg, #F0E68C 0%, #DAA520 100%);
}

.reward-card.bronze {
  background: linear-gradient(135deg, #CD7F32 0%, #8B4513 100%);
}

.reward-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.2);
}

.reward-badge {
  display: inline-block;
  background: rgba(0, 0, 0, 0.15);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 700;
  position: relative;
  z-index: 1;
}

.reward-amount {
  font-size: 1.875rem;
  font-weight: 700;
  color: rgba(0, 0, 0, 0.8);
  margin-bottom: 0.5rem;
  position: relative;
  z-index: 1;
}

.reward-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: rgba(0, 0, 0, 0.7);
  position: relative;
  z-index: 1;
  margin-bottom: 1rem;
}

.reward-btn {
  display: inline-block;
  background: rgba(0, 0, 0, 0.15);
  color: rgba(0, 0, 0, 0.8);
  padding: 0.625rem 1.25rem;
  border-radius: 50px;
  font-size: 0.8rem;
  font-weight: 600;
  text-decoration: none;
  position: relative;
  z-index: 1;
  transition: all 0.2s;
}

.reward-btn:hover {
  background: rgba(0, 0, 0, 0.25);
  transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 1024px) {
  .features-container {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .footer-container {
    grid-template-columns: 1fr;
    gap: 3rem;
  }
  
  .promo-grid,
  .destinations-grid,
  .rewards-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 2.25rem;
  }
  
  .hero-subtitle {
    font-size: 1rem;
  }
  
  .booking-form {
    flex-direction: column;
    align-items: stretch;
  }
  
  .form-group,
  .date-group {
    min-width: 100%;
  }
  
  .swap-btn {
    align-self: center;
    margin: 0.5rem 0;
    transform: rotate(90deg);
  }
  
  .swap-btn:hover {
    transform: rotate(270deg);
  }
  
  .search-btn {
    width: 100%;
    margin-top: 0.5rem;
  }
  
  .features-container {
    grid-template-columns: 1fr;
  }
  
  .footer-links {
    grid-template-columns: repeat(2, 1fr);
  }

  .promo {
    padding: 3rem 1.5rem;
  }

  .section-title {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .destination-card {
    height: 160px;
    font-size: 1.25rem;
  }

  .destination-subtitle {
    font-size: 0.75rem;
  }

  .reward-card {
    padding: 1.25rem;
  }

  .reward-amount {
    font-size: 1.5rem;
  }

  .promo-grid,
  .destinations-grid,
  .rewards-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .header-container {
    padding: 0.75rem 1rem;
  }
  
  .nav-link {
    padding: 0.5rem;
    font-size: 0.875rem;
  }
  
  .nav-btn {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
  }
  
  .hero-content {
    padding: 3rem 1rem 1.5rem;
  }
  
  .hero-title {
    font-size: 1.875rem;
  }
  
  .booking-card {
    margin: 0 1rem;
    width: calc(100% - 2rem);
  }
  
  .booking-tabs {
    justify-content: flex-start;
  }
  
  .tab-btn {
    min-width: 70px;
    padding: 0.75rem 0.5rem;
  }
  
  .tab-label {
    font-size: 0.625rem;
  }
  
  .booking-form {
    padding: 1rem;
    flex-wrap: wrap;
  }

  .search-btn,
  .tracking-btn {
    padding: 0.75rem 1.25rem;
    font-size: 0.9rem;
  }

  .tracking-btn {
    min-width: 100px;
  }

  .footer-links {
    grid-template-columns: 1fr;
  }

  .promo {
    padding: 2rem 1rem;
  }

  .section-title {
    font-size: 1.25rem;
    margin-bottom: 1rem;
  }

  .promo-image {
    height: 120px;
  }

  .promo-building,
  .promo-hotel,
  .promo-food,
  .promo-travel {
    font-size: 0.85rem;
  }

  .promo-content {
    padding: 0.75rem;
  }

  .promo-discount {
    font-size: 1.25rem;
  }

  .promo-desc {
    font-size: 0.7rem;
  }

  .destinations {
    padding: 2rem 1rem;
  }

  .destinations-grid {
    gap: 1rem;
  }

  .destination-card {
    height: 150px;
    font-size: 1.25rem;
  }

  .rewards {
    padding: 2rem 1rem;
  }

  .rewards-grid {
    gap: 1rem;
  }

  .reward-card {
    padding: 1rem;
  }

  .reward-amount {
    font-size: 1.5rem;
  }

  .reward-label {
    font-size: 0.75rem;
  }
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
  animation: fadeIn 0.3s ease;
  overflow: hidden;
}

/* Hide scrollbar when modal is open */
.modal-overlay::-webkit-scrollbar {
  display: none;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.modal-content {
  background: white;
  border-radius: 16px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  animation: slideUp 0.3s ease;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

/* Hide scrollbar in modal content */
.modal-content::-webkit-scrollbar {
  width: 0;
}

.modal-content {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-close {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  width: 40px;
  height: 40px;
  border: none;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-dark);
  transition: all 0.2s;
  z-index: 10;
}

.modal-close:hover {
  background: var(--color-bg-light);
  border-radius: 50%;
}

.modal-body {
  padding: 0;
  display: flex;
  flex-direction: column;
}

.modal-image-container {
  width: 100%;
  height: 280px;
  overflow: hidden;
  border-radius: 16px 16px 0 0;
  background: var(--color-bg-light);
}

.modal-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
}

.modal-header {
  text-align: center;
  margin-bottom: 1.5rem;
  padding: 2rem 2rem 0;
}

.modal-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin: 0 0 0.5rem;
}

.modal-label {
  font-size: 0.875rem;
  color: var(--color-primary);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0;
}

.modal-discount-section {
  background: linear-gradient(135deg, var(--color-primary) 0%, #0c5bb5 100%);
  color: white;
  padding: 2rem;
  border-radius: 0;
  text-align: center;
  margin-bottom: 2rem;
  margin-left: 0;
  margin-right: 0;
}

.discount-value {
  font-size: 2.5rem;
  font-weight: 700;
}

.modal-section {
  margin-bottom: 2rem;
  padding: 0 2rem;
}

.section-heading {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin: 0 0 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid var(--color-bg-light);
}

.section-text {
  font-size: 0.95rem;
  color: var(--color-text-light);
  line-height: 1.7;
  margin: 0;
}

.duration-text {
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-primary);
  margin: 0;
  background: var(--color-bg-light);
  padding: 1rem;
  border-radius: 8px;
}

.terms-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.terms-list li {
  font-size: 0.9rem;
  color: var(--color-text-light);
  line-height: 1.6;
  display: flex;
  gap: 0.75rem;
}

.term-number {
  color: var(--color-primary);
  font-weight: 600;
  flex-shrink: 0;
}

.term-text {
  text-align: justify;
}

.modal-action-btn {
  width: calc(100% - 4rem);
  padding: 1rem;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  margin: 1rem 2rem 2rem;
}

.modal-action-btn:hover {
  background: #0c5bb5;
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(22, 117, 231, 0.3);
}

.modal-info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.modal-info-card {
  background: var(--color-bg-light);
  padding: 1rem;
  border-radius: 12px;
  text-align: center;
}

.info-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text-light);
  margin: 0 0 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.info-value {
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-primary);
  margin: 0;
}

/* Responsive Modal */
@media (max-width: 768px) {
  .modal-content {
    max-height: 95vh;
  }

  .modal-info-grid {
    grid-template-columns: 1fr;
  }

  .modal-body {
    padding: 1.75rem 1.5rem 1.5rem;
  }

  .modal-title {
    font-size: 1.5rem;
  }

  .modal-discount-section {
    padding: 1.5rem;
  }

  .discount-value {
    font-size: 2rem;
  }

  .section-heading {
    font-size: 1rem;
  }

  .section-text,
  .duration-text {
    font-size: 0.9rem;
  }
}

/* Loading & Alert Styles */
.loading-indicator {
  padding: 2rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  color: var(--color-text-light);
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid var(--color-primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-alert,
.success-alert {
  margin-top: 1rem;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  font-size: 0.95rem;
}

.error-alert {
  background-color: #fee;
  color: #c33;
  border-left: 4px solid #c33;
}

.success-alert {
  background-color: #efe;
  color: #3c3;
  border-left: 4px solid #3c3;
}

.error-alert p,
.success-alert p {
  margin: 0;
}

/* Notification Modal Styles */
.notification-modal {
  max-width: 400px;
  text-align: center;
  padding: 0;
}

.notification-body {
  padding: 3rem 2rem 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

.notification-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  color: white;
}

.notification-icon.info {
  background: linear-gradient(135deg, #1675E7 0%, #0C9DE7 100%);
}

.notification-icon.warning {
  background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%);
}

.notification-icon.success {
  background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
}

.notification-icon.error {
  background: linear-gradient(135deg, #FF6464 0%, #DC143C 100%);
}

.notification-message {
  font-size: 1rem;
  color: var(--color-text-dark);
  margin: 0;
  line-height: 1.6;
}

.notification-btn {
  padding: 0.75rem 2.5rem;
  background: linear-gradient(135deg, #8B6914 0%, #A0826D 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  margin-top: 0.5rem;
}

.notification-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(139, 105, 20, 0.3);
}

/* Search Modal Styles */
.search-modal {
  max-width: 700px;
  max-height: 85vh;
  overflow-y: auto;
}

.search-modal-header {
  padding: 2rem;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  color: white;
  text-align: center;
  border-radius: 16px 16px 0 0;
}

.search-modal-header h2 {
  font-size: 1.5rem;
  margin: 0 0 0.5rem 0;
}

.search-info {
  font-size: 0.95rem;
  opacity: 0.9;
  margin: 0;
}

.trains-list {
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.train-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem;
  background: var(--color-bg-light);
  transition: all 0.3s;
  margin: 1rem 2rem;
}

.train-card:hover {
  border-color: var(--color-primary);
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.1);
  transform: translateY(-2px);
}

.train-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.train-name {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.train-name h3 {
  font-size: 1.125rem;
  margin: 0;
  color: var(--color-text-dark);
}

.train-number {
  font-size: 0.875rem;
  color: var(--color-text-light);
  margin-top: 0.25rem;
  display: block;
}

.train-classes {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.class-badge {
  display: inline-block;
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: white;
  padding: 0.375rem 0.75rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 500;
  white-space: nowrap;
}

.train-timing {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
}

.time-block {
  text-align: center;
  flex: 1;
}

.time {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-text-dark);
}

.time-block .label {
  font-size: 0.75rem;
  color: var(--color-text-light);
  margin-top: 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.duration {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  gap: 0.5rem;
}

.duration .line {
  width: 100%;
  height: 2px;
  background: var(--color-primary);
  flex: 1;
}

.duration-text {
  font-size: 0.875rem !important;
  background: transparent !important;
  padding: 0 !important;
  color: var(--color-text-light);
}

.train-details {
  display: flex;
  gap: 2rem;
  margin-bottom: 1rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  flex: 1;
}

.detail-item .label {
  font-size: 0.75rem;
  color: var(--color-text-light);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.detail-item .value {
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-text-dark);
}

.detail-item .value.price {
  color: var(--color-primary);
  font-size: 1.125rem;
}

.train-action-btn {
  width: 100%;
  padding: 0.875rem;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.train-action-btn:hover {
  background: #0c5bb5;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

.no-results {
  padding: 3rem 2rem;
  text-align: center;
  color: var(--color-text-light);
}

/* Tracking Modal */
.tracking-modal {
  max-width: 420px;
}

.tracking-modal-body {
  padding: 2rem 1.5rem;
}

.tracking-modal-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  color: var(--color-primary);
}

.tracking-modal-header h2 {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
  color: var(--color-text-dark);
}

.tracking-description {
  font-size: 0.95rem;
  color: var(--color-text-light);
  margin-bottom: 2rem;
  line-height: 1.6;
}

.tracking-form {
  margin-bottom: 2rem;
}

.tracking-input {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.tracking-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(22, 117, 231, 0.1);
}

.tracking-error {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: #fee;
  color: #c33;
  border-radius: 6px;
  font-size: 0.9rem;
  margin-top: 0.75rem;
}

.tracking-error svg {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
}

.tracking-modal-actions {
  display: flex;
  gap: 1rem;
}

.btn-cancel,
.btn-track {
  flex: 1;
  padding: 0.875rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancel {
  background: #f0f0f0;
  color: var(--color-text-dark);
}

.btn-cancel:hover {
  background: #e5e5e5;
}

.btn-track {
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

.btn-track:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(22, 117, 231, 0.4);
}

.btn-track:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--color-text-dark);
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.modal-close:hover {
  background: #f0f0f0;
}


.no-results p {
  font-size: 1rem;
  margin: 0;
}

.search-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.search-btn:disabled:hover {
  transform: none;
  box-shadow: 0 4px 15px rgba(22, 117, 231, 0.3);
}

/* Hide scrollbar globally when modal is shown */
:global(body.modal-open) {
  overflow: hidden;
  scrollbar-width: none;
}

:global(body.modal-open::-webkit-scrollbar) {
  display: none;
}
</style>

