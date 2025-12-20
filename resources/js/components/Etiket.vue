<template>
  <div class="etiket-container">
    <!-- Header -->
    <header class="header">
      <div class="header-content">
        <button class="back-btn" @click="backToSuccess">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <h1>Tiket Kereta</h1>
        <button class="download-btn" @click="downloadTicket">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M21 15V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V15M7 10L12 15M12 15L17 10M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Unduh
        </button>
      </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Loading State -->
      <div v-if="isLoading" class="loading-container">
        <div class="spinner"></div>
        <p>Memuat tiket Anda...</p>
      </div>

      <!-- Ticket Card -->
      <div v-else class="ticket-card" ref="ticketRef">
        <!-- Ticket Header (Top Part - Colored) -->
        <div class="ticket-header">
          <div class="ticket-logo">
            <h2>SwiftRail</h2>
            <p class="tagline">Perjalanan Nyaman, Harga Terjangkau</p>
          </div>
          <div class="ticket-type">
            <span>TIKET KERETA</span>
          </div>
        </div>

        <!-- Order Number -->
        <div class="order-section">
          <div class="label">Nomor Pesanan</div>
          <div class="barcode">{{ orderNumber }}</div>
        </div>

        <!-- Route Section -->
        <div class="route-section">
          <div class="station-info">
            <div class="station">
              <div class="time">{{ departure }}</div>
              <div class="station-name">{{ fromStation }}</div>
            </div>
            <div class="route-line">
              <svg width="40" height="2" viewBox="0 0 40 2" xmlns="http://www.w3.org/2000/svg">
                <line x1="0" y1="1" x2="40" y2="1" stroke="currentColor" stroke-width="2" stroke-dasharray="5,5"/>
              </svg>
              <div class="train-icon">🚂</div>
            </div>
            <div class="station">
              <div class="time">{{ arrival }}</div>
              <div class="station-name">{{ toStation }}</div>
            </div>
          </div>
        </div>

        <!-- Divider -->
        <div class="divider"></div>

        <!-- Ticket Details Grid -->
        <div class="details-grid">
          <div class="detail-item">
            <div class="label">Kereta</div>
            <div class="value">{{ trainName }} #{{ trainNumber }}</div>
          </div>

          <div class="detail-item">
            <div class="label">Tanggal Keberangkatan</div>
            <div class="value">{{ formatDate(date) }}</div>
          </div>

          <div class="detail-item">
            <div class="label">Kelas</div>
            <div class="value class-badge">{{ trainClass }}</div>
          </div>

          <div class="detail-item">
            <div class="label">Kursi</div>
            <div class="value seats-info">{{ seats }}</div>
          </div>

          <div class="detail-item">
            <div class="label">Nama Penumpang</div>
            <div class="value">{{ passengerName }}</div>
          </div>

          <div class="detail-item">
            <div class="label">Total Pembayaran</div>
            <div class="value amount">Rp {{ total.toLocaleString('id-ID') }}</div>
          </div>
        </div>

        <!-- Divider -->
        <div class="divider"></div>

        <!-- QR Code Section -->
        <div class="qr-section">
          <div class="label-center">Kode QR Tiket</div>
          <div class="qr-container">
            <div class="qr-placeholder">
              <svg width="120" height="120" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                <rect x="10" y="10" width="100" height="100" fill="white" stroke="#333" stroke-width="2"/>
                <rect x="15" y="15" width="20" height="20" fill="#333"/>
                <rect x="85" y="15" width="20" height="20" fill="#333"/>
                <rect x="15" y="85" width="20" height="20" fill="#333"/>
                <g opacity="0.3">
                  <rect x="40" y="40" width="8" height="8" fill="#333"/>
                  <rect x="50" y="40" width="8" height="8" fill="#333"/>
                  <rect x="40" y="50" width="8" height="8" fill="#333"/>
                  <rect x="55" y="50" width="8" height="8" fill="#333"/>
                </g>
              </svg>
            </div>
          </div>
        </div>

        <!-- Important Notes -->
        <div class="notes-section">
          <div class="label-center">Informasi Penting</div>
          <div class="notes-list">
            <div class="note">
              <span class="note-icon">✓</span>
              <span>Tunjukkan tiket ini kepada petugas stasiun saat check-in</span>
            </div>
            <div class="note">
              <span class="note-icon">✓</span>
              <span>Hadir 30 menit sebelum keberangkatan</span>
            </div>
            <div class="note">
              <span class="note-icon">✓</span>
              <span>Bawa identitas asli dan boarding pass</span>
            </div>
            <div class="note">
              <span class="note-icon">✓</span>
              <span>Pantau status perjalanan di aplikasi SwiftRail</span>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="ticket-footer">
          <p>Terima kasih telah menggunakan SwiftRail</p>
          <p class="footer-note">Simpan tiket ini untuk referensi perjalanan Anda</p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="action-buttons">
        <button class="btn btn-primary" @click="shareTicket">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="18" cy="5" r="3" stroke="currentColor" stroke-width="2"/>
            <circle cx="6" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
            <circle cx="18" cy="19" r="3" stroke="currentColor" stroke-width="2"/>
            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="currentColor" stroke-width="2"/>
            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="currentColor" stroke-width="2"/>
          </svg>
          Bagikan Tiket
        </button>
        <button class="btn btn-secondary" @click="backToSuccess">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 12H21M3 12L11 4M3 12L11 20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Kembali
        </button>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { showSuccess, showError } from '@/utils/notification'

const route = useRoute()
const router = useRouter()
const ticketRef = ref(null)
const isLoading = ref(true)

// Data dari query atau API
const orderNumber = ref(route.query.orderNumber || 'ORD-0000000')
const trainName = ref(route.query.trainName || '-')
const trainNumber = ref(route.query.trainNumber || '-')
const trainClass = ref(route.query.trainClass || '-')
const fromStation = ref(route.query.fromStation || '-')
const toStation = ref(route.query.toStation || '-')
const date = ref(route.query.date || '')
const departure = ref(route.query.departure || '-')
const arrival = ref(route.query.arrival || '-')
const total = ref(parseInt(route.query.total) || 0)
const passengerName = ref(route.query.passengerName || 'Penumpang')
const ticketNumber = ref(route.query.ticketNumber || '')
const qrCode = ref(route.query.qrCode || '')
const status = ref(route.query.status || 'confirmed')
const seatNumber = ref(route.query.seat_number || '')

// Fetch booking data from API if bookingId is provided
const fetchBookingData = async () => {
  try {
    isLoading.value = true
    const bookingId = route.query.bookingId || route.params.id
    
    if (!bookingId) {
      console.warn('No booking ID provided, using query parameters')
      isLoading.value = false
      return
    }

    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token')
    const response = await fetch(`/api/v1/bookings/${bookingId}`, {
      headers: {
        'Authorization': token ? `Bearer ${token}` : ''
      }
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const result = await response.json()

    if (result.success && result.data) {
      const data = result.data
      orderNumber.value = data.booking_code || route.query.orderNumber
      ticketNumber.value = data.ticket_number
      trainName.value = data.train?.name || '-'
      trainNumber.value = data.train?.number || '-'
      trainClass.value = data.class || '-'
      fromStation.value = data.from_station?.name || '-'
      toStation.value = data.to_station?.name || '-'
      date.value = data.schedule?.travel_date || ''
      departure.value = data.schedule?.departure_time || '-'
      arrival.value = data.schedule?.arrival_time || '-'
      total.value = data.price || 0
      passengerName.value = data.passenger_name || 'Penumpang'
      qrCode.value = data.qr_code || ''
      status.value = data.status || 'confirmed'
      seatNumber.value = data.seat_number || ''
    }
    isLoading.value = false
  } catch (error) {
    console.error('Error fetching booking:', error)
    isLoading.value = false
  }
}

const seats = computed(() => {
  const seatsString = route.query.seats || seatNumber.value || ''
  if (!seatsString) return '-'
  return seatsString.split(',').map(s => s.trim()).filter(s => s).join(', ')
})

// Format date
const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
}

// Download ticket
const downloadTicket = async () => {
  try {
    // Simple browser print functionality
    window.print()
    showSuccess('Siap untuk diunduh. Pilih "Simpan sebagai PDF" pada dialog print.')
  } catch (error) {
    console.error('Download error:', error)
    showError(error, 'Gagal mengunduh tiket')
  }
}

// Share ticket
const shareTicket = async () => {
  try {
    const shareData = {
      title: 'SwiftRail - Tiket Kereta',
      text: `Nomor Pesanan: ${orderNumber.value}\nKereta: ${trainName.value} #${trainNumber.value}\nRute: ${fromStation.value} → ${toStation.value}`,
      url: window.location.href
    }
    
    if (navigator.share) {
      await navigator.share(shareData)
    } else {
      // Fallback: copy to clipboard
      const text = `SwiftRail Tiket\nNomor: ${orderNumber.value}\nKereta: ${trainName.value} #${trainNumber.value}\n${fromStation.value} → ${toStation.value}\n${departure.value} - ${arrival.value}`
      await navigator.clipboard.writeText(text)
      showSuccess('Tiket disalin ke clipboard!')
    }
  } catch (error) {
    console.error('Share error:', error)
  }
}

// Back to success page
const backToSuccess = () => {
  router.back()
}

onMounted(async () => {
  await fetchBookingData()
  window.scrollTo(0, 0)
})
</script>

<style scoped>
.etiket-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  flex-direction: column;
}

/* Header */
.header {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  padding: 16px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 600px;
  margin: 0 auto;
  width: 100%;
}

.header h1 {
  color: white;
  font-size: 18px;
  font-weight: 600;
  margin: 0;
}

.back-btn,
.download-btn {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

/* Loading State */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: white;
  text-align: center;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top: 4px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.loading-container p {
  font-size: 16px;
  font-weight: 500;
  margin: 0;
}


.back-btn:hover,
.download-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
}

/* Main Content */
.main-content {
  flex: 1;
  padding: 24px 20px;
  max-width: 600px;
  margin: 0 auto;
  width: 100%;
  overflow-y: auto;
}

/* Ticket Card */
.ticket-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  margin-bottom: 24px;
}

/* Ticket Header */
.ticket-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.ticket-logo h2 {
  font-size: 24px;
  font-weight: 700;
  margin: 0 0 4px 0;
}

.tagline {
  font-size: 11px;
  opacity: 0.9;
  margin: 0;
}

.ticket-type {
  background: rgba(255, 255, 255, 0.2);
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Order Section */
.order-section {
  padding: 20px 24px;
  border-bottom: 2px dashed #f0f0f0;
  text-align: center;
}

.label {
  font-size: 11px;
  color: #999;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
  margin-bottom: 8px;
}

.label-center {
  font-size: 11px;
  color: #999;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
  margin-bottom: 12px;
  text-align: center;
}

.barcode {
  font-family: 'Courier New', monospace;
  font-size: 20px;
  font-weight: 700;
  letter-spacing: 2px;
  color: #333;
  user-select: all;
}

/* Route Section */
.route-section {
  padding: 20px 24px;
  border-bottom: 2px dashed #f0f0f0;
}

.station-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.station {
  flex: 1;
  text-align: center;
}

.time {
  font-size: 20px;
  font-weight: 700;
  color: #333;
  line-height: 1;
}

.station-name {
  font-size: 12px;
  color: #666;
  margin-top: 4px;
  font-weight: 500;
}

.route-line {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  padding: 0 8px;
}

.train-icon {
  font-size: 20px;
}

/* Divider */
.divider {
  height: 2px;
  background: linear-gradient(90deg, transparent, #ddd, transparent);
  margin: 0;
}

/* Details Grid */
.details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  padding: 20px 24px;
  border-bottom: 2px dashed #f0f0f0;
}

.detail-item {
  display: flex;
  flex-direction: column;
}

.detail-item .label {
  margin-bottom: 6px;
}

.value {
  font-size: 14px;
  font-weight: 600;
  color: #333;
}

.class-badge {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  display: inline-block;
  text-align: center;
}

.seats-info {
  font-family: 'Courier New', monospace;
  background: rgba(102, 126, 234, 0.1);
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 13px;
}

.amount {
  color: #4CAF50;
  font-size: 16px;
}

/* QR Section */
.qr-section {
  padding: 20px 24px;
  border-bottom: 2px dashed #f0f0f0;
  text-align: center;
}

.qr-container {
  display: flex;
  justify-content: center;
  padding: 16px;
  background: #f9f9f9;
  border-radius: 8px;
}

.qr-placeholder {
  width: 140px;
  height: 140px;
  background: white;
  border: 2px solid #ddd;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Notes Section */
.notes-section {
  padding: 20px 24px;
  border-bottom: 2px dashed #f0f0f0;
}

.notes-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.note {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  font-size: 12px;
  color: #666;
  line-height: 1.4;
}

.note-icon {
  color: #4CAF50;
  font-weight: 700;
  flex-shrink: 0;
}

/* Footer */
.ticket-footer {
  padding: 20px 24px;
  text-align: center;
  background: #f9f9f9;
}

.ticket-footer p {
  font-size: 13px;
  color: #333;
  font-weight: 500;
  margin: 0 0 6px 0;
}

.footer-note {
  font-size: 11px;
  color: #999;
  font-weight: normal;
  margin: 0;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 40px;
}

.btn {
  padding: 14px 24px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-primary {
  background: white;
  color: #667eea;
  border: 2px solid white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.5);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.3);
  border-color: white;
}

/* Responsive */
@media (max-width: 640px) {
  .ticket-header {
    padding: 16px;
  }

  .ticket-logo h2 {
    font-size: 20px;
  }

  .details-grid {
    grid-template-columns: 1fr;
    padding: 16px;
  }

  .main-content {
    padding: 16px 12px;
  }

  .ticket-card {
    margin-bottom: 16px;
  }

  .action-buttons {
    gap: 10px;
  }
}

/* Print Styles */
@media print {
  .etiket-container {
    background: white;
  }

  .header,
  .action-buttons {
    display: none;
  }

  .main-content {
    padding: 0;
  }

  .ticket-card {
    box-shadow: none;
    margin-bottom: 0;
  }
}
</style>
