<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { getOrderConfirmation } from '@/utils/api'
import { getOrderSummary, saveOrderSummary } from '@/utils/storage'
import { formatPrice, formatDate, showError, showSuccess } from '@/utils/helpers'

const router = useRouter()
const route = useRoute()

// Loading and error states
const isLoading = ref(false)
const hasError = ref(false)
const errorMessage = ref('')

// build order info from route.query so the page reflects previous selections
const q = route.query || {}
const seatsParam = q.seats || ''
const seats = seatsParam ? String(seatsParam).split(',').filter(s => s) : []
const passengersParam = q.passengers || '1'

const defaultPrice = (cls) => {
  const clsStr = String(cls || '').toLowerCase()
  // Mapping: Eksekutif 3 (Economy) = 250k, Eksekutif 2 (Business) = 350k, Eksekutif 1 (Executive) = 450k
  // Handle both display format (Eksekutif X) and API format (economy/business/executive)
  if (clsStr.includes('3') || clsStr.includes('economy') || clsStr.includes('econom')) {
    return 250000 // Economy
  } else if (clsStr.includes('2') || clsStr.includes('business')) {
    return 350000 // Business
  } else if (clsStr.includes('1') || clsStr.includes('executive')) {
    return 450000 // Executive
  }
  // If can't determine class, default to executive price
  return 450000
}

const order = ref({
  date: q.date || '03 December 2025',
  departureTime: q.departure || '14:15',
  arrivalTime: q.arrival || '17:30',
  from: q.fromStation || 'Surabaya Gubeng (SGU)',
  to: q.toStation || 'Solo Balapan (SLO)',
  trainName: q.trainName || 'Mutiara Selatan',
  classType: q.trainClass || 'Eksekutif 1',
  passengerName: seats.length ? `NURIS SAFIRA` : 'NURIS SAFIRA',
  passengerType: 'Dewasa',
  coach: q.trainClass || 'Eksekutif 1',
  seat: seats[0] || '',
  price: defaultPrice(q.trainClass),
  platformFee: 0
})

// Fetch order confirmation from API
const fetchOrderConfirmation = async () => {
  try {
    isLoading.value = true
    hasError.value = false
    errorMessage.value = ''
    
    // Get booking ID from query or storage
    const bookingId = route.query.bookingId || getOrderSummary()?.bookingId
    
    if (!bookingId) {
      // If no booking ID, use data from query params
      showSuccess('Konfirmasi pesanan siap ditampilkan')
      return
    }
    
    // Fetch order confirmation from API
    const response = await getOrderConfirmation(bookingId)
    const confirmationData = response.data || response || {}
    
    // Update order with API data if available
    if (confirmationData && Object.keys(confirmationData).length > 0) {
      order.value = {
        ...order.value,
        ...confirmationData,
        // Keep formatted versions from original if needed
        date: confirmationData.date || order.value.date,
        departureTime: confirmationData.departureTime || order.value.departureTime,
        arrivalTime: confirmationData.arrivalTime || order.value.arrivalTime,
        price: confirmationData.price || order.value.price,
        platformFee: confirmationData.platformFee || order.value.platformFee
      }
      
      // Update storage with confirmed order data
      saveOrderSummary({
        ...getOrderSummary(),
        ...confirmationData,
        confirmedAt: new Date().toISOString()
      })
      
      showSuccess('Konfirmasi pesanan dimuat')
    }
  } catch (error) {
    hasError.value = true
    errorMessage.value = error.message || 'Gagal memuat konfirmasi pesanan. Silakan coba lagi.'
    showError(error, 'Konfirmasi Pesanan')
    console.error('Failed to fetch order confirmation:', error)
    // Don't prevent the component from rendering - use existing order data
  } finally {
    isLoading.value = false
  }
}

const totalPayment = computed(() => order.value.price + order.value.platformFee)
const handlePay = () => {
  // Navigate to payment selection, pass total and complete booking data
  router.push({
    path: '/payment-methods',
    query: {
      total: totalPayment.value,
      orderNumber: `SR${Date.now()}`,
      // Pass all booking details
      trainName: order.value.trainName,
      trainNumber: route.query.trainNumber || '',
      trainClass: order.value.classType,
      fromStation: order.value.from,
      toStation: order.value.to,
      date: order.value.date,
      departure: order.value.departureTime,
      arrival: order.value.arrivalTime,
      passengers: passengersParam,
      seats: seats.join(','),
      passengerName: order.value.passengerName,
      passengerType: order.value.passengerType
    }
  })
}

const handleAddToCart = () => {
  alert("Ticket added to Cart (dummy action).")
}

const goBack = () => {
  router.push({
    path: '/select-seat',
    query: {
      trainId: route.query.bookingId || '',
      trainName: route.query.trainName || order.value.trainName,
      trainNumber: route.query.trainNumber || '',
      fromStation: route.query.fromStation || order.value.from,
      toStation: route.query.toStation || order.value.to,
      date: route.query.date || order.value.date,
      departure: route.query.departure || order.value.departureTime,
      arrival: route.query.arrival || order.value.arrivalTime,
      trainClass: route.query.trainClass || order.value.classType,
      passengers: route.query.passengers || '1',
      seats: route.query.seats || ''
    }
  })
}

// Load order confirmation when component mounts
onMounted(async () => {
  // First, try to get all data from query params (from SelectSeat)
  if (q.trainName && q.date && q.departure) {
    // All data from SelectSeat is available, use it directly
    order.value = {
      date: q.date || '03 December 2025',
      departureTime: q.departure || '14:15',
      arrivalTime: q.arrival || '17:30',
      from: q.fromStation || 'Surabaya Gubeng (SGU)',
      to: q.toStation || 'Solo Balapan (SLO)',
      trainName: q.trainName || 'Mutiara Selatan',
      trainNumber: q.trainNumber || '',
      classType: q.trainClass || 'Eksekutif 1',
      coach: q.trainClass || 'Eksekutif 1',
      seat: seats[0] || '',
      seats: seats.join(', '),
      passengers: passengersParam,
      price: defaultPrice(q.trainClass),
      platformFee: 0
    }
    console.log('Order data from SelectSeat:', order.value)
  }
  
  // Try to get user data from storage
  try {
    const orderSummary = getOrderSummary()
    if (orderSummary?.userData) {
      const userData = orderSummary.userData
      let userName = userData.name || userData.full_name
      
      if (!userName && (userData.first_name || userData.last_name)) {
        const firstName = userData.first_name || ''
        const lastName = userData.last_name || ''
        userName = `${firstName} ${lastName}`.trim()
      }
      
      if (!userName) {
        userName = userData.username || 'PENUMPANG'
      }
      
      order.value.passengerName = userName.toUpperCase()
      order.value.passengerType = 'Dewasa'
      console.log('Passenger name from orderSummary:', order.value.passengerName)
    } else {
      const userDataStr = localStorage.getItem('userProfile') || localStorage.getItem('userData')
      if (userDataStr) {
        const userData = JSON.parse(userDataStr)
        let userName = userData.name || userData.full_name
        
        if (!userName && (userData.first_name || userData.last_name)) {
          const firstName = userData.first_name || ''
          const lastName = userData.last_name || ''
          userName = `${firstName} ${lastName}`.trim()
        }
        
        if (!userName) {
          userName = userData.username || 'PENUMPANG'
        }
        
        order.value.passengerName = userName.toUpperCase()
        order.value.passengerType = 'Dewasa'
        console.log('Passenger name from localStorage:', order.value.passengerName)
      }
    }
  } catch (error) {
    console.error('Failed to load passenger name:', error)
  }
  
  // Don't call fetchOrderConfirmation if we have data from SelectSeat
  if (!q.trainName) {
    await fetchOrderConfirmation()
  }
})
</script>

<template>
  <div class="checkout-page">

    <header class="header">
      <button class="back-btn" @click="goBack" aria-label="Kembali ke Pilih Kursi">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M10 17L5 12M5 12L10 7M5 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      <div class="header-content">
        <h1 class="header-title">Konfirmasi Kursi</h1>
      </div>
    </header>

    <div class="content-wrapper">
      <!-- Loading State -->
      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <h3>Memuat Konfirmasi Pesanan...</h3>
        <p>Tunggu sebentar, kami sedang memproses pesanan Anda</p>
      </div>

      <!-- Error State -->
      <div v-else-if="hasError && errorMessage" class="error-state">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
          <path d="M12 7V13M12 17H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <h3>Terjadi Kesalahan</h3>
        <p>{{ errorMessage }}</p>
        <button class="retry-btn" @click="fetchOrderConfirmation">Coba Lagi</button>
      </div>

      <!-- Order Content -->
      <div v-else class="order-content">
        <div class="card">
          <h3 class="card-title">Kereta Pergi</h3>

          <p class="date-row">
            {{ order.date }} • {{ order.departureTime }} - {{ order.arrivalTime }}
          </p>

          <p class="route">{{ order.from }} → {{ order.to }}</p>

          <p class="train-info">
            {{ order.trainName }} • {{ order.classType.toUpperCase() }}
          </p>

          <div class="passenger-box">
            <div class="left">
              <p class="passenger-name">{{ order.passengerName }}</p>
              <p class="seat-info">{{ order.coach }} • {{ order.seat }}</p>
            </div>
            <div class="tag">{{ order.passengerType }}</div>
          </div>
        </div>

        <div class="card">
          <h3 class="card-title">Rincian Harga</h3>

          <div class="train-row">
            <div>
              <p class="train-name">{{ order.trainName }}</p>
              <span class="pill">{{ order.classType }}</span>
            </div>
          </div>

          <div class="price-item">
            <span>Dewasa x1</span>
            <strong>Rp{{ order.price.toLocaleString() }}</strong>
          </div>

          <div class="price-item">
            <span>Platform Fee</span>
            <strong>Rp{{ order.platformFee }}</strong>
        </div>

        <div class="total-box">
          <span>Total Pembayaran</span>
          <strong>Rp{{ totalPayment.toLocaleString() }}</strong>
        </div>
      </div>

      <button class="pay-btn" @click="handlePay">PAY</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.checkout-page {
  width: 100%;
  min-height: 100vh;
  background: var(--color-bg-light);
  color: var(--color-text-dark);
  font-family: inherit;
}

.top-header { display:none; }

.header {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(22, 117, 231, 0.15);
}

.back-btn {
  background: rgba(255,255,255,0.2);
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 8px;
  cursor: pointer;
  display:flex; align-items:center; justify-content:center; color:white;
}

.back-btn:hover { background: rgba(255,255,255,0.28) }

.header-content { flex:1 }
.header-title { font-size:20px; font-weight:600; margin:0; color:white }

.extras-list { display:flex; gap:10px; overflow-x:auto }

.extra-pill {
  padding: 6px 12px;
  background: rgba(255,255,255,0.12);
  border-radius: 18px;
  font-size: 13px;
  color: var(--color-white);
  white-space: nowrap;
}

.extra-pill.active { background: var(--color-white); color: var(--color-text-dark) }
.checkmark { margin-right:6px; font-weight:700 }

.content-wrapper { padding: 18px }

/* Loading State */
.loading-state {
  text-align: center;
  padding: 80px 20px 60px;
  background: white;
  border-radius: 8px;
  color: var(--color-text-light);
  margin-bottom: 18px;
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
  margin-bottom: 18px;
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

.order-content {
  display: contents;
}

.card {
  background: var(--color-white);
  padding: 18px;
  border-radius: 12px;
  margin-bottom: 18px;
  border: 1px solid rgba(14, 23, 36, 0.06);
}

.card-title { font-size:16px; font-weight:700; margin-bottom:12px }
.date-row { color: var(--color-text-light); margin-bottom:8px; font-size:14px }
.route { font-size:16px; font-weight:700; margin-bottom:6px }
.train-info { color: var(--color-text-light); margin-bottom:12px }

.passenger-box {
  display:flex; justify-content:space-between; align-items:center;
  background: #fbfdff; padding:12px; border-radius:10px;
}
.passenger-name { font-weight:700 }
.tag { background: rgba(22,117,231,0.12); color: var(--color-primary); padding:4px 10px; border-radius:16px; font-size:13px }

.price-item { display:flex; justify-content:space-between; margin-bottom:10px }
.total-box { margin-top:14px; padding-top:12px; border-top:1px solid rgba(14,23,36,0.06); display:flex; justify-content:space-between; font-size:18px }

.pill { display:inline-block; background: rgba(13, 82, 217, 0.08); padding:4px 8px; border-radius:6px; font-size:12px; color: var(--color-primary) }

.pay-btn {
  width:100%; padding:14px; background: linear-gradient(135deg,var(--color-primary),var(--color-primary-dark)); color:var(--color-white); border:none; border-radius:10px; font-size:16px; font-weight:700; margin-bottom:10px;
}

/* small responsive tweak */
@media (min-width: 768px) {
  .content-wrapper { padding: 28px }
  .card { padding: 20px }
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
