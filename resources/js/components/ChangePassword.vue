<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Form data
const formData = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

// Form state
const isLoading = ref(false)
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)
const successMessage = ref('')
const showSuccess = ref(false)
const passwordStrength = ref(0)

// Validation
const errors = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

// Calculate password strength
const calculatePasswordStrength = (password) => {
  let strength = 0
  if (!password) return 0

  // Length check
  if (password.length >= 8) strength += 1
  if (password.length >= 12) strength += 1

  // Character type checks
  if (/[a-z]/.test(password)) strength += 1
  if (/[A-Z]/.test(password)) strength += 1
  if (/[0-9]/.test(password)) strength += 1
  if (/[^a-zA-Z0-9]/.test(password)) strength += 1

  return Math.ceil((strength / 6) * 100)
}

const getPasswordStrengthLabel = (strength) => {
  if (strength < 25) return 'Sangat Lemah'
  if (strength < 50) return 'Lemah'
  if (strength < 75) return 'Cukup Kuat'
  return 'Kuat'
}

const getPasswordStrengthColor = (strength) => {
  if (strength < 25) return '#dc2626'
  if (strength < 50) return '#f97316'
  if (strength < 75) return '#eab308'
  return '#16a34a'
}

// Validation
const validateForm = () => {
  // Reset errors
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })

  let isValid = true

  // Validate current password
  if (!formData.currentPassword) {
    errors.currentPassword = 'Password saat ini harus diisi'
    isValid = false
  }

  // Validate new password
  if (!formData.newPassword) {
    errors.newPassword = 'Password baru harus diisi'
    isValid = false
  } else if (formData.newPassword.length < 8) {
    errors.newPassword = 'Password minimal 8 karakter'
    isValid = false
  } else if (calculatePasswordStrength(formData.newPassword) < 50) {
    errors.newPassword = 'Password terlalu lemah. Gunakan kombinasi huruf besar, kecil, angka, dan simbol'
    isValid = false
  }

  // Check if new password is same as current
  if (formData.newPassword === formData.currentPassword) {
    errors.newPassword = 'Password baru harus berbeda dengan password saat ini'
    isValid = false
  }

  // Validate confirm password
  if (!formData.confirmPassword) {
    errors.confirmPassword = 'Konfirmasi password harus diisi'
    isValid = false
  } else if (formData.confirmPassword !== formData.newPassword) {
    errors.confirmPassword = 'Password tidak sesuai'
    isValid = false
  }

  return isValid
}

// Submit handler
const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  isLoading.value = true
  try {
    // Simulasi API call untuk verifikasi password lama dan update password baru
    await new Promise(resolve => setTimeout(resolve, 1500))

    successMessage.value = 'Password berhasil diperbarui!'
    showSuccess.value = true

    // Reset form
    formData.currentPassword = ''
    formData.newPassword = ''
    formData.confirmPassword = ''
    passwordStrength.value = 0

    // Hide success message dan kembali
    setTimeout(() => {
      router.push('/profile')
    }, 2000)
  } catch (error) {
    console.error('Error changing password:', error)
  } finally {
    isLoading.value = false
  }
}

const handleCancel = () => {
  router.back()
}

const updatePasswordStrength = () => {
  passwordStrength.value = calculatePasswordStrength(formData.newPassword)
}
</script>

<template>
  <div class="change-password-page">
    <!-- Header -->
    <header class="password-header">
      <div class="header-container">
        <button class="back-btn" @click="handleCancel">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <h1 class="header-title">Ganti Password</h1>
        <div class="header-spacer"></div>
      </div>
    </header>

    <div class="password-container">
      <!-- Security Info Card -->
      <div class="security-info">
        <div class="info-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
            <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="info-content">
          <h3>Keamanan Akun Anda</h3>
          <p>Ganti password secara berkala untuk menjaga keamanan akun Anda dari akses tidak sah.</p>
        </div>
      </div>

      <!-- Password Form -->
      <form @submit.prevent="handleSubmit" class="password-form">
        <!-- Current Password Field -->
        <div class="form-group">
          <label for="currentPassword" class="form-label">
            Password Saat Ini
            <span class="required">*</span>
          </label>
          <div class="input-wrapper password-wrapper">
            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect x="3" y="11" width="18" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
              <path d="M7 11V7C7 4.23858 9.23858 2 12 2C14.7614 2 17 4.23858 17 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <input
              id="currentPassword"
              v-model="formData.currentPassword"
              :type="showCurrentPassword ? 'text' : 'password'"
              class="form-input"
              placeholder="Masukkan password saat ini"
              :class="{ 'input-error': errors.currentPassword }"
            />
            <button
              type="button"
              class="toggle-password"
              @click="showCurrentPassword = !showCurrentPassword"
              :title="showCurrentPassword ? 'Sembunyikan' : 'Tampilkan'"
            >
              <svg v-if="!showCurrentPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
              </svg>
              <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
          <p v-if="errors.currentPassword" class="error-message">{{ errors.currentPassword }}</p>
        </div>

        <!-- New Password Field -->
        <div class="form-group">
          <label for="newPassword" class="form-label">
            Password Baru
            <span class="required">*</span>
          </label>
          <div class="input-wrapper password-wrapper">
            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect x="3" y="11" width="18" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
              <path d="M7 11V7C7 4.23858 9.23858 2 12 2C14.7614 2 17 4.23858 17 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <input
              id="newPassword"
              v-model="formData.newPassword"
              :type="showNewPassword ? 'text' : 'password'"
              class="form-input"
              placeholder="Masukkan password baru"
              :class="{ 'input-error': errors.newPassword }"
              @input="updatePasswordStrength"
            />
            <button
              type="button"
              class="toggle-password"
              @click="showNewPassword = !showNewPassword"
              :title="showNewPassword ? 'Sembunyikan' : 'Tampilkan'"
            >
              <svg v-if="!showNewPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
              </svg>
              <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>

          <!-- Password Strength Indicator -->
          <div v-if="formData.newPassword" class="password-strength">
            <div class="strength-bar">
              <div 
                class="strength-fill" 
                :style="{ 
                  width: `${passwordStrength}%`,
                  backgroundColor: getPasswordStrengthColor(passwordStrength)
                }"
              ></div>
            </div>
            <p class="strength-label" :style="{ color: getPasswordStrengthColor(passwordStrength) }">
              Kekuatan: {{ getPasswordStrengthLabel(passwordStrength) }}
            </p>
          </div>

          <!-- Password Requirements -->
          <div v-if="formData.newPassword" class="password-requirements">
            <p class="requirements-title">Syarat Password:</p>
            <ul class="requirements-list">
              <li :class="{ met: formData.newPassword.length >= 8 }">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Minimal 8 karakter
              </li>
              <li :class="{ met: /[a-z]/.test(formData.newPassword) }">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Mengandung huruf kecil (a-z)
              </li>
              <li :class="{ met: /[A-Z]/.test(formData.newPassword) }">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Mengandung huruf besar (A-Z)
              </li>
              <li :class="{ met: /[0-9]/.test(formData.newPassword) }">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Mengandung angka (0-9)
              </li>
              <li :class="{ met: /[^a-zA-Z0-9]/.test(formData.newPassword) }">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Mengandung karakter khusus (!@#$%^&*)
              </li>
            </ul>
          </div>

          <p v-if="errors.newPassword" class="error-message">{{ errors.newPassword }}</p>
        </div>

        <!-- Confirm Password Field -->
        <div class="form-group">
          <label for="confirmPassword" class="form-label">
            Konfirmasi Password Baru
            <span class="required">*</span>
          </label>
          <div class="input-wrapper password-wrapper">
            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect x="3" y="11" width="18" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
              <path d="M7 11V7C7 4.23858 9.23858 2 12 2C14.7614 2 17 4.23858 17 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <input
              id="confirmPassword"
              v-model="formData.confirmPassword"
              :type="showConfirmPassword ? 'text' : 'password'"
              class="form-input"
              placeholder="Konfirmasi password baru"
              :class="{ 'input-error': errors.confirmPassword }"
            />
            <button
              type="button"
              class="toggle-password"
              @click="showConfirmPassword = !showConfirmPassword"
              :title="showConfirmPassword ? 'Sembunyikan' : 'Tampilkan'"
            >
              <svg v-if="!showConfirmPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
              </svg>
              <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
          <p v-if="errors.confirmPassword" class="error-message">{{ errors.confirmPassword }}</p>
        </div>

        <!-- Success Message -->
        <Transition name="fade">
          <div v-if="showSuccess" class="success-message">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
            </svg>
            <span>{{ successMessage }}</span>
          </div>
        </Transition>

        <!-- Form Actions -->
        <div class="form-actions">
          <button type="button" class="btn-cancel" @click="handleCancel" :disabled="isLoading">
            Batal
          </button>
          <button type="submit" class="btn-submit" :disabled="isLoading">
            <span v-if="!isLoading">Ubah Password</span>
            <span v-else class="loading">
              <span class="spinner"></span>
              Memproses...
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.change-password-page {
  width: 100%;
  min-height: 100vh;
  background: var(--color-bg-light);
}

/* Header */
.password-header {
  background: var(--color-white);
  border-bottom: 1px solid #e5e7eb;
  position: sticky;
  top: 0;
  z-index: 50;
}

.header-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 1rem 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.back-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: var(--color-bg-light);
  border: none;
  border-radius: 10px;
  color: var(--color-text-dark);
  cursor: pointer;
}

.back-btn:hover {
  background: #e5e7eb;
  color: var(--color-primary);
}

.header-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-text-dark);
}

.header-spacer {
  flex: 1;
}

/* Container */
.password-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
}

/* Security Info Card */
.security-info {
  display: flex;
  align-items: flex-start;
  gap: 1.25rem;
  background: linear-gradient(135deg, rgba(22, 117, 231, 0.1) 0%, rgba(9, 216, 227, 0.1) 100%);
  border: 1.5px solid #dbeafe;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.info-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  background: var(--color-primary);
  border-radius: 10px;
  color: white;
  flex-shrink: 0;
}

.info-content h3 {
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin-bottom: 0.375rem;
}

.info-content p {
  font-size: 0.8875rem;
  color: var(--color-text-light);
  line-height: 1.5;
}

/* Password Form */
.password-form {
  background: var(--color-white);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

/* Form Groups */
.form-group {
  margin-bottom: 1.75rem;
}

.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 0.625rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.required {
  color: #dc2626;
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
  display: flex;
  align-items: center;
  justify-content: center;
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
  transition: all 0.2s ease;
}

.form-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(22, 117, 231, 0.1);
  background: var(--color-white);
}

.form-input::placeholder {
  color: #9ca3af;
}

.form-input.input-error {
  border-color: #dc2626;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-input.input-error:focus {
  border-color: #dc2626;
}

.toggle-password {
  position: absolute;
  right: 0.875rem;
  background: none;
  border: none;
  color: var(--color-text-light);
  cursor: pointer;
  padding: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toggle-password:hover {
  color: var(--color-primary);
}

.error-message {
  font-size: 0.8125rem;
  color: #dc2626;
  margin-top: 0.375rem;
}

/* Password Strength */
.password-strength {
  margin-top: 0.75rem;
  padding: 0.75rem 0;
}

.strength-bar {
  width: 100%;
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 0.375rem;
}

.strength-fill {
  height: 100%;
  border-radius: 3px;
  transition: all 0.3s ease;
}

.strength-label {
  font-size: 0.75rem;
  font-weight: 600;
}

/* Password Requirements */
.password-requirements {
  margin-top: 1rem;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 10px;
}

.requirements-title {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 0.625rem;
}

.requirements-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.requirements-list li {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8125rem;
  color: var(--color-text-light);
}

.requirements-list li svg {
  color: #d1d5db;
  flex-shrink: 0;
}

.requirements-list li.met {
  color: #16a34a;
}

.requirements-list li.met svg {
  color: #16a34a;
}

/* Success Message */
.success-message {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  background: #dcfce7;
  border: 1.5px solid #86efac;
  border-radius: 10px;
  color: #166534;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

.success-message svg {
  color: #16a34a;
  flex-shrink: 0;
}

/* Form Actions */
.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.btn-cancel,
.btn-submit {
  flex: 1;
  padding: 0.875rem 1.5rem;
  font-size: 0.95rem;
  font-weight: 600;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancel {
  background: var(--color-bg-light);
  color: var(--color-text-dark);
  border: 1.5px solid #e5e7eb;
}

.btn-cancel:hover:not(:disabled) {
  border-color: var(--color-text-dark);
  background: #f3f4f6;
}

.btn-cancel:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-submit {
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  color: var(--color-white);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(22, 117, 231, 0.3);
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.loading {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Fade Transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Responsive */
@media (max-width: 768px) {
  .password-container {
    padding: 1rem;
  }

  .password-form {
    padding: 1.5rem;
  }

  .header-container {
    padding: 0.75rem 1rem;
  }

  .header-title {
    font-size: 1.25rem;
  }

  .security-info {
    padding: 1.25rem;
  }

  .form-actions {
    flex-direction: column;
  }
}

@media (max-width: 480px) {
  .password-form {
    padding: 1rem;
  }

  .security-info {
    gap: 1rem;
    padding: 1rem;
  }

  .info-icon {
    width: 40px;
    height: 40px;
  }

  .info-content h3 {
    font-size: 0.95rem;
  }

  .form-label {
    font-size: 0.8125rem;
  }

  .form-input {
    font-size: 1rem;
  }

  .btn-cancel,
  .btn-submit {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
  }
}

</style>
