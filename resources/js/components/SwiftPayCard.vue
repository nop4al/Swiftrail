<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/config/axios'
import PaymentModal from './modals/PaymentModal.vue'

const router = useRouter()
const loading = ref(false)
const error = ref('')
const wallet = ref(null)
const balance = ref(0)
const paymentModal = ref(null)

// Check if user is logged in
const isLoggedIn = computed(() => {
    return !!localStorage.getItem('auth_token') || !!sessionStorage.getItem('auth_token')
})

const fetchWallet = async () => {
    // Don't fetch if not logged in
    if (!isLoggedIn.value) {
        return
    }
    
    try {
        loading.value = true
        error.value = ''
        
        const response = await axios.get('/swiftpay/wallet')
        wallet.value = response.data.data
        balance.value = parseFloat(response.data.data.balance) || 0
    } catch (err) {
        console.error('Error fetching wallet:', err)
        error.value = err.message || 'Gagal memuat wallet'
    } finally {
        loading.value = false
    }
}

const goToTopUp = () => {
    router.push({ name: 'SwiftPay' })
}

const goToHistory = () => {
    router.push({ name: 'SwiftPayHistory' })
}

const goToScan = () => {
    // Will implement QR scan later
    alert('Fitur scan akan segera tersedia')
}

onMounted(() => {
    fetchWallet()
})

const formatMoney = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount)
}
</script>

<template>
    <div class="swiftpay-card-container" v-if="isLoggedIn">
        <!-- Loading State -->
        <div v-if="loading" class="swiftpay-card skeleton">
            <div class="skeleton-line"></div>
            <div class="skeleton-line short"></div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="swiftpay-card error">
            <div class="error-icon">⚠️</div>
            <p>{{ error }}</p>
            <button @click="fetchWallet" class="retry-btn">Coba Lagi</button>
        </div>

        <!-- Card Content -->
        <div v-else-if="wallet" class="swiftpay-card">
            <!-- Card Header -->
            <div class="card-header">
                <div class="card-logo">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="24" height="24" rx="6" fill="#1675E7"/>
                        <path d="M6 8C6 6.89543 6.89543 6 8 6H16C17.1046 6 18 6.89543 18 8V14C18 15.1046 17.1046 16 16 16H8C6.89543 16 6 15.1046 6 14V8Z" fill="white"/>
                        <rect x="8" y="8" width="3" height="2" rx="0.5" fill="#1675E7"/>
                        <rect x="13" y="8" width="3" height="2" rx="0.5" fill="#1675E7"/>
                        <circle cx="9" cy="18" r="1.5" fill="white"/>
                        <circle cx="15" cy="18" r="1.5" fill="white"/>
                    </svg>
                </div>
                <div class="card-title">SwiftPay</div>
            </div>

            <!-- Balance Section -->
            <div class="balance-section">
                <div class="balance-label">Saldo Anda</div>
                <div class="balance-amount">{{ formatMoney(balance) }}</div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="action-btn scan" @click="goToScan" title="Scan QR">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="14" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="3" y="14" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <line x1="17" y1="14" x2="21" y2="14" stroke="currentColor" stroke-width="2"/>
                        <line x1="14" y1="17" x2="21" y2="17" stroke="currentColor" stroke-width="2"/>
                        <line x1="17" y1="20" x2="21" y2="20" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span>Scan</span>
                </button>

                <button class="action-btn topup" @click="goToTopUp" title="Top Up">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span>Top Up</span>
                </button>

                <button class="action-btn history" @click="goToHistory" title="Riwayat">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 7V12L16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span>Riwayat</span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.swiftpay-card-container {
    width: 100%;
    margin-bottom: 24px;
    padding: 0 1.5rem;
}

/* CARD STYLING */
.swiftpay-card {
    background: linear-gradient(135deg, #1675E7 0%, #0d5acc 100%);
    border-radius: 16px;
    padding: 24px;
    color: white;
    box-shadow: 0 10px 40px rgba(22, 117, 231, 0.15), 0 2px 10px rgba(22, 117, 231, 0.08);
    position: relative;
    overflow: hidden;
}

.swiftpay-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
    background-size: 20px 20px;
    pointer-events: none;
}

/* CARD HEADER */
.card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.card-logo {
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-title {
    font-size: 18px;
    font-weight: 700;
    letter-spacing: 0.5px;
}

/* BALANCE SECTION */
.balance-section {
    margin-bottom: 24px;
    position: relative;
    z-index: 1;
    text-align: left;
}

.balance-label {
    font-size: 12px;
    opacity: 0.85;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
    font-weight: 500;
}

.balance-amount {
    font-size: 2.2rem;
    font-weight: 800;
    letter-spacing: -1px;
    line-height: 1;
}

/* ACTION BUTTONS */
.action-buttons {
    display: flex;
    gap: 12px;
    justify-content: space-around;
    position: relative;
    z-index: 1;
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 12px;
    padding: 14px 16px;
    color: white;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 12px;
    font-weight: 600;
    flex: 1;
    min-height: 80px;
    justify-content: center;
}

.action-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-2px);
}

.action-btn:active {
    transform: translateY(0);
}

/* SKELETON LOADING */
.swiftpay-card.skeleton {
    background: linear-gradient(90deg, #e0e7ff 25%, #f0f4ff 50%, #e0e7ff 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    height: 160px;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.skeleton-line {
    height: 16px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
    margin-bottom: 12px;
}

.skeleton-line.short {
    width: 60%;
}

/* ERROR STATE */
.swiftpay-card.error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    background: #fee2e2;
    color: #991b1b;
    min-height: 160px;
    padding: 24px;
}

.error-icon {
    font-size: 32px;
}

.error p {
    margin: 0;
    font-size: 14px;
}

.retry-btn {
    background: #991b1b;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    margin-top: 8px;
}

.retry-btn:hover {
    background: #7f1515;
}

/* RESPONSIVE */
@media (max-width: 600px) {
    .swiftpay-card {
        padding: 16px;
    }

    .balance-amount {
        font-size: 1.5rem;
    }

    .balance-section {
        margin-bottom: 20px;
    }

    .action-btn {
        padding: 10px 12px;
        font-size: 10px;
    }

    .card-header {
        margin-bottom: 20px;
    }

    .balance-section {
        margin-bottom: 20px;
    }
}
</style>
