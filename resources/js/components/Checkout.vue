<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { processPayment } from '@/utils/api'
import { getOrderSummary, getPaymentData, clearAllData } from '@/utils/storage'
import { formatPrice, showError, showSuccess } from '@/utils/helpers'
import QRCode from 'qrcode'

const router = useRouter()
const route = useRoute()

// Loading and error states
const isProcessing = ref(false)
const hasError = ref(false)
const errorMessage = ref('')
const paymentStatus = ref(null) // 'success' or 'failed'

// Payment code dan QR
const paymentCode = ref('')
const showQRModal = ref(false)
const qrCodeImage = ref('')
const qrisLogo = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"%3E%3Crect width="100" height="100" fill="%23000080"/%3E%3Ctext x="50" y="50" text-anchor="middle" dominant-baseline="middle" fill="white" font-size="8" font-weight="bold"%3EQRIS%3C/text%3E%3C/svg%3E'

const total = Number(route.query.total || 0)
const orderNumber = route.query.orderNumber || ''
const paymentMethod = route.query.paymentMethod || 'swiftpay'
const selectedBank = route.query.selectedBank || 'MANDIRI'
const trainName = route.query.trainName || ''
const trainNumber = route.query.trainNumber || ''
const trainClass = route.query.trainClass || 'Eksekutif'
const passengersString = String(route.query.passengers || '1')
const adultCount = parseInt(passengersString.charAt(0)) || 1
const childCount = parseInt(passengersString.charAt(1)) || 0
const infantCount = parseInt(passengersString.charAt(2)) || 0
const passengers = adultCount + childCount + infantCount
const seatsString = route.query.seats || ''
const passengerName = route.query.passengerName || ''
const passengerType = route.query.passengerType || 'Dewasa'
const fromStation = route.query.fromStation || ''
const toStation = route.query.toStation || ''
const date = route.query.date || ''
const departure = route.query.departure || ''
const arrival = route.query.arrival || ''

const seats = computed(() => {
  if (!seatsString) return []
  return seatsString.split(',').map(s => s.trim()).filter(s => s)
})

const ticketPrice = Math.round(total / passengers)
const platformFee = 0 
const finalTotal = total + platformFee

const paymentMethodDisplay = computed(() => {
  if (paymentMethod === 'swiftpay') return 'SwiftPay'
  if (paymentMethod === 'qris') return 'QRIS'
  if (paymentMethod === 'ib') return `Internet Banking - ${selectedBank}`
  return 'Metode Pembayaran'
})

// Generate payment code untuk bank transfer
const generatePaymentCode = () => {
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
  const code = bankCodes[selectedBank] || '014'
  const random = Math.floor(Math.random() * 1000000).toString().padStart(6, '0')
  return `BK${code}${random}`
}

// Generate QR Code for QRIS
const generateQRCode = async () => {
  try {
    const qrData = `00020126360014com.midtrans${orderNumber}${total}`.substring(0, 120)
    const canvas = await QRCode.toCanvas(qrData, {
      width: 300,
      margin: 2,
      color: {
        dark: '#000000',
        light: '#FFFFFF'
      }
    })
    qrCodeImage.value = canvas.toDataURL('image/png')
    showQRModal.value = true
  } catch (error) {
    console.error('Failed to generate QR code:', error)
    showError(error, 'Generate QR Code')
  }
}

// Process payment via API
const processPaymentTransaction = async () => {
  try {
    isProcessing.value = true
    hasError.value = false
    errorMessage.value = ''
    paymentStatus.value = null
    
    // Get booking and payment data from storage
    const orderSummary = getOrderSummary()
    const paymentData = getPaymentData()
    const userProfile = JSON.parse(localStorage.getItem('userProfile') || '{}')
    const authToken = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token')
    
    console.log('=== Payment Processing ===')
    console.log('paymentMethod:', paymentMethod)
    console.log('finalTotal:', finalTotal)

    // For all payment methods, process payment successfully
    // In production, this would integrate with real payment gateways
    try {
      // Prepare payment payload
      const paymentPayload = {
        order_id: orderNumber,
        booking_id: orderSummary?.bookingId || route.query.bookingId || 'temp-' + orderNumber,
        payment_method: paymentMethod,
        amount: Math.round(finalTotal * 0.901), // Base amount without tax
        tax: Math.round(finalTotal * 0.099), // 10% tax
        gross_amount: Math.round(finalTotal),
        currency: 'IDR',
        bank_name: paymentMethod === 'ib' ? selectedBank : null,
        customer_name: userProfile.name || passengerName || 'Penumpang',
        customer_email: userProfile.email || 'customer@swiftrail.com',
        user_id: userProfile.id || userProfile.user_id,
      }

      console.log('Payment payload:', JSON.stringify(paymentPayload, null, 2))

      // For SwiftPay specifically, can integrate with Midtrans if desired
      // For now, all methods just process as successful
      
      // Simulate successful payment processing
      const paymentResult = {
        status: 'success',
        success: true,
        orderNumber: orderNumber,
        bookingId: orderSummary?.bookingId || route.query.bookingId,
        message: `Pembayaran via ${paymentMethodDisplay.value} berhasil!`
      }

      console.log('Payment result:', paymentResult)

      if (paymentResult.status === 'success' || paymentResult.success === true) {
        paymentStatus.value = 'success'
        showSuccess('Pembayaran berhasil diproses!')
        
        // Navigate to success page
        setTimeout(() => {
          router.push({
            path: '/payment-success',
            query: {
              orderNumber: paymentResult.orderNumber || orderNumber,
              bookingId: paymentResult.bookingId || orderSummary?.bookingId || 'temp-' + orderNumber,
              total: finalTotal,
              paymentMethod,
              trainName,
              trainNumber,
              trainClass,
              fromStation,
              toStation,
              date,
              departure,
              arrival,
              seats: seatsString,
              passengerName: userProfile.name || passengerName || 'Penumpang',
              paymentCode: paymentMethod === 'ib' ? generatePaymentCode() : null
            }
          })
        }, 800)
      } else {
        hasError.value = true
        errorMessage.value = paymentResult.message || 'Pembayaran gagal. Silakan coba lagi.'
        isProcessing.value = false
      }
    } catch (paymentError) {
      console.error('Payment error:', paymentError)
      hasError.value = true
      errorMessage.value = paymentError.message || 'Terjadi kesalahan saat memproses pembayaran'
      isProcessing.value = false
      showError(paymentError, 'Pembayaran')
    }
  } catch (error) {
    paymentStatus.value = 'failed'
    hasError.value = true
    errorMessage.value = error.message || 'Gagal memproses pembayaran. Silakan coba lagi.'
    showError(error, 'Pembayaran')
    console.error('Payment processing failed:', error)
  } finally {
    isProcessing.value = false
  }
}

const goBack = () => router.back()

const goToEtiket = () => {
  // Navigate to etiket/ticket page with booking details
  const orderSummary = getOrderSummary()
  const bookingId = orderSummary?.bookingId || route.query.bookingId || 'temp-' + orderNumber
  
  router.push({
    path: '/etiket',
    query: {
      orderNumber,
      bookingId,
      trainName,
      trainNumber,
      trainClass,
      seats: seatsString,
      passengers: passengersString,
      fromStation,
      toStation,
      date,
      departure,
      arrival,
      total: finalTotal
    }
  }).catch(err => {
    console.error('Navigation to etiket failed:', err)
    // Fallback: try /booking or /tickets
    router.push('/booking').catch(() => {
      router.push('/').catch(err => console.error('Navigation failed:', err))
    })
  })
}

// Initialize payment methods on mount
onMounted(async () => {
  // Generate payment code untuk bank transfer
  if (paymentMethod === 'ib') {
    paymentCode.value = generatePaymentCode()
  }
  
  // Generate QR code untuk QRIS (show modal nanti setelah mount)
  if (paymentMethod === 'qris') {
    await generateQRCode()
  }
})
</script>

<template>
  <div class="checkout-page">
    <!-- Header -->
    <header class="header">
      <button class="back-btn" @click="goBack" aria-label="Kembali">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M10 17L5 12M5 12L10 7M5 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      <div class="header-content">
        <h1 class="header-title">Konfirmasi Pembayaran</h1>
      </div>
    </header>

    <!-- Content -->
    <main class="content-wrapper">
      <!-- Payment Method -->
      <div class="payment-method-card">
        <div class="method-header">Metode Pembayaran</div>
        <div class="method-display">
          <div class="method-icon">
            <img v-if="paymentMethod === 'qris'" :src="qrisLogo" alt="QRIS" class="qris-logo" />
            <svg v-else-if="paymentMethod === 'swiftpay'" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
              <rect x="2" y="5" width="20" height="14" rx="2" fill="currentColor"/>
              <path d="M6 10h12M6 15h8" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
              <rect x="2" y="5" width="20" height="14" rx="2" fill="currentColor"/>
            </svg>
          </div>
          <div class="method-info">
            <div class="method-name">{{ paymentMethodDisplay }}</div>
            <div class="method-sub">Pembayaran aman</div>
          </div>
          <button class="change-btn" @click="goBack">Ubah</button>
        </div>
      </div>

      <!-- Bank Payment Code (untuk Internet Banking) -->
      <div v-if="paymentMethod === 'ib'" class="payment-code-card">
        <div class="code-header">Kode Pembayaran</div>
        <div class="code-display">
          <div class="code-info">
            <p class="code-label">Masukkan kode ini di {{ selectedBank }}:</p>
            <div class="code-box">
              <span class="code-text">{{ paymentCode }}</span>
              <button class="copy-btn" @click="() => {
                navigator.clipboard.writeText(paymentCode);
                showSuccess('Kode pembayaran disalin!');
              }">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect x="9" y="9" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2"/>
                  <path d="M5 15H4C2.89543 15 2 14.1046 2 13V4C2 2.89543 2.89543 2 4 2H13C14.1046 2 15 2.89543 15 4V5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Salin
              </button>
            </div>
          </div>
          <p class="code-instruction">Gunakan kode ini untuk transfer via Internet Banking {{ selectedBank }}. Pastikan nominal sesuai: Rp {{ finalTotal.toLocaleString('id-ID') }}</p>
        </div>
      </div>

      <!-- Passenger Info -->
      <div class="passenger-info-card" v-if="passengerName">
        <div class="info-header">Penumpang</div>
        <div class="passenger-details">
          <div class="passenger-item">
            <span class="label">Nama:</span>
            <span class="value">{{ passengerName }}</span>
          </div>
          <div class="passenger-item">
            <span class="label">Kategori:</span>
            <span class="value">{{ passengerType }}</span>
          </div>
          <div class="passenger-breakdown">
            <div v-if="adultCount > 0" class="breakdown-item">{{ adultCount }} Dewasa</div>
            <div v-if="childCount > 0" class="breakdown-item">{{ childCount }} Anak</div>
            <div v-if="infantCount > 0" class="breakdown-item">{{ infantCount }} Bayi</div>
          </div>
        </div>
      </div>

      <!-- Detail Harga -->
      <div class="detail-harga-card">
        <div class="detail-header">Detail Harga</div>
        
        <!-- Train and Seats Info -->
        <div class="detail-item">
          <div class="detail-label">
            <div class="train-info-block">
              <div class="train-header">
                <span class="train-name">{{ trainName }}</span>
                <span class="train-number" v-if="trainNumber">#{{ trainNumber }}</span>
              </div>
              <div class="train-route-info">
                <span class="route">{{ fromStation }} → {{ toStation }}</span>
              </div>
              <div class="train-seats">
                <span class="seats-label">Kursi:</span>
                <span class="seats-list">{{ seats.length ? seats.join(', ') : '-' }}</span>
              </div>
            </div>
            <span class="train-class">{{ trainClass }}</span>
          </div>
          <div class="detail-value">
            <span class="qty">{{ passengers }} tiket</span>
            <span class="price">Rp {{ ticketPrice.toLocaleString('id-ID') }}</span>
          </div>
        </div>

        <div class="divider"></div>

        <!-- Platform Fee -->
        <div class="detail-item">
          <div class="detail-label">
            <span class="fee-label">Platform Fee</span>
          </div>
          <div class="detail-value">Rp {{ platformFee.toLocaleString('id-ID') }}</div>
        </div>

        <div class="divider"></div>

        <!-- Total Payment -->
        <div class="total-payment">
          <span class="total-label">Total Pembayaran</span>
          <span class="total-amount">Rp {{ finalTotal.toLocaleString('id-ID') }}</span>
        </div>
      </div>
    </main>

    <!-- Payment Button -->
    <div class="sticky-actions">
      <button 
        v-if="!paymentStatus"
        class="pay-button" 
        @click="processPaymentTransaction"
        :disabled="isProcessing"
      >
        <span v-if="isProcessing" class="spinner-small"></span>
        {{ isProcessing ? 'Memproses...' : 'LANJUTKAN PEMBAYARAN' }}
      </button>
      <div v-else-if="paymentStatus === 'success'" class="status-success">
        <div class="success-icon">✓</div>
        <div class="success-message">Pembayaran Berhasil!</div>
        <p class="success-subtext">Terima kasih, pesanan Anda telah dikonfirmasi</p>
        <button class="etiket-button" @click="goToEtiket">
          LIHAT ETIKET & DETAIL PESANAN
        </button>
      </div>
      <div v-else class="status-message error">
        ✗ Pembayaran gagal. {{ errorMessage }}
      </div>
    </div>
  </div>

  <!-- QR Code Modal untuk QRIS -->
  <div v-if="showQRModal && paymentMethod === 'qris'" class="qr-modal-overlay" @click.self="showQRModal = false">
    <div class="qr-modal-content">
      <div class="qr-modal-header">
        <h2>Pembayaran QRIS</h2>
        <button class="qr-close-btn" @click="showQRModal = false">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>
      </div>
      <div class="qr-modal-body">
        <div class="qr-code-container">
          <img v-if="qrCodeImage" :src="qrCodeImage" alt="QRIS QR Code" class="qr-code-image" />
        </div>
        <div class="qr-info">
          <p class="qr-amount">Total Pembayaran: <strong>Rp {{ total.toLocaleString('id-ID') }}</strong></p>
          <p class="qr-instruction">Tunjukkan kode QR ini ke kasir atau pindai dengan aplikasi pembayaran Anda</p>
          <p class="qr-order">No. Pesanan: {{ orderNumber }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Header */
.header {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(22, 117, 231, 0.15);
  position: sticky;
  top: 0;
  z-index: 100;
}

.back-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.header-title {
  font-size: 20px;
  font-weight: 600;
  margin: 0;
}

/* Layout */
.checkout-page {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: #f8f9fa;
}

.content-wrapper {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
  padding-bottom: 100px; 
}

/* Payment Method Card */
.payment-method-card {
  background: white;
  padding: 16px;
  border-radius: 12px;
  border: 1px solid rgba(14, 23, 36, 0.06);
  margin-bottom: 12px;
}

.method-header {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-light);
  margin-bottom: 12px;
}

.method-display {
  display: flex;
  align-items: center;
  gap: 12px;
}

.method-icon {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: white;
  border-radius: 10px;
  flex-shrink: 0;
}

.method-info {
  flex: 1;
}

.method-name {
  font-weight: 700;
  font-size: 15px;
  color: var(--color-text);
}

.method-sub {
  font-size: 12px;
  color: var(--color-text-light);
  margin-top: 4px;
}

.change-btn {
  background: transparent;
  border: 1px solid var(--color-primary);
  color: var(--color-primary);
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.change-btn:hover {
  background: rgba(22, 117, 231, 0.1);
}

.qris-logo {
  width: 24px;
  height: 24px;
  object-fit: contain;
}

/* Passenger Info Card */
.passenger-info-card {
  background: white;
  padding: 16px;
  border-radius: 12px;
  border: 1px solid rgba(14, 23, 36, 0.06);
  margin-bottom: 12px;
}

.info-header {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-light);
  margin-bottom: 12px;
}

.passenger-details {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.passenger-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.passenger-item .label {
  color: var(--color-text-light);
  font-weight: 500;
  font-size: 13px;
}

.passenger-item .value {
  color: var(--color-text);
  font-weight: 600;
  font-size: 14px;
}

.passenger-breakdown {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid rgba(14, 23, 36, 0.06);
}

.breakdown-item {
  background: rgba(22, 117, 231, 0.1);
  color: var(--color-primary);
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

/* Detail Harga Card */
.detail-harga-card {
  background: white;
  padding: 16px;
  border-radius: 12px;
  border: 1px solid rgba(14, 23, 36, 0.06);
  margin-bottom: 12px;
}

.detail-header {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-light);
  margin-bottom: 16px;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 12px;
}

.detail-label {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.train-info-block {
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex: 1;
}

.train-header {
  display: flex;
  align-items: center;
  gap: 8px;
}

.train-name {
  font-weight: 700;
  color: var(--color-text);
  font-size: 15px;
}

.train-number {
  font-size: 12px;
  color: var(--color-text-light);
  background: rgba(22, 117, 231, 0.1);
  padding: 2px 6px;
  border-radius: 4px;
}

.train-route-info {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
}

.route {
  color: var(--color-text);
  font-weight: 500;
}

.train-seats {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
}

.seats-label {
  color: var(--color-text-light);
  font-weight: 500;
}

.seats-list {
  color: var(--color-text);
  font-weight: 600;
}

.train-route {
  font-weight: 600;
  color: var(--color-text);
}

.train-class {
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: white;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  white-space: nowrap;
}

.fee-label {
  color: var(--color-text);
  font-weight: 500;
}

.info-icon {
  color: var(--color-text-light);
  cursor: help;
}

.detail-value {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}

.qty {
  font-size: 13px;
  color: var(--color-text-light);
}

.price {
  font-weight: 700;
  color: var(--color-text);
}

.divider {
  height: 1px;
  background: rgba(14, 23, 36, 0.06);
  margin: 12px 0;
}

/* Total Payment */
.total-payment {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: linear-gradient(135deg, rgba(22, 117, 231, 0.08), rgba(52, 156, 255, 0.08));
  border-radius: 10px;
  margin-top: 12px;
}

.total-label {
  font-weight: 600;
  color: var(--color-text);
}

.total-amount {
  font-size: 18px;
  font-weight: 800;
  color: var(--color-primary);
}

/* Procedures Card */
.procedures-card {
  background: white;
  padding: 16px;
  border-radius: 12px;
  border: 1px solid rgba(14, 23, 36, 0.06);
  margin-bottom: 12px;
}

.procedures-toggle {
  display: flex;
  align-items: center;
  gap: 12px;
  background: transparent;
  border: none;
  width: 100%;
  cursor: pointer;
  color: var(--color-text);
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s ease;
}

.procedures-toggle:hover {
  color: var(--color-primary);
}
.sticky-actions {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 12px 16px 16px;
  background: white;
  border-top: 1px solid rgba(14, 23, 36, 0.06);
  display: flex;
  gap: 12px;
}

.pay-button {
  flex: 1;
  padding: 16px;
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
  transition: all 0.3s ease;
}

.pay-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(22, 117, 231, 0.4);
}

.pay-button:active {
  transform: translateY(0);
}

.pay-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none !important;
}

.spinner-small {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-right: 8px;
  vertical-align: middle;
}

.status-message {
  flex: 1;
  padding: 16px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 14px;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
}

.status-message.success {
  background: #f0fdf4;
  color: #15803d;
  border: 1px solid #86efac;
}

.status-message.error {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fca5a5;
}

.status-success {
  flex: 1;
  padding: 20px 16px;
  border-radius: 10px;
  background: linear-gradient(135deg, #f0fdf4 0%, #e0f8f1 100%);
  border: 2px solid #86efac;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.status-success .success-icon {
  width: 48px;
  height: 48px;
  background: #22c55e;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 28px;
  font-weight: bold;
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

.status-success .success-message {
  font-weight: 700;
  font-size: 16px;
  color: #15803d;
}

.status-success .success-subtext {
  font-size: 13px;
  color: #4b7e5f;
  margin: 0;
}

.etiket-button {
  width: 100%;
  padding: 14px 16px;
  background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
  transition: all 0.3s ease;
  margin-top: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.etiket-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(34, 197, 94, 0.4);
}

.etiket-button:active {
  transform: translateY(0);
}


/* Responsive */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 640px) {
  .content-wrapper {
    padding: 12px;
  }

  .header-title {
    font-size: 18px;
  }

  .detail-item {
    flex-direction: column;
    align-items: flex-start;
  }

  .detail-value {
    align-items: flex-start;
    width: 100%;
  }

  .total-amount {
    font-size: 16px;
  }
}

/* Payment Code Card Styles */
.payment-code-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  color: white;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.payment-code-card .title {
  font-size: 14px;
  font-weight: 500;
  opacity: 0.9;
  margin-bottom: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.payment-code-card .code-box {
  background: rgba(255, 255, 255, 0.15);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 8px;
  padding: 16px 12px;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-family: 'Courier New', monospace;
}

.payment-code-card .code {
  font-size: 20px;
  font-weight: 600;
  letter-spacing: 2px;
  flex: 1;
  user-select: all;
}

.payment-code-card .copy-btn {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.payment-code-card .copy-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
  transform: translateY(-2px);
}

.payment-code-card .copy-btn:active {
  transform: translateY(0);
}

.payment-code-card .copy-btn.copied {
  background: rgba(76, 175, 80, 0.3);
  border-color: rgba(76, 175, 80, 0.6);
}

.payment-code-card .instructions {
  font-size: 13px;
  opacity: 0.9;
  margin-bottom: 12px;
  line-height: 1.5;
}

.payment-code-card .amount {
  font-size: 12px;
  opacity: 0.8;
  padding: 8px 12px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  display: inline-block;
  margin-top: 4px;
}

.payment-code-card .amount strong {
  font-size: 14px;
  font-weight: 600;
}

/* QR Modal Styles */
.qr-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
  animation: fadeIn 0.3s ease;
}

.qr-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
  animation: fadeIn 0.3s ease;
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
    transform: translateY(30px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.qr-modal .content {
  background: white;
  border-radius: 16px;
  padding: 32px;
  max-width: 400px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  text-align: center;
  animation: slideUp 0.3s ease;
  position: relative;
}

.qr-modal-content {
  background: white;
  border-radius: 16px;
  padding: 32px;
  max-width: 400px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  text-align: center;
  animation: slideUp 0.3s ease;
  position: relative;
}

.qr-modal .close-btn {
  position: absolute;
  top: 12px;
  right: 12px;
  background: #f5f5f5;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  color: #666;
  transition: all 0.2s ease;
}

.qr-close-btn {
  position: absolute;
  top: 12px;
  right: 12px;
  background: #f5f5f5;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  color: #666;
  transition: all 0.2s ease;
}

.qr-close-btn:hover {
  background: #e0e0e0;
  transform: rotate(90deg);
}

.qr-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.qr-modal-header h2 {
  font-size: 18px;
  font-weight: 600;
  color: #222;
  margin: 0;
}

.qr-modal-body {
  text-align: center;
}

.qr-code-container {
  background: #f9f9f9;
  border: 2px solid #efefef;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.qr-code-image {
  max-width: 280px;
  width: 100%;
  height: auto;
}

.qr-info {
  background: #f0f7ff;
  border-left: 4px solid #2196F3;
  padding: 16px;
  border-radius: 8px;
  text-align: center;
}

.qr-amount {
  font-size: 14px;
  font-weight: 600;
  color: #222;
  margin: 0 0 8px 0;
}

.qr-amount strong {
  color: #2196F3;
  font-size: 16px;
}

.qr-instruction {
  font-size: 12px;
  color: #666;
  margin: 8px 0;
}

.qr-order {
  font-size: 12px;
  color: #2196F3;
  font-weight: 500;
  margin: 8px 0 0 0;
}

.qr-modal .title {
  font-size: 18px;
  font-weight: 600;
  color: #222;
  margin-bottom: 8px;
}

.qr-modal .subtitle {
  font-size: 13px;
  color: #999;
  margin-bottom: 24px;
}

.qr-modal .qr-container {
  background: #f9f9f9;
  border: 2px solid #efefef;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.qr-modal .qr-image {
  max-width: 100%;
  height: auto;
}

.qr-modal .qr-info {
  background: #f0f7ff;
  border-left: 4px solid #2196F3;
  padding: 12px;
  border-radius: 4px;
  margin-bottom: 16px;
  text-align: left;
  font-size: 13px;
}

.qr-modal .qr-info .info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  color: #333;
}

.qr-modal .qr-info .info-row:last-child {
  margin-bottom: 0;
}

.qr-modal .qr-info .label {
  font-weight: 500;
  color: #666;
}

.qr-modal .qr-info .value {
  font-weight: 600;
  color: #2196F3;
}

.qr-modal .action-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  width: 100%;
  transition: all 0.3s ease;
}

.qr-modal .action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.qr-modal .action-btn:active {
  transform: translateY(0);
}

@media (max-width: 640px) {
  .qr-modal .content {
    padding: 24px;
  }

  .qr-modal .title {
    font-size: 16px;
  }

  .qr-modal .qr-container {
    padding: 12px;
  }

  .payment-code-card {
    padding: 16px;
  }

  .payment-code-card .code {
    font-size: 16px;
  }

  .payment-code-card .code-box {
    padding: 12px;
  }
}
</style>
