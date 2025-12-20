<template>
  <div class="my-ticket-page">
    <!-- Header -->
    <header class="header">
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
            <span class="logo-subtitle">SUPER APP</span>
          </div>
        </div>
        
        <nav class="nav">
          <router-link to="/profile" class="nav-link">Profile</router-link>
        </nav>
      </div>
    </header>

    <div class="ticket-container">
      <!-- Filter Tabs -->
      <div class="filter-tabs">
        <button 
          v-for="tab in filterTabs" 
          :key="tab.id"
          :class="['tab-btn', { active: activeFilter === tab.id }]"
          @click="activeFilter = tab.id"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Empty State -->
      <div v-if="filteredTickets.length === 0" class="empty-state">
        <div class="empty-icon">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75 3.54-2.16-2.66c-.44-.53-1.25-.58-1.78-.15-.53.43-.58 1.24-.15 1.77l3 3.73c.39.48.96.75 1.54.75s1.15-.27 1.54-.75l3.5-4.29c.43-.53.39-1.34-.15-1.78-.53-.44-1.34-.39-1.77.15z"/>
          </svg>
        </div>
        <h3>Belum Ada Tiket</h3>
        <p>{{ getEmptyMessage() }}</p>
        <router-link to="/explore" class="btn-primary">
          Pesan Tiket Sekarang
        </router-link>
      </div>

      <!-- Tickets List -->
      <div v-else class="tickets-list">
        <div v-for="ticket in filteredTickets" :key="ticket.id" class="ticket-card">
          <!-- Ticket Header with Status -->
          <div class="ticket-header">
            <div class="ticket-info">
              <div class="train-info">
                <h3 class="train-name">{{ ticket.trainName }}</h3>
                <p class="train-code">KA {{ ticket.trainCode }}</p>
              </div>
              <div class="ticket-date">
                <span class="date">{{ formatDate(ticket.departureDate) }}</span>
                <span class="time">{{ ticket.departureTime }}</span>
              </div>
            </div>
            <div :class="['status-badge', ticket.status.toLowerCase()]">
              {{ ticket.status }}
            </div>
          </div>

          <!-- Route Info -->
          <div class="route-section">
            <div class="station">
              <span class="station-name">{{ ticket.from }}</span>
              <span class="station-time">{{ ticket.departureTime }}</span>
            </div>
            <div class="route-line">
              <div class="line"></div>
              <span class="duration">{{ ticket.duration }}</span>
            </div>
            <div class="station">
              <span class="station-name">{{ ticket.to }}</span>
              <span class="station-time">{{ ticket.arrivalTime }}</span>
            </div>
          </div>

          <!-- Passenger & Class Info -->
          <div class="ticket-details">
            <div class="detail-item">
              <span class="detail-label">Penumpang</span>
              <span class="detail-value">{{ ticket.passengerName }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Kelas</span>
              <span class="detail-value">{{ ticket.class }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Kursi</span>
              <span class="detail-value">{{ ticket.seat }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Harga</span>
              <span class="detail-price">{{ formatPrice(ticket.price) }}</span>
            </div>
          </div>

          <!-- Ticket Number -->
          <div class="ticket-number">
            <span class="label">Nomor Tiket:</span>
            <span class="number">{{ ticket.ticketNumber }}</span>
          </div>

          <!-- Action Buttons -->
          <div class="ticket-actions">
            <button 
              v-if="ticket.status === 'Aktif'"
              class="btn-primary"
              @click="showETicket(ticket)"
            >
              E-Tiket & QR
            </button>
            <button 
              v-if="ticket.status === 'Aktif'"
              class="btn-secondary"
              @click="showTicketDetail(ticket)"
            >
              Lihat Detail
            </button>
            <button 
              v-if="ticket.status === 'Aktif'"
              class="btn-danger"
              @click="openRefundModal(ticket)"
            >
              Ajukan Refund
            </button>
            <button 
              v-if="ticket.status === 'Selesai'"
              class="btn-secondary"
              @click="showReviewModal(ticket)"
            >
              Beri Rating
            </button>
            <button 
              v-if="ticket.status === 'Dibatalkan'"
              class="btn-secondary"
              @click="showTicketDetail(ticket)"
            >
              Lihat Detail
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="filteredTickets.length > 0" class="pagination">
        <button 
          :disabled="currentPage === 1"
          @click="currentPage--"
          class="pagination-btn"
        >
          ← Sebelumnya
        </button>
        <span class="page-info">Halaman {{ currentPage }} dari {{ totalPages }}</span>
        <button 
          :disabled="currentPage === totalPages"
          @click="currentPage++"
          class="pagination-btn"
        >
          Berikutnya →
        </button>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showDetail" class="modal-overlay" @click="showDetail = false">
      <div class="modal-content modal-lg" @click.stop>
        <div class="modal-header">
          <h2>Detail Tiket</h2>
          <button class="close-btn" @click="showDetail = false">×</button>
        </div>
        <div class="modal-body">
          <div v-if="selectedTicket" class="detail-content">
            <!-- Train Info -->
            <div class="detail-section">
              <h3>Informasi Kereta</h3>
              <div class="detail-grid">
                <div class="detail-item-full">
                  <span class="detail-label">Nama Kereta</span>
                  <span class="detail-value-large">{{ selectedTicket.trainName }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Kode Kereta</span>
                  <span class="detail-value">KA {{ selectedTicket.trainCode }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Status</span>
                  <span :class="['detail-value', 'status-' + selectedTicket.status.toLowerCase()]">{{ selectedTicket.status }}</span>
                </div>
              </div>
            </div>

            <!-- Route Info -->
            <div class="detail-section">
              <h3>Rute Perjalanan</h3>
              <div class="route-detail">
                <div class="station-detail">
                  <span class="station-label">Keberangkatan</span>
                  <span class="station-name">{{ selectedTicket.from }}</span>
                  <span class="station-time">{{ selectedTicket.departureTime }}</span>
                  <span class="station-date">{{ formatDate(selectedTicket.departureDate) }}</span>
                </div>
                <div class="route-arrow">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                  </svg>
                  <span>{{ selectedTicket.duration }}</span>
                </div>
                <div class="station-detail">
                  <span class="station-label">Tiba</span>
                  <span class="station-name">{{ selectedTicket.to }}</span>
                  <span class="station-time">{{ selectedTicket.arrivalTime }}</span>
                </div>
              </div>
            </div>

            <!-- Passenger Info -->
            <div class="detail-section">
              <h3>Data Penumpang</h3>
              <div class="detail-grid">
                <div class="detail-item-full">
                  <span class="detail-label">Nama Penumpang</span>
                  <span class="detail-value-large">{{ selectedTicket.passengerName }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Kelas</span>
                  <span class="detail-value">{{ selectedTicket.class }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Kursi</span>
                  <span class="detail-value">{{ selectedTicket.seat }}</span>
                </div>
              </div>
            </div>

            <!-- Price Info -->
            <div class="detail-section">
              <h3>Harga</h3>
              <div class="price-box">
                <span class="price-label">Total Harga:</span>
                <span class="price-value">{{ formatPrice(selectedTicket.price) }}</span>
              </div>
            </div>

            <!-- Ticket Number -->
            <div class="detail-section">
              <h3>Nomor Tiket</h3>
              <div class="ticket-number-box">
                <span class="ticket-number-text">{{ selectedTicket.ticketNumber }}</span>
              </div>
            </div>

            <div class="form-actions">
              <button class="btn-primary" @click="showDetail = false">Tutup</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- E-Ticket Modal -->
    <div v-if="showETicketModal" class="modal-overlay-fullscreen" @click="showETicketModal = false">
      <div class="eticket-modal-wrapper" @click.stop>
        <!-- Close Button -->
        <button class="close-btn-fullscreen" @click="showETicketModal = false">✕</button>

        <!-- E-Ticket Container -->
        <div class="eticket-modal-content">
          <div class="boarding-pass-modal">
            <!-- Top Section -->
            <div class="pass-header-modal">
              <h1>Boarding Pass</h1>
            </div>

            <!-- Main Content -->
            <div class="pass-content-modal">
              <!-- Logos Section -->
              <div class="logos-section-modal">
                <div class="logo-item">
                  <svg width="50" height="35" viewBox="0 0 50 35" fill="none">
                    <rect width="50" height="35" rx="4" fill="#1675E7"/>
                    <text x="25" y="20" font-size="16" font-weight="bold" fill="white" text-anchor="middle">SR</text>
                  </svg>
                  <span>SwiftRail</span>
                </div>
                <div class="logo-item">
                  <svg width="50" height="35" viewBox="0 0 50 35" fill="none">
                    <circle cx="25" cy="17" r="16" fill="#10B981"/>
                    <text x="25" y="22" font-size="12" font-weight="bold" fill="white" text-anchor="middle">80%</text>
                  </svg>
                  <span>Selamat Melayani</span>
                </div>
              </div>

              <!-- Train Info -->
              <div class="train-info-modal">
                <h2>{{ selectedTicket.trainName }}</h2>
                <p>Kode pemesanan: {{ selectedTicket.ticketNumber }}</p>
              </div>

              <!-- Route -->
              <div class="route-section-modal">
                <div class="route-item">
                  <span class="code">{{ getStationCode(selectedTicket.from) }}</span>
                  <span class="name">{{ selectedTicket.from }}</span>
                </div>
                <div class="route-divider">
                  <span>🚂</span>
                  <span>{{ selectedTicket.duration }}</span>
                </div>
                <div class="route-item">
                  <span class="code">{{ getStationCode(selectedTicket.to) }}</span>
                  <span class="name">{{ selectedTicket.to }}</span>
                </div>
              </div>

              <!-- Times -->
              <div class="times-section-modal">
                <div class="time-item">
                  <span class="label">Keberangkatan</span>
                  <span class="time">{{ selectedTicket.departureTime }}</span>
                  <span class="date">{{ formatDate(selectedTicket.departureDate) }}</span>
                </div>
                <div class="time-divider"></div>
                <div class="time-item">
                  <span class="label">Tiba</span>
                  <span class="time">{{ selectedTicket.arrivalTime }}</span>
                  <span class="date">{{ formatDate(selectedTicket.departureDate) }}</span>
                </div>
              </div>

              <!-- Dashed Line -->
              <div class="dashed-line"></div>

              <!-- Passenger Info -->
              <div class="info-section-modal">
                <div class="info-row">
                  <div class="info-col">
                    <span class="label">Penumpang</span>
                    <span class="value">{{ selectedTicket.passengerName }}</span>
                  </div>
                  <div class="info-col">
                    <span class="label">NIK</span>
                    <span class="value">3572024605050001</span>
                  </div>
                </div>
                <div class="info-row">
                  <div class="info-col">
                    <span class="label">Tipe Penumpang</span>
                    <span class="value">UMUM</span>
                  </div>
                  <div class="info-col">
                    <span class="label">No</span>
                    <span class="value">/ 0 -</span>
                  </div>
                </div>
              </div>

              <!-- QR Code -->
              <div class="qr-section-modal">
                <p>Pindai kode ini di Gerbang</p>
                <div class="qr-code-modal" ref="eticketQRCode"></div>
                <p class="qr-info">{{ selectedTicket.ticketNumber }}<br/>Dicetak: {{ formatDateTime(new Date().toISOString()) }}</p>
              </div>

              <!-- Buttons -->
              <div class="eticket-actions-modal">
                <button class="btn-print-modal" @click="printETicket">
                  🖨️ Cetak
                </button>
                <button class="btn-share-modal" @click="shareETicket">
                  📤 Bagikan
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Rating Modal -->
    <div v-if="showRating" class="modal-overlay" @click="showRating = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Beri Rating & Ulasan</h2>
          <button class="close-btn" @click="showRating = false">×</button>
        </div>
        <div class="modal-body">
          <div v-if="selectedTicket" class="rating-content">
            <div class="train-info-box">
              <h4>{{ selectedTicket.trainName }} (KA {{ selectedTicket.trainCode }})</h4>
              <p class="info-text">{{ selectedTicket.from }} → {{ selectedTicket.to }}</p>
            </div>

            <!-- Star Rating -->
            <div class="form-group">
              <label>Rating Kereta</label>
              <div class="star-rating">
                <button 
                  v-for="star in 5" 
                  :key="star"
                  :class="['star', { filled: star <= ratingValue }]"
                  @click="ratingValue = star"
                  @mouseover="hoverRating = star"
                  @mouseleave="hoverRating = 0"
                >
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                  </svg>
                </button>
                <span v-if="ratingValue > 0" class="rating-text">{{ getRatingText(ratingValue) }}</span>
              </div>
            </div>

            <!-- Cleanliness -->
            <div class="form-group">
              <label>Kebersihan Kereta</label>
              <div class="option-buttons">
                <button 
                  v-for="option in ['Sangat Kotor', 'Kotor', 'Biasa', 'Bersih', 'Sangat Bersih']"
                  :key="option"
                  :class="['option-btn', { selected: cleanliness === option }]"
                  @click="cleanliness = option"
                >
                  {{ option }}
                </button>
              </div>
            </div>

            <!-- Service -->
            <div class="form-group">
              <label>Kualitas Pelayanan</label>
              <div class="option-buttons">
                <button 
                  v-for="option in ['Buruk', 'Kurang', 'Cukup', 'Bagus', 'Luar Biasa']"
                  :key="option"
                  :class="['option-btn', { selected: service === option }]"
                  @click="service = option"
                >
                  {{ option }}
                </button>
              </div>
            </div>

            <!-- Review Text -->
            <div class="form-group">
              <label>Ulasan Anda</label>
              <textarea 
                v-model="reviewText" 
                class="form-input"
                placeholder="Bagikan pengalaman Anda selama perjalanan..."
                rows="5"
              ></textarea>
              <span class="char-count">{{ reviewText.length }}/500</span>
            </div>

            <div class="form-actions">
              <button class="btn-secondary" @click="showRating = false">Batal</button>
              <button class="btn-primary" @click="submitRating">Kirim Rating</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Refund Modal -->
    <div v-if="showRefund" class="modal-overlay" @click="showRefund = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Ajukan Refund</h2>
          <button class="close-btn" @click="showRefund = false">×</button>
        </div>
        <div class="modal-body">
          <div v-if="selectedTicket" class="refund-info">
            <h4>{{ selectedTicket.trainName }} (KA {{ selectedTicket.trainCode }})</h4>
            <p class="info-text">{{ selectedTicket.from }} → {{ selectedTicket.to }}</p>
            <p class="refund-amount">Jumlah Refund: <strong>{{ formatPrice(selectedTicket.price) }}</strong></p>
          </div>

          <div class="form-group">
            <label>Alasan Pembatalan</label>
            <select v-model="refundReason" class="form-input">
              <option value="">Pilih alasan...</option>
              <option value="emergency">Keperluan Mendesak</option>
              <option value="illness">Sakit</option>
              <option value="change-plan">Berubah Rencana</option>
              <option value="technical">Masalah Teknis</option>
              <option value="other">Lainnya</option>
            </select>
          </div>

          <div class="form-group">
            <label>Keterangan Tambahan</label>
            <textarea 
              v-model="refundNotes" 
              class="form-input"
              placeholder="Jelaskan alasan pembatalan Anda..."
              rows="4"
            ></textarea>
          </div>

          <div class="form-actions">
            <button class="btn-secondary" @click="showRefund = false">Batal</button>
            <button class="btn-primary" @click="submitRefund">Ajukan Refund</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import QRCode from 'qrcode';

const router = useRouter();

const activeFilter = ref('all');
const currentPage = ref(1);
const itemsPerPage = 5;
const eticketQRCode = ref(null);

const filterTabs = [
  { id: 'all', label: 'Semua Tiket' },
  { id: 'active', label: 'Aktif' },
  { id: 'completed', label: 'Selesai' },
  { id: 'cancelled', label: 'Dibatalkan' }
];

// Sample ticket data
const tickets = reactive([
  {
    id: 1,
    trainName: 'Argo Bromo Anggrek',
    trainCode: '2',
    from: 'Jakarta Kota',
    to: 'Surabaya Pasar Turi',
    departureDate: '2025-12-20',
    departureTime: '08:20',
    arrivalTime: '16:25',
    duration: '8h 5m',
    passengerName: 'Muhammad Rizki',
    class: 'Eksekutif',
    seat: 'A12',
    price: 450000,
    ticketNumber: 'SR-2025-001234',
    status: 'Aktif'
  },
  {
    id: 2,
    trainName: 'Gajayana',
    trainCode: '62',
    from: 'Jakarta Kota',
    to: 'Malang',
    departureDate: '2025-11-15',
    departureTime: '07:00',
    arrivalTime: '17:30',
    duration: '10h 30m',
    passengerName: 'Muhammad Rizki',
    class: 'Bisnis',
    seat: 'B24',
    price: 380000,
    ticketNumber: 'SR-2025-001233',
    status: 'Selesai'
  },
  {
    id: 3,
    trainName: 'Mutiara Timur',
    trainCode: '100',
    from: 'Jakarta Gambir',
    to: 'Medan',
    departureDate: '2025-10-10',
    departureTime: '14:00',
    arrivalTime: '08:00',
    duration: '18h',
    passengerName: 'Muhammad Rizki',
    class: 'Eksekutif',
    seat: 'C5',
    price: 600000,
    ticketNumber: 'SR-2025-001232',
    status: 'Selesai'
  },
  {
    id: 4,
    trainName: 'Srikandi',
    trainCode: '50',
    from: 'Bandung',
    to: 'Jakarta Kota',
    departureDate: '2025-09-25',
    departureTime: '06:30',
    arrivalTime: '08:45',
    duration: '2h 15m',
    passengerName: 'Muhammad Rizki',
    class: 'Ekonomi',
    seat: 'D15',
    price: 150000,
    ticketNumber: 'SR-2025-001231',
    status: 'Dibatalkan'
  },
  {
    id: 5,
    trainName: 'Krakatau',
    trainCode: '201',
    from: 'Jakarta Kota',
    to: 'Yogyakarta',
    departureDate: '2025-12-28',
    departureTime: '20:00',
    arrivalTime: '06:00',
    duration: '10h',
    passengerName: 'Muhammad Rizki',
    class: 'Bisnis',
    seat: 'A8',
    price: 320000,
    ticketNumber: 'SR-2025-001230',
    status: 'Aktif'
  },
  {
    id: 6,
    trainName: 'Bima',
    trainCode: '30',
    from: 'Semarang',
    to: 'Surabaya',
    departureDate: '2025-08-15',
    departureTime: '12:00',
    arrivalTime: '16:00',
    duration: '4h',
    passengerName: 'Muhammad Rizki',
    class: 'Eksekutif',
    seat: 'E3',
    price: 250000,
    ticketNumber: 'SR-2025-001229',
    status: 'Selesai'
  }
]);

const showRefund = ref(false);
const showDetail = ref(false);
const showRating = ref(false);
const showETicketModal = ref(false);
const selectedTicket = ref(null);
const refundReason = ref('');
const refundNotes = ref('');
const ratingValue = ref(0);
const hoverRating = ref(0);
const cleanliness = ref('');
const service = ref('');
const reviewText = ref('');

const filteredTickets = computed(() => {
  let filtered = tickets;

  if (activeFilter.value === 'active') {
    filtered = tickets.filter(t => t.status === 'Aktif');
  } else if (activeFilter.value === 'completed') {
    filtered = tickets.filter(t => t.status === 'Selesai');
  } else if (activeFilter.value === 'cancelled') {
    filtered = tickets.filter(t => t.status === 'Dibatalkan');
  }

  return filtered.sort((a, b) => new Date(b.departureDate) - new Date(a.departureDate));
});

const totalPages = computed(() => Math.ceil(filteredTickets.value.length / itemsPerPage));

const paginatedTickets = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredTickets.value.slice(start, start + itemsPerPage);
});

function formatDate(dateStr) {
  const options = { weekday: 'short', month: 'short', day: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
}

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(price);
}

function getEmptyMessage() {
  if (activeFilter.value === 'active') {
    return 'Anda belum memiliki tiket aktif saat ini.';
  } else if (activeFilter.value === 'completed') {
    return 'Belum ada perjalanan yang selesai.';
  } else if (activeFilter.value === 'cancelled') {
    return 'Tidak ada tiket yang dibatalkan.';
  }
  return 'Belum ada tiket. Mulai pesan tiket sekarang!';
}

function showTicketDetail(ticket) {
  selectedTicket.value = ticket;
  showDetail.value = true;
}

function showETicket(ticket) {
  selectedTicket.value = ticket;
  showETicketModal.value = true;
}

// Generate QR code when E-Ticket modal is shown
watch(showETicketModal, (newVal) => {
  if (newVal && selectedTicket.value && eticketQRCode.value) {
    // Clear previous canvas if any
    eticketQRCode.value.innerHTML = '';
    
    // Generate new QR code
    QRCode.toCanvas(eticketQRCode.value, selectedTicket.value.ticketNumber, {
      errorCorrectionLevel: 'H',
      type: 'image/png',
      quality: 0.95,
      margin: 1,
      width: 180,
      color: {
        dark: '#000000',
        light: '#FFFFFF'
      }
    }, (error) => {
      if (error) console.error('QR Code Error:', error);
    });
  }
});

function openRefundModal(ticket) {
  selectedTicket.value = ticket;
  showRefund.value = true;
  refundReason.value = '';
  refundNotes.value = '';
}

function submitRefund() {
  if (!refundReason.value || !refundNotes.value) {
    alert('Harap lengkapi semua kolom');
    return;
  }

  console.log('Refund submitted:', {
    ticket: selectedTicket.value,
    reason: refundReason.value,
    notes: refundNotes.value
  });

  // Update ticket status
  const ticket = tickets.find(t => t.id === selectedTicket.value.id);
  if (ticket) {
    ticket.status = 'Dibatalkan';
  }

  showRefund.value = false;
  alert('Permohonan refund telah dikirim. Silakan tunggu persetujuan.');
}

function showReviewModal(ticket) {
  selectedTicket.value = ticket;
  showRating.value = true;
  ratingValue.value = 0;
  hoverRating.value = 0;
  cleanliness.value = '';
  service.value = '';
  reviewText.value = '';
}

function getRatingText(rating) {
  const texts = {
    1: 'Sangat Buruk',
    2: 'Buruk',
    3: 'Cukup',
    4: 'Bagus',
    5: 'Luar Biasa'
  };
  return texts[rating] || '';
}

function submitRating() {
  if (!ratingValue.value || !cleanliness.value || !service.value || !reviewText.value) {
    alert('Harap lengkapi semua field rating');
    return;
  }

  console.log('Rating submitted:', {
    ticket: selectedTicket.value,
    rating: ratingValue.value,
    cleanliness: cleanliness.value,
    service: service.value,
    review: reviewText.value
  });

  showRating.value = false;
  alert('Terima kasih! Rating dan ulasan Anda telah tersimpan.');
}

function formatDateTime(dateStr) {
  const date = new Date(dateStr);
  const dateStr2 = date.toLocaleDateString('id-ID', { 
    weekday: 'short', 
    month: 'long', 
    day: 'numeric',
    year: 'numeric'
  });
  const timeStr = date.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
  return `${dateStr2} ${timeStr}`;
}

function getStationCode(stationName) {
  const codes = {
    'Jakarta Kota': 'JKT',
    'Surabaya Pasar Turi': 'SBY',
    'Jakarta Gambir': 'JGB',
    'Yogyakarta': 'YK',
    'Bandung': 'BD',
    'Semarang': 'SMG',
    'Malang': 'MLG',
    'Medan': 'MDN'
  };
  return codes[stationName] || stationName.substring(0, 2).toUpperCase();
}

function printETicket() {
  window.print();
}

function shareETicket() {
  const text = `Saya telah memesan tiket ${selectedTicket.value.trainName} dari ${selectedTicket.value.from} ke ${selectedTicket.value.to} pada ${formatDate(selectedTicket.value.departureDate)}. Booking code: ${selectedTicket.value.ticketNumber}`;
  
  if (navigator.share) {
    navigator.share({
      title: 'E-Tiket',
      text: text
    }).catch(err => console.error('Share error:', err));
  } else {
    navigator.clipboard.writeText(text);
    alert('Informasi tiket disalin ke clipboard');
  }
}

</script>

<style scoped>
/* Header */
.header {
  background: var(--color-white);
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  position: sticky;
  top: 0;
  z-index: 50;
}

.header-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 80px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: inherit;
}

.logo-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
}

.logo-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.logo-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--color-primary);
  line-height: 1;
}

.logo-subtitle {
  font-size: 10px;
  font-weight: 600;
  color: var(--color-secondary);
  letter-spacing: 0.05em;
  line-height: 1;
}

.nav {
  display: flex;
  align-items: center;
  gap: 24px;
}

.nav-link {
  font-size: 14px;
  font-weight: 500;
  color: var(--color-text-light);
  text-decoration: none;
  transition: color 0.3s ease;
}

.nav-link:hover {
  color: var(--color-primary);
}

/* Main Container */
.my-ticket-page {
  width: 100%;
  min-height: 100vh;
  background-color: var(--color-bg-light);
}

.ticket-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 32px 16px 40px;
  font-family: 'Geist', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  color: var(--color-text-dark);
}

/* Filter Tabs */
.filter-tabs {
  display: flex;
  gap: 12px;
  margin-bottom: 28px;
  border-bottom: 2px solid #f0f0f0;
  overflow-x: auto;
}

.tab-btn {
  padding: 12px 20px;
  background: none;
  border: none;
  border-bottom: 3px solid transparent;
  color: var(--color-text-light);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.tab-btn:hover {
  color: var(--color-primary);
}

.tab-btn.active {
  color: var(--color-primary);
  border-bottom-color: var(--color-primary);
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.empty-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 20px;
  color: #d1d5db;
}

.empty-icon svg {
  width: 100%;
  height: 100%;
}

.empty-state h3 {
  font-size: 20px;
  font-weight: 600;
  margin: 0 0 8px 0;
  color: var(--color-text-dark);
}

.empty-state p {
  font-size: 14px;
  color: var(--color-text-light);
  margin: 0 0 24px 0;
}

.btn-primary {
  display: inline-block;
  padding: 12px 28px;
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: var(--color-white);
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(22, 117, 231, 0.4);
}

/* Tickets List */
.tickets-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.ticket-card {
  background: var(--color-white);
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.ticket-card:hover {
  border-color: var(--color-primary);
  box-shadow: 0 4px 16px rgba(22, 117, 231, 0.1);
  transform: translateY(-2px);
}

.ticket-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.ticket-info {
  display: flex;
  align-items: center;
  gap: 24px;
}

.train-info h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: var(--color-text-dark);
}

.train-code {
  margin: 4px 0 0 0;
  font-size: 12px;
  color: var(--color-text-light);
}

.ticket-date {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.date {
  font-size: 13px;
  color: var(--color-text-light);
  font-weight: 500;
}

.time {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-dark);
}

.status-badge {
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge.aktif {
  background: rgba(16, 185, 129, 0.1);
  color: #10B981;
}

.status-badge.selesai {
  background: rgba(59, 130, 246, 0.1);
  color: #3B82F6;
}

.status-badge.dibatalkan {
  background: rgba(239, 68, 68, 0.1);
  color: #EF4444;
}

/* Route Section */
.route-section {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f0f0f0;
}

.station {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
}

.station-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-dark);
}

.station-time {
  font-size: 12px;
  color: var(--color-text-light);
}

.route-line {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.line {
  width: 2px;
  height: 40px;
  background: linear-gradient(180deg, var(--color-primary), var(--color-secondary));
  border-radius: 2px;
}

.duration {
  font-size: 11px;
  color: var(--color-text-light);
  font-weight: 500;
}

/* Ticket Details */
.ticket-details {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f0f0f0;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-label {
  font-size: 12px;
  color: var(--color-text-light);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-value {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-dark);
}

.detail-price {
  font-size: 14px;
  font-weight: 700;
  color: var(--color-primary);
}

/* Ticket Number */
.ticket-number {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  padding: 12px;
  background: linear-gradient(135deg, rgba(22, 117, 231, 0.05), rgba(34, 197, 94, 0.05));
  border-radius: 8px;
  border-left: 3px solid var(--color-primary);
}

.ticket-number .label {
  font-size: 12px;
  color: var(--color-text-light);
  font-weight: 600;
}

.ticket-number .number {
  font-size: 14px;
  font-weight: 700;
  color: var(--color-primary);
  font-family: 'Courier New', monospace;
}

/* Ticket Actions */
.ticket-actions {
  display: flex;
  gap: 12px;
}

.btn-secondary {
  flex: 1;
  padding: 10px 16px;
  background: var(--color-white);
  color: var(--color-primary);
  border: 2px solid var(--color-primary);
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background: rgba(22, 117, 231, 0.05);
  transform: translateY(-1px);
}

.btn-danger {
  flex: 1;
  padding: 10px 16px;
  background: rgba(239, 68, 68, 0.1);
  color: #EF4444;
  border: 2px solid #EF4444;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-danger:hover {
  background: #EF4444;
  color: var(--color-white);
  transform: translateY(-1px);
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid #f0f0f0;
}

.pagination-btn {
  padding: 10px 20px;
  background: var(--color-white);
  color: var(--color-primary);
  border: 2px solid var(--color-primary);
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: var(--color-primary);
  color: var(--color-white);
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 13px;
  color: var(--color-text-light);
  font-weight: 500;
}

/* Modal */
.modal-overlay {
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
  padding: 20px;
}

/* E-Ticket Modal Fullscreen */
.modal-overlay-fullscreen {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  animation: fadeIn 0.3s ease;
  padding: 20px;
  overflow-y: auto;
}

.eticket-modal-wrapper {
  position: relative;
  width: 100%;
  max-width: 900px;
  animation: slideIn 0.3s ease;
}

.close-btn-fullscreen {
  position: absolute;
  top: -40px;
  right: 0;
  background: var(--color-white);
  border: none;
  color: var(--color-text-dark);
  font-size: 32px;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  z-index: 2001;
}

.close-btn-fullscreen:hover {
  background: var(--color-primary);
  color: var(--color-white);
}

.eticket-modal-content {
  width: 100%;
}

.boarding-pass-modal {
  background: var(--color-white);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.pass-header-modal {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 40px 24px;
  color: var(--color-white);
  text-align: center;
  position: relative;
  overflow: hidden;
}

.pass-header-modal::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -10%;
  width: 400px;
  height: 400px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
}

.pass-header-modal h1 {
  position: relative;
  z-index: 2;
  margin: 0;
  font-size: 32px;
  font-weight: 700;
  letter-spacing: 2px;
}

.pass-content-modal {
  padding: 32px;
  color: var(--color-text-dark);
}

/* Logos Section Modal */
.logos-section-modal {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 2px dashed #e5e7eb;
}

.logo-item {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 12px;
  color: var(--color-text-light);
  font-weight: 600;
}

/* Train Info Modal */
.train-info-modal {
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.train-info-modal h2 {
  margin: 0 0 4px 0;
  font-size: 24px;
  font-weight: 700;
}

.train-info-modal p {
  margin: 0;
  font-size: 12px;
  color: var(--color-text-light);
}

/* Route Section Modal */
.route-section-modal {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 24px;
  padding: 20px;
  background: #f9fafb;
  border-radius: 12px;
}

.route-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  flex: 1;
  text-align: center;
}

.route-item .code {
  font-size: 16px;
  font-weight: 700;
  color: var(--color-primary);
}

.route-item .name {
  font-size: 13px;
  color: var(--color-text-light);
  font-weight: 500;
}

.route-divider {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  margin: 0 12px;
  text-align: center;
}

.route-divider span:first-child {
  font-size: 24px;
}

.route-divider span:last-child {
  font-size: 11px;
  color: var(--color-text-light);
  font-weight: 600;
}

/* Times Section Modal */
.times-section-modal {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
}

.time-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.time-item .label {
  font-size: 11px;
  color: var(--color-text-light);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.time-item .time {
  font-size: 24px;
  font-weight: 700;
  color: var(--color-primary);
}

.time-item .date {
  font-size: 12px;
  color: var(--color-text-light);
}

.time-divider {
  width: 2px;
  height: 40px;
  background: #e5e7eb;
  margin-top: 12px;
}

/* Dashed Line */
.dashed-line {
  width: 100%;
  height: 2px;
  border-top: 2px dashed #d1d5db;
  margin: 24px 0;
  position: relative;
}

.dashed-line::before,
.dashed-line::after {
  content: '';
  position: absolute;
  top: 50%;
  width: 16px;
  height: 16px;
  background: var(--color-white);
  border: 2px solid #d1d5db;
  border-radius: 50%;
  transform: translateY(-50%);
}

.dashed-line::before {
  left: -12px;
}

.dashed-line::after {
  right: -12px;
}

/* Info Section Modal */
.info-section-modal {
  margin-bottom: 24px;
}

.info-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  margin-bottom: 16px;
}

.info-row:last-child {
  margin-bottom: 0;
}

.info-col {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.info-col .label {
  font-size: 11px;
  color: var(--color-text-light);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.info-col .value {
  font-size: 14px;
  font-weight: 700;
  color: var(--color-text-dark);
}

/* QR Section Modal */
.qr-section-modal {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 24px;
  background: #f9fafb;
  border-radius: 12px;
  margin-bottom: 24px;
}

.qr-section-modal p:first-child {
  margin: 0;
  font-size: 12px;
  color: var(--color-text-dark);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.qr-code-modal {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px;
  background: var(--color-white);
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

.qr-code-modal canvas {
  max-width: 100%;
  height: auto;
}

.qr-info {
  margin: 0;
  font-size: 11px;
  color: var(--color-text-light);
  text-align: center;
  font-family: 'Courier New', monospace;
  line-height: 1.6;
}

/* E-Ticket Actions */
.eticket-actions-modal {
  display: flex;
  gap: 12px;
  justify-content: center;
  padding-top: 20px;
  border-top: 1px solid #f0f0f0;
}

.btn-print-modal,
.btn-share-modal {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-print-modal {
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: var(--color-white);
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

.btn-print-modal:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(22, 117, 231, 0.4);
}

.btn-share-modal {
  background: var(--color-white);
  color: var(--color-primary);
  border: 2px solid var(--color-primary);
}

.btn-share-modal:hover {
  background: var(--color-primary);
  color: var(--color-white);
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
  background: var(--color-white);
  width: 100%;
  max-width: 500px;
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: var(--color-white);
}

.modal-header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
}

.close-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: var(--color-white);
  font-size: 32px;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.modal-body {
  padding: 24px;
  overflow-y: auto;
  max-height: 60vh;
}

.refund-info {
  background: linear-gradient(135deg, rgba(22, 117, 231, 0.05), rgba(34, 197, 94, 0.05));
  padding: 16px;
  border-radius: 12px;
  margin-bottom: 20px;
  border-left: 3px solid var(--color-primary);
}

.refund-info h4 {
  margin: 0 0 8px 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-dark);
}

.info-text {
  margin: 0 0 12px 0;
  font-size: 12px;
  color: var(--color-text-light);
}

.refund-amount {
  margin: 0;
  font-size: 13px;
  color: var(--color-text-light);
}

.refund-amount strong {
  color: var(--color-primary);
  font-size: 14px;
}

/* Form Styles */
.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text-dark);
}

.form-input {
  width: 100%;
  padding: 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 13px;
  font-family: inherit;
  transition: all 0.3s ease;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(22, 117, 231, 0.1);
}

.form-actions {
  display: flex;
  gap: 12px;
  padding-top: 20px;
  border-top: 1px solid #f0f0f0;
}

.form-actions button {
  flex: 1;
  padding: 12px;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.form-actions .btn-secondary {
  background: var(--color-white);
  color: var(--color-primary);
  border: 2px solid var(--color-primary);
}

.form-actions .btn-secondary:hover {
  background: rgba(22, 117, 231, 0.05);
}

.form-actions .btn-primary {
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: var(--color-white);
  border: none;
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

.form-actions .btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(22, 117, 231, 0.4);
}

/* Modal Large Size */
.modal-content.modal-lg {
  max-width: 600px;
}

/* Detail Modal Styles */
.detail-content {
  width: 100%;
}

.detail-section {
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f0f0f0;
}

.detail-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.detail-section h3 {
  margin: 0 0 16px 0;
  font-size: 14px;
  font-weight: 700;
  color: var(--color-text-dark);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.detail-item,
.detail-item-full {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-item-full {
  grid-column: 1 / -1;
}

.detail-label {
  font-size: 12px;
  color: var(--color-text-light);
  font-weight: 500;
}

.detail-value {
  font-size: 14px;
  color: var(--color-text-dark);
  font-weight: 600;
}

.detail-value-large {
  font-size: 16px;
  color: var(--color-text-dark);
  font-weight: 700;
}

.detail-value.status-aktif {
  color: #10B981;
  padding: 4px 12px;
  background: rgba(16, 185, 129, 0.1);
  border-radius: 6px;
  display: inline-block;
  width: fit-content;
}

.detail-value.status-selesai {
  color: #3B82F6;
  padding: 4px 12px;
  background: rgba(59, 130, 246, 0.1);
  border-radius: 6px;
  display: inline-block;
  width: fit-content;
}

.detail-value.status-dibatalkan {
  color: #EF4444;
  padding: 4px 12px;
  background: rgba(239, 68, 68, 0.1);
  border-radius: 6px;
  display: inline-block;
  width: fit-content;
}

/* Route Detail */
.route-detail {
  display: flex;
  align-items: center;
  gap: 16px;
}

.station-detail {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.station-label {
  font-size: 11px;
  color: var(--color-text-light);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.station-name {
  font-size: 14px;
  color: var(--color-text-dark);
  font-weight: 700;
}

.station-time {
  font-size: 16px;
  color: var(--color-primary);
  font-weight: 700;
}

.station-date {
  font-size: 12px;
  color: var(--color-text-light);
}

.route-arrow {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: var(--color-primary);
}

.route-arrow svg {
  width: 24px;
  height: 24px;
}

.route-arrow span {
  font-size: 11px;
  color: var(--color-text-light);
  font-weight: 600;
}

/* Price Box */
.price-box {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: linear-gradient(135deg, rgba(22, 117, 231, 0.05), rgba(34, 197, 94, 0.05));
  border-radius: 12px;
  border-left: 3px solid var(--color-primary);
}

.price-label {
  font-size: 13px;
  color: var(--color-text-light);
  font-weight: 600;
}

.price-value {
  font-size: 20px;
  color: var(--color-primary);
  font-weight: 700;
}

/* Ticket Number Box */
.ticket-number-box {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  background: linear-gradient(135deg, rgba(22, 117, 231, 0.05), rgba(34, 197, 94, 0.05));
  border-radius: 12px;
  border: 2px dashed var(--color-primary);
}

.ticket-number-text {
  font-family: 'Courier New', monospace;
  font-size: 18px;
  font-weight: 700;
  color: var(--color-primary);
  letter-spacing: 1px;
}

/* Rating Modal Styles */
.rating-content {
  width: 100%;
}

.train-info-box {
  background: linear-gradient(135deg, rgba(22, 117, 231, 0.05), rgba(34, 197, 94, 0.05));
  padding: 16px;
  border-radius: 12px;
  margin-bottom: 20px;
  border-left: 3px solid var(--color-primary);
}

.train-info-box h4 {
  margin: 0 0 8px 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-dark);
}

.train-info-box .info-text {
  margin: 0;
}

/* Star Rating */
.star-rating {
  display: flex;
  align-items: center;
  gap: 12px;
}

.star {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.star svg {
  width: 32px;
  height: 32px;
  color: #d1d5db;
  transition: all 0.2s ease;
}

.star.filled svg {
  color: #fbbf24;
}

.star:hover svg {
  color: #fbbf24;
  transform: scale(1.1);
}

.rating-text {
  font-size: 13px;
  color: var(--color-text-light);
  font-weight: 600;
  min-width: 80px;
}

/* Option Buttons */
.option-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.option-btn {
  padding: 10px 16px;
  background: var(--color-white);
  color: var(--color-text-light);
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.option-btn:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.option-btn.selected {
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: var(--color-white);
  border-color: transparent;
}

/* Character Count */
.char-count {
  display: block;
  text-align: right;
  margin-top: 4px;
  font-size: 12px;
  color: var(--color-text-light);
  font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
  .header-container {
    padding: 0 16px;
    height: 70px;
  }

  .logo-text {
    display: none;
  }

  .ticket-container {
    padding: 20px 12px 32px;
  }

  .filter-tabs {
    gap: 8px;
    margin-bottom: 20px;
  }

  .tab-btn {
    padding: 10px 16px;
    font-size: 12px;
  }

  .ticket-card {
    padding: 16px;
  }

  .ticket-header {
    flex-direction: column;
    gap: 12px;
  }

  .ticket-info {
    width: 100%;
    justify-content: space-between;
  }

  .status-badge {
    align-self: flex-start;
  }

  .ticket-details {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .ticket-actions {
    flex-direction: column;
  }

  .pagination {
    flex-direction: column;
    gap: 12px;
  }

  .pagination-btn {
    width: 100%;
  }

  .modal-content {
    max-width: 90vw;
    max-height: 85vh;
  }
}

@media (max-width: 480px) {
  .ticket-container {
    padding: 16px 12px 24px;
  }

  .ticket-card {
    padding: 12px;
  }

  .train-info h3 {
    font-size: 14px;
  }

  .route-section {
    gap: 8px;
  }

  .btn-primary {
    width: 100%;
  }

  .ticket-header {
    margin-bottom: 12px;
    padding-bottom: 12px;
  }
}

/* Print Styles */
@media print {
  /* Hide all page content */
  .my-ticket-page {
    background: white !important;
  }

  .header {
    display: none !important;
  }

  .ticket-container {
    display: none !important;
  }

  /* Show modal as main content */
  .modal-overlay-fullscreen {
    position: static !important;
    background: white !important;
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
    height: auto !important;
    display: flex !important;
    align-items: flex-start !important;
    justify-content: center !important;
    z-index: 1 !important;
  }

  .eticket-modal-wrapper {
    position: static !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  .close-btn-fullscreen {
    display: none !important;
  }

  .eticket-modal-content {
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .boarding-pass-modal {
    width: 100% !important;
    max-width: 100% !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    page-break-after: always;
  }

  .pass-header-modal {
    padding: 40px 30px;
    background: #f5f5f5 !important;
    page-break-after: avoid;
  }

  .pass-header-modal h1 {
    color: #000 !important;
  }

  .pass-content-modal {
    padding: 30px;
  }

  /* Hide action buttons */
  .eticket-actions-modal {
    display: none !important;
  }

  /* Reset text color for print */
  * {
    color: #000 !important;
    background: transparent !important;
  }

  h1, h2, h3, h4, h5, h6, p, span, div, label {
    color: #000 !important;
  }

  /* Print-friendly styling */
  .logos-section-modal {
    background: transparent !important;
    border-bottom: 2px dashed #000 !important;
  }

  .train-info-modal {
    background: transparent !important;
    border-bottom: 1px solid #000 !important;
  }

  .route-section-modal {
    background: #fafafa !important;
    border: 1px solid #999 !important;
  }

  .times-section-modal {
    background: transparent !important;
  }

  .info-section-modal {
    background: transparent !important;
  }

  .dashed-line {
    border-top: 2px dashed #000 !important;
  }

  .dashed-line::before,
  .dashed-line::after {
    border: 2px solid #000 !important;
    background: white !important;
  }

  .qr-section-modal {
    background: white !important;
    border: 1px solid #999 !important;
    page-break-inside: avoid;
  }

  .qr-code-modal {
    background: white !important;
    border: 1px solid #000 !important;
  }

  .qr-code-modal canvas {
    display: block !important;
    margin: 0 auto !important;
  }

  /* Remove any shadows and unnecessary effects */
  .boarding-pass-modal,
  .pass-header-modal,
  .pass-content-modal {
    box-shadow: none !important;
    border-radius: 0 !important;
  }

  /* Ensure modal is visible and not cut off */
  body {
    margin: 0 !important;
    padding: 0 !important;
  }

  html {
    margin: 0 !important;
    padding: 0 !important;
  }
}
</style>
