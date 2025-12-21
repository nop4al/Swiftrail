<script setup>
import { ref, computed, onMounted, nextTick, watch } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const props = defineProps({
    isModal: {
        type: Boolean,
        default: false, // false = page mode, true = modal mode
    },
});

const currentStep = ref("form"); // form, invoice, success
const showModal = ref(props.isModal); // Control modal visibility

/* USER DATA */
const user = ref({
    name: "-",
    email: "-",
    id: "-",
    balance: 0,
});
const loading = ref(false);

// Parse dari localStorage langsung di setup
const storedProfile = localStorage.getItem("userProfile");
console.log("=== SwiftpayTopup.vue - storedProfile ===", storedProfile);

if (storedProfile) {
    try {
        const profile = JSON.parse(storedProfile);
        console.log("=== Parsed profile ===", profile);

        const firstName = profile.first_name || profile.name || "-";
        const lastName = profile.last_name || "";
        const fullName = lastName ? `${firstName} ${lastName}` : firstName;

        console.log("=== Building fullName ===", {
            firstName,
            lastName,
            fullName,
        });

        user.value = {
            name: fullName,
            email: profile.email || "-",
            id: profile.user_id || profile.id || "-",
            balance: 0,
        };
        console.log("=== User value updated ===", user.value);
    } catch (e) {
        console.error("=== Error parsing localStorage profile ===", e);
    }
} else {
    console.warn("=== No userProfile in localStorage ===");
}

/* STATE */
const amount = ref(0);
const emailInput = ref("");
const successAmount = ref(0); // Track amount for success page
const successTax = ref(0); // Track tax for success page
const successTotal = ref(0); // Track total for success page

/* LOAD USER DATA ON MOUNT */
onMounted(async () => {
    // Check query params for payment callback
    const urlParams = new URLSearchParams(window.location.search);
    const paymentStatus = urlParams.get("status");
    const orderId = urlParams.get("order_id");

    console.log("=== Payment Callback ===");
    console.log("Status:", paymentStatus);
    console.log("Order ID:", orderId);

    if (paymentStatus === "success") {
        console.log("Payment success detected!");
        currentStep.value = "success";
        // Clean up URL
        window.history.replaceState(
            {},
            document.title,
            window.location.pathname
        );
    } else if (paymentStatus === "error") {
        alert("Pembayaran gagal. Silakan coba lagi.");
        currentStep.value = "form";
        window.history.replaceState(
            {},
            document.title,
            window.location.pathname
        );
    } else if (paymentStatus === "pending") {
        alert("Pembayaran sedang diproses. Silakan tunggu...");
        currentStep.value = "form";
        window.history.replaceState(
            {},
            document.title,
            window.location.pathname
        );
    }

    await fetchUserData();

    // Refresh wallet after payment success
    if (paymentStatus === "success") {
        await new Promise((resolve) => setTimeout(resolve, 1000)); // Wait 1s for backend to process

        // Try to read persisted last topup from localStorage first (fallback for unauthenticated redirect)
        try {
            const raw = localStorage.getItem("swiftpay_last_topup");
            if (raw) {
                const parsed = JSON.parse(raw);
                if (
                    parsed &&
                    parsed.order_id &&
                    parsed.order_id.toString() === orderId
                ) {
                    successAmount.value =
                        Number(parsed.amount) || successAmount.value;
                    successTax.value = Number(parsed.tax) || successTax.value;
                    successTotal.value =
                        Number(parsed.total) || successTotal.value;
                    // remove after use
                    localStorage.removeItem("swiftpay_last_topup");
                }
            }
        } catch (e) {
            console.warn("Failed to read swiftpay_last_topup", e);
        }

        // If amounts still zero, try to load from API
        if (!successAmount.value && orderId) await loadSuccessDetails(orderId);

        await refreshWallet();
    }

    await nextTick();
    console.log("Component mounted, user data:", user.value);
});

const refreshWallet = async () => {
    try {
        const token =
            localStorage.getItem("auth_token") ||
            sessionStorage.getItem("auth_token");
        if (!token) return;

        const walletResponse = await fetch("/api/v1/swiftpay/wallet", {
            headers: {
                Authorization: `Bearer ${token}`,
                "Content-Type": "application/json",
            },
        });

        if (walletResponse.ok) {
            const walletData = await walletResponse.json();
            console.log("=== Wallet Refreshed ===");
            console.log("Wallet data:", walletData);

            const balanceValue =
                walletData.data?.balance ||
                walletData.data?.current_balance ||
                0;
            user.value.balance =
                typeof balanceValue === "string"
                    ? parseFloat(balanceValue)
                    : Number(balanceValue) || 0;
            console.log("New balance:", user.value.balance);
        }
    } catch (err) {
        console.error("Error refreshing wallet:", err);
    }
};

// Load success details (nominal, tax, total) by order_id by querying server-side payment table first (public),
// then fallback to authenticated endpoints if needed.
const loadSuccessDetails = async (orderId) => {
    try {
        // 1) try public endpoint backed by Payment table
        try {
            const res = await fetch(
                `/api/v1/midtrans/payment/${encodeURIComponent(orderId)}`
            );
            if (res.ok) {
                const json = await res.json();
                if (json && json.data) {
                    successAmount.value = Number(json.data.amount) || 0;
                    successTax.value = Number(json.data.tax) || 0;
                    successTotal.value = Number(json.data.total) || 0;
                    return;
                }
            }
        } catch (e) {
            console.warn("Public payment lookup failed", e);
        }

        // 2) fallback: try authenticated endpoints (if token available)
        const token =
            localStorage.getItem("auth_token") ||
            sessionStorage.getItem("auth_token");
        if (!token) return;

        const endpoints = [
            "/api/v1/swiftpay/topups",
            "/api/v1/swiftpay/history",
            "/api/v1/swiftpay/transactions",
        ];
        for (const url of endpoints) {
            try {
                const res = await fetch(url, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        "Content-Type": "application/json",
                    },
                });
                if (!res.ok) continue;
                const json = await res.json();
                let items = null;
                if (Array.isArray(json)) items = json;
                else if (json.data && Array.isArray(json.data))
                    items = json.data;
                else if (json.items && Array.isArray(json.items))
                    items = json.items;

                if (!items) continue;

                const match = items.find((it) => {
                    const id =
                        it.order_id ||
                        it.orderId ||
                        it.reference ||
                        it.id ||
                        it.transaction_id;
                    return id && id.toString() === orderId.toString();
                });

                if (match) {
                    // normalize fields
                    const amt = Number(
                        match.amount ??
                            match.nominal ??
                            match.value ??
                            match.total ??
                            match.gross_amount ??
                            0
                    );
                    const tx = Number(
                        match.tax ?? (amt ? Math.round(amt * 0.11) : 0)
                    );
                    const tot = Number(
                        match.total ?? match.gross_amount ?? amt + tx
                    );

                    successAmount.value = amt;
                    successTax.value = tx;
                    successTotal.value = tot;
                    return;
                }
            } catch (e) {
                console.warn("loadSuccessDetails failed for", url, e);
            }
        }
    } catch (e) {
        console.error("loadSuccessDetails error", e);
    }
};

/* TABS & HISTORY */
const activeTab = ref("topup"); // 'topup' | 'history'
const historyList = ref([]);
const historyLoading = ref(false);
const historyError = ref("");

// underline animation state
const tabsRef = ref(null);
const underlineLeft = ref(0);
const underlineWidth = ref(0);

const updateUnderline = () => {
    try {
        const el = tabsRef.value;
        if (!el) return;
        const active = el.querySelector(".tab.active");
        if (!active) {
            underlineLeft.value = 0;
            underlineWidth.value = 0;
            return;
        }
        const tabsRect = el.getBoundingClientRect();
        const btnRect = active.getBoundingClientRect();
        underlineLeft.value = Math.round(
            btnRect.left - tabsRect.left + el.scrollLeft
        );
        underlineWidth.value = Math.round(btnRect.width);
    } catch (e) {
        console.warn("updateUnderline failed", e);
    }
};

// update on resize
if (typeof window !== "undefined") {
    window.addEventListener("resize", () => {
        // small debounce
        setTimeout(updateUnderline, 80);
    });
}

const fetchHistory = async () => {
    try {
        historyLoading.value = true;
        historyError.value = "";

        let token =
            localStorage.getItem("auth_token") ||
            sessionStorage.getItem("auth_token");
        if (!token && window.__AUTH_TOKEN__) token = window.__AUTH_TOKEN__;
        if (!token) {
            historyError.value = "Token tidak ditemukan.";
            historyList.value = [];
            return;
        }

        const urls = [
            "/api/v1/swiftpay/history",
            "/api/v1/swiftpay/topups",
            "/api/v1/swiftpay/transactions",
        ];

        let data = null;
        for (const url of urls) {
            try {
                const res = await fetch(url, {
                    method: "GET",
                    headers: {
                        Authorization: `Bearer ${token}`,
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                });
                const json = await res.json();
                if (res.ok && json.data) {
                    data = json.data;
                    break;
                }
                if (res.ok && Array.isArray(json)) {
                    data = json;
                    break;
                }
            } catch (e) {
                console.warn("fetchHistory failed for", url, e);
            }
        }

        if (!data) {
            historyList.value = [];
            historyError.value = "Tidak ada riwayat.";
            return;
        }

        // normalize raw data (support array or object shapes)
        const raw = Array.isArray(data)
            ? data
            : data.items || data.transactions || [];
        historyList.value = raw.map((t) => normalizeTransaction(t));
    } catch (err) {
        console.error(err);
        historyError.value = err.message || "Gagal memuat riwayat.";
    } finally {
        historyLoading.value = false;
    }
};

watch(activeTab, (v) => {
    if (v === "history") fetchHistory();
    // update underline after DOM updates
    nextTick(() => updateUnderline());
});

// normalize transaction object (Midtrans or local API)
const normalizeTransaction = (tx) => {
    const orderId =
        tx.order_id ||
        tx.orderId ||
        tx.reference_number ||
        tx.reference ||
        tx.transaction_id ||
        tx.transactionId ||
        tx.id ||
        tx.order_id_raw ||
        "-";
    let timeRaw =
        tx.transaction_time ||
        tx.completed_at ||
        tx.created_at ||
        tx.date ||
        tx.time ||
        tx.timestamp ||
        "";
    let time = "-";
    try {
        if (timeRaw) {
            const d = new Date(timeRaw);
            if (!isNaN(d)) {
                time = d.toLocaleString("id-ID", {
                    dateStyle: "medium",
                    timeStyle: "short",
                });
            } else {
                time = timeRaw.toString();
            }
        }
    } catch (e) {
        time = timeRaw || "-";
    }
    const amount = Number(
        tx.gross_amount || tx.amount || tx.total || tx.nominal || tx.value || 0
    );
    const method =
        tx.payment_type ||
        tx.payment_method ||
        tx.method ||
        tx.channel ||
        tx.bank ||
        (tx.raw && tx.raw.payment_type) ||
        "-";
    const rawStatus = (
        tx.transaction_status ||
        tx.status ||
        tx.state ||
        tx.raw_status ||
        ""
    )
        .toString()
        .toLowerCase();

    let statusKey = "tertunda";
    if (rawStatus.includes("pending")) statusKey = "tertunda";
    else if (
        rawStatus.includes("settlement") ||
        rawStatus.includes("capture") ||
        rawStatus.includes("success")
    )
        statusKey = "berhasil";
    else if (rawStatus.includes("cancel") || rawStatus.includes("cancelled"))
        statusKey = "dibatalkan";
    else if (
        rawStatus.includes("deny") ||
        rawStatus.includes("expire") ||
        rawStatus.includes("failure") ||
        rawStatus.includes("failed") ||
        rawStatus.includes("deny")
    )
        statusKey = "gagal";

    return {
        orderId,
        time,
        amount,
        method,
        statusKey,
        raw: tx,
    };
};

const fetchUserData = async () => {
    try {
        loading.value = true;

        // Ambil dari localStorage
        const storedProfile = localStorage.getItem("userProfile");
        const token =
            localStorage.getItem("auth_token") ||
            sessionStorage.getItem("auth_token");

        if (storedProfile) {
            try {
                const profile = JSON.parse(storedProfile);
                const firstName = profile.first_name || profile.name || "-";
                const lastName = profile.last_name || "";
                const fullName = lastName
                    ? `${firstName} ${lastName}`
                    : firstName;

                user.value = {
                    name: fullName,
                    email: profile.email || "-",
                    id: profile.user_id || profile.id || "-",
                    balance: 0,
                };
                console.log("User from localStorage:", user.value);
            } catch (e) {
                console.error("Error parsing localStorage profile:", e);
            }
        }

        // Fetch wallet balance dari API
        if (token) {
            try {
                const walletResponse = await fetch("/api/v1/swiftpay/wallet", {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        "Content-Type": "application/json",
                    },
                });

                if (walletResponse.ok) {
                    const walletData = await walletResponse.json();
                    console.log("Wallet data:", walletData);

                    const balanceValue =
                        walletData.data?.balance ||
                        walletData.data?.current_balance ||
                        0;
                    user.value.balance =
                        typeof balanceValue === "string"
                            ? parseFloat(balanceValue)
                            : Number(balanceValue) || 0;
                    console.log("Balance updated:", user.value.balance);
                }
            } catch (err) {
                console.error("Error fetching wallet:", err);
            }
        }
    } catch (err) {
        console.error("Error fetching user:", err);
        if (!user.value.name || user.value.name === "-") {
            user.value = {
                name: "-",
                email: "-",
                id: "-",
                balance: 0,
            };
        }
    } finally {
        loading.value = false;
    }
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
    if (!n || isNaN(n)) return "0";
    return Number(n).toLocaleString("id-ID", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });
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

/* STEP: INVOICE -> SUCCESS (integrate Midtrans Snap) */
const confirmPayment = async () => {
    try {
        loading.value = true;
        const token =
            localStorage.getItem("auth_token") ||
            sessionStorage.getItem("auth_token");

        // Parse user ID dari localStorage
        let userId = null;
        const storedProfile = localStorage.getItem("userProfile");
        if (storedProfile) {
            try {
                const profile = JSON.parse(storedProfile);
                userId = profile.id || profile.user_id;
            } catch (e) {
                console.error("Error parsing userId:", e);
            }
        }

        const payload = {
            order_id: orderId.value,
            gross_amount: Math.round(total.value),
            amount: Math.round(amount.value),
            tax: Math.round(tax.value),
            user_id: userId,
            customer: {
                name: user.value.name,
                email: emailInput.value || user.value.email,
            },
        };

        // Save untuk success page
        successAmount.value = amount.value;
        successTax.value = tax.value;
        successTotal.value = total.value;
        // persist last topup amounts locally so we can show them after redirect
        try {
            const last = {
                order_id: orderId.value,
                amount: Math.round(amount.value),
                tax: Math.round(tax.value),
                total: Math.round(total.value),
            };
            localStorage.setItem("swiftpay_last_topup", JSON.stringify(last));
        } catch (e) {
            console.warn("Failed to persist swiftpay_last_topup", e);
        }

        console.log("=== PAYMENT REQUEST ===");
        console.log("Payload:", JSON.stringify(payload, null, 2));

        const res = await fetch("/api/v1/midtrans/create", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Authorization: token ? `Bearer ${token}` : "",
            },
            body: JSON.stringify(payload),
        });

        console.log("Response status:", res.status);
        const data = await res.json();
        console.log("=== MIDTRANS RESPONSE ===");
        console.log("Full response:", JSON.stringify(data, null, 2));

        if (!res.ok) {
            throw new Error(`API Error: ${data.message || "Unknown error"}`);
        }

        const redirectUrl = data.redirect_url ?? data.data?.redirect_url;
        console.log("Extracted redirect_url:", redirectUrl);

        if (!redirectUrl) {
            console.error("No redirect URL found in response!");
            console.error("Response data:", data);
            throw new Error(
                "Midtrans tidak mengembalikan redirect URL. Response: " +
                    JSON.stringify(data)
            );
        }

        console.log("Redirecting to:", redirectUrl);
        // Redirect ke Midtrans payment page
        window.location.href = redirectUrl;
    } catch (e) {
        loading.value = false;
        console.error("=== PAYMENT ERROR ===");
        console.error("Error:", e);
        alert("Terjadi kesalahan. Silakan coba lagi: " + e.message);
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
    activeTab.value = "history";
};

const goToProfile = () => {
    router.push({ name: "Profile" });
};

const goHome = () => {
    router.push({ name: "Home" });
};

const goToSwiftpayPage = () => {
    router.push({ name: "SwiftPay" });
};

/* MODAL CONTROL */
const closeModal = () => {
    showModal.value = false;
};
</script>

<template>
    <!-- MODAL MODE: Full overlay with close button -->
    <template v-if="isModal && showModal">
        <div class="modal-overlay">
            <div class="modal-content">
                <!-- Close button -->
                <button class="modal-close-btn" @click="closeModal">
                    <svg
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M18 6L6 18M6 6L18 18"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        />
                    </svg>
                </button>

                <!-- MAIN CONTENT in MODAL -->
                <div class="modal-body">
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
                                            <circle
                                                cx="9"
                                                cy="18"
                                                r="1.5"
                                                fill="white"
                                            />
                                            <circle
                                                cx="15"
                                                cy="18"
                                                r="1.5"
                                                fill="white"
                                            />
                                        </svg>
                                    </div>
                                    <div class="logo-text">
                                        <span class="logo-title">SwiftPay</span>
                                        <span class="logo-subtitle"
                                            >SUPER APP</span
                                        >
                                    </div>
                                </div>

                                <nav class="nav">
                                    <button
                                        class="nav-link"
                                        @click="goToProfile"
                                    >
                                        Profile
                                    </button>
                                    <button
                                        class="nav-link"
                                        @click="goToHistory"
                                    >
                                        History
                                    </button>
                                </nav>
                            </div>
                        </header>

                        <div class="tabs">
                            <button
                                :class="[
                                    'tab',
                                    { active: activeTab === 'topup' },
                                ]"
                                @click="activeTab = 'topup'"
                            >
                                Top Up
                            </button>
                            <button
                                :class="[
                                    'tab',
                                    { active: activeTab === 'history' },
                                ]"
                                @click="activeTab = 'history'"
                            >
                                Riwayat Top Up
                            </button>
                        </div>

                        <main v-if="activeTab === 'topup'" class="container">
                            <section class="left">
                                <div class="card profile">
                                    <h3>{{ user.name || "-" }}</h3>
                                    <p>{{ user.email || "-" }}</p>
                                    <p>ID: {{ user.id || "-" }}</p>

                                    <div class="balance">
                                        <span>Saldo Anda: </span>
                                        <strong
                                            >Rp
                                            {{ format(user.balance) }}</strong
                                        >
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
                                        Minimal Rp 10.000 · Maksimal Rp
                                        1.000.000
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
                                        v-model="emailInput"
                                    />
                                </div>

                                <div class="info-box">
                                    ℹ️ Saldo SwiftPay akan bertambah setelah
                                    pembayaran berhasil.
                                </div>

                                <button
                                    :disabled="!canPay"
                                    class="pay-btn"
                                    @click="goToInvoice"
                                >
                                    Bayar Sekarang
                                </button>

                                <p class="legal">
                                    Dengan melanjutkan, Anda menyetujui
                                    <span>Syarat & Ketentuan</span> SwiftPay.
                                </p>
                            </aside>
                        </main>

                        <!-- HISTORY VIEW (modal) -->
                        <main v-if="activeTab === 'history'" class="container">
                            <section class="left">
                                <div class="card">
                                    <h3>Riwayat Top Up</h3>

                                    <div
                                        v-if="historyLoading"
                                        class="loading-skeleton"
                                    >
                                        <div class="skeleton-line"></div>
                                        <div class="skeleton-line short"></div>
                                    </div>

                                    <div v-else>
                                        <div v-if="historyError" class="error">
                                            {{ historyError }}
                                        </div>

                                        <div
                                            v-else-if="historyList.length === 0"
                                            class="hint"
                                        >
                                            Belum ada riwayat top up.
                                        </div>

                                        <ul v-else class="history-list">
                                            <li
                                                v-for="(tx, i) in historyList"
                                                :key="tx.orderId || i"
                                                class="history-item"
                                            >
                                                <div class="history-left">
                                                    <div class="history-order">
                                                        {{ tx.orderId }}
                                                    </div>
                                                    <div class="history-date">
                                                        {{ tx.time }}
                                                    </div>
                                                </div>

                                                <div class="history-middle">
                                                    <div class="history-amount">
                                                        Rp
                                                        {{ format(tx.amount) }}
                                                    </div>
                                                    <div class="history-method">
                                                        {{ tx.method }}
                                                    </div>
                                                </div>

                                                <div class="history-right">
                                                    <span class="status-badge">
                                                        <span
                                                            :class="[
                                                                'status-dot',
                                                                tx.statusKey,
                                                            ]"
                                                        ></span>
                                                        <span
                                                            class="status-label"
                                                            >{{
                                                                tx.statusKey
                                                            }}</span
                                                        >
                                                    </span>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </section>

                            <aside class="checkout">
                                <div class="card">
                                    <h4>Informasi</h4>
                                    <p class="hint">
                                        Anda dapat melihat riwayat top up di
                                        sini.
                                    </p>
                                </div>
                            </aside>
                        </main>
                    </div>

                    <!-- STEP 2: INVOICE POPUP -->
                    <div v-if="currentStep === 'invoice'" class="invoice-page">
                        <div class="invoice-card">
                            <button
                                type="button"
                                class="back-btn"
                                @click="goBackToForm"
                            >
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

                                <button
                                    class="confirm-btn"
                                    @click="confirmPayment"
                                >
                                    KONFIRMASI
                                </button>

                                <p class="legal">
                                    Dengan klik "Konfirmasi", Anda menyetujui
                                    Syarat & Ketentuan SwiftPay
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
                                    <span>IDR {{ format(successAmount) }}</span>
                                </div>

                                <div class="row">
                                    <span>Tax (PPN 11%)</span>
                                    <span>IDR {{ format(successTax) }}</span>
                                </div>

                                <div class="row total">
                                    <span>Total</span>
                                    <span>IDR {{ format(successTotal) }}</span>
                                </div>

                                <div class="row">
                                    <span>Tanggal</span>
                                    <span>{{ transactionDate }}</span>
                                </div>
                            </div>

                            <div class="actions">
                                <button class="primary" @click="goHome">
                                    Beranda
                                </button>

                                <button
                                    class="secondary"
                                    @click="goToSwiftpayPage"
                                >
                                    Ke SwiftPay
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- PAGE MODE: Normal page (no modal overlay) -->
    <template v-else>
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

            <div class="tabs">
                <button
                    :class="['tab', { active: activeTab === 'topup' }]"
                    @click="activeTab = 'topup'"
                >
                    Top Up
                </button>
                <button
                    :class="['tab', { active: activeTab === 'history' }]"
                    @click="activeTab = 'history'"
                >
                    Riwayat Top Up
                </button>
            </div>

            <main v-if="activeTab === 'topup'" class="container">
                <section class="left">
                    <div class="card profile">
                        <h3>{{ user.name || "-" }}</h3>
                        <p>{{ user.email || "-" }}</p>
                        <p>ID: {{ user.id || "-" }}</p>

                        <div class="balance">
                            <span>Saldo Anda: </span>
                            <strong>Rp {{ format(user.balance) }}</strong>
                        </div>
                    </div>

                    <div class="card">
                        <h3>Nominal Top Up SwiftPay</h3>

                        <input
                            v-model.number="amount"
                            type="number"
                            placeholder="Rp 0"
                            min="10000"
                            max="1000000"
                        />

                        <p class="hint">
                            Minimal Rp 10.000 · Maksimal Rp 1.000.000
                        </p>
                    </div>

                    <div class="card">
                        <h3>Email untuk Invoice</h3>

                        <input
                            v-model="emailInput"
                            type="email"
                            :placeholder="user.email || 'Email'"
                        />

                        <p class="hint">
                            Gunakan email yang terdaftar untuk kemudahan
                        </p>
                    </div>
                </section>

                <section class="right">
                    <div class="card">
                        <h3>Ringkasan Pembayaran</h3>

                        <div class="row">
                            <span>Nominal</span>
                            <span>IDR {{ format(amount) }}</span>
                        </div>

                        <div class="row">
                            <span>Pajak (11%)</span>
                            <span>IDR {{ format(tax) }}</span>
                        </div>

                        <div class="row" style="font-weight: 700">
                            <span>Total</span>
                            <span>IDR {{ format(total) }}</span>
                        </div>

                        <div class="actions">
                            <button
                                class="primary"
                                @click="confirmPayment"
                                :disabled="amount <= 0 || loading"
                            >
                                {{
                                    loading
                                        ? "Loading..."
                                        : "Lanjutkan Pembayaran"
                                }}
                            </button>
                        </div>
                    </div>
                </section>
            </main>

            <!-- HISTORY VIEW (page) -->
            <main v-if="activeTab === 'history'" class="container">
                <section class="left">
                    <div class="card">
                        <h3>Riwayat Top Up</h3>

                        <div v-if="historyLoading" class="loading-skeleton">
                            <div class="skeleton-line"></div>
                            <div class="skeleton-line short"></div>
                        </div>

                        <div v-else>
                            <div v-if="historyError" class="error">
                                {{ historyError }}
                            </div>

                            <div
                                v-else-if="historyList.length === 0"
                                class="hint"
                            >
                                Belum ada riwayat top up.
                            </div>

                            <ul v-else class="history-list">
                                <li
                                    v-for="(tx, i) in historyList"
                                    :key="tx.orderId || i"
                                    class="history-item"
                                >
                                    <div class="history-left">
                                        <div class="history-order">
                                            {{ tx.orderId }}
                                        </div>
                                        <div class="history-date">
                                            {{ tx.time }}
                                        </div>
                                    </div>

                                    <div class="history-middle">
                                        <div class="history-amount">
                                            Rp {{ format(tx.amount) }}
                                        </div>
                                        <div class="history-method">
                                            {{ tx.method }}
                                        </div>
                                    </div>

                                    <div class="history-right">
                                        <span class="status-badge">
                                            <span
                                                :class="[
                                                    'status-dot',
                                                    tx.statusKey,
                                                ]"
                                            ></span>
                                            <span class="status-label">{{
                                                tx.statusKey
                                            }}</span>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="right">
                    <div class="card">
                        <h4>Informasi</h4>
                        <p class="hint">
                            Anda dapat melihat riwayat top up di sini.
                        </p>
                    </div>
                </section>
            </main>
        </div>

        <!-- STEP 2: INVOICE -->
        <div v-if="currentStep === 'invoice'" class="invoice-card">
            <h3>Invoice</h3>

            <div class="details">
                <div class="row">
                    <span>Nomor Pesanan</span>
                    <span>{{ orderId }}</span>
                </div>

                <div class="row">
                    <span>Tanggal Transaksi</span>
                    <span>{{ transactionDate }}</span>
                </div>

                <div class="row">
                    <span>Nama Pelanggan</span>
                    <span>{{ user.name }}</span>
                </div>

                <div class="row">
                    <span>Email</span>
                    <span>{{ emailInput || user.email }}</span>
                </div>

                <div class="row">
                    <span>Nominal</span>
                    <span>IDR {{ format(amount) }}</span>
                </div>

                <div class="row">
                    <span>Pajak (11%)</span>
                    <span>IDR {{ format(tax) }}</span>
                </div>

                <div class="row">
                    <span>Total</span>
                    <span>IDR {{ format(total) }}</span>
                </div>

                <div class="row">
                    <span>Tanggal</span>
                    <span>{{ transactionDate }}</span>
                </div>
            </div>

            <div class="actions">
                <button class="secondary" @click="goBackToForm">
                    Ubah Nominal
                </button>

                <button
                    class="primary"
                    @click="processPayment"
                    :disabled="loading"
                >
                    {{ loading ? "Loading..." : "Proses Pembayaran" }}
                </button>
            </div>
        </div>

        <!-- STEP 3: SUCCESS -->
        <div v-if="currentStep === 'success'" class="page-success-overlay">
            <div class="success-card">
                <div class="success-icon">✓</div>

                <h2>Pembayaran Berhasil!</h2>

                <p>Saldo Anda telah ditambahkan</p>

                <div class="details">
                    <div class="row">
                        <span>Nomor Pesanan</span>
                        <span>{{ orderId }}</span>
                    </div>

                    <div class="row">
                        <span>Nominal Top Up</span>
                        <span>IDR {{ format(successAmount) }}</span>
                    </div>

                    <div class="row">
                        <span>Tax (PPN 11%)</span>
                        <span>IDR {{ format(successTax) }}</span>
                    </div>

                    <div class="row">
                        <span>Total</span>
                        <span>IDR {{ format(successTotal) }}</span>
                    </div>

                    <div class="row">
                        <span>Waktu</span>
                        <span>{{ transactionDate }}</span>
                    </div>
                </div>

                <div class="actions">
                    <button class="primary" @click="goHome">Beranda</button>

                    <button class="secondary" @click="goToSwiftpayPage">
                        Ke SwiftPay
                    </button>
                </div>
            </div>
        </div>
    </template>
</template>

<style scoped>
/* ===== MODAL OVERLAY ===== */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}

.modal-content {
    background: white;
    border-radius: 12px;
    max-width: 900px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-close-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    background: none;
    border: none;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #666;
    z-index: 10000;
    transition: color 0.2s;
}

.modal-close-btn:hover {
    color: #333;
}

.modal-body {
    padding: 0;
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

.card h3 {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #1f2937;
}

.card .row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f3f4f6;
    font-size: 14px;
    color: #374151;
}

.card .row:last-child {
    border-bottom: none;
}

.card .row span:first-child {
    color: #6b7280;
    font-weight: 500;
}

.card .row span:last-child {
    font-weight: 600;
    color: #111827;
}

.card .row[style*="font-weight: 700"] {
    padding: 16px 0;
    font-size: 16px;
    font-weight: 700;
    border-top: 2px solid #f3f4f6;
}

.card .row[style*="font-weight: 700"] span:last-child {
    color: #1675e7;
    font-size: 18px;
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

/* Page-mode success overlay (ensure not cut by fixed header) */
.page-success-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    /* leave some space for fixed header */
    padding-top: calc(64px + 20px);
    z-index: 1000;
}

.success-card {
    width: 420px;
    background: white;
    border-radius: 20px;
    padding: 40px 32px;
    color: #1a1a2e;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
}

.success-icon {
    width: 72px;
    height: 72px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: white;
    margin: 0 auto 24px;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.success-card h2 {
    color: #1a1a2e;
    font-size: 24px;
    margin-bottom: 8px;
    font-weight: 700;
}

.success-card > p {
    font-size: 14px;
    opacity: 0.7;
    margin-bottom: 28px;
    color: #666;
}

.success-card .details {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 28px;
    border: 1px solid #e0e7ff;
}

.success-card .row {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    margin: 12px 0;
    color: #333;
}

.success-card .row span:first-child {
    color: #6b7280;
    font-weight: 500;
}

.success-card .row span:last-child {
    font-weight: 700;
    color: #1a1a2e;
}

.actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 24px;
}

.primary {
    background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
    color: white;
    padding: 16px;
    border-radius: 12px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(30, 136, 229, 0.3);
}

.primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(30, 136, 229, 0.4);
}

.primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.secondary {
    background: white;
    color: #1e88e5;
    border: 2px solid #1e88e5;
    padding: 14px;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
}

.secondary:hover {
    background: #f0f6ff;
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

@media (max-width: 600px) {
    .header-container {
        flex-direction: column;
        gap: 16px;
    }

    .nav {
        width: 100%;
        justify-content: center;
    }

    .invoice-card,
    .success-card {
        width: 100%;
        max-width: 420px;
    }
}

/* Tabs & History styles */
.tabs {
    max-width: 1200px;
    margin: -30px auto 0; /* pull tabs closer to header */
    padding: 0 1.5rem;
    display: flex;
    gap: 8px;
    justify-content: flex-start;
}

.tab {
    padding: 8px 14px;
    background: white;
    border-radius: 999px;
    border: 1px solid #e5e7eb;
    cursor: pointer;
    font-weight: 600;
}

.tab.active {
    background: #1e88e5;
    color: white;
    border-color: #1e88e5;
}

.history-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.history-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border-radius: 10px;
    background: #f8f9ff;
}

.history-amount {
    font-weight: 700;
}

.history-date {
    font-size: 12px;
    color: #6b7280;
}

.history-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 200px;
}

.history-middle {
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: flex-end;
    flex: 1;
}

.history-right {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 120px;
    justify-content: flex-end;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.06);
    padding: 6px 10px;
    border-radius: 999px;
}

.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}

/* status colors */
.status-dot.tertunda {
    background: #f59e0b;
} /* amber */
.status-dot.berhasil {
    background: #10b981;
} /* green */
.status-dot.gagal {
    background: #ef4444;
} /* red */
.status-dot.dibatalkan {
    background: #6b7280;
} /* gray */

.status-label {
    font-size: 13px;
    color: #374151;
    text-transform: capitalize;
}
</style>
