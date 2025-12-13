<template>
  <div class="check-refund-page">
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

    <div class="refund-container">
      <!-- Page Title -->
      <div class="page-title">
        <h1>Cek Status Refund</h1>
        <p>Pantau status refund tiket Anda secara real-time</p>
      </div>

      <!-- Search Section -->
      <div class="search-section">
        <div class="search-box">
          <input 
            v-model="searchRefundNumber" 
            type="text" 
            placeholder="Masukkan nomor tiket atau booking code..."
            class="search-input"
            @keyup.enter="searchRefund"
          >
          <button class="search-btn" @click="searchRefund">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"></circle>
              <path d="m21 21-4.35-4.35"></path>
            </svg>
            Cari
          </button>
        </div>
      </div>

      <!-- Results Section -->
      <div v-if="refundFound" class="refund-status">
        <!-- Refund Header -->
        <div class="refund-header">
          <div class="header-info">
            <h2>{{ selectedRefund.trainName }}</h2>
            <p class="route-info">{{ selectedRefund.from }} → {{ selectedRefund.to }}</p>
            <p class="date-info">{{ formatDate(selectedRefund.departureDate) }}</p>
          </div>
          <div :class="['status-badge', 'status-' + selectedRefund.status.toLowerCase()]">
            {{ selectedRefund.status }}
          </div>
        </div>

        <!-- Refund Info Grid -->
        <div class="refund-info-grid">
          <div class="info-item">
            <span class="label">Nomor Tiket</span>
            <span class="value">{{ selectedRefund.ticketNumber }}</span>
          </div>
          <div class="info-item">
            <span class="label">Jumlah Refund</span>
            <span class="value amount">{{ formatPrice(selectedRefund.amount) }}</span>
          </div>
          <div class="info-item">
            <span class="label">Alasan Pembatalan</span>
            <span class="value">{{ selectedRefund.reason }}</span>
          </div>
          <div class="info-item">
            <span class="label">Tanggal Pengajuan</span>
            <span class="value">{{ formatDate(selectedRefund.submittedDate) }}</span>
          </div>
        </div>

        <!-- Timeline Status -->
        <div class="timeline-section">
          <h3>Tahapan Proses Refund</h3>
          
          <div class="timeline">
            <!-- Step 1: Diajukan -->
            <div class="timeline-item">
              <div :class="['timeline-dot', { completed: isStepCompleted(1) }]">
                <svg v-if="isStepCompleted(1)" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
                <span v-else>1</span>
              </div>
              <div class="timeline-content">
                <h4>Permohonan Diajukan</h4>
                <p class="timeline-date">{{ formatDate(selectedRefund.submittedDate) }}</p>
                <p v-if="selectedRefund.status === 'Menunggu'" class="timeline-status status-pending">Sedang diproses</p>
              </div>
            </div>

            <!-- Step 2: Diverifikasi -->
            <div class="timeline-item">
              <div :class="['timeline-dot', { completed: isStepCompleted(2) }]">
                <svg v-if="isStepCompleted(2)" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
                <span v-else>2</span>
              </div>
              <div class="timeline-content">
                <h4>Verifikasi Dokumen</h4>
                <p v-if="selectedRefund.verifiedDate" class="timeline-date">{{ formatDate(selectedRefund.verifiedDate) }}</p>
                <p v-else class="timeline-date">Menunggu...</p>
                <p v-if="selectedRefund.status === 'Disetujui' || selectedRefund.status === 'Selesai'" class="timeline-status status-approved">Disetujui</p>
                <p v-else-if="selectedRefund.status === 'Ditolak'" class="timeline-status status-rejected">Ditolak</p>
                <p v-else class="timeline-status status-pending">Sedang diproses</p>
              </div>
            </div>

            <!-- Step 3: Dana Dikembalikan -->
            <div class="timeline-item">
              <div :class="['timeline-dot', { completed: isStepCompleted(3) }]">
                <svg v-if="isStepCompleted(3)" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
                <span v-else>3</span>
              </div>
              <div class="timeline-content">
                <h4>Dana Dikembalikan</h4>
                <p v-if="selectedRefund.refundedDate" class="timeline-date">{{ formatDate(selectedRefund.refundedDate) }}</p>
                <p v-else class="timeline-date">Menunggu...</p>
                <p v-if="selectedRefund.status === 'Selesai'" class="timeline-status status-completed">Selesai</p>
                <p v-else class="timeline-status status-pending">Menunggu persetujuan</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes Section -->
        <div v-if="selectedRefund.notes" class="notes-section">
          <h3>Catatan Admin</h3>
          <div class="notes-box">
            {{ selectedRefund.notes }}
          </div>
        </div>

        <!-- Action Buttons -->
        <div v-if="selectedRefund.status === 'Menunggu'" class="action-buttons">
          <button class="btn-cancel" @click="cancelRefund">
            Batalkan Permohonan
          </button>
        </div>

        <!-- Back Button -->
        <button class="btn-back" @click="resetSearch">
          ← Cari Refund Lain
        </button>
      </div>

      <!-- No Results -->
      <div v-else-if="searchAttempted && !refundFound" class="no-results">
        <div class="no-results-icon">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
          </svg>
        </div>
        <h3>Refund Tidak Ditemukan</h3>
        <p>Silakan periksa kembali nomor tiket atau booking code Anda</p>
        <button class="btn-retry" @click="resetSearch">Coba Lagi</button>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <div class="empty-icon">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
          </svg>
        </div>
        <h3>Cari Status Refund Anda</h3>
        <p>Masukkan nomor tiket atau booking code untuk melihat status refund secara real-time</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const searchRefundNumber = ref('');
const searchAttempted = ref(false);
const refundFound = ref(false);
const selectedRefund = ref(null);

// Mock refund data
const refundsDatabase = [
  {
    id: 1,
    trainName: 'Argo Bromo Anggrek',
    trainCode: '2',
    ticketNumber: 'SR-2025-001234',
    from: 'Jakarta Kota',
    to: 'Surabaya Pasar Turi',
    departureDate: '2025-12-20',
    amount: 450000,
    reason: 'Keperluan Mendesak',
    submittedDate: '2025-12-13',
    verifiedDate: '2025-12-14',
    refundedDate: null,
    status: 'Disetujui',
    notes: 'Dokumen telah diverifikasi. Dana akan dikembalikan dalam 3-5 hari kerja.'
  },
  {
    id: 2,
    trainName: 'Gajayana',
    trainCode: '62',
    ticketNumber: 'SR-2025-001233',
    from: 'Jakarta Kota',
    to: 'Malang',
    departureDate: '2025-11-15',
    amount: 380000,
    reason: 'Sakit',
    submittedDate: '2025-11-10',
    verifiedDate: '2025-11-11',
    refundedDate: '2025-11-15',
    status: 'Selesai',
    notes: 'Refund telah selesai dan dana sudah masuk ke rekening Anda.'
  },
  {
    id: 3,
    trainName: 'Mutiara Timur',
    trainCode: '100',
    ticketNumber: 'SR-2025-001232',
    from: 'Jakarta Gambir',
    to: 'Medan',
    departureDate: '2025-10-10',
    amount: 600000,
    reason: 'Masalah Teknis',
    submittedDate: '2025-10-05',
    verifiedDate: null,
    refundedDate: null,
    status: 'Menunggu',
    notes: null
  },
  {
    id: 4,
    trainName: 'Srikandi',
    trainCode: '50',
    ticketNumber: 'SR-2025-001231',
    from: 'Bandung',
    to: 'Jakarta Kota',
    departureDate: '2025-09-25',
    amount: 150000,
    reason: 'Berubah Rencana',
    submittedDate: '2025-09-20',
    verifiedDate: '2025-09-21',
    refundedDate: null,
    status: 'Ditolak',
    notes: 'Permintaan refund ditolak karena sudah melewati batas waktu pembatalan (24 jam sebelum keberangkatan).'
  }
];

function searchRefund() {
  searchAttempted.value = true;
  
  if (!searchRefundNumber.value.trim()) {
    refundFound.value = false;
    return;
  }

  const refund = refundsDatabase.find(r => 
    r.ticketNumber.toLowerCase().includes(searchRefundNumber.value.toLowerCase()) ||
    r.trainCode.toLowerCase().includes(searchRefundNumber.value.toLowerCase())
  );

  if (refund) {
    selectedRefund.value = refund;
    refundFound.value = true;
  } else {
    refundFound.value = false;
  }
}

function resetSearch() {
  searchRefundNumber.value = '';
  searchAttempted.value = false;
  refundFound.value = false;
  selectedRefund.value = null;
}

function isStepCompleted(step) {
  if (!selectedRefund.value) return false;
  
  const status = selectedRefund.value.status;
  if (step === 1) return true; // First step always completed
  if (step === 2) return status === 'Disetujui' || status === 'Selesai';
  if (step === 3) return status === 'Selesai';
  
  return false;
}

function formatDate(dateStr) {
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
}

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(price);
}

function cancelRefund() {
  if (confirm('Apakah Anda yakin ingin membatalkan permohonan refund ini?')) {
    const refund = refundsDatabase.find(r => r.id === selectedRefund.value.id);
    if (refund) {
      refund.status = 'Dibatalkan';
      selectedRefund.value = { ...refund };
    }
    alert('Permohonan refund telah dibatalkan.');
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
.refund-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 40px 16px;
  font-family: 'Geist', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  color: var(--color-text-dark);
}

/* Page Title */
.page-title {
  text-align: center;
  margin-bottom: 40px;
}

.page-title h1 {
  margin: 0 0 8px 0;
  font-size: 32px;
  font-weight: 700;
}

.page-title p {
  margin: 0;
  font-size: 16px;
  color: var(--color-text-light);
}

/* Search Section */
.search-section {
  margin-bottom: 40px;
}

.search-box {
  display: flex;
  gap: 12px;
  background: var(--color-white);
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.search-input {
  flex: 1;
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
  transition: all 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(22, 117, 231, 0.1);
}

.search-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: var(--color-white);
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.search-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

/* Refund Status Section */
.refund-status {
  background: var(--color-white);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  padding: 32px;
}

/* Refund Header */
.refund-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 32px;
  padding-bottom: 24px;
  border-bottom: 1px solid #f0f0f0;
}

.header-info h2 {
  margin: 0 0 8px 0;
  font-size: 24px;
  font-weight: 700;
}

.route-info {
  margin: 0 0 4px 0;
  font-size: 14px;
  color: var(--color-text-light);
}

.date-info {
  margin: 0;
  font-size: 13px;
  color: var(--color-text-light);
}

.status-badge {
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
}

.status-badge.status-menunggu {
  background: rgba(245, 158, 11, 0.1);
  color: #F59E0B;
}

.status-badge.status-disetujui {
  background: rgba(16, 185, 129, 0.1);
  color: #10B981;
}

.status-badge.status-ditolak {
  background: rgba(239, 68, 68, 0.1);
  color: #EF4444;
}

.status-badge.status-selesai {
  background: rgba(59, 130, 246, 0.1);
  color: #3B82F6;
}

/* Refund Info Grid */
.refund-info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 24px;
  margin-bottom: 32px;
  padding-bottom: 32px;
  border-bottom: 1px solid #f0f0f0;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.info-item .label {
  font-size: 12px;
  color: var(--color-text-light);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.info-item .value {
  font-size: 14px;
  font-weight: 700;
  color: var(--color-text-dark);
}

.info-item .value.amount {
  font-size: 16px;
  color: var(--color-primary);
}

/* Timeline Section */
.timeline-section {
  margin-bottom: 32px;
  padding-bottom: 32px;
  border-bottom: 1px solid #f0f0f0;
}

.timeline-section h3 {
  margin: 0 0 24px 0;
  font-size: 16px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.timeline {
  position: relative;
  padding-left: 40px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 11px;
  top: 40px;
  bottom: 0;
  width: 2px;
  background: linear-gradient(180deg, var(--color-primary), var(--color-secondary));
}

.timeline-item {
  display: flex;
  gap: 24px;
  margin-bottom: 32px;
  position: relative;
}

.timeline-item:last-child {
  margin-bottom: 0;
}

.timeline-dot {
  position: absolute;
  left: -40px;
  top: 0;
  width: 24px;
  height: 24px;
  background: var(--color-white);
  border: 3px solid var(--color-primary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  color: var(--color-primary);
}

.timeline-dot.completed {
  background: var(--color-primary);
  color: var(--color-white);
  border-color: var(--color-primary);
}

.timeline-dot svg {
  width: 14px;
  height: 14px;
}

.timeline-content {
  flex: 1;
}

.timeline-content h4 {
  margin: 0 0 4px 0;
  font-size: 14px;
  font-weight: 700;
}

.timeline-date {
  margin: 0 0 8px 0;
  font-size: 13px;
  color: var(--color-text-light);
}

.timeline-status {
  margin: 0;
  font-size: 12px;
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 600;
  width: fit-content;
}

.timeline-status.status-pending {
  background: rgba(245, 158, 11, 0.1);
  color: #F59E0B;
}

.timeline-status.status-approved {
  background: rgba(16, 185, 129, 0.1);
  color: #10B981;
}

.timeline-status.status-rejected {
  background: rgba(239, 68, 68, 0.1);
  color: #EF4444;
}

.timeline-status.status-completed {
  background: rgba(59, 130, 246, 0.1);
  color: #3B82F6;
}

/* Notes Section */
.notes-section {
  margin-bottom: 32px;
  padding-bottom: 32px;
  border-bottom: 1px solid #f0f0f0;
}

.notes-section h3 {
  margin: 0 0 16px 0;
  font-size: 16px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.notes-box {
  padding: 16px;
  background: #f9fafb;
  border-left: 3px solid var(--color-primary);
  border-radius: 8px;
  font-size: 14px;
  line-height: 1.6;
  color: var(--color-text-dark);
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 12px;
  margin-bottom: 24px;
}

.btn-cancel {
  padding: 12px 24px;
  background: rgba(239, 68, 68, 0.1);
  color: #EF4444;
  border: 2px solid #EF4444;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-cancel:hover {
  background: #EF4444;
  color: var(--color-white);
}

/* Back Button */
.btn-back {
  width: 100%;
  padding: 12px;
  background: var(--color-white);
  color: var(--color-primary);
  border: 2px solid var(--color-primary);
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-back:hover {
  background: var(--color-primary);
  color: var(--color-white);
}

/* No Results */
.no-results {
  text-align: center;
  padding: 60px 20px;
  background: var(--color-white);
  border-radius: 16px;
}

.no-results-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 20px;
  color: #d1d5db;
}

.no-results-icon svg {
  width: 100%;
  height: 100%;
}

.no-results h3 {
  font-size: 20px;
  font-weight: 600;
  margin: 0 0 8px 0;
}

.no-results p {
  font-size: 14px;
  color: var(--color-text-light);
  margin: 0 0 20px 0;
}

.btn-retry {
  padding: 10px 20px;
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: var(--color-white);
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-retry:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: var(--color-white);
  border-radius: 16px;
}

.empty-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 20px;
  color: var(--color-primary);
  opacity: 0.3;
}

.empty-icon svg {
  width: 100%;
  height: 100%;
}

.empty-state h3 {
  font-size: 20px;
  font-weight: 600;
  margin: 0 0 8px 0;
}

.empty-state p {
  font-size: 14px;
  color: var(--color-text-light);
  margin: 0;
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

  .refund-container {
    padding: 24px 12px;
  }

  .page-title h1 {
    font-size: 24px;
  }

  .search-box {
    flex-direction: column;
  }

  .refund-header {
    flex-direction: column;
    gap: 16px;
  }

  .refund-info-grid {
    grid-template-columns: 1fr;
  }

  .timeline {
    padding-left: 30px;
  }

  .timeline-item {
    gap: 16px;
  }

  .timeline-dot {
    left: -35px;
    width: 20px;
    height: 20px;
    font-size: 11px;
  }

  .timeline::before {
    left: 7px;
  }
}

@media (max-width: 480px) {
  .refund-container {
    padding: 16px 12px;
  }

  .page-title h1 {
    font-size: 20px;
  }

  .page-title p {
    font-size: 14px;
  }

  .refund-status {
    padding: 20px;
  }

  .refund-header {
    gap: 12px;
  }

  .refund-info-grid {
    gap: 12px;
  }

  .timeline {
    padding-left: 25px;
  }

  .timeline-dot {
    left: -30px;
  }

  .timeline::before {
    left: 5px;
  }
}
</style>
