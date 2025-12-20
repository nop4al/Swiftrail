<script setup>
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

// Parse query params from Checkout
const orderNumber = route.query.orderNumber || ''
const total = Number(route.query.total || 0)
const paymentMethod = route.query.paymentMethod || 'swiftpay'
const trainName = route.query.trainName || ''
const trainNumber = route.query.trainNumber || ''
const trainClass = route.query.trainClass || 'Eksekutif'
const fromStation = route.query.fromStation || ''
const toStation = route.query.toStation || ''
const date = route.query.date || ''
const departure = route.query.departure || ''
const arrival = route.query.arrival || ''
const seats = computed(() => {
  const seatsString = route.query.seats || ''
  if (!seatsString) return []
  return seatsString.split(',').map(s => s.trim()).filter(s => s)
})
const passengerName = route.query.passengerName || ''

const paymentMethodDisplay = computed(() => {
  if (paymentMethod === 'swiftpay') return 'SwiftPay'
  if (paymentMethod === 'qris') return 'QRIS'
  if (paymentMethod === 'ib') return `Internet Banking`
  return 'Metode Pembayaran'
})

const viewTicket = () => {
  // Navigate to Etiket (ticket) page
  router.push({
    path: '/etiket',
    query: {
      orderNumber,
      trainName,
      trainNumber,
      trainClass,
      fromStation,
      toStation,
      date,
      departure,
      arrival,
      seats: route.query.seats,
      passengerName,
      total
    }
  })
}

const backToHome = () => {
  router.push({ path: '/' })
}
</script>

<template>
  <div class="success-page">
    <!-- Header with success icon -->
    <div class="success-header">
      <div class="success-icon">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/>
        </svg>
      </div>
      <h1 class="success-title">Pembayaran Berhasil!</h1>
      <p class="success-subtitle">Tiket Anda telah dipesan dengan sukses</p>
    </div>

    <!-- Main content -->
    <main class="content-wrapper">
      <!-- Order Number Section -->
      <div class="order-card">
        <div class="order-label">Nomor Pesanan</div>
        <div class="order-number">{{ orderNumber }}</div>
      </div>

      <!-- Payment Details -->
      <div class="details-card">
        <div class="card-title">Detail Pembayaran</div>
        
        <div class="detail-row">
          <span class="label">Metode Pembayaran:</span>
          <span class="value">{{ paymentMethodDisplay }}</span>
        </div>
        
        <div class="detail-row">
          <span class="label">Total Pembayaran:</span>
          <span class="value amount">Rp {{ total.toLocaleString('id-ID') }}</span>
        </div>

        <div class="detail-row">
          <span class="label">Status:</span>
          <span class="value status-success">Berhasil</span>
        </div>
      </div>

      <!-- Booking Details -->
      <div class="details-card">
        <div class="card-title">Detail Pemesanan</div>
        
        <div class="detail-row">
          <span class="label">Kereta:</span>
          <span class="value">{{ trainName }} #{{ trainNumber }}</span>
        </div>

        <div class="detail-row">
          <span class="label">Rute:</span>
          <span class="value">{{ fromStation }} → {{ toStation }}</span>
        </div>

        <div class="detail-row">
          <span class="label">Tanggal Keberangkatan:</span>
          <span class="value">{{ date }} • {{ departure }}</span>
        </div>

        <div class="detail-row">
          <span class="label">Kelas:</span>
          <span class="value class-badge">{{ trainClass }}</span>
        </div>

        <div class="detail-row">
          <span class="label">Kursi:</span>
          <span class="value seats-list">{{ seats.join(', ') }}</span>
        </div>

        <div class="detail-row">
          <span class="label">Nama Penumpang:</span>
          <span class="value">{{ passengerName }}</span>
        </div>
      </div>

      <!-- Important Notes -->
      <div class="notes-card">
        <div class="notes-title">Informasi Penting</div>
        <div class="note-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          <span>Tiket telah dikirim ke email Anda</span>
        </div>
        <div class="note-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          <span>Harap tiba 30 menit sebelum keberangkatan</span>
        </div>
        <div class="note-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          <span>Bawa identitas asli pada saat check-in</span>
        </div>
      </div>
    </main>

    <!-- Action Buttons -->
    <div class="action-buttons">
      <button class="btn-primary" @click="viewTicket">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
          <polyline points="14 2 14 8 20 8"></polyline>
          <line x1="12" y1="19" x2="12" y2="11"></line>
          <polyline points="9 14 12 11 15 14"></polyline>
        </svg>
        Lihat Tiket
      </button>
      <button class="btn-secondary" @click="backToHome">Kembali ke Beranda</button>
    </div>
  </div>
</template>

<style scoped>
.success-page {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: #f8f9fa;
}

/* Success Header */
.success-header {
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  color: white;
  padding: 40px 16px;
  text-align: center;
  position: relative;
}

.success-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  animation: scaleIn 0.6s ease-out;
}

.success-icon svg {
  color: white;
}

.success-title {
  font-size: 28px;
  font-weight: 800;
  margin: 0 0 8px;
}

.success-subtitle {
  font-size: 14px;
  opacity: 0.9;
  margin: 0;
}

/* Content */
.content-wrapper {
  flex: 1;
  padding: 20px 16px;
  overflow-y: auto;
}

/* Order Card */
.order-card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  border: 2px solid var(--color-primary);
  margin-bottom: 16px;
  text-align: center;
}

.order-label {
  font-size: 12px;
  color: var(--color-text-light);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
}

.order-number {
  font-size: 24px;
  font-weight: 800;
  color: var(--color-primary);
  font-family: 'Courier New', monospace;
  letter-spacing: 2px;
}

/* Details Cards */
.details-card {
  background: white;
  padding: 16px;
  border-radius: 12px;
  border: 1px solid rgba(14, 23, 36, 0.06);
  margin-bottom: 12px;
}

.card-title {
  font-size: 14px;
  font-weight: 700;
  color: var(--color-text);
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 2px solid rgba(22, 117, 231, 0.1);
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
  font-size: 14px;
}

.detail-row:last-child {
  margin-bottom: 0;
}

.label {
  color: var(--color-text-light);
  font-weight: 500;
}

.value {
  color: var(--color-text);
  font-weight: 600;
  text-align: right;
}

.value.amount {
  font-size: 16px;
  color: var(--color-primary);
  font-weight: 800;
}

.status-success {
  background: #e8f5e9;
  color: #2e7d32;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 700;
}

.class-badge {
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: white;
  padding: 4px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
}

.seats-list {
  font-family: 'Courier New', monospace;
  background: rgba(22, 117, 231, 0.05);
  padding: 4px 8px;
  border-radius: 4px;
}

/* Notes Card */
.notes-card {
  background: white;
  padding: 16px;
  border-radius: 12px;
  border: 1px solid rgba(14, 23, 36, 0.06);
  margin-bottom: 16px;
}

.notes-title {
  font-size: 14px;
  font-weight: 700;
  color: var(--color-text);
  margin-bottom: 12px;
}

.note-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 10px;
  font-size: 13px;
  color: var(--color-text-light);
}

.note-item:last-child {
  margin-bottom: 0;
}

.note-item svg {
  color: #4caf50;
  flex-shrink: 0;
  margin-top: 2px;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 12px;
  padding: 16px;
  background: white;
  border-top: 1px solid rgba(14, 23, 36, 0.06);
}

.btn-primary,
.btn-secondary {
  flex: 1;
  padding: 14px 16px;
  border: none;
  border-radius: 10px;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-primary {
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: white;
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(22, 117, 231, 0.4);
}

.btn-secondary {
  background: transparent;
  border: 1.5px solid var(--color-primary);
  color: var(--color-primary);
}

.btn-secondary:hover {
  background: rgba(22, 117, 231, 0.1);
}

/* Animation */
@keyframes scaleIn {
  from {
    transform: scale(0);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

/* Responsive */
@media (max-width: 640px) {
  .success-header {
    padding: 30px 16px;
  }

  .success-title {
    font-size: 24px;
  }

  .action-buttons {
    flex-direction: column;
  }
}
</style>
