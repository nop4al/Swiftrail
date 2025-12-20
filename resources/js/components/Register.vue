<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const firstName = ref('')
const lastName = ref('')
const email = ref('')
const phoneNumber = ref('')
const gender = ref('')
const password = ref('')
const confirmPassword = ref('')
const acceptTerms = ref(false)
const isLoading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const validateForm = () => {
  if (!firstName.value || !lastName.value || !email.value || !phoneNumber.value || !gender.value || !password.value || !confirmPassword.value) {
    alert('Silakan isi semua field')
    return false
  }

  if (!/^\d{10,12}$/.test(phoneNumber.value)) {
    alert('Nomor telepon harus 10-12 digit')
    return false
  }

  if (password.value !== confirmPassword.value) {
    alert('Password tidak cocok')
    return false
  }

  if (password.value.length < 8) {
    alert('Password minimal 8 karakter')
    return false
  }

  if (!acceptTerms.value) {
    alert('Anda harus menerima syarat dan ketentuan')
    return false
  }

  return true
}

const handleRegister = async () => {
  if (!validateForm()) return

  isLoading.value = true
  try {
    const response = await fetch('/api/v1/auth/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        first_name: firstName.value,
        last_name: lastName.value,
        email: email.value,
        phone_number: phoneNumber.value,
        gender: gender.value,
        password: password.value,
        password_confirmation: confirmPassword.value,
      })
    })

    const data = await response.json()
    
    if (!response.ok) {
      alert(data.message || 'Registrasi gagal')
      return
    }

    if (data.data?.access_token) {
      localStorage.setItem('auth_token', data.data.access_token)
    }
    if (data.data?.user) {
      localStorage.setItem('userProfile', JSON.stringify(data.data.user))
    }
    
    alert('Registrasi berhasil! Silakan login.')
    router.push('/login')
  } catch (error) {
    console.error('Register error:', error)
    alert('Terjadi kesalahan. Silakan coba lagi.')
  } finally {
    isLoading.value = false
  }
}

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

const toggleConfirmPasswordVisibility = () => {
  showConfirmPassword.value = !showConfirmPassword.value
}

const handleBack = () => {
  router.back()
}
</script>

<template>
  <div class="register-page">
    <div class="register-container">
      <div class="register-form-section">
        <button class="back-btn" @click="handleBack" title="Kembali">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <div class="form-wrapper">
          <h1>Buat Akun Baru</h1>
          <p class="form-subtitle">Daftarkan diri Anda untuk memulai perjalanan bersama SwiftRail</p>

          <form @submit.prevent="handleRegister" class="register-form">
            <div class="form-group-row">
              <div class="form-group">
                <label for="firstName" class="form-label">Nama Depan</label>
                <div class="input-wrapper">
                  <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/>
                    <path d="M4 20C4 20 6 17 12 17C18 17 20 20 20 20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                  <input id="firstName" v-model="firstName" type="text" class="form-input" placeholder="Contoh: Ahmad" required/>
                </div>
              </div>

              <div class="form-group">
                <label for="lastName" class="form-label">Nama Belakang</label>
                <div class="input-wrapper">
                  <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/>
                    <path d="M4 20C4 20 6 17 12 17C18 17 20 20 20 20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                  <input id="lastName" v-model="lastName" type="text" class="form-input" placeholder="Contoh: Wijaya" required/>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="email" class="form-label">Email</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                  <path d="M2 6L12 13L22 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <input id="email" v-model="email" type="email" class="form-input" placeholder="Masukkan email Anda" required/>
              </div>
            </div>

            <div class="form-group">
              <label for="password" class="form-label">Password</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect x="3" y="11" width="18" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
                  <path d="M7 11V7C7 4.23858 9.23858 2 12 2C14.7614 2 17 4.23858 17 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input id="password" v-model="password" :type="showPassword ? 'text' : 'password'" class="form-input" placeholder="Minimal 8 karakter" required/>
                <button type="button" class="toggle-password" @click="togglePasswordVisibility">
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

            <div class="form-group">
              <label for="confirm-password" class="form-label">Konfirmasi Password</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect x="3" y="11" width="18" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
                  <path d="M7 11V7C7 4.23858 9.23858 2 12 2C14.7614 2 17 4.23858 17 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input id="confirm-password" v-model="confirmPassword" :type="showConfirmPassword ? 'text' : 'password'" class="form-input" placeholder="Ulangi password Anda" required/>
                <button type="button" class="toggle-password" @click="toggleConfirmPasswordVisibility">
                  <svg v-if="!showConfirmPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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

            <div class="form-group">
              <label class="form-label">Nomor Telepon</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                </svg>
                <input v-model="phoneNumber" type="tel" class="form-input" placeholder="Contoh: 08123456789" pattern="[0-9]{10,12}" required/>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Jenis Kelamin</label>
              <div class="gender-buttons">
                <button type="button" class="gender-btn" :class="{ active: gender === 'Laki-laki' }" @click="gender = 'Laki-laki'">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 12V22M8 18H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  Laki-laki
                </button>
                <button type="button" class="gender-btn" :class="{ active: gender === 'Perempuan' }" @click="gender = 'Perempuan'">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 12V20M12 20C9 20 7 22 7 22M12 20C15 20 17 22 17 22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  Perempuan
                </button>
              </div>
            </div>

            <label class="checkbox-group">
              <input v-model="acceptTerms" type="checkbox"/>
              <span>Saya setuju dengan <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a></span>
            </label>

            <button type="submit" class="register-btn" :disabled="isLoading">
              <span v-if="!isLoading">Daftar</span>
              <span v-else>Memproses...</span>
            </button>
          </form>

          <p class="login-link">
            Sudah punya akun? <router-link to="/login">Masuk di sini</router-link>
          </p>
        </div>
      </div>

      <div class="register-branding">
        <div class="branding-content">
          <div class="features-list">
            <h2>Mengapa Bergabung dengan SwiftRail?</h2>
            <div class="feature-group">
              <div class="feature-item">
                <div class="feature-icon">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </div>
                <div class="feature-text">
                  <h3>Pemesanan Instan</h3>
                  <p>Pesan tiket kereta dalam hitungan detik</p>
                </div>
              </div>

              <div class="feature-item">
                <div class="feature-icon">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                  </svg>
                </div>
                <div class="feature-text">
                  <h3>Pembayaran Aman</h3>
                  <p>Transaksi terenkripsi dengan teknologi terbaru</p>
                </div>
              </div>

              <div class="feature-item">
                <div class="feature-icon">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2"/>
                    <path d="M7 13L11 17L17 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </div>
                <div class="feature-text">
                  <h3>E-Tiket Digital</h3>
                  <p>Terima tiket langsung di smartphone Anda</p>
                </div>
              </div>

              <div class="feature-item">
                <div class="feature-icon">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 18.72C18.93 18.46 19.77 17.96 20.4 17.28C22.31 15.16 22.03 11.98 19.78 10.2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M12 5C12 5 14.5 7.5 14.5 9.5C14.5 10.88 13.38 12 12 12C10.62 12 9.5 10.88 9.5 9.5C9.5 7.5 12 5 12 5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </div>
                <div class="feature-text">
                  <h3>Dukungan 24/7</h3>
                  <p>Tim customer service siap membantu kapan saja</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.register-page { width: 100%; min-height: 100vh; background: var(--color-bg-light); }
.register-container { display: grid; grid-template-columns: 1fr 1fr; min-height: 100vh; }
.register-form-section { background: white; padding: 3rem; display: flex; align-items: center; justify-content: center; overflow-y: auto; position: relative; }
.form-wrapper { width: 100%; max-width: 420px; }
.back-btn { position: absolute; top: 1.5rem; left: 1.5rem; width: 40px; height: 40px; border: none; background: var(--color-bg-light); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--color-text-dark); cursor: pointer; transition: all 0.3s; padding: 0; }
.back-btn:hover { background: var(--color-primary); color: white; transform: translateX(-2px); }
.back-btn svg { width: 16px; height: 16px; }
.form-wrapper h1 { font-size: 1.875rem; font-weight: 700; color: var(--color-text-dark); margin-bottom: 0.5rem; }
.form-subtitle { font-size: 0.9375rem; color: var(--color-text-light); margin-bottom: 2rem; }
.register-form { margin-bottom: 1.5rem; }
.form-group { margin-bottom: 1.5rem; }
.form-group-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
@media (max-width: 640px) { .form-group-row { grid-template-columns: 1fr; gap: 0.75rem; } }
.form-label { display: block; font-size: 0.875rem; font-weight: 600; color: var(--color-text-dark); margin-bottom: 0.625rem; }
.input-wrapper { position: relative; display: flex; align-items: center; }
.input-icon { position: absolute; left: 0.875rem; color: var(--color-text-light); pointer-events: none; }
.form-input { width: 100%; padding: 0.875rem 0.875rem 0.875rem 2.75rem; font-size: 0.9375rem; font-weight: 500; color: var(--color-text-dark); background: var(--color-bg-light); border: 1.5px solid #e5e7eb; border-radius: 10px; transition: all 0.2s; }
.form-input:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(22, 117, 231, 0.1); background: white; }
.form-input::placeholder { color: #9ca3af; }
.toggle-password { position: absolute; right: 0.875rem; background: none; border: none; color: var(--color-text-light); cursor: pointer; padding: 0.25rem; display: flex; align-items: center; justify-content: center; transition: color 0.2s; }
.toggle-password:hover { color: var(--color-primary); }
.checkbox-group { display: flex; align-items: flex-start; gap: 0.625rem; margin-bottom: 1.5rem; font-size: 0.875rem; color: var(--color-text-dark); line-height: 1.5; }
.checkbox-group input { margin-top: 3px; width: 16px; height: 16px; cursor: pointer; accent-color: var(--color-primary); flex-shrink: 0; }
.checkbox-group a { color: var(--color-primary); text-decoration: none; font-weight: 600; }
.checkbox-group a:hover { text-decoration: underline; }
.register-btn { width: 100%; padding: 0.875rem; font-size: 1rem; font-weight: 600; color: white; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%); border: none; border-radius: 10px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(22, 117, 231, 0.3); cursor: pointer; }
.register-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(22, 117, 231, 0.4); }
.register-btn:disabled { opacity: 0.7; cursor: not-allowed; }
.login-link { text-align: center; font-size: 0.9375rem; color: var(--color-text-light); }
.login-link a { color: var(--color-primary); font-weight: 600; text-decoration: none; transition: opacity 0.2s; }
.login-link a:hover { opacity: 0.8; text-decoration: underline; }
.register-branding { background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary) 50%, var(--color-secondary) 100%); color: white; padding: 3rem; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
.register-branding::before { content: ''; position: absolute; inset: 0; background-image: radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(9, 216, 227, 0.15) 0%, transparent 40%); pointer-events: none; }
.branding-content { position: relative; z-index: 1; }
.features-list h2 { font-size: 1.875rem; font-weight: 700; margin-bottom: 2rem; line-height: 1.3; }
.feature-group { display: flex; flex-direction: column; gap: 1.5rem; }
.feature-item { display: flex; gap: 1rem; }
.feature-icon { flex-shrink: 0; display: flex; align-items: center; justify-content: center; width: 48px; height: 48px; background: rgba(255, 255, 255, 0.15); border-radius: 12px; color: white; }
.feature-text h3 { font-size: 0.95rem; font-weight: 700; margin-bottom: 0.25rem; }
.feature-text p { font-size: 0.85rem; opacity: 0.95; line-height: 1.4; }
.gender-buttons { display: flex; gap: 12px; }
.gender-btn { flex: 1; padding: 12px 16px; display: flex; align-items: center; justify-content: center; gap: 8px; border: 2px solid #e5e7eb; background-color: #fff; color: #6b7280; border-radius: 10px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.3s ease; }
.gender-btn:hover { border-color: #d1d5db; background-color: #f9fafb; }
.gender-btn.active { border-color: #8b5cf6; background-color: #f3e8ff; color: #7c3aed; }
.gender-btn svg { width: 20px; height: 20px; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
@keyframes slideInLeft { from { opacity: 0; transform: translateX(-50px); } to { opacity: 1; transform: translateX(0); } }
@keyframes slideInRight { from { opacity: 0; transform: translateX(50px); } to { opacity: 1; transform: translateX(0); } }
@media (max-width: 768px) { .register-container { grid-template-columns: 1fr; } .register-branding { display: none; } .register-form-section { padding: 2rem 1.5rem; min-height: 100vh; } .form-wrapper { max-width: 100%; } .form-wrapper h1 { font-size: 1.5rem; } .back-btn { width: 36px; height: 36px; } }
@media (max-width: 480px) { .register-form-section { padding: 1.5rem 1rem; } .form-wrapper h1 { font-size: 1.25rem; } .form-wrapper p { font-size: 0.875rem; } .checkbox-group { font-size: 0.8125rem; } }
</style>
