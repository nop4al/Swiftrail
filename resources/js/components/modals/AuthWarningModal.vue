<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const show = ref(false)

const showWarning = () => {
  show.value = true
}

const handleConfirm = () => {
  show.value = false
  router.push('/login')
}

const handleCancel = () => {
  show.value = false
}

// Expose methods to parent
defineExpose({
  showWarning
})
</script>

<template>
  <div v-if="show" class="modal-overlay">
    <div class="modal-content">
      <button class="close-btn" @click="handleCancel" aria-label="Close">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
      
      <div class="modal-icon">
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="iconGradient" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" style="stop-color: #1675E7; stop-opacity: 1" />
              <stop offset="100%" style="stop-color: #09D8E3; stop-opacity: 1" />
            </linearGradient>
          </defs>
          <circle cx="24" cy="24" r="22" fill="url(#iconGradient)" fill-opacity="0.15" stroke="url(#iconGradient)" stroke-width="2"/>
          <text x="24" y="32" text-anchor="middle" font-size="32" font-weight="bold" fill="url(#iconGradient)">!</text>
        </svg>
      </div>
      
      <h2 class="modal-title">Login Diperlukan</h2>
      
      <p class="modal-message">
        Anda harus login terlebih dahulu untuk mengakses halaman ini. Silakan login dengan akun Anda atau daftar jika belum memiliki akun.
      </p>
      
      <div class="modal-buttons">
        <button class="btn btn-primary" @click="handleConfirm">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.5.5a2.121 2.121 0 0 1 3 3L12 15H9v-3L18.5.5z"></path>
          </svg>
          Lanjut ke Login
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.modal-content {
  background: #ffffff;
  border-radius: 16px;
  padding: 32px;
  max-width: 400px;
  width: 90%;
  text-align: center;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  animation: slideUp 0.3s ease-out;
  position: relative;
}

.close-btn {
  position: absolute;
  top: 12px;
  right: 12px;
  background: none;
  border: none;
  cursor: pointer;
  color: #999999;
  padding: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background-color: rgba(0, 0, 0, 0.05);
  color: #333333;
}

@keyframes slideUp {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-icon {
  margin-bottom: 20px;
  display: flex;
  justify-content: center;
}

.modal-title {
  font-size: 20px;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0 0 12px 0;
}

.modal-message {
  font-size: 14px;
  color: #555555;
  line-height: 1.6;
  margin: 0 0 24px 0;
}

.modal-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.btn {
  padding: 12px 16px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #1675E7 0%, #09D8E3 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(22, 117, 231, 0.4);
}
</style>
