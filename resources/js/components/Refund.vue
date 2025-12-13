<template>
  <div class="refund-page">
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
      <div class="page-header">
        <h1>Riwayat Pengajuan Refund</h1>
        <p>Kelola dan pantau status pengajuan pengembalian dana Anda</p>
      </div>

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
      <div v-if="filteredRefunds.length === 0" class="empty-state">
        <div class="empty-icon">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
          </svg>
        </div>
        <h3>Belum Ada Pengajuan Refund</h3>
        <p>{{ getEmptyMessage() }}</p>
        <router-link to="/my-ticket" class="btn-primary">
          Kembali ke Tiket Saya
        </router-link>
      </div>

      <!-- Refunds List -->
      <div v-else class="refunds-list">
        <div v-for="refund in filteredRefunds" :key="refund.id" class="refund-card">
          <!-- Refund Header -->
          <div class="refund-header">
            <div class="refund-info">
              <h3>{{ refund.trainName }}</h3>
              <p class="refund-date">Diajukan pada {{ formatDate(refund.submittedDate) }}</p>
            </div>
            <div :class="['status-badge', refund.status.toLowerCase()]">
              {{ refund.status }}
            </div>
          </div>

          <!-- Ticket Info -->
          <div class="ticket-info">
            <div class="info-row">
              <span class="label">Nomor Tiket</span>
              <span class="value">{{ refund.ticketNumber }}</span>
            </div>
            <div class="info-row">
              <span class="label">Rute</span>
              <span class="value">{{ refund.from }} → {{ refund.to }}</span>
            </div>
            <div class="info-row">
              <span class="label">Tanggal Perjalanan</span>
              <span class="value">{{ formatDate(refund.departureDate) }}</span>
            </div>
            <div class="info-row">
              <span class="label">Alasan</span>
              <span class="value">{{ refund.reason }}</span>
            </div>
          </div>

          <!-- Refund Amount -->
          <div class="amount-section">
            <span class="amount-label">Jumlah Refund</span>
            <span class="amount-value">{{ formatPrice(refund.amount) }}</span>
          </div>

          <!-- Status Timeline -->
          <div class="status-timeline">
            <div class="timeline-item" :class="{ completed: refund.status !== 'Ditolak' }">
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <span class="timeline-title">Diajukan</span>
                <span class="timeline-date">{{ formatDate(refund.submittedDate) }}</span>
              </div>
            </div>

            <div class="timeline-item" :class="{ completed: refund.status === 'Disetujui' || refund.status === 'Selesai' }">
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <span class="timeline-title">Diverifikasi</span>
                <span v-if="refund.verifiedDate" class="timeline-date">{{ formatDate(refund.verifiedDate) }}</span>
                <span v-else class="timeline-date pending">Menunggu verifikasi...</span>
              </div>
            </div>

            <div class="timeline-item" :class="{ completed: refund.status === 'Selesai' }">
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <span class="timeline-title">Dana Dikembalikan</span>
                <span v-if="refund.refundedDate" class="timeline-date">{{ formatDate(refund.refundedDate) }}</span>
                <span v-else class="timeline-date pending">Menunggu...</span>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="refund.notes" class="notes-section">
            <span class="notes-label">Catatan Admin</span>
            <p class="notes-text">{{ refund.notes }}</p>
          </div>

          <!-- Action Buttons -->
          <div class="refund-actions">
            <button 
              v-if="refund.status === 'Menunggu'"
              class="btn-secondary"
              @click="cancelRefund(refund.id)"
            >
              Batalkan Pengajuan
            </button>
            <button 
              class="btn-secondary"
              @click="showRefundDetail(refund)"
            >
              Lihat Detail Lengkap
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="filteredRefunds.length > 0" class="pagination">
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
  </div>
</template>

<script setup>
import { reactive, computed, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const activeFilter = ref('all');
const currentPage = ref(1);
const itemsPerPage = 5;

const filterTabs = [
  { id: 'all', label: 'Semua Pengajuan' },
  { id: 'pending', label: 'Menunggu' },
  { id: 'approved', label: 'Disetujui' },
  { id: 'rejected', label: 'Ditolak' },
  { id: 'completed', label: 'Selesai' }
];

// Sample refund data
const refunds = reactive([
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
    submittedDate: '2025-12-15',
    verifiedDate: '2025-12-16',
    refundedDate: '2025-12-17',
    status: 'Selesai',
    notes: 'Refund telah berhasil diproses dan ditransfer ke rekening Anda.'
  },
  {
    id: 2,
    trainName: 'Gajayana',
    trainCode: '62',
    ticketNumber: 'SR-2025-001233',
    from: 'Jakarta Kota',
    to: 'Malang',
    departureDate: '2025-11-20',
    amount: 380000,
    reason: 'Sakit',
    submittedDate: '2025-11-18',
    verifiedDate: '2025-11-19',
    refundedDate: null,
    status: 'Disetujui',
    notes: ''
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
    reason: 'Berubah Rencana',
    submittedDate: '2025-10-08',
    verifiedDate: null,
    refundedDate: null,
    status: 'Menunggu',
    notes: ''
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
    reason: 'Masalah Teknis',
    submittedDate: '2025-09-20',
    verifiedDate: '2025-09-21',
    refundedDate: null,
    status: 'Ditolak',
    notes: 'Pengajuan ditolak karena sudah melewati batas waktu pembatalan (24 jam sebelum keberangkatan).'
  },
  {
    id: 5,
    trainName: 'Krakatau',
    trainCode: '201',
    ticketNumber: 'SR-2025-001230',
    from: 'Jakarta Kota',
    to: 'Yogyakarta',
    departureDate: '2025-12-28',
    amount: 320000,
    reason: 'Keperluan Mendesak',
    submittedDate: '2025-12-14',
    verifiedDate: '2025-12-14',
    refundedDate: '2025-12-15',
    status: 'Selesai',
    notes: 'Dana refund telah dikirim ke rekening terdaftar Anda.'
  }
]);

const filteredRefunds = computed(() => {
  let filtered = refunds;

  if (activeFilter.value === 'pending') {
    filtered = refunds.filter(r => r.status === 'Menunggu');
  } else if (activeFilter.value === 'approved') {
    filtered = refunds.filter(r => r.status === 'Disetujui');
  } else if (activeFilter.value === 'rejected') {
    filtered = refunds.filter(r => r.status === 'Ditolak');
  } else if (activeFilter.value === 'completed') {
    filtered = refunds.filter(r => r.status === 'Selesai');
  }

  return filtered.sort((a, b) => new Date(b.submittedDate) - new Date(a.submittedDate));
});

const totalPages = computed(() => Math.ceil(filteredRefunds.value.length / itemsPerPage));

const paginatedRefunds = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredRefunds.value.slice(start, start + itemsPerPage);
});

function formatDate(dateStr) {
  const options = { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' };
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
  if (activeFilter.value === 'pending') {
    return 'Tidak ada pengajuan yang menunggu verifikasi.';
  } else if (activeFilter.value === 'approved') {
    return 'Tidak ada pengajuan yang disetujui.';
  } else if (activeFilter.value === 'rejected') {
    return 'Tidak ada pengajuan yang ditolak.';
  } else if (activeFilter.value === 'completed') {
    return 'Belum ada refund yang selesai diproses.';
  }
  return 'Belum ada pengajuan refund. Kelola tiket Anda di halaman tiket saya.';
}

function cancelRefund(refundId) {
  const refund = refunds.find(r => r.id === refundId);
  if (refund && confirm('Apakah Anda yakin ingin membatalkan pengajuan refund ini?')) {
    const index = refunds.indexOf(refund);
    refunds.splice(index, 1);
    alert('Pengajuan refund telah dibatalkan.');
  }
}

function showRefundDetail(refund) {
  console.log('Refund detail:', refund);
  // Implementasi: buka modal detail refund
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
.refund-page {
  width: 100%;
  min-height: 100vh;
  background-color: var(--color-bg-light);
}

.refund-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 32px 16px 40px;
  font-family: 'Geist', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  color: var(--color-text-dark);
}

/* Page Header */
.page-header {
  margin-bottom: 32px;
}

.page-header h1 {
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: 700;
  color: var(--color-text-dark);
}

.page-header p {
  margin: 0;
  font-size: 14px;
  color: var(--color-text-light);
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

/* Refunds List */
.refunds-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.refund-card {
  background: var(--color-white);
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 24px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.refund-card:hover {
  border-color: var(--color-primary);
  box-shadow: 0 4px 16px rgba(22, 117, 231, 0.1);
  transform: translateY(-2px);
}

.refund-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.refund-info h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: var(--color-text-dark);
}

.refund-date {
  margin: 4px 0 0 0;
  font-size: 12px;
  color: var(--color-text-light);
}

.status-badge {
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
}

.status-badge.menunggu {
  background: rgba(245, 158, 11, 0.1);
  color: #F59E0B;
}

.status-badge.disetujui {
  background: rgba(16, 185, 129, 0.1);
  color: #10B981;
}

.status-badge.ditolak {
  background: rgba(239, 68, 68, 0.1);
  color: #EF4444;
}

.status-badge.selesai {
  background: rgba(59, 130, 246, 0.1);
  color: #3B82F6;
}

/* Ticket Info */
.ticket-info {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f0f0f0;
}

.info-row {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.info-row .label {
  font-size: 12px;
  color: var(--color-text-light);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-row .value {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-dark);
}

/* Amount Section */
.amount-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding: 16px;
  background: linear-gradient(135deg, rgba(22, 117, 231, 0.05), rgba(34, 197, 94, 0.05));
  border-radius: 12px;
  border-left: 4px solid var(--color-primary);
}

.amount-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text-light);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.amount-value {
  font-size: 18px;
  font-weight: 700;
  color: var(--color-primary);
}

/* Status Timeline */
.status-timeline {
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f0f0f0;
}

.timeline-item {
  display: flex;
  gap: 16px;
  margin-bottom: 20px;
  position: relative;
}

.timeline-item:last-child {
  margin-bottom: 0;
}

.timeline-dot {
  width: 12px;
  height: 12px;
  background: #e5e7eb;
  border-radius: 50%;
  margin-top: 2px;
  flex-shrink: 0;
}

.timeline-item.completed .timeline-dot {
  background: var(--color-primary);
  box-shadow: 0 0 0 4px rgba(22, 117, 231, 0.1);
}

.timeline-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.timeline-title {
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text-dark);
}

.timeline-date {
  font-size: 12px;
  color: var(--color-text-light);
}

.timeline-date.pending {
  color: #F59E0B;
  font-weight: 500;
}

/* Notes Section */
.notes-section {
  margin-bottom: 20px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
  border-left: 3px solid #F59E0B;
}

.notes-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--color-text-light);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: block;
  margin-bottom: 8px;
}

.notes-text {
  margin: 0;
  font-size: 13px;
  color: var(--color-text-dark);
  line-height: 1.6;
}

/* Refund Actions */
.refund-actions {
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
    padding: 20px 12px 32px;
  }

  .page-header h1 {
    font-size: 22px;
  }

  .filter-tabs {
    gap: 8px;
    margin-bottom: 20px;
  }

  .tab-btn {
    padding: 10px 16px;
    font-size: 12px;
  }

  .refund-card {
    padding: 16px;
  }

  .ticket-info {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .refund-actions {
    flex-direction: column;
  }

  .pagination {
    flex-direction: column;
    gap: 12px;
  }

  .pagination-btn {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .refund-container {
    padding: 16px 12px 24px;
  }

  .page-header h1 {
    font-size: 18px;
  }

  .refund-card {
    padding: 12px;
  }

  .refund-header {
    flex-direction: column;
    gap: 12px;
  }

  .amount-section {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
