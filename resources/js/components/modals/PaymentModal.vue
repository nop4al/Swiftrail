<template>
    <div v-if="isOpen" class="payment-modal-overlay" @click="closeModal">
        <div class="payment-modal" @click.stop>
            <div class="payment-modal-header">
                <h2>Topup SwiftPay</h2>
                <button class="close-btn" @click="closeModal">&times;</button>
            </div>
            
            <div class="payment-modal-body">
                <!-- Amount Input -->
                <div class="form-group">
                    <label>Jumlah Topup (Rp)</label>
                    <input 
                        v-model.number="topupAmount" 
                        type="number" 
                        placeholder="Masukkan jumlah"
                        min="10000"
                        step="10000"
                        class="form-input"
                    />
                </div>

                <!-- Payment Button -->
                <button 
                    @click="processPayment" 
                    :disabled="loading || topupAmount < 10000"
                    class="payment-btn"
                >
                    <span v-if="!loading">Bayar Rp {{ formatCurrency(topupAmount) }}</span>
                    <span v-else>Processing...</span>
                </button>

                <!-- Error Message -->
                <div v-if="errorMessage" class="error-message">
                    {{ errorMessage }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const isOpen = ref(false);
const topupAmount = ref(50000);
const loading = ref(false);
const errorMessage = ref('');

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID').format(value || 0);
};

const openModal = () => {
    isOpen.value = true;
    errorMessage.value = '';
};

const closeModal = () => {
    isOpen.value = false;
    errorMessage.value = '';
};

const processPayment = async () => {
    if (topupAmount.value < 10000) {
        errorMessage.value = 'Minimum topup Rp 10.000';
        return;
    }

    loading.value = true;
    errorMessage.value = '';

    try {
        const token = localStorage.getItem("auth_token") || sessionStorage.getItem("auth_token");
        const storedProfile = localStorage.getItem("userProfile");
        let userId = null;
        let userName = 'Customer';
        let userEmail = 'customer@example.com';

        if (storedProfile) {
            try {
                const profile = JSON.parse(storedProfile);
                userId = profile.id || profile.user_id;
                userName = profile.name || 'Customer';
                userEmail = profile.email || 'customer@example.com';
            } catch (e) {
                console.error("Error parsing profile:", e);
            }
        }

        const orderId = `ORD-TOPUP-${Date.now()}`;
        const grossAmount = Math.round(topupAmount.value * 1.11); // Add PPN 11%
        const amount = topupAmount.value;
        const tax = grossAmount - amount;

        const payload = {
            order_id: orderId,
            gross_amount: grossAmount,
            amount: amount,
            tax: tax,
            user_id: userId,
            customer: {
                name: userName,
                email: userEmail,
            },
        };

        console.log("Payment payload:", payload);

        const res = await fetch("/api/v1/midtrans/create", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json",
                "Authorization": token ? `Bearer ${token}` : "",
            },
            body: JSON.stringify(payload),
        });

        const data = await res.json();
        console.log("Midtrans response:", data);

        const token_snap = data.token ?? data.data?.token ?? null;

        if (!token_snap) {
            throw new Error("No token received from Midtrans");
        }

        // Load and trigger Snap
        if (!window.snap) {
            await new Promise((resolve, reject) => {
                const script = document.createElement("script");
                script.src = "https://app.sandbox.midtrans.com/snap/snap.js";
                script.setAttribute("data-client-key", "SB-Mid-client-3nS_QmQqFF714vxt");
                script.async = true;
                script.onload = () => resolve();
                script.onerror = () => reject(new Error("Failed to load Snap"));
                document.head.appendChild(script);
            });
        }

        // Wait for Snap to initialize
        await new Promise(resolve => setTimeout(resolve, 500));

        if (window.snap) {
            window.snap.pay(token_snap, {
                onSuccess: function (result) {
                    console.log("Payment success:", result);
                    closeModal();
                    // You can emit event or call callback here
                    window.dispatchEvent(new CustomEvent('payment-success', { detail: result }));
                },
                onPending: function (result) {
                    console.log("Payment pending:", result);
                    closeModal();
                },
                onError: function (result) {
                    console.error("Payment error:", result);
                    errorMessage.value = "Pembayaran gagal: " + (result?.status_message || "Unknown error");
                },
                onClose: function () {
                    console.log("Snap closed");
                    // Keep modal open, don't close on snap close
                },
            });
        } else {
            throw new Error("Midtrans Snap not loaded");
        }

    } catch (error) {
        console.error("Payment error:", error);
        errorMessage.value = "Error: " + error.message;
    } finally {
        loading.value = false;
    }
};

// Export functions for parent component
defineExpose({ openModal, closeModal });
</script>

<style scoped>
.payment-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.payment-modal {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    width: 90%;
    max-width: 400px;
    overflow: hidden;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.payment-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
}

.payment-modal-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #333;
}

.close-btn {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #999;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s;
}

.close-btn:hover {
    background: #e0e0e0;
    color: #333;
}

.payment-modal-body {
    padding: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
    font-size: 14px;
}

.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    font-family: inherit;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.payment-btn {
    width: 100%;
    padding: 14px 24px;
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 16px;
}

.payment-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

.payment-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.error-message {
    margin-top: 16px;
    padding: 12px 16px;
    background: #fee;
    color: #c33;
    border-radius: 8px;
    font-size: 14px;
    border-left: 4px solid #c33;
}
</style>
