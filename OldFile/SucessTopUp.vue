<script setup>
import { computed } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

/* =====================
   AMBIL DATA TRANSAKSI
===================== */
const saved = sessionStorage.getItem("swiftpay_invoice");
const data = saved ? JSON.parse(saved) : {};

const amount = data.amount || 0;
const service = data.service || null;

if (!service) {
    router.replace({ name: "TopUp" });
}

/* =====================
   HITUNG
===================== */
const tax = computed(() => amount * 0.11);
const total = computed(() => amount + tax.value);

const transactionDate = new Date().toLocaleString("id-ID", {
    dateStyle: "medium",
    timeStyle: "short",
});

const format = (n) => n.toLocaleString("id-ID", { minimumFractionDigits: 2 });

/* =====================
   ACTION
===================== */
const backToTopUp = () => {
    router.push({ name: "TopUp" });
};

const goToHistory = () => {
    router.push("/history-top-up");
};
</script>

<template>
    <div class="success-page">
        <div class="success-card">
            <!-- ICON -->
            <div class="icon">✅</div>

            <h2>Top Up Berhasil</h2>
            <p class="subtitle">
                Saldo SwiftPay Anda telah berhasil ditambahkan
            </p>

            <!-- SUMMARY -->
            <div class="summary">
                <div class="row">
                    <span>Metode Pembayaran</span>
                    <span>{{ service.name }}</span>
                </div>

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

            <!-- ACTION -->
            <div class="actions">
                <button class="primary" @click="backToTopUp">
                    Kembali ke Top Up
                </button>

                <button class="secondary" @click="goToHistory">
                    Lihat Riwayat
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.success-page {
    min-height: 100vh;
    background: linear-gradient(180deg, #020617, #0f172a);
    display: flex;
    align-items: center;
    justify-content: center;
}

.success-card {
    width: 420px;
    background: #1e293b;
    border-radius: 16px;
    padding: 32px;
    color: white;
    text-align: center;
}

.icon {
    font-size: 56px;
    margin-bottom: 12px;
}

.subtitle {
    font-size: 14px;
    opacity: 0.8;
    margin-bottom: 24px;
}

.summary {
    text-align: left;
    margin-bottom: 24px;
}

.row {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    margin: 10px 0;
}

.total {
    font-weight: bold;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 10px;
}

.actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.primary {
    background: #22c55e;
    color: #022c22;
    padding: 14px;
    border-radius: 10px;
    font-weight: 600;
}

.secondary {
    background: transparent;
    color: #93c5fd;
    border: 1px solid #93c5fd;
    padding: 14px;
    border-radius: 10px;
}
</style>
