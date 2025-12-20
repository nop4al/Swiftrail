<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Login State
const isLoggedIn = ref(false)
const adminEmail = ref('')
const adminPassword = ref('')
const rememberMe = ref(false)
const isLoading = ref(false)
const showPassword = ref(false)

// Admin Dashboard State
const adminName = ref('Admin SwiftRail')
const adminRole = ref('Administrator')
const activeMenu = ref('dashboard')
const activeTab = ref('refund-list')

// Chart references
const revenueChartCanvas = ref(null)
const ticketChartCanvas = ref(null)
const userChartCanvas = ref(null)

// Refund Management State
const refunds = ref([])

const filterRefundStatus = ref('all')
const newRefundForm = ref({
  orderId: '',
  amount: '',
  reason: '',
  user: ''
})

const filteredRefunds = computed(() => {
  if (filterRefundStatus.value === 'all') {
    return refunds.value
  }
  return refunds.value.filter(r => r.status === filterRefundStatus.value)
})

// Master Data State
const masterDataType = ref('trains')
const stations = ref([])

const trainTypes = ref([])

const routes = ref([])

// Kereta dengan jadwal
const trains = ref([])

const showNewStationForm = ref(false)
const newStation = ref({ code: '', name: '', city: '', active: true })
const showNewTrainTypeForm = ref(false)
const newTrainType = ref({ code: '', name: '', capacity: '', active: true })

const showNewScheduleForm = ref(false)
const newSchedule = ref({ 
  trainCode: '', 
  routeCode: '', 
  departureTime: '', 
  arrivalTime: '', 
  trainType: '', 
  status: 'active' 
})

const showNewTrainForm = ref(false)
const newTrain = ref({
  code: '',
  name: '',
  type: '',
  capacity: '',
  status: 'active'
})
const isLoadingTrain = ref(false)
const trainMessage = ref({ type: '', text: '' })

const expandedTrainId = ref(null)
const showAddScheduleToTrain = ref(null)
const newScheduleForTrain = ref({
  route: '',
  departureTime: '',
  arrivalTime: '',
  days: 'Setiap Hari'
})

const showNewRouteForm = ref(false)
const newRoute = ref({
  code: '',
  origin: '',
  destination: '',
  stops: '',
  departureTime: '',
  estimatedDuration: '',
  distance: '',
  status: 'active'
})
const routeMessage = ref({ type: '', text: '' })
const isLoadingRoute = ref(false)

const handleAddRoute = async () => {
  if (!newRoute.value.code.trim()) {
    routeMessage.value = { type: 'error', text: 'Kode Rute tidak boleh kosong' }
    return
  }
  if (!newRoute.value.origin.trim()) {
    routeMessage.value = { type: 'error', text: 'Stasiun awal tidak boleh kosong' }
    return
  }
  if (!newRoute.value.destination.trim()) {
    routeMessage.value = { type: 'error', text: 'Stasiun tujuan tidak boleh kosong' }
    return
  }
  if (!newRoute.value.departureTime) {
    routeMessage.value = { type: 'error', text: 'Jam keberangkatan tidak boleh kosong' }
    return
  }
  if (!newRoute.value.estimatedDuration || parseInt(newRoute.value.estimatedDuration) <= 0) {
    routeMessage.value = { type: 'error', text: 'Durasi perjalanan harus lebih dari 0' }
    return
  }
  if (routes.value.some(r => r.code === newRoute.value.code)) {
    routeMessage.value = { type: 'error', text: 'Kode Rute sudah ada' }
    return
  }

  isLoadingRoute.value = true
  routeMessage.value = { type: '', text: '' }

  try {
    const id = Math.max(...routes.value.map(r => r.id), 0) + 1
    routes.value.push({
      id,
      code: newRoute.value.code.trim(),
      origin: newRoute.value.origin.trim(),
      destination: newRoute.value.destination.trim(),
      stops: newRoute.value.stops.trim().split(',').filter(s => s.trim()),
      departureTime: newRoute.value.departureTime,
      estimatedDuration: parseInt(newRoute.value.estimatedDuration),
      distance: newRoute.value.distance.trim(),
      status: newRoute.value.status,
      createdAt: new Date().toISOString()
    })

    routeMessage.value = { type: 'success', text: 'Rute berhasil ditambahkan' }
    showNewRouteForm.value = false
    newRoute.value = { code: '', origin: '', destination: '', stops: '', departureTime: '', estimatedDuration: '', distance: '', status: 'active' }

    setTimeout(() => {
      routeMessage.value = { type: '', text: '' }
    }, 3000)
  } catch (error) {
    routeMessage.value = { type: 'error', text: 'Gagal menambahkan rute' }
  } finally {
    isLoadingRoute.value = false
  }
}

const handleDeleteRoute = (id) => {
  if (!confirm('Yakin ingin menghapus rute ini?')) return
  const index = routes.value.findIndex(r => r.id === id)
  if (index !== -1) {
    routes.value.splice(index, 1)
    routeMessage.value = { type: 'success', text: 'Rute berhasil dihapus' }
    setTimeout(() => {
      routeMessage.value = { type: '', text: '' }
    }, 3000)
  }
}

const handleAddRefund = () => {
  if (!newRefundForm.value.orderId || !newRefundForm.value.amount) {
    alert('Silakan isi semua field')
    return
  }
  const newRefund = {
    id: 'REF' + String(refunds.value.length + 1).padStart(3, '0'),
    ...newRefundForm.value,
    status: 'pending',
    date: new Date().toISOString().split('T')[0]
  }
  refunds.value.unshift(newRefund)
  newRefundForm.value = { orderId: '', amount: '', reason: '', user: '' }
}

const handleUpdateRefundStatus = (refund, newStatus) => {
  const index = refunds.value.findIndex(r => r.id === refund.id)
  if (index !== -1) {
    refunds.value[index].status = newStatus
  }
}

const handleAddStation = () => {
  if (!newStation.value.code || !newStation.value.name || !newStation.value.city) {
    alert('Silakan isi semua field')
    return
  }
  const id = Math.max(...stations.value.map(s => s.id), 0) + 1
  stations.value.push({ id, ...newStation.value })
  showNewStationForm.value = false
  newStation.value = { code: '', name: '', city: '', active: true }
}

const handleDeleteStation = (id) => {
  const index = stations.value.findIndex(s => s.id === id)
  if (index !== -1) {
    stations.value.splice(index, 1)
  }
}

const handleAddTrainType = () => {
  if (!newTrainType.value.code || !newTrainType.value.name || !newTrainType.value.capacity) {
    alert('Silakan isi semua field')
    return
  }
  const id = Math.max(...trainTypes.value.map(t => t.id), 0) + 1
  trainTypes.value.push({ id, ...newTrainType.value })
  showNewTrainTypeForm.value = false
  newTrainType.value = { code: '', name: '', capacity: '', active: true }
}

const handleDeleteTrainType = (id) => {
  const index = trainTypes.value.findIndex(t => t.id === id)
  if (index !== -1) {
    trainTypes.value.splice(index, 1)
  }
}

const handleAddSchedule = () => {
  if (!newSchedule.value.trainCode || !newSchedule.value.routeCode || !newSchedule.value.departureTime || !newSchedule.value.arrivalTime) {
    alert('Silakan isi semua field')
    return
  }
  const id = Math.max(...schedules.value.map(s => s.id), 0) + 1
  schedules.value.push({ id, ...newSchedule.value })
  showNewScheduleForm.value = false
  newSchedule.value = { trainCode: '', routeCode: '', departureTime: '', arrivalTime: '', trainType: '', status: 'active' }
}

const handleDeleteSchedule = (id) => {
  const index = schedules.value.findIndex(s => s.id === id)
  if (index !== -1) {
    schedules.value.splice(index, 1)
  }
}

const handleAddTrain = async () => {
  // Validation
  if (!newTrain.value.code.trim()) {
    trainMessage.value = { type: 'error', text: 'Kode Kereta tidak boleh kosong' }
    return
  }
  if (!newTrain.value.name.trim()) {
    trainMessage.value = { type: 'error', text: 'Nama Kereta tidak boleh kosong' }
    return
  }
  if (!newTrain.value.type.trim()) {
    trainMessage.value = { type: 'error', text: 'Tipe Kereta tidak boleh kosong' }
    return
  }
  if (!newTrain.value.capacity || parseInt(newTrain.value.capacity) <= 0) {
    trainMessage.value = { type: 'error', text: 'Kapasitas harus lebih dari 0' }
    return
  }
  
  // Check duplicate code
  if (trains.value.some(t => t.code === newTrain.value.code)) {
    trainMessage.value = { type: 'error', text: 'Kode Kereta sudah ada' }
    return
  }

  isLoadingTrain.value = true
  trainMessage.value = { type: '', text: '' }
  
  try {
    const id = Math.max(...trains.value.map(t => t.id), 0) + 1
    trains.value.push({
      id,
      code: newTrain.value.code.trim(),
      name: newTrain.value.name.trim(),
      type: newTrain.value.type.trim(),
      capacity: parseInt(newTrain.value.capacity),
      status: newTrain.value.status,
      schedules: [],
      createdAt: new Date().toISOString()
    })
    
    trainMessage.value = { type: 'success', text: 'Kereta berhasil ditambahkan' }
    showNewTrainForm.value = false
    newTrain.value = { code: '', name: '', type: '', capacity: '', status: 'active' }
    
    // Clear message after 3 seconds
    setTimeout(() => {
      trainMessage.value = { type: '', text: '' }
    }, 3000)
  } catch (error) {
    trainMessage.value = { type: 'error', text: 'Gagal menambahkan kereta' }
  } finally {
    isLoadingTrain.value = false
  }
}

const handleDeleteTrain = (id) => {
  if (!confirm('Yakin ingin menghapus kereta ini?')) return
  const index = trains.value.findIndex(t => t.id === id)
  if (index !== -1) {
    trains.value.splice(index, 1)
    trainMessage.value = { type: 'success', text: 'Kereta berhasil dihapus' }
    setTimeout(() => {
      trainMessage.value = { type: '', text: '' }
    }, 3000)
  }
}

const handleAddScheduleToTrain = (trainId) => {
  if (!newScheduleForTrain.value.route || !newScheduleForTrain.value.departureTime || !newScheduleForTrain.value.arrivalTime) {
    alert('Silakan isi semua field jadwal')
    return
  }
  const train = trains.value.find(t => t.id === trainId)
  if (train) {
    const scheduleId = Math.max(...train.schedules.map(s => s.id), 0) + 1
    train.schedules.push({
      id: scheduleId,
      ...newScheduleForTrain.value
    })
    showAddScheduleToTrain.value = null
    newScheduleForTrain.value = { route: '', departureTime: '', arrivalTime: '', days: 'Setiap Hari' }
  }
}

const handleDeleteTrainSchedule = (trainId, scheduleId) => {
  const train = trains.value.find(t => t.id === trainId)
  if (train) {
    const index = train.schedules.findIndex(s => s.id === scheduleId)
    if (index !== -1) {
      train.schedules.splice(index, 1)
    }
  }
}

const handleAdminLogin = async () => {
  if (!adminEmail.value || !adminPassword.value) {
    alert('Silakan isi semua field')
    return
  }

  isLoading.value = true
  try {
    const response = await fetch('/api/v1/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        email: adminEmail.value,
        password: adminPassword.value,
      })
    })

    const data = await response.json()
    
    if (!response.ok) {
      alert(data.message || 'Login gagal')
      return
    }

    // Check if user is admin
    if (data.data?.user?.role !== 'admin') {
      alert('Anda tidak memiliki akses admin')
      return
    }

    // Simpan API token dan user profile
    if (data.data?.access_token) {
      localStorage.setItem('auth_token', data.data.access_token)
    }
    if (data.data?.user) {
      localStorage.setItem('userProfile', JSON.stringify(data.data.user))
      adminName.value = `${data.data.user.first_name} ${data.data.user.last_name}`
    }
    
    isLoggedIn.value = true
  } catch (error) {
    console.error('Admin login error:', error)
    alert('Terjadi kesalahan. Silakan coba lagi.')
  } finally {
    isLoading.value = false
  }
}

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

const handleLogout = () => {
  isLoggedIn.value = false
  adminEmail.value = ''
  adminPassword.value = ''
  localStorage.removeItem('auth_token')
  localStorage.removeItem('userProfile')
  // Trigger event untuk update header dan component lain
  window.dispatchEvent(new Event('auth-changed'))
  router.push('/login')
}

// Initialize charts
const initCharts = () => {
  // Pastikan Chart.js sudah loaded
  if (typeof Chart === 'undefined') {
    console.error('Chart.js tidak loaded')
    return
  }

  // Revenue Chart (Line Chart)
  if (revenueChartCanvas.value) {
    const revenueCtx = revenueChartCanvas.value.getContext('2d')
    new Chart(revenueCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [{
          label: 'Revenue (Juta Rp)',
          data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
          borderColor: '#1675E7',
          backgroundColor: 'rgba(22, 117, 231, 0.1)',
          borderWidth: 3,
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#1675E7',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointRadius: 5,
          pointHoverRadius: 7
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: true,
            labels: {
              font: { size: 12, weight: 'bold' },
              color: '#333'
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { color: '#666', font: { size: 11 } },
            grid: { color: '#e5e7eb' }
          },
          x: {
            ticks: { color: '#666', font: { size: 11 } },
            grid: { color: '#e5e7eb' }
          }
        }
      }
    })
  }

  // Ticket Sales Chart (Bar Chart)
  if (ticketChartCanvas.value) {
    const ticketCtx = ticketChartCanvas.value.getContext('2d')
    new Chart(ticketCtx, {
      type: 'bar',
      data: {
        labels: ['KRL', 'LRT', 'Kereta Jarak Jauh', 'Bandara', 'Lokal'],
        datasets: [{
          label: 'Tiket Terjual',
          data: [0, 0, 0, 0, 0],
          backgroundColor: [
            '#1675E7',
            '#09D8E3',
            '#FF6B6B',
            '#FFA500',
            '#4ECDC4'
          ],
          borderRadius: 8,
          borderSkipped: false
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        indexAxis: 'y',
        plugins: {
          legend: {
            display: true,
            labels: {
              font: { size: 12, weight: 'bold' },
              color: '#333'
            }
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            ticks: { color: '#666', font: { size: 11 } },
            grid: { color: '#e5e7eb' }
          },
          y: {
            ticks: { color: '#666', font: { size: 11 } },
            grid: { display: false }
          }
        }
      }
    })
  }

  // User Growth Chart (Doughnut Chart)
  if (userChartCanvas.value) {
    const userCtx = userChartCanvas.value.getContext('2d')
    new Chart(userCtx, {
      type: 'doughnut',
      data: {
        labels: ['Regular', 'Silver Member', 'Gold Member', 'Platinum Member'],
        datasets: [{
          data: [0, 0, 0, 0],
          backgroundColor: [
            '#E5E7EB',
            '#FFD700',
            '#C0C0C0',
            '#D946A6'
          ],
          borderColor: '#fff',
          borderWidth: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              font: { size: 11 },
              color: '#333',
              padding: 15
            }
          }
        }
      }
    })
  }
}

// Check jika sudah login saat mount
onMounted(() => {
  const token = localStorage.getItem('auth_token')
  const userProfile = localStorage.getItem('userProfile')
  
  if (token && userProfile) {
    const user = JSON.parse(userProfile)
    if (user.role === 'admin') {
      isLoggedIn.value = true
      adminName.value = `${user.first_name} ${user.last_name}`
      // Load Chart.js script
      const script = document.createElement('script')
      script.src = 'https://cdn.jsdelivr.net/npm/chart.js'
      script.async = true
      script.onload = () => {
        setTimeout(initCharts, 100)
      }
      document.head.appendChild(script)
    } else {
      router.push('/login')
    }
  } else {
    router.push('/login')
  }
})
</script>

<template>
  <!-- Admin Login Page -->
  <div v-if="!isLoggedIn" class="admin-login-page">
    <div class="admin-login-container">
      <!-- Left Section - Branding -->
      <div class="admin-branding">
        <div class="branding-content">
          <div class="logo">
            <div class="logo-icon">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="24" height="24" rx="6" fill="#1675E7"/>
                <path d="M6 8C6 6.89543 6.89543 6 8 6H16C17.1046 6 18 6.89543 18 8V14C18 15.1046 17.1046 16 16 16H8C6.89543 16 6 15.1046 6 14V8Z" fill="white"/>
                <rect x="8" y="8" width="3" height="2" rx="0.5" fill="#1675E7"/>
                <rect x="13" y="8" width="3" height="2" rx="0.5" fill="#1675E7"/>
                <circle cx="9" cy="18" r="1.5" fill="white"/>
                <circle cx="15" cy="18" r="1.5" fill="white"/>
              </svg>
            </div>
            <div class="logo-text">
              <span class="logo-title">SwiftRail</span>
              <span class="logo-subtitle">ADMIN</span>
            </div>
          </div>
          
          <div class="branding-message">
            <h2>Panel Administrasi</h2>
            <p>Kelola sistem SwiftRail dengan mudah dan efisien.</p>
          </div>

          <div class="benefits">
            <div class="benefit-item">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
              </svg>
              <span>Manajemen Data Lengkap</span>
            </div>
            <div class="benefit-item">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2"/>
                <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <span>Akses Real-time</span>
            </div>
            <div class="benefit-item">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 1L15.09 8.26H23L17.55 12.25L19.64 19.52L12 15.5L4.36 19.52L6.45 12.25L1 8.26H8.91L12 1Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              </svg>
              <span>Kontrol Penuh Sistem</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Section - Form -->
      <div class="admin-form-section">
        <div class="form-wrapper">
          <h1>Masuk ke Panel Admin</h1>
          <p class="form-subtitle">Masukkan kredensial admin Anda</p>

          <form @submit.prevent="handleAdminLogin" class="admin-form">
            <div class="form-group">
              <label for="admin-email" class="form-label">Email Admin</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                  <path d="M2 6L12 13L22 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <input
                  id="admin-email"
                  v-model="adminEmail"
                  type="email"
                  class="form-input"
                  placeholder="Masukkan email admin"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="admin-password" class="form-label">Password</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect x="3" y="11" width="18" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
                  <path d="M7 11V7C7 4.23858 9.23858 2 12 2C14.7614 2 17 4.23858 17 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input
                  id="admin-password"
                  v-model="adminPassword"
                  :type="showPassword ? 'text' : 'password'"
                  class="form-input"
                  placeholder="Masukkan password admin"
                  required
                />
                <button
                  type="button"
                  class="toggle-password"
                  @click="togglePasswordVisibility"
                >
                  <svg v-if="!showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 5C7 5 2.73 8.11 1 12.46C2.73 16.89 7 20 12 20C17 20 21.27 16.89 23 12.46C21.27 8.11 17 5 12 5ZM12 17C9.33 17 7 14.67 7 12C7 9.33 9.33 7 12 7C14.67 7 17 9.33 17 12C17 14.67 14.67 17 12 17Z" fill="currentColor"/>
                    <circle cx="12" cy="12" r="2" fill="currentColor"/>
                  </svg>
                  <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11.83 9L15.64 12.81C15.87 12.35 16 11.83 16 11.29C16 9.91 15.09 8.75 13.71 8.75C13.17 8.75 12.65 8.88 12.19 9.11M7.53 9.8C6.58 10.45 5.77 11.27 5.14 12.29M7 17.05V17H7.07C7.25 17.34 7.44 17.67 7.65 17.99M19.07 4.57L18 3.5M9.9 5.02L8.81 3.93" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M1 1L23 23" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </button>
              </div>
            </div>

            <div class="form-options">
              <label class="checkbox">
                <input v-model="rememberMe" type="checkbox" />
                <span>Ingat saya</span>
              </label>
              <a href="#" class="forgot-password">Lupa password?</a>
            </div>

            <button type="submit" class="admin-login-btn" :disabled="isLoading">
              <span v-if="!isLoading">Masuk ke Admin</span>
              <span v-else>Memproses...</span>
            </button>
          </form>

          <div class="info-box">
            <p><strong>Admin Credentials:</strong></p>
            <p>Email: admin@swiftrail.my.id</p>
            <p>Password: SwiftRailGACOR</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Admin Dashboard Page -->
  <div v-else class="admin-dashboard">
    <!-- Header -->
    <header class="admin-header">
      <div class="header-container">
        <div class="logo">
          <div class="logo-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect width="24" height="24" rx="6" fill="#1675E7"/>
              <path d="M6 8C6 6.89543 6.89543 6 8 6H16C17.1046 6 18 6.89543 18 8V14C18 15.1046 17.1046 16 16 16H8C6.89543 16 6 15.1046 6 14V8Z" fill="white"/>
              <rect x="8" y="8" width="3" height="2" rx="0.5" fill="#1675E7"/>
              <rect x="13" y="8" width="3" height="2" rx="0.5" fill="#1675E7"/>
              <circle cx="9" cy="18" r="1.5" fill="white"/>
              <circle cx="15" cy="18" r="1.5" fill="white"/>
            </svg>
          </div>
          <div class="logo-text">
            <span class="logo-title">SwiftRail</span>
            <span class="logo-subtitle">ADMIN</span>
          </div>
        </div>
        
        <nav class="nav">
          <div class="user-profile-header">
            <div class="avatar-header">{{ adminName.charAt(0) }}</div>
            <div class="user-info">
              <p class="user-name-header">{{ adminName }}</p>
              <p class="user-role-header">{{ adminRole }}</p>
            </div>
          </div>
          <button class="logout-btn-header" @click="handleLogout">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Logout
          </button>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <div class="dashboard-content">
      <!-- Content Wrapper (Sidebar + Main Area) -->
      <div class="dashboard-wrapper">
        <!-- Sidebar Menu -->
        <aside class="sidebar">
        <nav class="sidebar-nav">
          <button 
            :class="['menu-item', { active: activeMenu === 'dashboard' }]"
            @click="activeMenu = 'dashboard'"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect x="3" y="3" width="8" height="8" rx="1" stroke="currentColor" stroke-width="1.5"/>
              <rect x="13" y="3" width="8" height="8" rx="1" stroke="currentColor" stroke-width="1.5"/>
              <rect x="3" y="13" width="8" height="8" rx="1" stroke="currentColor" stroke-width="1.5"/>
              <rect x="13" y="13" width="8" height="8" rx="1" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            Dashboard
          </button>
          
          <button 
            :class="['menu-item', { active: activeMenu === 'refund' }]"
            @click="activeMenu = 'refund'"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M3 9L5 21H19L21 9M7 13V19M12 13V19M17 13V19M1 9H23M6 5H18C19.1046 5 20 5.89543 20 7V9H4V7C4 5.89543 4.89543 5 6 5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Refund
          </button>

          <button 
            :class="['menu-item', { active: activeMenu === 'master-data' }]"
            @click="activeMenu = 'master-data'"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect x="3" y="3" width="18" height="3" rx="1" stroke="currentColor" stroke-width="1.5"/>
              <rect x="3" y="8" width="18" height="3" rx="1" stroke="currentColor" stroke-width="1.5"/>
              <rect x="3" y="13" width="18" height="3" rx="1" stroke="currentColor" stroke-width="1.5"/>
              <rect x="3" y="18" width="18" height="3" rx="1" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            Master Data
          </button>
        </nav>
      </aside>

      <!-- Main Area -->
      <div class="main-area">
        <!-- Dashboard Section -->
        <div v-if="activeMenu === 'dashboard'" class="content-section">
          <div class="welcome-section">
            <h1>Selamat Datang, {{ adminName }}!</h1>
            <p>Kelola sistem SwiftRail dari panel administrasi ini</p>
          </div>

          <!-- Dashboard Cards -->
          <div class="dashboard-grid">
            <div class="dashboard-card">
              <div class="card-header">
                <h3>Total Pengguna</h3>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/>
                  <path d="M6 20c0-3.3 2.69-6 6-6s6 2.7 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </div>
              <p class="card-value">0</p>
              <p class="card-subtitle">+0 bulan ini</p>
            </div>

            <div class="dashboard-card">
              <div class="card-header">
                <h3>Total Transaksi</h3>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect x="2" y="6" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
                  <path d="M2 10H22" stroke="currentColor" stroke-width="2"/>
                </svg>
              </div>
              <p class="card-value">0</p>
              <p class="card-subtitle">+0 hari ini</p>
            </div>

            <div class="dashboard-card">
              <div class="card-header">
                <h3>Revenue</h3>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12 1V23M17 4H9C7.89543 4 7 4.89543 7 6V18C7 19.1046 7.89543 20 9 20H15C16.1046 20 17 19.1046 17 18V6C17 4.89543 16.1046 4 15 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </div>
              <p class="card-value">Rp 0</p>
              <p class="card-subtitle">+0% bulan lalu</p>
            </div>
          </div>

          <!-- Charts Section -->
          <div class="charts-section">
            <div class="charts-grid">
          <!-- Revenue Chart -->
          <div class="chart-container">
            <h3>Revenue Trend</h3>
            <canvas ref="revenueChartCanvas"></canvas>
          </div>

          <!-- Ticket Sales Chart -->
          <div class="chart-container">
            <h3>Penjualan Tiket per Kategori</h3>
            <canvas ref="ticketChartCanvas"></canvas>
          </div>

          <!-- User Membership Chart -->
          <div class="chart-container full-width">
            <h3>Distribusi Member</h3>
            <div class="chart-wrapper">
              <canvas ref="userChartCanvas"></canvas>
            </div>
          </div>
            </div>
          </div>
        </div>

        <!-- Refund Section -->
        <div v-if="activeMenu === 'refund'" class="content-section">
          <div class="section-header">
            <h2>Manajemen Refund</h2>
          </div>

          <div class="refund-tabs">
            <button 
              v-for="status in ['all', 'pending', 'approved', 'processed', 'rejected']"
              :key="status"
              :class="['tab-btn', { active: filterRefundStatus === status }]"
              @click="filterRefundStatus = status"
            >
              {{ status === 'all' ? 'Semua' : status.charAt(0).toUpperCase() + status.slice(1) }}
            </button>
          </div>

          <div class="refund-content">
            <div class="refund-list">
              <div class="refund-table">
                <table>
                  <thead>
                    <tr>
                      <th>Refund ID</th>
                      <th>Order ID</th>
                      <th>User</th>
                      <th>Jumlah</th>
                      <th>Alasan</th>
                      <th>Status</th>
                      <th>Tanggal</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="refund in filteredRefunds" :key="refund.id">
                      <td><strong>{{ refund.id }}</strong></td>
                      <td>{{ refund.orderId }}</td>
                      <td>{{ refund.user }}</td>
                      <td>Rp {{ refund.amount.toLocaleString('id-ID') }}</td>
                      <td>{{ refund.reason }}</td>
                      <td>
                        <span :class="['status-badge', refund.status]">
                          {{ refund.status.charAt(0).toUpperCase() + refund.status.slice(1) }}
                        </span>
                      </td>
                      <td>{{ refund.date }}</td>
                      <td>
                        <div class="action-btns">
                          <button v-if="refund.status === 'pending'" class="btn-small approve" @click="handleUpdateRefundStatus(refund, 'approved')">Terima</button>
                          <button v-if="refund.status !== 'processed' && refund.status !== 'rejected'" class="btn-small reject" @click="handleUpdateRefundStatus(refund, 'rejected')">Tolak</button>
                          <button v-if="refund.status === 'approved'" class="btn-small process" @click="handleUpdateRefundStatus(refund, 'processed')">Proses</button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Master Data Section -->
        <div v-if="activeMenu === 'master-data'" class="content-section">
          <div class="section-header">
            <h2>Manajemen Master Data</h2>
          </div>

          <div class="master-tabs">
            <button 
              v-for="type in ['trains', 'routes']"
              :key="type"
              :class="['tab-btn', { active: masterDataType === type }]"
              @click="masterDataType = type"
            >
              {{ type === 'trains' ? 'Kereta' : 'Rute' }}
            </button>
          </div>

          <!-- Trains Management -->
          <div v-if="masterDataType === 'trains'" class="master-data-content">
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
              <button class="btn-primary" @click="showNewTrainForm = !showNewTrainForm">
                + Tambah Kereta Baru
              </button>
            </div>

            <!-- Add New Train Form -->
            <div v-if="showNewTrainForm" class="form-card">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3>Form Kereta Baru</h3>
                <button class="close-btn" @click="showNewTrainForm = false">×</button>
              </div>

              <!-- Message Alert -->
              <div v-if="trainMessage.text" :class="['message-alert', trainMessage.type]" style="margin-bottom: 1rem;">
                {{ trainMessage.text }}
              </div>

              <div class="form-grid">
                <div class="form-group">
                  <label>Kode Kereta <span style="color: #dc2626;">*</span></label>
                  <input 
                    v-model="newTrain.code" 
                    type="text" 
                    placeholder="Contoh: EXP-001" 
                    class="form-input"
                    @input="trainMessage.type = ''"
                    maxlength="20"
                  />
                  <small style="color: #6b7280;">Format: EXP-001, KRL-002</small>
                </div>
                <div class="form-group">
                  <label>Nama Kereta <span style="color: #dc2626;">*</span></label>
                  <input 
                    v-model="newTrain.name" 
                    type="text" 
                    placeholder="Argo Bromo Anggrek" 
                    class="form-input"
                    @input="trainMessage.type = ''"
                    maxlength="50"
                  />
                </div>
                <div class="form-group">
                  <label>Tipe Kereta <span style="color: #dc2626;">*</span></label>
                  <select v-model="newTrain.type" class="form-input" @change="trainMessage.type = ''">
                    <option value="">-- Pilih Tipe --</option>
                    <option value="Executive">Executive</option>
                    <option value="Business">Business</option>
                    <option value="Economy">Economy</option>
                    <option value="KRL">KRL</option>
                    <option value="LRT">LRT</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Kapasitas <span style="color: #dc2626;">*</span></label>
                  <input 
                    v-model="newTrain.capacity" 
                    type="number" 
                    placeholder="500" 
                    class="form-input"
                    @input="trainMessage.type = ''"
                    min="1"
                    max="9999"
                  />
                  <small style="color: #6b7280;">Penumpang maksimal</small>
                </div>
                <div class="form-group">
                  <label>Status</label>
                  <select v-model="newTrain.status" class="form-input">
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                    <option value="maintenance">Maintenance</option>
                  </select>
                </div>
              </div>
              <div class="form-actions">
                <button 
                  class="btn-success" 
                  @click="handleAddTrain"
                  :disabled="isLoadingTrain"
                >
                  <span v-if="!isLoadingTrain">Simpan</span>
                  <span v-else>Menyimpan...</span>
                </button>
                <button 
                  class="btn-cancel" 
                  @click="showNewTrainForm = false"
                  :disabled="isLoadingTrain"
                >
                  Batal
                </button>
              </div>
            </div>

            <!-- Trains List with Schedules -->
            <div class="trains-list">
              <div v-if="trains.length === 0" class="empty-state">
                <p>Belum ada kereta. Tambahkan kereta baru untuk memulai.</p>
              </div>

              <div v-for="train in trains" :key="train.id" class="train-card">
                <div class="train-header" @click="expandedTrainId = expandedTrainId === train.id ? null : train.id" style="cursor: pointer;">
                  <div class="train-info">
                    <h4>{{ train.code }} - {{ train.name }}</h4>
                    <p class="train-meta">
                      <span>Tipe: {{ train.type }}</span>
                      <span>Kapasitas: {{ train.capacity }}</span>
                      <span :class="['status', train.status]">{{ train.status }}</span>
                    </p>
                  </div>
                  <div class="expand-icon" :class="{ expanded: expandedTrainId === train.id }">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                  </div>
                </div>

                <div v-if="expandedTrainId === train.id" class="train-actions">
                  <button class="btn-delete" @click="handleDeleteTrain(train.id)">
                    Hapus Kereta
                  </button>
                </div>

                <!-- Schedules Section -->
                <div v-if="expandedTrainId === train.id" class="schedules-section">
                  <div style="margin-top: 1rem; border-top: 1px solid #e5e7eb; padding-top: 1rem;">
                    <h5 style="margin-bottom: 1rem; font-weight: 600;">Jadwal Kereta</h5>

                    <!-- Add Schedule Form -->
                    <div v-if="showAddScheduleToTrain && expandedTrainId === train.id" class="form-card" style="margin-bottom: 1rem;">
                      <h6>Tambah Jadwal Baru</h6>
                      <div class="form-grid">
                        <div class="form-group">
                          <label>Rute</label>
                          <input v-model="newScheduleForTrain.route" type="text" placeholder="Contoh: Jakarta - Bandung" class="form-input" />
                        </div>
                        <div class="form-group">
                          <label>Jam Keberangkatan</label>
                          <input v-model="newScheduleForTrain.departureTime" type="time" class="form-input" />
                        </div>
                        <div class="form-group">
                          <label>Jam Kedatangan</label>
                          <input v-model="newScheduleForTrain.arrivalTime" type="time" class="form-input" />
                        </div>
                        <div class="form-group">
                          <label>Hari Operasional</label>
                          <input v-model="newScheduleForTrain.days" type="text" placeholder="Contoh: Setiap Hari" class="form-input" />
                        </div>
                      </div>
                      <div class="form-actions">
                        <button class="btn-success" @click="handleAddScheduleToTrain(train.id)">Simpan</button>
                        <button class="btn-cancel" @click="showAddScheduleToTrain = false">Batal</button>
                      </div>
                    </div>

                    <!-- Add Schedule Button -->
                    <button v-if="!showAddScheduleToTrain || expandedTrainId !== train.id" class="btn-primary" style="margin-bottom: 1rem; width: 100%;" @click="showAddScheduleToTrain = true">
                      + Tambah Jadwal
                    </button>

                    <!-- Schedules Table -->
                    <div v-if="train.schedules.length > 0" class="data-table">
                      <table>
                        <thead>
                          <tr>
                            <th>Rute</th>
                            <th>Jam Berangkat</th>
                            <th>Jam Tiba</th>
                            <th>Hari Operasional</th>
                            <th>Aksi</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="schedule in train.schedules" :key="schedule.id">
                            <td>{{ schedule.route }}</td>
                            <td>{{ schedule.departureTime }}</td>
                            <td>{{ schedule.arrivalTime }}</td>
                            <td>{{ schedule.days }}</td>
                            <td>
                              <button class="btn-delete" @click="handleDeleteTrainSchedule(train.id, schedule.id)">Hapus</button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                    <div v-else class="empty-state" style="padding: 1rem; font-size: 0.875rem;">
                      Belum ada jadwal untuk kereta ini.
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Routes Management -->
          <div v-if="masterDataType === 'routes'" class="master-data-content">
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
              <button class="btn-primary" @click="showNewRouteForm = !showNewRouteForm">
                + Tambah Rute Baru
              </button>
            </div>

            <!-- Add New Route Form -->
            <div v-if="showNewRouteForm" class="form-card">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3>Form Rute Baru</h3>
                <button class="close-btn" @click="showNewRouteForm = false">×</button>
              </div>

              <!-- Message Alert -->
              <div v-if="routeMessage.text" :class="['message-alert', routeMessage.type]" style="margin-bottom: 1rem;">
                {{ routeMessage.text }}
              </div>

              <div class="form-grid">
                <div class="form-group">
                  <label>Kode Rute <span style="color: #dc2626;">*</span></label>
                  <input 
                    v-model="newRoute.code" 
                    type="text" 
                    placeholder="Contoh: RT-001" 
                    class="form-input"
                    @input="routeMessage.type = ''"
                    maxlength="20"
                  />
                  <small style="color: #6b7280;">Format unik untuk identifikasi rute</small>
                </div>
                <div class="form-group">
                  <label>Stasiun Awal <span style="color: #dc2626;">*</span></label>
                  <input 
                    v-model="newRoute.origin" 
                    type="text" 
                    placeholder="Jakarta Kota" 
                    class="form-input"
                    @input="routeMessage.type = ''"
                    maxlength="50"
                  />
                </div>
                <div class="form-group">
                  <label>Stasiun Tujuan <span style="color: #dc2626;">*</span></label>
                  <input 
                    v-model="newRoute.destination" 
                    type="text" 
                    placeholder="Surabaya Gubeng" 
                    class="form-input"
                    @input="routeMessage.type = ''"
                    maxlength="50"
                  />
                </div>
                <div class="form-group">
                  <label>Jam Keberangkatan <span style="color: #dc2626;">*</span></label>
                  <input 
                    v-model="newRoute.departureTime" 
                    type="time" 
                    class="form-input"
                    @input="routeMessage.type = ''"
                  />
                </div>
                <div class="form-group">
                  <label>Durasi Perjalanan (jam) <span style="color: #dc2626;">*</span></label>
                  <input 
                    v-model="newRoute.estimatedDuration" 
                    type="number" 
                    placeholder="12" 
                    class="form-input"
                    @input="routeMessage.type = ''"
                    min="1"
                    max="999"
                  />
                </div>
                <div class="form-group">
                  <label>Jarak (km)</label>
                  <input 
                    v-model="newRoute.distance" 
                    type="text" 
                    placeholder="720" 
                    class="form-input"
                    maxlength="20"
                  />
                </div>
                <div class="form-group">
                  <label>Stasiun Antara (pisahkan dengan koma)</label>
                  <input 
                    v-model="newRoute.stops" 
                    type="text" 
                    placeholder="Cirebon, Pekalongan, Semarang" 
                    class="form-input"
                  />
                  <small style="color: #6b7280;">Opsional: Daftar stasiun yang dilalui</small>
                </div>
                <div class="form-group">
                  <label>Status</label>
                  <select v-model="newRoute.status" class="form-input">
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                  </select>
                </div>
              </div>
              <div class="form-actions">
                <button 
                  class="btn-success" 
                  @click="handleAddRoute"
                  :disabled="isLoadingRoute"
                >
                  <span v-if="!isLoadingRoute">Simpan</span>
                  <span v-else>Menyimpan...</span>
                </button>
                <button 
                  class="btn-cancel" 
                  @click="showNewRouteForm = false"
                  :disabled="isLoadingRoute"
                >
                  Batal
                </button>
              </div>
            </div>

            <!-- Routes List -->
            <div class="routes-list">
              <div v-if="routes.length === 0" class="empty-state">
                <p>Belum ada rute. Tambahkan rute baru untuk memulai.</p>
              </div>

              <div v-for="route in routes" :key="route.id" class="route-card">
                <div class="route-header">
                  <div class="route-info">
                    <h4>{{ route.code }} - {{ route.origin }} → {{ route.destination }}</h4>
                    <p class="route-meta">
                      <span>Jam: {{ route.departureTime }}</span>
                      <span>Durasi: {{ route.estimatedDuration }} jam</span>
                      <span v-if="route.distance">Jarak: {{ route.distance }} km</span>
                      <span :class="['status', route.status]">{{ route.status === 'active' ? 'Aktif' : 'Nonaktif' }}</span>
                    </p>
                  </div>
                  <button class="btn-delete" @click="handleDeleteRoute(route.id)">Hapus</button>
                </div>
                <div v-if="route.stops && route.stops.length > 0" class="route-stops">
                  <p style="margin: 0.5rem 0; color: #6b7280; font-size: 0.875rem;">
                    <strong>Stasiun Antara:</strong> {{ route.stops.join(' → ') }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
/* ===== LOGIN PAGE STYLES ===== */
.admin-login-page {
  width: 100%;
  min-height: 100vh;
  background: var(--color-bg-light);
}

.admin-login-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 100vh;
}

.admin-branding {
  background: linear-gradient(135deg, #1e293b 0%, var(--color-primary) 50%, var(--color-secondary) 100%);
  color: white;
  padding: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.admin-branding::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: 
    radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(9, 216, 227, 0.15) 0%, transparent 40%);
  pointer-events: none;
}

.branding-content {
  position: relative;
  z-index: 1;
  text-align: center;
  max-width: 400px;
}

.logo {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.625rem;
  margin-bottom: 2rem;
  color: white;
}

.logo-icon {
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-text {
  display: flex;
  flex-direction: column;
  text-align: left;
}

.logo-title {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1.2;
}

.logo-subtitle {
  font-size: 0.625rem;
  font-weight: 600;
  letter-spacing: 0.1em;
  opacity: 0.9;
}

.branding-message {
  margin-bottom: 3rem;
}

.branding-message h2 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.branding-message p {
  font-size: 1rem;
  opacity: 0.95;
  line-height: 1.6;
}

.benefits {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  text-align: left;
}

.benefit-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 0.95rem;
}

.benefit-item svg {
  flex-shrink: 0;
}

.admin-form-section {
  background: white;
  padding: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow-y: auto;
}

.form-wrapper {
  width: 100%;
  max-width: 420px;
}

.form-wrapper h1 {
  font-size: 1.875rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin-bottom: 0.5rem;
}

.form-subtitle {
  font-size: 0.9375rem;
  color: var(--color-text-light);
  margin-bottom: 2rem;
}

.admin-form {
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 0.625rem;
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
  pointer-events: none;
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
  background: white;
}

.form-input::placeholder {
  color: #9ca3af;
}

.toggle-password {
  position: absolute;
  right: 0.875rem;
  background: none;
  border: none;
  color: var(--color-text-light);
  cursor: pointer;
  padding: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toggle-password:hover {
  color: var(--color-primary);
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  font-size: 0.875rem;
}

.checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  color: var(--color-text-dark);
}

.checkbox input {
  cursor: pointer;
  width: 16px;
  height: 16px;
  accent-color: var(--color-primary);
}

.forgot-password {
  color: var(--color-primary);
  text-decoration: none;
}

.forgot-password:hover {
  opacity: 0.8;
}

.admin-login-btn {
  width: 100%;
  padding: 0.875rem;
  font-size: 1rem;
  font-weight: 600;
  color: white;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  border: none;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(22, 117, 231, 0.3);
  cursor: pointer;
}

.admin-login-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(22, 117, 231, 0.4);
}

.admin-login-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.info-box {
  margin-top: 2rem;
  padding: 1.5rem;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 10px;
  text-align: left;
  font-size: 0.875rem;
  color: var(--color-text-dark);
}

.info-box p {
  margin: 0.5rem 0;
}

.info-box p:first-child {
  font-weight: 600;
  margin-bottom: 1rem;
}

/* ===== DASHBOARD STYLES ===== */
.admin-dashboard {
  width: 100%;
  min-height: 100vh;
  background: var(--color-bg-light);
}

.admin-header {
  background: var(--color-white);
  border-bottom: 1px solid #e5e7eb;
  position: sticky;
  top: 0;
  z-index: 100;
  display: flex;
  align-items: center;
}

.header-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0.75rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  transition: opacity 0.2s;
}

.logo:hover {
  opacity: 0.8;
}

.logo-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.logo-text {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
  line-height: 1;
}

.logo-title {
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-text-dark);
  line-height: 1;
  margin: 0;
}

.logo-subtitle {
  font-size: 0.65rem;
  font-weight: 600;
  color: var(--color-text-light);
  letter-spacing: 0.1em;
  line-height: 1;
  margin: 0;
}

.nav {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-profile-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.avatar-header {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 0.8rem;
}

.user-info {
  display: flex;
  flex-direction: column;
  gap: 0;
  line-height: 1;
}

.user-name-header {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-dark);
  margin: 0;
  line-height: 1.1;
}

.user-role-header {
  font-size: 0.65rem;
  color: var(--color-text-light);
  margin: 0;
  line-height: 1;
}

.logout-btn-header {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.4rem 0.8rem;
  background: #fef2f2;
  color: #f97316;
  border: 1px solid #fed7aa;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.logout-btn-header:hover {
  background: #fee2e2;
  border-color: #fbcfe8;
}

.dashboard-content {
  display: flex;
  flex-direction: column;
  max-width: 100%;
  margin: 0;
  gap: 0;
  padding: 0;
  min-height: auto;
}

.dashboard-wrapper {
  display: flex;
  max-width: 1400px;
  margin: 0 auto;
  gap: 2rem;
  padding: 1.5rem;
  width: 100%;
  min-height: calc(100vh - 100px - 200px);
}

/* Sidebar */
.sidebar {
  width: 180px;
  position: sticky;
  top: 100px;
  height: fit-content;
  flex-shrink: 0;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  color: var(--color-text-dark);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.menu-item:hover {
  background: var(--color-bg-light);
  border-color: var(--color-primary);
}

.menu-item.active {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
}

.menu-item svg {
  width: 20px;
  height: 20px;
}

.main-area {
  flex: 1;
  min-width: 0;
  padding-bottom: 2rem;
}

.content-section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.section-header h2 {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin: 0;
}

/* Refund Styles */
.refund-tabs,
.master-tabs {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.tab-btn {
  padding: 0.75rem 1.25rem;
  background: transparent;
  border: none;
  border-bottom: 3px solid transparent;
  color: var(--color-text-light);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-btn:hover {
  color: var(--color-primary);
}

.tab-btn.active {
  color: var(--color-primary);
  border-bottom-color: var(--color-primary);
}

.refund-table,
.data-table {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  overflow-x: auto;
}

.refund-table table,
.data-table table {
  width: 100%;
  border-collapse: collapse;
  min-width: 800px;
}

.refund-table th,
.data-table th {
  background: var(--color-bg-light);
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: var(--color-text-dark);
  border-bottom: 2px solid #e5e7eb;
}

.refund-table td,
.data-table td {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.refund-table tbody tr:hover,
.data-table tbody tr:hover {
  background: var(--color-bg-light);
}

.status-badge {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
}

.status-badge.pending {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.approved {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.processed {
  background: #dbeafe;
  color: #0c2340;
}

.status-badge.rejected {
  background: #fee2e2;
  color: #991b1b;
}

.status.active {
  background: #d1fae5;
  color: #065f46;
  padding: 0.375rem 0.75rem;
  border-radius: 6px;
  font-weight: 600;
}

.status.inactive {
  background: #f3f4f6;
  color: #6b7280;
  padding: 0.375rem 0.75rem;
  border-radius: 6px;
  font-weight: 600;
}

.status.maintenance {
  background: #fcd34d;
  color: #78350f;
  padding: 0.375rem 0.75rem;
  border-radius: 6px;
  font-weight: 600;
}

/* Train Cards Styling */
.trains-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1.5rem;
}

.train-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.train-card:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.train-header {
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  border-bottom: 1px solid #f3f4f6;
  transition: background 0.2s ease;
}

.train-header:hover {
  background: var(--color-bg-light);
}

.train-info {
  flex: 1;
}

.train-info h4 {
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 0.5rem;
}

.train-meta {
  display: flex;
  gap: 1.5rem;
  font-size: 0.875rem;
  color: var(--color-text-light);
  flex-wrap: wrap;
}

.train-meta span {
  display: flex;
  align-items: center;
}

.expand-icon {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-light);
  transition: transform 0.2s ease;
}

.expand-icon.expanded {
  transform: rotate(180deg);
}

.train-actions {
  padding: 0 1.5rem;
  display: flex;
  gap: 0.75rem;
  padding-bottom: 0.75rem;
}

.schedules-section {
  padding: 0 1.5rem 1.5rem 1.5rem;
  background: var(--color-bg-light);
}

.schedules-section h5 {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--color-text-dark);
}

.schedules-section h6 {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 1rem;
}

.empty-state {
  text-align: center;
  color: var(--color-text-light);
  padding: 2rem;
  background: white;
  border-radius: 8px;
  border: 1px dashed #e5e7eb;
}

.action-btns {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.btn-small {
  padding: 0.375rem 0.75rem;
  font-size: 0.8rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 500;
}

.btn-small.approve {
  background: #d1fae5;
  color: #065f46;
}

.btn-small.approve:hover {
  background: #a7f3d0;
}

.btn-small.reject {
  background: #fee2e2;
  color: #991b1b;
}

.btn-small.reject:hover {
  background: #fecaca;
}

.btn-small.process {
  background: #dbeafe;
  color: #0c2340;
}

.btn-small.process:hover {
  background: #bfdbfe;
}

.master-data-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.btn-primary {
  align-self: flex-start;
  padding: 0.75rem 1.5rem;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary:hover {
  background: var(--color-primary-dark);
  transform: translateY(-2px);
}

.form-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  margin-bottom: 1.5rem;
}

.form-card h3 {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin-bottom: 1.5rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 0.5rem;
}

.form-input {
  padding: 0.75rem;
  font-size: 0.9375rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-family: inherit;
}

.form-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(22, 117, 231, 0.1);
}

.form-actions {
  display: flex;
  gap: 0.75rem;
}

.btn-success {
  padding: 0.75rem 1.5rem;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.btn-success:hover {
  background: #059669;
}

.btn-cancel {
  padding: 0.75rem 1.5rem;
  background: #f3f4f6;
  color: var(--color-text-dark);
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.btn-cancel:hover {
  background: #e5e7eb;
}

.btn-delete {
  padding: 0.375rem 0.75rem;
  background: #fee2e2;
  color: #991b1b;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
}

.btn-delete:hover {
  background: #fecaca;
}

.welcome-section {
  margin-bottom: 1.5rem;
}

.welcome-section h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin-bottom: 0.25rem;
}

.welcome-section p {
  font-size: 0.875rem;
  color: var(--color-text-light);
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

.dashboard-card {
  background: var(--color-white);
  padding: 1rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
}

.dashboard-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.card-header h3 {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text-light);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0;
}

.card-header svg {
  color: var(--color-primary);
}

.card-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin: 0.5rem 0;
}

.card-subtitle {
  font-size: 0.875rem;
  color: var(--color-text-light);
  margin: 0;
}

.charts-section {
  margin-bottom: 2rem;
}

.charts-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.chart-container {
  background: var(--color-white);
  padding: 1.25rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chart-container h3 {
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin-bottom: 1rem;
}

.chart-container.full-width {
  grid-column: 1 / -1;
}

.chart-wrapper {
  display: flex;
  justify-content: center;
  max-width: 400px;
  margin: 0 auto;
}

.chart-container canvas {
  max-width: 100%;
  height: auto;
}

/* ===== ROUTE CARD STYLES ===== */
.routes-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1.5rem;
}

.route-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.route-card:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.route-header {
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  border-bottom: 1px solid #f3f4f6;
}

.route-info {
  flex: 1;
}

.route-info h4 {
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 0.5rem;
}

.route-meta {
  display: flex;
  gap: 1.5rem;
  font-size: 0.875rem;
  color: var(--color-text-light);
  flex-wrap: wrap;
  margin: 0;
}

.route-meta span {
  display: flex;
  align-items: center;
}

.route-stops {
  padding: 0.75rem 1.5rem;
  background: var(--color-bg-light);
  border-top: 1px solid #e5e7eb;
}

.route-stops p {
  margin: 0;
  font-size: 0.875rem;
}

/* Responsive */
@media (max-width: 1024px) {
  .admin-login-container {
    grid-template-columns: 1fr;
  }

  .admin-branding {
    display: none;
  }

  .dashboard-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .features-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .charts-grid {
    grid-template-columns: 1fr;
  }

  .chart-container.full-width {
    grid-column: 1;
  }
}

@media (max-width: 768px) {
  .header-container {
    flex-direction: column;
    gap: 1rem;
  }

  .admin-info {
    width: 100%;
    justify-content: space-between;
  }

  .dashboard-grid {
    grid-template-columns: 1fr;
  }

  .features-grid {
    grid-template-columns: 1fr;
  }

  .form-wrapper {
    max-width: 100%;
  }
}

@media (max-width: 480px) {
  .dashboard-content {
    padding: 1.5rem 1rem;
  }

  .welcome-section h1 {
    font-size: 1.5rem;
  }

  .dashboard-card {
    padding: 1rem;
  }

  .card-value {
    font-size: 1.5rem;
  }
}

/* ===== MESSAGE ALERT STYLES ===== */
.message-alert {
  padding: 1rem;
  border-radius: 8px;
  font-weight: 500;
  font-size: 0.9375rem;
  border: 1px solid;
  animation: slideDown 0.3s ease-out;
}

.message-alert.success {
  background-color: #d1fae5;
  color: #065f46;
  border-color: #a7f3d0;
}

.message-alert.error {
  background-color: #fee2e2;
  color: #991b1b;
  border-color: #fecaca;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ===== CLOSE BUTTON STYLES ===== */
.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--color-text-light);
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s ease;
  width: 24px;
  height: 24px;
}

.close-btn:hover {
  color: #f97316;
}

.form-card .close-btn:hover {
  color: var(--color-primary);
}

</style>
