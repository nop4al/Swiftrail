<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const currentStep = ref("form"); // form, invoice, success

/* API STATE */
const loading = ref(false);
const error = ref("");
const wallet = ref(null);
const user = ref({
    name: "",
    email: "",
    id: "",
    balance: 0,
});

/* STATE */
const amount = ref(0);
const email = ref("");

/* COMPUTED */
const displayAmount = computed(() =>
    amount.value ? `Rp ${amount.value.toLocaleString("id-ID")}` : ""
);

const amountError = computed(() => {
    if (!amount.value) return "";
    if (amount.value < 10000) return "Minimal top up Rp 10.000";
    if (amount.value > 1000000) return "Maksimal top up Rp 1.000.000";
    return "";
});

const canPay = computed(() => amount.value >= 10000 && amount.value <= 1000000);

const tax = computed(() => amount.value * 0.11);
const total = computed(() => amount.value + tax.value);

/* GENERATE ID */
const orderId = computed(
    () =>
        `ORD-${new Date()
            .toISOString()
            .slice(0, 10)
            .replaceAll("-", "")}-${Math.floor(Math.random() * 9000 + 1000)}`
);

const transactionId = computed(
    () => `TRX-${Math.random().toString(36).substring(2, 10).toUpperCase()}`
);

/* TRANSACTION DATE */
const transactionDate = computed(() =>
    new Date().toLocaleString("id-ID", {
        dateStyle: "medium",
        timeStyle: "short",
    })
);

/* METHODS */
const handleAmount = (e) => {
    const raw = e.target.value.replace(/\D/g, "");
    amount.value = Number(raw);
};

const format = (n) => {
    // Handle NaN and undefined
    if (!n || isNaN(n)) return "0";
    return Number(n).toLocaleString("id-ID", { minimumFractionDigits: 0, maximumFractionDigits: 0 });
};

/* FETCH WALLET DATA */
const fetchWallet = async () => {
    try {
        loading.value = true;
        error.value = "";
        
        // Get token from localStorage or sessionStorage
        let token = localStorage.getItem("auth_token") || sessionStorage.getItem("auth_token");
        
        // If no token, try to get from window
        if (!token && window.__AUTH_TOKEN__) {
            token = window.__AUTH_TOKEN__;
        }
        
        if (!token) {
            throw new Error("Token autentikasi tidak ditemukan. Silakan login kembali.");
        }
        
        const response = await fetch("/api/v1/swiftpay/wallet", {
            method: "GET",
            headers: {
                "Authorization": `Bearer ${token}`,
                "Content-Type": "application/json",
                "Accept": "application/json",
            },
        });

        // Debug info
        console.log("Wallet response status:", response.status);
        const data = await response.json();
        console.log("Wallet response data:", data);

        if (!response.ok) {
            throw new Error(data.message || `API Error: ${response.status}`);
        }

        wallet.value = data.data;
        
        // Update user info from wallet data
        user.value.name = data.data.user?.name || "User";
        user.value.email = data.data.user?.email || "";
        user.value.id = `WL-${data.data.id}`;
        
        // Fix balance parsing - handle both numeric dan string format
        const balanceValue = data.data.balance || data.data.current_balance || 0;
        user.value.balance = typeof balanceValue === 'string' ? parseFloat(balanceValue) : Number(balanceValue) || 0;
        
        console.log("Balance parsed:", user.value.balance);
    } catch (err) {
        console.error("Error fetching wallet:", err);
        error.value = err.message || "Gagal memuat data wallet. Coba lagi.";
    } finally {
        loading.value = false;
    }
};

/* STEP: FORM -> INVOICE */
const goToInvoice = () => {
    if (amount.value < 10000) {
        alert("Minimal top up Rp 10.000");
        return;
    }
    if (amount.value > 1000000) {
        alert("Maksimal top up Rp 1.000.000");
        return;
    }
    currentStep.value = "invoice";
};

/* STEP: INVOICE -> SUCCESS (Call SwiftPay Topup API) */
const confirmPayment = async () => {
    try {
        loading.value = true;
        error.value = "";
        const token = localStorage.getItem("auth_token") || sessionStorage.getItem("auth_token");
        
        const payload = {
            amount: Math.round(amount.value),
            payment_method: "bank",
            bank_name: "BCA",
        };

        const response = await fetch("/api/v1/swiftpay/topup", {
            method: "POST",
            headers: {
                "Authorization": `Bearer ${token}`,
                "Content-Type": "application/json",
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || "Gagal melakukan topup");
        }

        // Update wallet balance after successful topup
        wallet.value = data.data?.wallet || wallet.value;
        const balanceValue = wallet.value?.balance || data.data?.balance || 0;
        user.value.balance = typeof balanceValue === 'string' ? parseFloat(balanceValue) : Number(balanceValue) || 0;
        
        currentStep.value = "success";
    } catch (err) {
        console.error("Error confirming payment:", err);
        error.value = err.message || "Terjadi kesalahan. Silakan coba lagi.";
        alert(error.value);
    } finally {
        loading.value = false;
    }
};

/* BACK NAVIGATION */
const goBackToForm = () => {
    currentStep.value = "form";
};

const backToForm = () => {
    currentStep.value = "form";
};

const goToHistory = () => {
    router.push({ name: "SwiftPay" });
};

const goToProfile = () => {
    router.push({ name: "Profile" });
};

/* LIFECYCLE */
onMounted(() => {
    fetchWallet();
});
</script>

<template>
    <!-- LOADING OVERLAY -->
    <div v-if="loading" class="loading-overlay">
        <div class="spinner"></div>
        <p>Memproses...</p>
    </div>

    <!-- ERROR MESSAGE -->
    <div v-if="error" class="error-banner">
        <span>⚠️ {{ error }}</span>
        <button @click="error = ''">×</button>
    </div>

    <!-- STEP 1: FORM -->
    <div v-if="currentStep === 'form'" class="profile-page">
        <header class="header">
            <div class="header-container">
                <div class="logo">
                    <div class="logo-icon">
                        <svg
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <rect
                                width="24"
                                height="24"
                                rx="6"
                                fill="#1675E7"
                            />
                            <path
                                d="M6 8C6 6.89543 6.89543 6 8 6H16C17.1046 6 18 6.89543 18 8V14C18 15.1046 17.1046 16 16 16H8C6.89543 16 6 15.1046 6 14V8Z"
                                fill="white"
                            />
                            <rect
                                x="8"
                                y="8"
                                width="3"
                                height="2"
                                rx="0.5"
                                fill="#1675E7"
                            />
                            <rect
                                x="13"
                                y="8"
                                width="3"
                                height="2"
                                rx="0.5"
                                fill="#1675E7"
                            />
                            <circle cx="9" cy="18" r="1.5" fill="white" />
                            <circle cx="15" cy="18" r="1.5" fill="white" />
                        </svg>
                    </div>
                    <div class="logo-text">
                        <span class="logo-title">SwiftPay</span>
                        <span class="logo-subtitle">SUPER APP</span>
                    </div>
                </div>

                <nav class="nav">
                    <button class="nav-link" @click="goToProfile">
                        Profile
                    </button>
                    <button class="nav-link" @click="goToHistory">
                        History
                    </button>
                </nav>
            </div>
        </header>

        <main class="container">
            <section class="left">
                <div class="card profile">
                    <div v-if="loading" class="loading-skeleton">
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line short"></div>
                        <div class="skeleton-line short"></div>
                    </div>
                    <div v-else>
                        <h3>{{ user.name || "Memuat..." }}</h3>
                        <p>{{ user.email || "-" }}</p>
                        <p>ID: {{ user.id || "-" }}</p>

                        <div v-if="user.balance && user.balance > 0" class="balance">
                            <span>Saldo Anda: </span>
                            <strong>Rp {{ format(user.balance) }}</strong>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3>Nominal Top Up SwiftPay</h3>

                    <input
                        type="text"
                        :value="displayAmount"
                        @input="handleAmount"
                        placeholder="Rp 0"
                    />

                    <small class="hint">
                        Minimal Rp 10.000 · Maksimal Rp 1.000.000
                    </small>

                    <small v-if="amountError" class="error">
                        {{ amountError }}
                    </small>
                </div>
            </section>

            <aside class="checkout">
                <h3>Ringkasan Top Up SwiftPay</h3>

                <div class="summary">
                    <p class="label">Nominal Top Up</p>
                    <p class="value">Rp {{ format(amount) }}</p>
                </div>

                <div class="field">
                    <label>Email (opsional)</label>
                    <input
                        type="email"
                        placeholder="contoh@email.com"
                        v-model="email"
                    />
                </div>

                <div class="info-box">
                    ℹ️ Saldo SwiftPay akan bertambah setelah pembayaran
                    berhasil.
                </div>

                <button
                    :disabled="!canPay || loading"
                    class="pay-btn"
                    @click="goToInvoice"
                >
                    {{ loading ? "Memproses..." : "Bayar Sekarang" }}
                </button>

                <p class="legal">
                    Dengan melanjutkan, Anda menyetujui
                    <span>Syarat & Ketentuan</span> SwiftPay.
                </p>
            </aside>
        </main>
    </div>

    <!-- STEP 2: INVOICE POPUP -->
    <div v-if="currentStep === 'invoice'" class="invoice-page">
        <div class="invoice-card">
            <button type="button" class="back-btn" @click="goBackToForm">
                ×
            </button>

            <div class="invoice-top">
                <p class="method">SwiftPay Top Up</p>

                <p class="total">IDR {{ format(total) }}</p>
            </div>

            <div class="invoice-bottom">
                <div class="row">
                    <span>Order ID</span>
                    <span>{{ orderId }}</span>
                </div>
                <div class="row">
                    <span>Transaction ID</span>
                    <span>{{ transactionId }}</span>
                </div>
                <div class="row">
                    <span>Tanggal Transaksi</span>
                    <span>{{ transactionDate }}</span>
                </div>

                <hr />

                <div class="row">
                    <span>Harga</span>
                    <span>IDR {{ format(amount) }}</span>
                </div>
                <div class="row">
                    <span>Tax (PPN 11%)</span>
                    <span>IDR {{ format(tax) }}</span>
                </div>

                <div class="row total-row">
                    <span>Total</span>
                    <span>IDR {{ format(total) }}</span>
                </div>

                <button class="confirm-btn" @click="confirmPayment" :disabled="loading">
                    {{ loading ? "MEMPROSES..." : "KONFIRMASI" }}
                </button>

                <p class="legal">
                    Dengan klik "Konfirmasi", Anda menyetujui Syarat & Ketentuan
                    SwiftPay
                </p>
            </div>
        </div>
    </div>

    <!-- STEP 3: SUCCESS POPUP -->
    <div v-if="currentStep === 'success'" class="success-page">
        <div class="success-card">
            <div class="icon">✅</div>

            <h2>Top Up Berhasil</h2>
            <p class="subtitle">
                Saldo SwiftPay Anda telah berhasil ditambahkan
            </p>

            <div class="summary">
                <div class="row">
                    <span>Nominal Top Up</span>
                    <span>IDR {{ format(amount) }}</span>
                </div>

                <div class="row">
                    <span>Tax (PPN 11%)</span>
                    <span>IDR {{ format(tax) }}</span>
                </div>

                <div class="row total">
                    <span>Total</span>
                    <span>IDR {{ format(total) }}</span>
                </div>

                <div class="row">
                    <span>Tanggal</span>
                    <span>{{ transactionDate }}</span>
                </div>
            </div>

            <div class="actions">
                <button class="primary" @click="backToForm">
                    Kembali ke Top Up
                </button>

                <button class="secondary" @click="goToHistory">
                    Lihat Riwayat
                </button>
            </div>
        </div>
    </div>

    <!-- STEP 2: INVOICE POPUP -->
    <div v-if="currentStep === 'invoice'" class="invoice-page">
        <div class="invoice-card">
            <button type="button" class="back-btn" @click="goBackToForm">
                ×
            </button>

            <div class="invoice-top">
                <p class="method">SwiftPay Top Up</p>

                <p class="total">IDR {{ format(total) }}</p>
            </div>

            <div class="invoice-bottom">
                <div class="row">
                    <span>Order ID</span>
                    <span>{{ orderId }}</span>
                </div>
                <div class="row">
                    <span>Transaction ID</span>
                    <span>{{ transactionId }}</span>
                </div>
                <div class="row">
                    <span>Tanggal Transaksi</span>
                    <span>{{ transactionDate }}</span>
                </div>

                <hr />

                <div class="row">
                    <span>Harga</span>
                    <span>IDR {{ format(amount) }}</span>
                </div>
                <div class="row">
                    <span>Tax (PPN 11%)</span>
                    <span>IDR {{ format(tax) }}</span>
                </div>

                <div class="row total-row">
                    <span>Total</span>
                    <span>IDR {{ format(total) }}</span>
                </div>

                <button class="confirm-btn" @click="confirmPayment" :disabled="loading">
                    {{ loading ? "MEMPROSES..." : "KONFIRMASI" }}
                </button>

                <p class="legal">
                    Dengan klik "Konfirmasi", Anda menyetujui Syarat & Ketentuan
                    SwiftPay
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ===== LOADING & ERROR STATES ===== */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.loading-overlay p {
    color: white;
    margin-top: 16px;
    font-size: 14px;
}

.error-banner {
    position: fixed;
    top: 70px;
    left: 50%;
    transform: translateX(-50%);
    background: #fee2e2;
    color: #991b1b;
    padding: 12px 16px;
    border-radius: 8px;
    display: flex;
    gap: 12px;
    align-items: center;
    z-index: 1500;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.error-banner button {
    background: none;
    border: none;
    color: #991b1b;
    cursor: pointer;
    font-size: 18px;
}

.loading-skeleton {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.skeleton-line {
    height: 16px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    border-radius: 4px;
    animation: loading 1.5s infinite;
}

.skeleton-line.short {
    width: 60%;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* ===== FORM STEP ===== */
.profile-page {
    width: 100%;
    min-height: 100vh;
    background: var(--color-bg-light);
    padding-top: 64px;
}

.header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: var(--color-white);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    z-index: 100;
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0.875rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.logo-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}

.logo-text {
    display: flex;
    flex-direction: column;
}

.logo-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--color-text-dark);
    line-height: 1.2;
}

.logo-subtitle {
    font-size: 0.625rem;
    font-weight: 600;
    color: var(--color-text-light);
    letter-spacing: 0.1em;
}

.nav {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.nav-link {
    font-size: 0.9375rem;
    font-weight: 500;
    color: var(--color-text-dark);
    padding: 0.5rem 1rem;
    background: none;
    border: none;
    cursor: pointer;
}

.nav-link:hover {
    color: var(--color-primary);
}

.container {
    display: flex;
    gap: 24px;
    padding: 32px;
    max-width: 1200px;
    margin: 0 auto;
}

.left {
    flex: 2;
}

.checkout {
    flex: 1;
    background: white;
    padding: 24px;
    border-radius: 14px;
    position: sticky;
    top: 90px;
    height: fit-content;
}

.summary {
    margin-bottom: 16px;
}

.summary .label {
    font-size: 13px;
    color: #6b7280;
}

.summary .value {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}

.field {
    margin-bottom: 16px;
}

.field label {
    font-size: 13px;
    color: #374151;
    display: block;
    margin-bottom: 6px;
}

.info-box {
    background: #f0f6ff;
    color: #1e3a8a;
    font-size: 13px;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.pay-btn {
    width: 100%;
    padding: 14px;
    font-size: 15px;
    font-weight: 600;
    border-radius: 10px;
    background: #1e88e5;
    color: white;
    border: none;
    cursor: pointer;
}

.pay-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.legal {
    font-size: 12px;
    color: #6b7280;
    margin-top: 12px;
    text-align: center;
}

.legal span {
    color: #1e88e5;
    cursor: pointer;
}

.card {
    background: white;
    padding: 24px;
    border-radius: 14px;
    margin-bottom: 24px;
}

.profile .balance {
    margin-top: 16px;
    padding: 12px;
    background: #f0f6ff;
    border-radius: 10px;
}

input {
    width: 100%;
    padding: 12px;
    margin-top: 8px;
    border-radius: 8px;
    border: 1px solid #ddd;
    font-size: 14px;
}

input:focus {
    outline: none;
    border-color: #1e88e5;
}

.payment-wrapper {
    display: flex;
    flex-direction: column;
}

.service-list {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-top: 16px;
}

.service-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    min-height: 80px;
    background: #f0f6ff;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.service-logo {
    width: 48px;
    height: 48px;
    background: white;
    border-radius: 10px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.service-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.service-text {
    font-size: 14px;
    font-weight: 500;
    color: #000000;
}

.service-card:hover,
.service-card.active {
    background: #1e88e5;
}

.service-card:hover .service-text,
.service-card.active .service-text {
    color: white;
}

.hint {
    color: #777;
    font-size: 12px;
}

.error {
    color: red;
    font-size: 12px;
}

/* ===== INVOICE STEP ===== */
.invoice-page {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 1000;
}

.invoice-card {
    width: 420px;
    border-radius: 16px;
    overflow: hidden;
    background: white;
    position: relative;
}

.invoice-top {
    background: #1e293b;
    color: white;
    padding: 32px 24px 28px;
    text-align: center;
}

.invoice-top .method {
    opacity: 0.8;
    margin-top: 4px;
}

.invoice-top .total {
    font-size: 28px;
    font-weight: bold;
    margin-top: 12px;
}

.invoice-bottom {
    background: white;
    color: #111827;
    padding: 24px;
}

.invoice-bottom .row {
    display: flex;
    justify-content: space-between;
    margin: 12px 0;
    font-size: 14px;
}

.invoice-bottom hr {
    border: none;
    border-top: 1px solid #e5e7eb;
    margin: 16px 0;
}

.invoice-bottom .total-row {
    font-weight: bold;
}

.confirm-btn {
    width: 100%;
    padding: 14px;
    border-radius: 999px;
    background: #fb923c;
    color: #020617;
    font-weight: bold;
    margin-top: 24px;
    border: none;
    cursor: pointer;
}

.back-btn {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 36px;
    height: 36px;
    background: rgba(0, 0, 0, 0.25);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: none;
    color: white;
    font-size: 20px;
    transition: background 0.2s ease;
}

.back-btn:hover {
    background: rgba(255, 255, 255, 0.18);
}

/* ===== SUCCESS STEP ===== */
.success-page {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 1000;
}

.success-card {
    width: 420px;
    background: white;
    border-radius: 16px;
    padding: 32px;
    color: #1a1a2e;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.icon {
    font-size: 56px;
    margin-bottom: 12px;
}

.success-card h2 {
    color: #1a1a2e;
    margin-bottom: 8px;
}

.subtitle {
    font-size: 14px;
    opacity: 0.7;
    margin-bottom: 24px;
    color: #555;
}

.summary {
    text-align: left;
    margin-bottom: 24px;
    background: #f8f9ff;
    padding: 16px;
    border-radius: 8px;
}

.success-card .row {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    margin: 10px 0;
    color: #333;
}

.success-card .total {
    font-weight: bold;
    border-top: 1px solid #e0e0e0;
    padding-top: 10px;
    color: #1a1a2e;
}

.actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.primary {
    background: #1e88e5;
    color: white;
    padding: 14px;
    border-radius: 10px;
    font-weight: 600;
    border: none;
    cursor: pointer;
}

.secondary {
    background: white;
    color: #1e88e5;
    border: 2px solid #1e88e5;
    padding: 12px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .container {
        flex-direction: column;
    }

    .checkout {
        position: static;
    }

    .payment-layout {
        flex-direction: column;
    }

    .method-list {
        width: 100%;
    }

    .service-list {
        grid-template-columns: repeat(3, 1fr);
    }

    .invoice-card,
    .success-card {
        width: 100%;
        max-width: 420px;
    }
}

@media (max-width: 600px) {
    .service-list {
        grid-template-columns: repeat(2, 1fr);
    }

    .header-container {
        flex-direction: column;
        gap: 16px;
    }

    .nav {
        width: 100%;
        justify-content: center;
    }
}
</style>
