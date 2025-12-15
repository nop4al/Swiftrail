<script setup>
import { computed } from "vue";
import { useRouter, useRoute } from "vue-router";

const router = useRouter();
const route = useRoute();

/* =====================
   DATA DARI TOPUP
===================== */
const saved = sessionStorage.getItem("swiftpay_invoice");
const invoiceData = saved ? JSON.parse(saved) : {};

const amount = invoiceData.amount || 0;
const service = invoiceData.service || null;

if (!service) {
    router.replace({ name: "TopUp" });
}
/* =====================
   GENERATE ID
===================== */
const orderId = `ORD-${new Date()
    .toISOString()
    .slice(0, 10)
    .replaceAll("-", "")}-${Math.floor(Math.random() * 9000 + 1000)}`;

const transactionId = `TRX-${Math.random()
    .toString(36)
    .substring(2, 10)
    .toUpperCase()}`;

/* =====================
   DATE & TIME
===================== */
const transactionDate = new Date().toLocaleString("id-ID", {
    dateStyle: "medium",
    timeStyle: "short",
});

/* =====================
   TAX & TOTAL
===================== */
const tax = computed(() => amount * 0.11);
const total = computed(() => amount + tax.value);

/* =====================
   FORMAT RUPIAH
===================== */
const format = (n) => n.toLocaleString("id-ID", { minimumFractionDigits: 2 });

/* =====================
   ACTION
===================== */
const confirmPayment = () => {
    router.push("/payment-qr");
};

const goBack = () => {
    console.log("Back clicked");
    router.push("/top-up");
};
</script>

<template>
    <div class="invoice-page">
        <div class="invoice-card">
            <!-- BACK BUTTON -->
            <button type="button" class="back-btn" @click="goBack">
                <img src="" alt="Kembali" />
            </button>

            <!-- TOP (BIRU) -->
            <div class="invoice-top">
                <div class="logo">
                    <img :src="service.logo" :alt="service.name" />
                </div>

                <p class="method">{{ service.name }}</p>

                <p class="total">IDR {{ format(total) }}</p>
            </div>

            <!-- BOTTOM (PUTIH) -->
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
                    <span>Metode Pembayaran</span>
                    <span>{{ service.name }}</span>
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

                <button class="confirm-btn" @click="confirmPayment">
                    KONFIRMASI
                </button>

                <p class="legal">
                    Dengan klik “Konfirmasi”, Anda menyetujui Syarat & Ketentuan
                    SwiftRail
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.invoice-page {
    min-height: 100vh;
    background: linear-gradient(180deg, #ffffff, #babdcb);
    display: flex;
    align-items: center;
    justify-content: center;
}

.invoice-card {
    width: 420px;
    border-radius: 16px;
    overflow: hidden; /* PENTING */
    background: white;
    position: relative;
}

.invoice-top {
    background: #1e293b; /* biru gelap */
    color: white;
    padding: 32px 24px 28px;
    text-align: center;
}

.invoice-top .logo img {
    width: 48px;
    height: 48px;
    margin-bottom: 8px;
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
}

.legal {
    font-size: 11px;
    opacity: 0.7;
    text-align: center;
    margin-top: 12px;
}

.back-btn {
    position: absolute;
    top: 16px;
    left: 16px;

    width: 36px;
    height: 36px;

    background: rgba(0, 0, 0, 0.25);
    border-radius: 10px;

    display: flex;
    align-items: center;
    justify-content: center;

    cursor: pointer;
    border: none;
}

.back-btn img {
    width: 18px;
    height: 18px;
    transition: background 0.2s ease;
}

.back-btn:hover {
    background: rgba(255, 255, 255, 0.18);
}
</style>
