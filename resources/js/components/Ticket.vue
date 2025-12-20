<template>
  <div class="ticket-page">
    <!-- Main Tab Navigation -->
    <div class="main-tabs">
      <button 
        class="main-tab-btn" 
        :class="{ active: mainTab === 'tickets' }"
        @click="mainTab = 'tickets'">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
          <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75 3.54-2.08-2.63-1.41 1.41L7.21 16l1.41 1.41 1.41-1.41 2.75-3.54-1.41-1.41z"/>
        </svg>
        Tiket Saya
      </button>
      <button 
        class="main-tab-btn" 
        :class="{ active: mainTab === 'refunds' }"
        @click="mainTab = 'refunds'">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
          <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
        </svg>
        Riwayat Refund
      </button>
    </div>

    <!-- TICKETS TAB -->
    <div v-if="mainTab === 'tickets'" class="ticket-container">
      <!-- Filter Tabs -->
      <div class="filter-tabs">
        <button v-for="tab in filterTabs" :key="tab.id" 
          class="tab-btn" 
          :class="{ active: activeFilter === tab.id }"
          @click="activeFilter = tab.id">
          {{ tab.label }}
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="empty-state">
        <div class="loading-spinner">
          <div class="spinner"></div>
        </div>
        <p>Memuat tiket...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="empty-state">
        <div class="empty-icon">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
          </svg>
        </div>
        <h3>Gagal Memuat Tiket</h3>
        <p>{{ error }}</p>
        <button class="btn-primary" @click="fetchTickets">Coba Lagi</button>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredTickets.length === 0" class="empty-state">
        <div class="empty-icon">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
          </svg>
        </div>
        <h3>Tidak Ada Tiket</h3>
        <p>{{ getEmptyMessage() }}</p>
        <router-link to="/booking" class="btn-primary">Pesan Tiket Sekarang</router-link>
      </div>

      <!-- Ticket List View -->
      <div v-else>
        <!-- Tickets List -->
        <div class="tickets-list">
          <div v-for="ticket in paginatedTickets" :key="ticket.id" class="ticket-card">
            <div class="ticket-header">
              <div class="ticket-info">
                <div class="train-info">
                  <h3>{{ ticket.trainName }}</h3>
                  <p class="train-code">{{ ticket.bookingCode }}</p>
                </div>
                <div class="ticket-date">
                  <span class="date">{{ formatDate(ticket.departureDate) }}</span>
                  <span class="time">{{ ticket.departureTime }}</span>
                </div>
              </div>
              <span class="status-badge" :class="ticket.status.toLowerCase()">{{ ticket.status }}</span>
            </div>

            <!-- Route Section -->
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

            <!-- Ticket Details -->
            <div class="ticket-details">
              <div class="detail-item">
                <span class="detail-label">Penumpang</span>
                <span class="detail-value">{{ ticket.passengerName }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Kursi</span>
                <span class="detail-value">{{ ticket.seatNumber }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Kelas</span>
                <span class="detail-value">{{ ticket.class }}</span>
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

            <!-- Ticket Actions -->
            <div class="ticket-actions">
              <button class="btn-secondary" @click="viewETicket(ticket)">Lihat E-Tiket</button>
              <button class="btn-secondary" @click="shareTicket(ticket)">Bagikan</button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="pagination">
          <button class="pagination-btn" :disabled="currentPage === 1" @click="currentPage--">Sebelumnya</button>
          <span class="page-info">Halaman {{ currentPage }} dari {{ totalPages }}</span>
          <button class="pagination-btn" :disabled="currentPage === totalPages" @click="currentPage++">Berikutnya</button>
        </div>
      </div>

      <!-- E-Ticket Detail View (Overlay when ticket is selected) -->
      <div v-if="showETicketDetail" class="eticket-container">
      <!-- Back Button -->
      <button class="back-btn" @click="showETicketDetail = false">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Kembali
      </button>

      <!-- Boarding Pass -->
      <div class="boarding-pass">
        <!-- Top Section with Background -->
        <div class="pass-header">
          <div class="header-content">
            <h1>Boarding Pass</h1>
          </div>
        </div>

        <!-- Main Content -->
        <div class="pass-content">
          <!-- Logos Section -->
          <div class="logos-section">
            <div class="logo-left">
              <svg width="60" height="40" viewBox="0 0 60 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="60" height="40" rx="4" fill="#1675E7"/>
                <text x="50%" y="50%" font-size="20" font-weight="bold" fill="white" text-anchor="middle" dominant-baseline="middle">SR</text>
              </svg>
              <span class="logo-text-small">SwiftRail</span>
            </div>
            <div class="logo-right">
              <svg width="60" height="40" viewBox="0 0 60 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="30" cy="20" r="18" fill="#10B981"/>
                <text x="50%" y="50%" font-size="16" font-weight="bold" fill="white" text-anchor="middle" dominant-baseline="middle">80%</text>
              </svg>
              <span class="logo-text-small">Selamat Melayani</span>
            </div>
          </div>

          <!-- Train Info -->
          <div class="train-info">
            <h2>{{ selectedTicket.trainName }}</h2>
            <p class="train-code">Kode pemesanan: {{ selectedTicket.bookingCode }}</p>
          </div>

          <!-- Route Section -->
          <div class="route-section">
            <div class="station-box">
              <span class="station-code">{{ selectedTicket.from.substring(0, 3).toUpperCase() }}</span>
              <span class="station-name">{{ selectedTicket.from }}</span>
            </div>

            <div class="route-middle">
              <div class="train-icon">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                </svg>
              </div>
              <span class="duration">{{ selectedTicket.duration }}</span>
            </div>

            <div class="station-box">
              <span class="station-code">{{ selectedTicket.to.substring(0, 3).toUpperCase() }}</span>
              <span class="station-name">{{ selectedTicket.to }}</span>
            </div>
          </div>

          <!-- Time Section -->
          <div class="time-section">
            <div class="time-box">
              <span class="time-label">Keberangkatan</span>
              <span class="time">{{ selectedTicket.departureTime }}</span>
              <span class="date">{{ formatDate(selectedTicket.departureDate) }}</span>
            </div>
            <div class="time-separator"></div>
            <div class="time-box">
              <span class="time-label">Tiba</span>
              <span class="time">{{ selectedTicket.arrivalTime }}</span>
              <span class="date">{{ formatDate(selectedTicket.departureDate) }}</span>
            </div>
          </div>

          <!-- Dashed Separator -->
          <div class="dashed-separator"></div>

          <!-- Passenger Info -->
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Penumpang</span>
              <span class="info-value">{{ selectedTicket.passengerName }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">NIK</span>
              <span class="info-value">{{ selectedTicket.nik }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Tipe Penumpang</span>
              <span class="info-value">{{ selectedTicket.passengerType }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">No</span>
              <span class="info-value">{{ selectedTicket.seatNumber }}</span>
            </div>
          </div>

          <!-- Additional Info Grid -->
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Kelas</span>
              <span class="info-value">{{ selectedTicket.class }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Gerbong</span>
              <span class="info-value">{{ selectedTicket.coach }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Tempat Duduk</span>
              <span class="info-value">{{ selectedTicket.seat }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Harga</span>
              <span class="info-value">{{ formatPrice(selectedTicket.price) }}</span>
            </div>
          </div>

          <!-- QR Code Section -->
          <div class="qr-section">
            <p class="qr-label">Pindai kode ini di Gerbang</p>
            <div class="qr-code" ref="qrCodeElement"></div>
            <p class="qr-info">{{ selectedTicket.qrCode }}<br/>Dicetak: {{ formatDateTime(selectedTicket.createdAt) }}</p>
          </div>

          <!-- Footer -->
          <div class="pass-footer">
            <p>Perjalanan yang aman dan menyenangkan!</p>
          </div>
        </div>
      </div>

      <!-- Print Button -->
      <div class="action-buttons">
        <button class="btn-print" @click="printTicket">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
          </svg>
          Cetak Tiket
        </button>
        <button class="btn-share" @click="shareTicket(selectedTicket)">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
            <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.15c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/>
          </svg>
          Bagikan
        </button>
      </div>
    </div>
    </div>

    <!-- REFUNDS TAB -->
    <div v-else-if="mainTab === 'refunds'" class="refunds-container">
      <div class="empty-state">
        <div class="empty-icon">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
          </svg>
        </div>
        <h3>Fitur Refund Segera Hadir</h3>
        <p>Kami sedang mempersiapkan fitur pengembalian dana yang lebih baik untuk Anda</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, ref, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import QRCode from 'qrcode';

const router = useRouter();
const route = useRoute();
const qrCodeElement = ref(null);

// Main Tab
const mainTab = ref('tickets');

// View states
const showETicketDetail = ref(false);
const selectedTicket = ref(null);

// Filter states
const activeFilter = ref('all');
const currentPage = ref(1);
const itemsPerPage = 5;
const loading = ref(true);
const error = ref(null);

const filterTabs = [
  { id: 'all', label: 'Semua Tiket' },
  { id: 'active', label: 'Aktif' },
  { id: 'completed', label: 'Selesai' }
];

// Ticket data dari API
const tickets = reactive([]);

// Fetch tickets dari API
const fetchTickets = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    const response = await fetch('/api/v1/bookings', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('authToken')}`
      }
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    
    // Clear and fill tickets array
    tickets.length = 0;
    tickets.push(...data);
    
  } catch (err) {
    error.value = 'Gagal memuat tiket. Silakan coba lagi.';
    console.error('Fetch error:', err);
    // Tampilkan empty state jika ada error
    tickets.length = 0;
  } finally {
    loading.value = false;
  }
};

const filteredTickets = computed(() => {
  let filtered = tickets;

  if (activeFilter.value === 'active') {
    filtered = tickets.filter(t => t.status === 'Aktif');
  } else if (activeFilter.value === 'completed') {
    filtered = tickets.filter(t => t.status === 'Selesai');
  }

  return filtered.sort((a, b) => new Date(b.departureDate) - new Date(a.departureDate));
});

const totalPages = computed(() => Math.ceil(filteredTickets.value.length / itemsPerPage));

const paginatedTickets = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredTickets.value.slice(start, start + itemsPerPage);
});

watch(activeFilter, () => {
  currentPage.value = 1;
});

watch(showETicketDetail, (newVal) => {
  if (newVal && selectedTicket.value && qrCodeElement.value) {
    setTimeout(() => {
      QRCode.toCanvas(qrCodeElement.value, selectedTicket.value.qrCode, {
        errorCorrectionLevel: 'H',
        type: 'image/png',
        quality: 0.95,
        margin: 1,
        width: 200,
        color: {
          dark: '#000000',
          light: '#FFFFFF'
        }
      }, (error) => {
        if (error) console.error('QR Code Error:', error);
      });
    }, 100);
  }
});

onMounted(() => {
  fetchTickets();
});

function formatDate(dateStr) {
  const options = { weekday: 'short', month: 'short', day: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
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
  }
  return 'Belum ada tiket. Mulai pesan tiket sekarang!';
}

function viewETicket(ticket) {
  selectedTicket.value = ticket;
  showETicketDetail.value = true;
}

function printTicket() {
  window.print();
}

function shareTicket(ticket) {
  const text = `Saya telah memesan tiket ${ticket.trainName} dari ${ticket.from} ke ${ticket.to} pada ${formatDate(ticket.departureDate)}. Booking code: ${ticket.bookingCode}`;
  
  if (navigator.share) {
    navigator.share({
      title: 'Boarding Pass',
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
/* Main Tab Navigation */
.main-tabs {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px 16px;
  display: flex;
  gap: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.main-tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: none;
  border: 2px solid transparent;
  color: var(--color-text-light);
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.main-tab-btn:hover {
  color: var(--color-primary);
  background-color: #f0f5ff;
}

.main-tab-btn.active {
  color: var(--color-primary);
  border-bottom: 2px solid var(--color-primary);
  background-color: #f0f5ff;
}

/* Ticket List View */
.ticket-page {
  width: 100%;
  min-height: 100vh;
  background-color: var(--color-bg-light);
}

.view-container {
  display: block;
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

/* Loading State */
.loading-spinner {
  width: 50px;
  height: 50px;
  margin: 0 auto 20px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e5e7eb;
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
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

/* E-Ticket View */
.eticket-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 40px 16px;
  font-family: 'Geist', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* Back Button */
.back-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  margin-bottom: 24px;
  background: var(--color-white);
  color: var(--color-primary);
  border: 2px solid var(--color-primary);
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.back-btn:hover {
  background: var(--color-primary);
  color: var(--color-white);
  transform: translateX(-4px);
}

.back-btn svg {
  width: 20px;
  height: 20px;
}

/* Boarding Pass */
.boarding-pass {
  background: var(--color-white);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  margin-bottom: 24px;
}

.pass-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 40px 24px;
  color: var(--color-white);
  text-align: center;
  position: relative;
  overflow: hidden;
}

.pass-header::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -10%;
  width: 400px;
  height: 400px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
}

.pass-header::after {
  content: '';
  position: absolute;
  bottom: -30%;
  left: -5%;
  width: 300px;
  height: 300px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
}

.header-content {
  position: relative;
  z-index: 2;
}

.pass-header h1 {
  margin: 0;
  font-size: 32px;
  font-weight: 700;
  letter-spacing: 2px;
}

/* Pass Content */
.pass-content {
  padding: 32px;
  color: var(--color-text-dark);
}

/* Logos Section */
.logos-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 2px dashed #e5e7eb;
}

.logo-left,
.logo-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-text-small {
  font-size: 12px;
  color: var(--color-text-light);
  font-weight: 600;
}

/* Train Info */
.train-info {
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.train-info h2 {
  margin: 0 0 4px 0;
  font-size: 24px;
  font-weight: 700;
}

.train-code {
  margin: 0;
  font-size: 12px;
  color: var(--color-text-light);
}

/* Station Box */
.station-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  flex: 1;
  text-align: center;
}

.station-code {
  font-size: 16px;
  font-weight: 700;
  color: var(--color-primary);
}

.station-name {
  font-size: 13px;
  color: var(--color-text-light);
  font-weight: 500;
}

.route-middle {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  margin: 0 12px;
}

.train-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-primary);
  border-radius: 50%;
  color: var(--color-white);
}

.train-icon svg {
  width: 24px;
  height: 24px;
}

/* E-Ticket Route Section */
.boarding-pass .route-section {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 24px;
  padding: 20px;
  background: #f9fafb;
  border-radius: 12px;
  border-bottom: none;
  padding-bottom: 20px;
}

/* Time Section */
.time-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
}

.time-box {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.time-label {
  font-size: 11px;
  color: var(--color-text-light);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.time {
  font-size: 24px;
  font-weight: 700;
  color: var(--color-primary);
}

.time-separator {
  width: 2px;
  height: 40px;
  background: #e5e7eb;
  margin-top: 12px;
}

/* Dashed Separator */
.dashed-separator {
  width: 100%;
  height: 2px;
  border-top: 2px dashed #d1d5db;
  margin: 24px 0;
  position: relative;
}

.dashed-separator::before,
.dashed-separator::after {
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

.dashed-separator::before {
  left: -12px;
}

.dashed-separator::after {
  right: -12px;
}

/* Info Grid */
.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.info-label {
  font-size: 11px;
  color: var(--color-text-light);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.info-value {
  font-size: 14px;
  font-weight: 700;
  color: var(--color-text-dark);
}

/* QR Code Section */
.qr-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 24px;
  background: #f9fafb;
  border-radius: 12px;
  margin-bottom: 24px;
}

.qr-label {
  margin: 0;
  font-size: 12px;
  color: var(--color-text-dark);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.qr-code {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px;
  background: var(--color-white);
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

.qr-code canvas {
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

/* Pass Footer */
.pass-footer {
  text-align: center;
  padding: 16px 0;
  border-top: 1px solid #f0f0f0;
}

.pass-footer p {
  margin: 0;
  font-size: 12px;
  color: var(--color-text-light);
  font-weight: 500;
  font-style: italic;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 16px;
  justify-content: center;
}

.btn-print,
.btn-share {
  display: flex;
  align-items: center;
  gap: 8px;
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

.btn-print {
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: var(--color-white);
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

.btn-print:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(22, 117, 231, 0.4);
}

.btn-share {
  background: var(--color-white);
  color: var(--color-primary);
  border: 2px solid var(--color-primary);
}

.btn-share:hover {
  background: var(--color-primary);
  color: var(--color-white);
}

/* Print Styles */
@media print {
  .header,
  .back-btn,
  .action-buttons {
    display: none;
  }

  .eticket-container {
    padding: 0;
  }

  .boarding-pass {
    max-width: 100%;
    box-shadow: none;
    border: none;
  }
}

/* Responsive */
@media (max-width: 768px) {
  .eticket-container {
    padding: 20px 12px;
  }

  .boarding-pass {
    border-radius: 12px;
  }

  .pass-header {
    padding: 30px 16px;
  }

  .pass-header h1 {
    font-size: 24px;
  }

  .pass-content {
    padding: 20px;
  }

  .logos-section {
    flex-direction: column;
    gap: 16px;
  }

  .train-info h2 {
    font-size: 18px;
  }

  .boarding-pass .route-section {
    flex-wrap: wrap;
    padding: 16px;
  }

  .time-section {
    flex-direction: column;
    gap: 12px;
  }

  .time-separator {
    width: 40px;
    height: 2px;
    margin: 8px 0;
  }

  .info-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .action-buttons {
    flex-direction: column;
  }

  .btn-print,
  .btn-share {
    width: 100%;
    justify-content: center;
  }

  .qr-section {
    padding: 16px;
  }

  .ticket-container {
    padding: 20px 12px;
  }

  .filter-tabs {
    margin-bottom: 20px;
  }

  .ticket-card {
    padding: 16px;
  }

  .ticket-header {
    flex-direction: column;
    gap: 12px;
  }

  .ticket-info {
    flex-direction: column;
    width: 100%;
  }

  .ticket-details {
    grid-template-columns: 1fr;
  }

  .ticket-actions {
    flex-direction: column;
  }
}
</style>
