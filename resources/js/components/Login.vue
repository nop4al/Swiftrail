<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const email = ref('')
const password = ref('')
const rememberMe = ref(false)
const isLoading = ref(false)
const showPassword = ref(false)

const handleLogin = async () => {
  if (!email.value || !password.value) {
    alert('Silakan isi semua field')
    return
  }

  isLoading.value = true
  try {
    const response = await fetch('/api/v1/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        email: email.value,
        password: password.value,
      })
    })

    const data = await response.json()
    
    if (!response.ok) {
      alert(data.message || 'Login gagal')
      return
    }

    // Simpan API token dan user profile
    if (data.data?.access_token) {
      localStorage.setItem('auth_token', data.data.access_token)
    }
    if (data.data?.user) {
      localStorage.setItem('userProfile', JSON.stringify(data.data.user))
      // Trigger event untuk update header dan component lain (SETELAH save data)
      window.dispatchEvent(new Event('auth-changed'))
      
      // Redirect semua user (termasuk admin) ke home, admin bisa akses admin panel via menu
      router.push('/home')
    } else {
      window.dispatchEvent(new Event('auth-changed'))
      router.push('/home')
    }
  } catch (error) {
    console.error('Login error:', error)
    alert('Terjadi kesalahan. Silakan coba lagi.')
  } finally {
    isLoading.value = false
  }
}

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

const handleBack = () => {
  router.back()
}
</script>

<template>
  <div class="login-page">
    <div class="login-container">
      <!-- Left Section - Branding -->
      <div class="login-branding">
        <div class="branding-content">
          <div class="logo">
            <div class="logo-icon">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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
          
          <div class="branding-message">
            <h2>Selamat Datang Kembali!</h2>
            <p>Nikmati kemudahan dalam memesan tiket kereta api dengan SwiftRail.</p>
          </div>

          <div class="benefits">
            <div class="benefit-item">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
              </svg>
              <span>Pemesanan Cepat & Mudah</span>
            </div>
            <div class="benefit-item">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2"/>
                <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <span>Akses 24/7</span>
            </div>
            <div class="benefit-item">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 1L15.09 8.26H23L17.55 12.25L19.64 19.52L12 15.5L4.36 19.52L6.45 12.25L1 8.26H8.91L12 1Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              </svg>
              <span>Penawaran Eksklusif</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Section - Form -->
      <div class="login-form-section">
        <button class="back-btn" @click="handleBack" title="Kembali">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <div class="form-wrapper">
          <h1>Masuk ke Akun Anda</h1>
          <p class="form-subtitle">Masukkan kredensial Anda untuk melanjutkan</p>

          <form @submit.prevent="handleLogin" class="login-form">
            <div class="form-group">
              <label for="email" class="form-label">Email</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                  <path d="M2 6L12 13L22 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <input
                  id="email"
                  v-model="email"
                  type="email"
                  class="form-input"
                  placeholder="Masukkan email Anda"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="password" class="form-label">Password</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect x="3" y="11" width="18" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
                  <path d="M7 11V7C7 4.23858 9.23858 2 12 2C14.7614 2 17 4.23858 17 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input
                  id="password"
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  class="form-input"
                  placeholder="Masukkan password Anda"
                  required
                />
                <button
                  type="button"
                  class="toggle-password"
                  @click="togglePasswordVisibility"
                >
                  <svg v-if="!showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 5C7 5 2.73 8.11 1 12.46C2.73 16.89 7 20 12 20C17 20 21.27 16.89 23 12.46C21.27 8.11 17 5 12 5ZM12 17C9.33 17 7 14.67 7 12C7 9.33 9.33 7 12 7C14.67 7 17 9.33 17 12C17 14.67 14.67 17 12 17Z" fill="currentColor"/>
                    <circle cx="12" cy="12" r="2" fill="currentColor"/>
                  </svg>
                  <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11.83 9L15.64 12.81C15.87 12.35 16 11.83 16 11.29C16 9.91 15.09 8.75 13.71 8.75C13.17 8.75 12.65 8.88 12.19 9.11M7.53 9.8C6.58 10.45 5.77 11.27 5.14 12.29M7 17.05V17H7.07C7.25 17.34 7.44 17.67 7.65 17.99M19.07 4.57L18 3.5M9.9 5.02L8.81 3.93" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M1 1L23 23" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </button>
              </div>
            </div>

            <div class="form-options">
              <label class="checkbox">
                <input v-model="rememberMe" type="checkbox" />
                <span>Ingat saya</span>
              </label>
              <a href="#" class="forgot-password">Lupa password?</a>
            </div>

            <button type="submit" class="login-btn" :disabled="isLoading">
              <span v-if="!isLoading">Masuk</span>
              <span v-else>Memproses...</span>
            </button>
          </form>

          <p class="signup-link">
            Belum punya akun? <router-link to="/register">Daftar di sini</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  width: 100%;
  min-height: 100vh;
  background: var(--color-bg-light);
}

.login-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 100vh;
}

.login-branding {
  background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary) 50%, var(--color-secondary) 100%);
  color: white;
  padding: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.login-branding::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: 
    radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(9, 216, 227, 0.15) 0%, transparent 40%);
  pointer-events: none;
}

.branding-content {
  position: relative;
  z-index: 1;
  text-align: center;
  max-width: 400px;
}

.logo {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.625rem;
  margin-bottom: 2rem;
}

.logo-icon {
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-text {
  display: flex;
  flex-direction: column;
  text-align: left;
}

.logo-title {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1.2;
}

.logo-subtitle {
  font-size: 0.625rem;
  font-weight: 600;
  letter-spacing: 0.1em;
  opacity: 0.9;
}

.branding-message {
  margin-bottom: 3rem;
}

.branding-message h2 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.branding-message p {
  font-size: 1rem;
  opacity: 0.95;
  line-height: 1.6;
}

.benefits {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  text-align: left;
}

.benefit-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 0.95rem;
}

.benefit-item svg {
  flex-shrink: 0;
}

/* Form Section */
.login-form-section {
  background: white;
  padding: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow-y: auto;
  position: relative;
}

.back-btn {
  position: absolute;
  top: 1.5rem;
  left: 1.5rem;
  width: 40px;
  height: 40px;
  border: none;
  background: var(--color-bg-light);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-dark);
  cursor: pointer;
  transition: all 0.3s;
  padding: 0;
}

.back-btn:hover {
  background: var(--color-primary);
  color: white;
  transform: translateX(-2px);
}

.form-wrapper {
  width: 100%;
  max-width: 420px;
}

.form-wrapper h1 {
  font-size: 1.875rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin-bottom: 0.5rem;
}

.form-subtitle {
  font-size: 0.9375rem;
  color: var(--color-text-light);
  margin-bottom: 2rem;
}

.login-form {
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 0.625rem;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 0.875rem;
  color: var(--color-text-light);
  pointer-events: none;
}

.form-input {
  width: 100%;
  padding: 0.875rem 0.875rem 0.875rem 2.75rem;
  font-size: 0.9375rem;
  font-weight: 500;
  color: var(--color-text-dark);
  background: var(--color-bg-light);
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
}

.form-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(22, 117, 231, 0.1);
  background: white;
}

.form-input::placeholder {
  color: #9ca3af;
}

.toggle-password {
  position: absolute;
  right: 0.875rem;
  background: none;
  border: none;
  color: var(--color-text-light);
  cursor: pointer;
  padding: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toggle-password:hover {
  color: var(--color-primary);
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  font-size: 0.875rem;
}

.checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  color: var(--color-text-dark);
}

.checkbox input {
  cursor: pointer;
  width: 16px;
  height: 16px;
  accent-color: var(--color-primary);
}

.forgot-password {
  color: var(--color-primary);
  text-decoration: none;
}

.forgot-password:hover {
  opacity: 0.8;
}

.login-btn {
  width: 100%;
  padding: 0.875rem;
  font-size: 1rem;
  font-weight: 600;
  color: white;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  border: none;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(22, 117, 231, 0.3);
  cursor: pointer;
}

.login-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(22, 117, 231, 0.4);
}

.login-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.divider {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin: 1.5rem 0;
  color: var(--color-text-light);
  font-size: 0.875rem;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #e5e7eb;
}

.social-login {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.875rem;
  margin-bottom: 1.5rem;
}

.social-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  font-size: 0.9375rem;
  font-weight: 600;
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  background: white;
  color: var(--color-text-dark);
  cursor: pointer;
}

.social-btn:hover {
  border-color: var(--color-primary);
  background: var(--color-bg-light);
}

.social-btn.google {
  color: var(--color-text-dark);
}

.signup-link {
  text-align: center;
  font-size: 0.9375rem;
  color: var(--color-text-light);
}

.signup-link a {
  color: var(--color-primary);
  font-weight: 600;
  text-decoration: none;
}

.signup-link a:hover {
  opacity: 0.8;
  text-decoration: underline;
}

/* Responsive */
@media (max-width: 768px) {
  .login-container {
    grid-template-columns: 1fr;
  }

  .login-branding {
    display: none;
  }

  .login-form-section {
    padding: 2rem 1.5rem;
    min-height: 100vh;
  }

  .form-wrapper {
    max-width: 100%;
  }

  .back-btn {
    width: 36px;
    height: 36px;
  }
  .form-wrapper h1 {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .login-form-section {
    padding: 1.5rem 1rem;
  }

  .form-wrapper h1 {
    font-size: 1.25rem;
  }

  .form-wrapper p {
    font-size: 0.875rem;
  }

  .social-login {
    gap: 0.625rem;
  }
}

</style>
