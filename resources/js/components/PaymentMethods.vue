<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { getPaymentMethods } from '@/utils/api'
import { savePaymentData } from '@/utils/storage'
import { formatPrice, showError, showSuccess } from '@/utils/helpers'
import QRCode from 'qrcode'

const router = useRouter()
const route = useRoute()

// Loading and error states
const isLoading = ref(false)
const hasError = ref(false)
const errorMessage = ref('')

const total = Number(route.query.total || 0)
const orderNumber = route.query.orderNumber || ''

const selected = ref('swiftpay')
const swiftBalance = ref(0) // Will be fetched from API
const paymentMethods = ref([])
const banks = ref([])
const selectedBank = ref(null)

// QRIS QR Code
const showQRModal = ref(false)
const qrCodeImage = ref('')

const swiftSufficient = () => swiftBalance.value >= total

// Generate payment code untuk bank transfer
const generatePaymentCode = () => {
  // Format: BK + bank code + random number
  const bankCodes = {
    'BCA': '014',
    'Mandiri': '008',
    'BRI': '009',
    'BTN': '011',
    'CIMB Niaga': '022',
    'Permata': '013',
    'Danamon': '011',
    'BNI': '009'
  }
  const code = bankCodes[selectedBank.value] || '014'
  const random = Math.floor(Math.random() * 1000000).toString().padStart(6, '0')
  return `BK${code}${random}`
}

// Fetch wallet balance from API
const fetchWalletBalance = async () => {
  try {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token')
    if (!token) {
      console.warn('No auth token for wallet fetch')
      return
    }

    const response = await fetch('/api/v1/swiftpay/wallet', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
    })

    if (response.ok) {
      const data = await response.json()
      const balance = data.data?.balance || 0
      swiftBalance.value = parseFloat(balance) || 0
      console.log('Wallet balance fetched:', swiftBalance.value)
    }
  } catch (err) {
    console.error('Error fetching wallet balance:', err)
    swiftBalance.value = 0
  }
}

// Fetch payment methods from API
const fetchPaymentMethods = async () => {
  try {
    isLoading.value = true
    hasError.value = false
    errorMessage.value = ''
    
    // Call API to get available payment methods
    const response = await getPaymentMethods()
    const methodsData = response.data || response || {}
    
    if (methodsData && methodsData.methods) {
      paymentMethods.value = methodsData.methods
    }
    
    if (methodsData && methodsData.banks) {
      banks.value = methodsData.banks
    }
    
    // Fallback to mock data if no banks from API
    if (!banks.value || banks.value.length === 0) {
      banks.value = [
        'BCA',
        'Mandiri',
        'BRI',
        'BTN',
        'CIMB Niaga',
        'Permata',
        'Danamon',
        'BNI'
      ]
    }
    
    if (banks.value.length > 0) {
      selectedBank.value = banks.value[0]
    }
    
    // Fetch wallet balance from dedicated API
    await fetchWalletBalance()
    
    showSuccess('Metode pembayaran dimuat')
  } catch (error) {
    hasError.value = true
    errorMessage.value = error.message || 'Gagal memuat metode pembayaran. Silakan coba lagi.'
    showError(error, 'Metode Pembayaran')
    console.error('Failed to fetch payment methods:', error)
    // Fallback to mock banks even on error
    banks.value = [
      'BCA',
      'Mandiri',
      'BRI',
      'BTN',
      'CIMB Niaga',
      'Permata',
      'Danamon',
      'BNI'
    ]
    if (banks.value.length > 0) {
      selectedBank.value = banks.value[0]
    }
    
    // Still try to fetch wallet balance even if payment methods fail
    await fetchWalletBalance()
  } finally {
    isLoading.value = false
  }
}

const goBack = () => router.back()

const doPay = () => {
  if (selected.value === 'swiftpay' && !swiftSufficient()) {
    showError(new Error('Saldo SwiftPay tidak mencukupi'), 'Metode Pembayaran')
    return
  }

  // Save payment method selection to storage
  savePaymentData({
    method: selected.value,
    bank: selected.value === 'ib' ? selectedBank.value : null,
    total: total,
    orderNumber: orderNumber,
    selectedAt: new Date().toISOString()
  })

  // Navigate to checkout with payment method and other details
  router.push({
    path: '/checkout',
    query: {
      total: route.query.total,
      orderNumber,
      paymentMethod: selected.value,
      selectedBank: selected.value === 'ib' ? selectedBank.value : '',
      trainName: route.query.trainName,
      trainNumber: route.query.trainNumber,
      trainClass: route.query.trainClass,
      passengers: route.query.passengers,
      fromStation: route.query.fromStation,
      toStation: route.query.toStation,
      date: route.query.date,
      departure: route.query.departure,
      arrival: route.query.arrival,
      seats: route.query.seats,
      passengerName: route.query.passengerName,
      passengerType: route.query.passengerType
    }
  })
}

// Load payment methods when component mounts
onMounted(async () => {
  await fetchPaymentMethods()
})
</script>

<template>
  <div class="payment-page">
    <header class="header">
      <button class="back-btn" @click="goBack" aria-label="Kembali">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M10 17L5 12M5 12L10 7M5 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      <div class="header-content">
        <h1 class="header-title">Pilih Metode Pembayaran</h1>
      </div>
    </header>

    <main class="content-wrapper">
      <!-- Loading State -->
      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <h3>Memuat Metode Pembayaran...</h3>
        <p>Tunggu sebentar, kami sedang mempersiapkan pilihan pembayaran Anda</p>
      </div>

      <!-- Error State -->
      <div v-else-if="hasError" class="error-state">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
          <path d="M12 7V13M12 17H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <h3>Terjadi Kesalahan</h3>
        <p>{{ errorMessage }}</p>
        <button class="retry-btn" @click="fetchPaymentMethods">Coba Lagi</button>
      </div>

      <!-- Payment Methods Content -->
      <div v-else class="payment-content">
        <div class="card">
          <!-- SwiftPay (distinct style) -->
          <div class="method swiftpay" :class="{ active: selected === 'swiftpay' }" @click="selected = 'swiftpay'">
            <div class="method-left">
              <div class="method-title">SwiftPay</div>
              <div class="method-sub">Saldo SwiftPay: Rp {{ swiftBalance.toLocaleString('id-ID') }}</div>
            </div>
            <div class="method-right">
              <div class="balance-badge">Rp {{ swiftBalance.toLocaleString('id-ID') }}</div>
              <div class="choose-label">{{ selected === 'swiftpay' ? 'Terpilih' : 'Pilih' }}</div>
            </div>
          </div>

        <!-- QRIS -->
        <div class="method" :class="{ active: selected === 'qris' }" @click="selected = 'qris'">
          <div class="method-left">
            <div class="method-title">QRIS</div>
            <div class="method-sub">Bayar lewat pemindai QR</div>
          </div>
          <div class="method-right">{{ selected === 'qris' ? 'Terpilih' : 'Pilih' }}</div>
        </div>

        <!-- Internet Banking with dropdown -->
        <div class="method" :class="{ active: selected === 'ib' }" @click="selected = 'ib'">
          <div class="method-left">
            <div class="method-title">Internet Banking</div>
            <div class="method-sub">Transfer via bank</div>
          </div>
          <div class="method-right">{{ selected === 'ib' ? 'Terpilih' : 'Pilih' }}</div>
        </div>

        <div v-if="selected === 'ib'" class="bank-select">
          <label for="bank" class="bank-label">Pilih Bank</label>
          <select id="bank" v-model="selectedBank" class="bank-dropdown">
            <option v-for="b in banks" :key="b" :value="b">{{ b }}</option>
          </select>
        </div>
        </div>

        <div class="summary-card">
          <div class="summary-row label-left">
            <span class="label">Total Pembayaran</span>
            <strong class="amount">Rp {{ total.toLocaleString('id-ID') }}</strong>
          </div>

          <div class="summary-actions">
            <button class="pay-action" :disabled="selected === 'swiftpay' && !swiftSufficient()" @click="doPay">PAY</button>
            <div v-if="selected === 'swiftpay' && !swiftSufficient()" class="warning">Saldo SwiftPay tidak mencukupi untuk pembayaran.</div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.header {
  display:flex; align-items:center; gap:16px; padding:16px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%); color:white; box-shadow:0 2px 8px rgba(22,117,231,0.15);
}
.back-btn { background: rgba(255,255,255,0.2); border:none; width:40px; height:40px; border-radius:8px; display:flex; align-items:center; justify-content:center; color:white }
.header-title { font-size:20px; font-weight:600 }
.content-wrapper { padding:18px }
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
.payment-content {
  display: contents;
}
.card { background:var(--color-white); padding:12px; border-radius:10px; border:1px solid rgba(14,23,36,0.06); margin-bottom:12px }
.method { display:flex; justify-content:space-between; align-items:center; padding:12px; border-radius:8px; cursor:pointer; border:1px solid rgba(14,23,36,0.04); margin-bottom:8px }
.method.active { box-shadow:0 4px 20px rgba(22,117,231,0.12); border-color: rgba(22,117,231,0.12) }
.method-title { font-weight:700 }
.method-sub { font-size:13px; color:var(--color-text-light) }
.summary-card { position:sticky; bottom:12px; background:var(--color-white); padding:12px; border-radius:10px; border:1px solid rgba(14,23,36,0.06); display:flex; flex-direction:column; gap:12px }
.pay-action { padding:12px; background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark)); color:var(--color-white); border:none; border-radius:8px; font-weight:700 }
.pay-action:disabled { opacity:0.5; cursor:not-allowed }

/* SwiftPay distinct styling */
.method.swiftpay { background: linear-gradient(135deg,#5dd6ff,#3a8dff); color: #fff; border: none }
.method.swiftpay .method-sub { color: rgba(255,255,255,0.9) }
.balance-badge { background: rgba(255,255,255,0.18); padding:6px 8px; border-radius:8px; font-weight:700; margin-bottom:6px }
.choose-label { font-size:12px; opacity:0.9 }
.bank-select { margin-top:8px; display:flex; flex-direction:column; gap:6px }
.bank-label { font-size:13px; color:var(--color-text); font-weight:600 }
.bank-dropdown { padding:8px; border-radius:8px; border:1px solid rgba(14,23,36,0.06) }
.warning { color:#b22222; font-size:13px }

/* Summary layout tweaks: put amount on left */
.summary-row.label-left { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:6px 2px }
.summary-row.label-left .amount { font-size:16px; font-weight:800 }
.summary-row.label-left .label { color:var(--color-text-light); font-size:14px; font-weight:600 }
.summary-actions { display:flex; flex-direction:column; gap:8px; margin-top:8px }

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* QR Modal Styles */
.qr-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease;
}

.qr-modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 420px;
  display: flex;
  flex-direction: column;
  animation: slideUp 0.3s ease;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.qr-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px;
  border-bottom: 1px solid #e0e4ea;
}

.qr-modal-header h2 {
  font-size: 18px;
  font-weight: 700;
  color: var(--color-text-dark);
  margin: 0;
}

.qr-close-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  color: var(--color-text-light);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.qr-close-btn:hover {
  color: var(--color-primary);
}

.qr-modal-body {
  padding: 24px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
}

.qr-code-container {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #e0e4ea;
}

.qr-code-image {
  width: 280px;
  height: 280px;
  display: block;
}

.qr-info {
  text-align: center;
  width: 100%;
}

.qr-amount {
  font-size: 16px;
  font-weight: 600;
  color: var(--color-text-dark);
  margin: 0 0 12px 0;
}

.qr-amount strong {
  color: var(--color-primary);
  font-size: 18px;
}

.qr-instruction {
  font-size: 13px;
  color: var(--color-text-light);
  margin: 0 0 12px 0;
  line-height: 1.5;
}

.qr-order {
  font-size: 12px;
  color: #999;
  margin: 0;
  font-family: 'Courier New', monospace;
}

.qr-modal-footer {
  padding: 16px 20px 20px;
  border-top: 1px solid #e0e4ea;
  display: flex;
  gap: 8px;
}

.qr-confirm-btn {
  flex: 1;
  padding: 12px 16px;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

.qr-confirm-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(22, 117, 231, 0.4);
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
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
</style>