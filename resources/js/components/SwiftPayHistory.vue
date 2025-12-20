<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const loading = ref(false)
const error = ref('')
const transactions = ref([])
const filterType = ref('all')
const filterStatus = ref('all')

const fetchTransactions = async () => {
    try {
        loading.value = true
        error.value = ''
        
        const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token')
        
        if (!token) {
            error.value = 'Silakan login terlebih dahulu'
            return
        }
        
        let url = '/api/v1/swiftpay/transactions?per_page=50'
        if (filterType.value !== 'all') url += `&type=${filterType.value}`
        if (filterStatus.value !== 'all') url += `&status=${filterStatus.value}`
        
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        })

        if (!response.ok) {
            throw new Error('Gagal memuat transaksi')
        }

        const data = await response.json()
        transactions.value = data.data.data || []
    } catch (err) {
        console.error('Error fetching transactions:', err)
        error.value = 'Gagal memuat data transaksi'
    } finally {
        loading.value = false
    }
}

const goBack = () => {
    router.back()
}

const formatMoney = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getTypeLabel = (type) => {
    const labels = {
        'topup': 'Top Up',
        'payment': 'Pembayaran',
        'refund': 'Pengembalian Dana',
        'adjustment': 'Penyesuaian'
    }
    return labels[type] || type
}

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Tertunda',
        'success': 'Berhasil',
        'failed': 'Gagal',
        'cancelled': 'Dibatalkan'
    }
    return labels[status] || status
}

const getStatusClass = (status) => {
    return {
        'pending': 'status-pending',
        'success': 'status-success',
        'failed': 'status-failed',
        'cancelled': 'status-cancelled'
    }[status] || ''
}

onMounted(() => {
    fetchTransactions()
})
</script>

<template>
    <div class="history-page">
        <!-- Header -->
        <header class="history-header">
            <button class="back-btn" @click="goBack">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <h1 class="history-title">Riwayat Transaksi</h1>
            <div style="width: 40px"></div>
        </header>

        <!-- Filters -->
        <div class="filters">
            <select v-model="filterType" @change="fetchTransactions" class="filter-select">
                <option value="all">Semua Tipe</option>
                <option value="topup">Top Up</option>
                <option value="payment">Pembayaran</option>
                <option value="refund">Pengembalian</option>
                <option value="adjustment">Penyesuaian</option>
            </select>

            <select v-model="filterStatus" @change="fetchTransactions" class="filter-select">
                <option value="all">Semua Status</option>
                <option value="pending">Tertunda</option>
                <option value="success">Berhasil</option>
                <option value="failed">Gagal</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
        </div>

        <!-- Content -->
        <div class="history-content">
            <!-- Loading State -->
            <div v-if="loading" class="loading-state">
                <div class="spinner"></div>
                <p>Memuat transaksi...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="error-state">
                <div class="error-icon">⚠️</div>
                <p>{{ error }}</p>
                <button @click="fetchTransactions" class="retry-btn">Coba Lagi</button>
            </div>

            <!-- Empty State -->
            <div v-else-if="transactions.length === 0" class="empty-state">
                <div class="empty-icon">📋</div>
                <p>Belum ada transaksi</p>
            </div>

            <!-- Transaction List -->
            <div v-else class="transaction-list">
                <div v-for="trx in transactions" :key="trx.id" class="transaction-item">
                    <div class="transaction-left">
                        <div class="transaction-type">
                            {{ getTypeLabel(trx.type) }}
                        </div>
                        <div class="transaction-date">
                            {{ formatDate(trx.created_at) }}
                        </div>
                    </div>

                    <div class="transaction-right">
                        <div class="transaction-amount" :class="{ 'amount-plus': trx.type === 'topup', 'amount-minus': trx.type !== 'topup' }">
                            {{ trx.type === 'topup' ? '+' : '-' }}{{ formatMoney(trx.amount) }}
                        </div>
                        <div :class="['transaction-status', getStatusClass(trx.status)]">
                            {{ getStatusLabel(trx.status) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.history-page {
    min-height: 100vh;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
}

/* HEADER */
.history-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: white;
    padding: 16px;
    border-bottom: 1px solid #e5e7eb;
    position: sticky;
    top: 0;
    z-index: 100;
}

.back-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: #1f2937;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
}

.back-btn:hover {
    background: #f3f4f6;
    border-radius: 8px;
}

.history-title {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

/* FILTERS */
.filters {
    display: flex;
    gap: 12px;
    padding: 16px;
    background: white;
    border-bottom: 1px solid #e5e7eb;
}

.filter-select {
    flex: 1;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    font-size: 13px;
    color: #374151;
    cursor: pointer;
}

.filter-select:focus {
    outline: none;
    border-color: #1675E7;
    box-shadow: 0 0 0 3px rgba(22, 117, 231, 0.1);
}

/* CONTENT */
.history-content {
    flex: 1;
    padding: 16px;
}

/* STATES */
.loading-state,
.error-state,
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 300px;
    color: #6b7280;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #e5e7eb;
    border-top-color: #1675E7;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-bottom: 12px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.error-icon,
.empty-icon {
    font-size: 48px;
    margin-bottom: 12px;
}

.error-state p {
    color: #991b1b;
    margin-bottom: 16px;
}

.retry-btn {
    background: #1675E7;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

.retry-btn:hover {
    background: #0d5acc;
}

/* TRANSACTION LIST */
.transaction-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.transaction-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 16px;
    border-radius: 12px;
    border-left: 4px solid #1675E7;
}

.transaction-left {
    flex: 1;
}

.transaction-type {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
}

.transaction-date {
    font-size: 12px;
    color: #9ca3af;
}

.transaction-right {
    text-align: right;
}

.transaction-amount {
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 4px;
}

.amount-plus {
    color: #059669;
}

.amount-minus {
    color: #dc2626;
}

.transaction-status {
    font-size: 11px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 6px;
    display: inline-block;
}

.status-success {
    background: #d1fae5;
    color: #065f46;
}

.status-pending {
    background: #fef3c7;
    color: #78350f;
}

.status-failed {
    background: #fee2e2;
    color: #991b1b;
}

.status-cancelled {
    background: #f3f4f6;
    color: #374151;
}

/* RESPONSIVE */
@media (max-width: 600px) {
    .history-header {
        padding: 12px;
    }

    .filters {
        flex-direction: column;
    }

    .history-title {
        font-size: 16px;
    }

    .transaction-item {
        padding: 12px;
    }

    .transaction-type {
        font-size: 13px;
    }

    .transaction-amount {
        font-size: 13px;
    }
}
</style>
