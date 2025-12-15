<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

/* Data Dummy */
const user = {
    name: "Andi Saputra",
    email: "andi@swiftrail.com",
    id: "SW-88291",
    balance: 250000,
};

/* STATE */
const amount = ref(0);
const selectedPayment = ref(null);

const email = ref("");

/* PAYMENT METHODS */
const paymentMethods = {
    "E-WALLET": [
        {
            id: "DANA",
            name: "DANA",
            logo: "/logos/dana.png",
        },
        {
            id: "SPAY",
            name: "ShopeePay",
            logo: "/logos/spay.png",
        },
        {
            id: "GOPAY",
            name: "GoPay",
            logo: "/logos/gopay.png",
        },
        {
            id: "OVO",
            name: "OVO",
            logo: "/logos/ovo.png",
        },
    ],

    ATM: [
        {
            id: "CIMB",
            name: "CIMB",
            logo: "/logos/cimb.png",
        },
        {
            id: "BNI",
            name: "BNI",
            logo: "/logos/bni.png",
        },
        {
            id: "BCA",
            name: "BCA",
            logo: "/logos/bca.png",
        },
        {
            id: "BANK LAIN",
            name: "Bank Lain",
            logo: "/logos/bank.png",
        },
        {
            id: "ATM NON MANDIRI",
            name: "ATM NON MANDIRI",
            logo: "/logos/mandiri.png",
        },
        {
            id: "ATM MANDIRI",
            name: "ATM MANDIRI",
            logo: "/logos/mandiri.png",
        },
    ],

    "INTERNET BANKING": [
        {
            id: "MANDIRI",
            name: "MANDIRI",
            logo: "/logos/mandiri.png",
        },
        {
            id: "CIMB",
            name: "CIMB",
            logo: "/logos/cimb.png",
        },
        {
            id: "BNI",
            name: "BNI",
            logo: "/logos/bni.png",
        },
        {
            id: "BCA",
            name: "BCA",
            logo: "/logos/bca.png",
        },
        {
            id: "BANK LAIN",
            name: "Bank Lain",
            logo: "/logos/bank.png",
        },
    ],

    "MOBILE BANKING": [
        {
            id: "MANDIRI",
            name: "MANDIRI",
            logo: "/logos/mandiri.png",
        },
        {
            id: "CIMB",
            name: "CIMB",
            logo: "/logos/cimb.png",
        },
        {
            id: "BNI",
            name: "BNI",
            logo: "/logos/bni.png",
        },
        {
            id: "BCA",
            name: "BCA",
            logo: "/logos/bca.png",
        },
        {
            id: "BANK LAIN",
            name: "Bank Lain",
            logo: "/logos/bank.png",
        },
    ],

    "SMS BANKING": [
        {
            id: "BNI",
            name: "BNI",
            logo: "/logos/bni.png",
        },
        {
            id: "BCA",
            name: "BCA",
            logo: "/logos/bca.png",
        },
    ],

    "GERAI RETAIL": [
        {
            id: "ALFAMART",
            name: "AlfaMart",
            logo: "/logos/alfamart.png",
        },
        {
            id: "INDOMARET",
            name: "Indomaret",
            logo: "/logos/indomaret.png",
        },
        {
            id: "SUPERINDO",
            name: "SuperIndo",
            logo: "/logos/superindo.png",
        },
    ],
};

const activeMethod = ref("E-WALLET");

const switchMethod = (method) => {
    activeMethod.value = method;
    selectedPayment.value = null; // reset layanan saat ganti metode
};

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

const canPay = computed(
    () =>
        amount.value >= 10000 &&
        amount.value <= 1000000 &&
        selectedPayment.value
);

/* METHODS */
const handleAmount = (e) => {
    const raw = e.target.value.replace(/\D/g, "");
    amount.value = Number(raw);
};

const format = (n) => n.toLocaleString("id-ID");

/* Bayar sekarang */
const goToInvoice = () => {
    if (!selectedPayment.value) return;

    const payload = {
        amount: amount.value,
        service: selectedPayment.value,
    };

    sessionStorage.setItem("swiftpay_invoice", JSON.stringify(payload));

    router.push({ name: "InvoiceTopUp" });
};
</script>

<template>
    <!-- Header -->
    <div class="profile-page">
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
                        <span class="logo-title">SwiftRail</span>
                        <span class="logo-subtitle">SUPER APP</span>
                    </div>
                </div>

                <nav class="nav">
                    <router-link to="/profile" class="nav-link"
                        >Profile</router-link
                    >
                    <router-link to="/history-top-up" class="nav-link"
                        >History</router-link
                    >
                </nav>
            </div>
        </header>

        <!-- CONTENT -->
        <main class="container">
            <!-- LEFT -->
            <section class="left">
                <!-- PROFILE CARD -->
                <div class="card profile">
                    <h3>{{ user.name }}</h3>
                    <p>{{ user.email }}</p>
                    <p>ID: {{ user.id }}</p>

                    <div class="balance">
                        <span>Saldo Anda: </span>
                        <strong>Rp {{ format(user.balance) }}</strong>
                    </div>
                </div>

                <!-- TOPUP AMOUNT -->
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

                <!-- PAYMENT METHOD -->
                <div class="card payment-wrapper">
                    <h3>Metode Pembayaran</h3>

                    <div class="payment-layout">
                        <!-- LEFT : METHOD LIST -->
                        <div class="method-list">
                            <button
                                v-for="method in Object.keys(paymentMethods)"
                                :key="method"
                                :class="{ active: activeMethod === method }"
                                @click="switchMethod(method)"
                            >
                                {{ method }}
                            </button>
                        </div>

                        <!-- RIGHT : SERVICES -->
                        <div class="service-list">
                            <div
                                v-for="service in paymentMethods[activeMethod]"
                                :key="service.id"
                                class="service-card"
                                :class="{
                                    active: selectedPayment?.id === service.id,
                                }"
                                @click="selectedPayment = service"
                            >
                                <div class="service-logo">
                                    <img :src="service.logo" alt="" />
                                </div>

                                <div class="service-text">
                                    {{ service.name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- RIGHT / CHECKOUT -->
            <aside class="checkout">
                <h3>Ringkasan Top Up SwiftPay</h3>

                <div class="summary">
                    <p class="label">Nominal Top Up</p>
                    <p class="value">Rp {{ format(amount) }}</p>
                </div>

                <div class="summary">
                    <p class="label">Metode Pembayaran</p>
                    <p class="value">
                        {{ selectedPayment?.name || "Belum dipilih" }}
                    </p>
                </div>

                <div class="field">
                    <label>Email (opsional)</label>
                    <input
                        type="email"
                        placeholder="contoh@email.com"
                        v-model="email"
                    />
                </div>

                <!-- INFO -->
                <div class="info-box">
                    ℹ️ Saldo SwiftPay akan bertambah setelah pembayaran
                    berhasil.
                </div>

                <button
                    :disabled="!canPay"
                    class="pay-btn"
                    @click="goToInvoice"
                >
                    Bayar Sekarang
                </button>

                <!-- LEGAL -->
                <p class="legal">
                    Dengan melanjutkan, Anda menyetujui
                    <span>Syarat & Ketentuan</span> SwiftRail.
                </p>
            </aside>
        </main>
    </div>
</template>

<style scoped>
.profile-page {
    width: 100%;
    min-height: 100vh;
    background: var(--color-bg-light);
    padding-top: 64px;
}

/* Header */
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
}

.nav-link:hover {
    color: var(--color-primary);
}

/* LAYOUT */
.container {
    display: flex;
    gap: 24px;
    padding: 32px;
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

/* CHECKOUT DETAIL */
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

/* FIELD */
.field {
    margin-bottom: 16px;
}

.field label {
    font-size: 13px;
    color: #374151;
    display: block;
    margin-bottom: 6px;
}

/* INFO BOX */
.info-box {
    background: #f0f6ff;
    color: #1e3a8a;
    font-size: 13px;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
}

/* BUTTON */
.pay-btn {
    width: 100%;
    padding: 14px;
    font-size: 15px;
    font-weight: 600;
    border-radius: 10px;
    background: #1e88e5;
    color: white;
}

.pay-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* LEGAL */
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

/* CARD */
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

/* INPUT */
input {
    width: 100%;
    padding: 12px;
    margin-top: 8px;
    border-radius: 8px;
    border: 1px solid #ddd;
}

input:focus {
    outline: none;
    border-color: #1e88e5;
}

/* PAYMENT */
.payment-group {
    margin-top: 16px;
}

.payment-wrapper {
    display: flex;
    flex-direction: column;
}

.payment-layout {
    display: flex;
    margin-top: 16px;
    gap: 16px;
}

/* KIRI: daftar metode */
.method-list {
    width: 220px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.method-list button {
    text-align: left;
    padding: 12px;
    border-radius: 8px;
    background: #f1f1f1;
}

.method-list button.active {
    background: #1e88e5;
    color: white;
}

/* GRID LAYANAN */
.service-list {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

/* CARD LAYANAN */
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

/* LOGO */
.service-logo {
    width: 48px;
    height: 48px;
    background: white;
    border-radius: 10px;
    flex-shrink: 0;
}

/* TEXT */
.service-card:hover .service-text,
.service-card.active .service-text {
    color: white;
}

.service-text {
    font-size: 14px;
    font-weight: 500;
    color: #000000;
}

/* HOVER */
.service-card:hover {
    background: #1e88e5;
    outline: 2px solid #ffffff;
}

/* ACTIVE (TERPILIH) */
.service-card.active {
    background: #1e88e5;
    outline: 2px solid #ffffff;
}

button {
    padding: 10px 14px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    background: #e0e0e0;
}

button.active {
    background: #1e88e5;
    color: white;
}

button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* TEXT */
.hint {
    color: #777;
}

.error {
    color: red;
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .container {
        flex-direction: column;
    }

    .checkout {
        position: static;
    }
}
</style>
