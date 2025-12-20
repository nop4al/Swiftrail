<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Active tab
const activeTab = ref('biodata')

// Form data
const formData = reactive({
  name: 'Andi Saputra',
  email: 'andi@swiftrail.com',
  phone: '-',
  identityNumber: '-',
  address: '-',
  identityType: 'KTP',
  city: '-',
})

// Password data
const passwordData = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

// Password strength
const passwordStrength = ref(0)
const passwordRequirements = ref({
  hasUppercase: false,
  hasLowercase: false,
  hasNumbers: false,
  hasSymbols: false,
  hasLength: false,
})

// Form state
const isLoading = ref(false)
const successMessage = ref('')
const showSuccess = ref(false)
const errors = reactive({
  name: '',
  email: '',
  phone: '',
  address: '',
  identityNumber: '',
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

// Calculate password strength
const calculatePasswordStrength = (password) => {
  passwordRequirements.value.hasUppercase = /[A-Z]/.test(password)
  passwordRequirements.value.hasLowercase = /[a-z]/.test(password)
  passwordRequirements.value.hasNumbers = /[0-9]/.test(password)
  passwordRequirements.value.hasSymbols = /[!@#$%^&*]/.test(password)
  passwordRequirements.value.hasLength = password.length >= 8

  const metRequirements = Object.values(passwordRequirements.value).filter(Boolean).length
  passwordStrength.value = Math.round((metRequirements / 5) * 100)
}

// Validation
const validateBiodata = () => {
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })

  let isValid = true

  // Validate name
  if (!formData.name.trim()) {
    errors.name = 'Nama harus diisi'
    isValid = false
  } else if (formData.name.trim().length < 3) {
    errors.name = 'Nama minimal 3 karakter'
    isValid = false
  }

  // Validate email
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!formData.email.trim()) {
    errors.email = 'Email harus diisi'
    isValid = false
  } else if (!emailRegex.test(formData.email)) {
    errors.email = 'Format email tidak valid'
    isValid = false
  }

  // Validate phone
  const phoneRegex = /^(\+62|62|0)[0-9]{9,12}$/
  if (!formData.phone.trim() || formData.phone === '-') {
    errors.phone = 'Nomor telepon harus diisi'
    isValid = false
  } else if (!phoneRegex.test(formData.phone.replace(/\s|-/g, ''))) {
    errors.phone = 'Nomor telepon tidak valid'
    isValid = false
  }

  // Validate address
  if (!formData.address.trim() || formData.address === '-') {
    errors.address = 'Alamat harus diisi'
    isValid = false
  } else if (formData.address.trim().length < 10) {
    errors.address = 'Alamat minimal 10 karakter'
    isValid = false
  }

  // Validate identity number
  if (!formData.identityNumber.trim() || formData.identityNumber === '-') {
    errors.identityNumber = 'Nomor identitas harus diisi'
    isValid = false
  }

  return isValid
}

const validatePassword = () => {
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })

  let isValid = true

  // Validate current password
  if (!passwordData.currentPassword) {
    errors.currentPassword = 'Kata sandi saat ini harus diisi'
    isValid = false
  }

  // Validate new password
  if (!passwordData.newPassword) {
    errors.newPassword = 'Kata sandi baru harus diisi'
    isValid = false
  } else if (!passwordRequirements.value.hasLength || !passwordRequirements.value.hasUppercase || 
             !passwordRequirements.value.hasLowercase || !passwordRequirements.value.hasNumbers) {
    errors.newPassword = 'Kata sandi tidak memenuhi persyaratan'
    isValid = false
  }

  // Validate confirm password
  if (!passwordData.confirmPassword) {
    errors.confirmPassword = 'Konfirmasi kata sandi harus diisi'
    isValid = false
  } else if (passwordData.newPassword !== passwordData.confirmPassword) {
    errors.confirmPassword = 'Kata sandi tidak cocok'
    isValid = false
  }

  return isValid
}

// Submit handler
const handleSubmit = async () => {
  const isValid = activeTab.value === 'biodata' ? validateBiodata() : validatePassword()
  
  if (!isValid) {
    return
  }

  isLoading.value = true
  try {
    // Simulasi API call
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    const message = activeTab.value === 'biodata' 
      ? 'Data pribadi berhasil diperbarui!' 
      : 'Kata sandi berhasil diubah!'
    
    successMessage.value = message
    showSuccess.value = true

    // Reset form jika password
    if (activeTab.value === 'password') {
      passwordData.currentPassword = ''
      passwordData.newPassword = ''
      passwordData.confirmPassword = ''
      passwordStrength.value = 0
    }

    // Hide success message dan kembali
    setTimeout(() => {
      router.push('/profile')
    }, 2000)
  } catch (error) {
    console.error('Error updating profile:', error)
  } finally {
    isLoading.value = false
  }
}

const handleCancel = () => {
  router.back()
}
</script>

<template>
  <div class="edit-profile-page">
    <!-- Header -->
    <header class="profile-header">
      <div class="header-container">
        <button class="back-btn" @click="handleCancel">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <h1 class="header-title">Edit Profil</h1>
        <div class="header-spacer"></div>
      </div>
    </header>

    <!-- Tabs -->
    <div class="tabs-container">
      <div class="tabs-header">
        <button
          class="tab-button"
          :class="{ active: activeTab === 'biodata' }"
          @click="activeTab = 'biodata'"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Biodata Diri
        </button>
        <button
          class="tab-button"
          :class="{ active: activeTab === 'password' }"
          @click="activeTab = 'password'"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          Kata Sandi & Email
        </button>
      </div>
    </div>

    <div class="edit-container">
      <form @submit.prevent="handleSubmit" class="edit-form">
        <!-- Biodata Tab -->
        <div v-show="activeTab === 'biodata'" class="tab-content">
          <!-- Personal Data Section -->
          <div class="form-section">
            <h3 class="form-section-title">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Data Pribadi
            </h3>

            <!-- Name & Email -->
            <div class="form-group">
              <label for="name" class="form-label">Nama Lengkap *</label>
              <div class="input-wrapper">
                <input
                  id="name"
                  v-model="formData.name"
                  type="text"
                  class="form-input"
                  placeholder="Masukkan nama lengkap"
                  :class="{ 'input-error': errors.name }"
                />
              </div>
              <p v-if="errors.name" class="error-message">{{ errors.name }}</p>
            </div>

            <div class="form-group">
              <label for="email" class="form-label">Email *</label>
              <div class="input-wrapper">
                <input
                  id="email"
                  v-model="formData.email"
                  type="email"
                  class="form-input"
                  placeholder="Masukkan email"
                  :class="{ 'input-error': errors.email }"
                />
              </div>
              <p v-if="errors.email" class="error-message">{{ errors.email }}</p>
            </div>

            <!-- Phone & Identity -->
            <div class="form-group">
              <label for="phone" class="form-label">Nomor Telepon *</label>
              <div class="input-wrapper">
                <input
                  id="phone"
                  v-model="formData.phone"
                  type="tel"
                  class="form-input"
                  placeholder="Masukkan nomor telepon"
                  :class="{ 'input-error': errors.phone }"
                />
              </div>
              <p v-if="errors.phone" class="error-message">{{ errors.phone }}</p>
            </div>

            <div class="form-group">
              <label for="identityNumber" class="form-label">Nomor Identitas (KTP) *</label>
              <div class="input-wrapper">
                <input
                  id="identityNumber"
                  v-model="formData.identityNumber"
                  type="text"
                  class="form-input"
                  placeholder="Masukkan nomor identitas"
                  :class="{ 'input-error': errors.identityNumber }"
                />
              </div>
              <p v-if="errors.identityNumber" class="error-message">{{ errors.identityNumber }}</p>
            </div>
          </div>

          <!-- Address Section -->
          <div class="form-section">
            <h3 class="form-section-title">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
              </svg>
              Dokumen & Alamat
            </h3>

            <div class="form-group">
              <label for="address" class="form-label">Domisili *</label>
              <div class="input-wrapper">
                <textarea
                  id="address"
                  v-model="formData.address"
                  class="form-input textarea"
                  placeholder="Masukkan alamat lengkap"
                  rows="4"
                  :class="{ 'input-error': errors.address }"
                ></textarea>
              </div>
              <p v-if="errors.address" class="error-message">{{ errors.address }}</p>
            </div>

            <div class="form-group">
              <label for="city" class="form-label">Kota/Kabupaten</label>
              <div class="input-wrapper">
                <input
                  id="city"
                  v-model="formData.city"
                  type="text"
                  class="form-input"
                  placeholder="Masukkan kota/kabupaten"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Password Tab -->
        <div v-show="activeTab === 'password'" class="tab-content">
          <div class="form-section">
            <h3 class="form-section-title">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              Keamanan Akun
            </h3>

            <!-- Current Password -->
            <div class="form-group">
              <label for="currentPassword" class="form-label">Kata Sandi Saat Ini *</label>
              <div class="input-wrapper">
                <input
                  id="currentPassword"
                  v-model="passwordData.currentPassword"
                  type="password"
                  class="form-input"
                  placeholder="Masukkan kata sandi saat ini"
                  :class="{ 'input-error': errors.currentPassword }"
                />
              </div>
              <p v-if="errors.currentPassword" class="error-message">{{ errors.currentPassword }}</p>
            </div>

            <!-- New Password -->
            <div class="form-group">
              <label for="newPassword" class="form-label">Kata Sandi Baru *</label>
              <div class="input-wrapper">
                <input
                  id="newPassword"
                  v-model="passwordData.newPassword"
                  type="password"
                  class="form-input"
                  placeholder="Masukkan kata sandi baru"
                  @input="calculatePasswordStrength(passwordData.newPassword)"
                  :class="{ 'input-error': errors.newPassword }"
                />
              </div>
              <p v-if="errors.newPassword" class="error-message">{{ errors.newPassword }}</p>

              <!-- Password Strength Indicator -->
              <div class="password-strength">
                <div class="strength-bar">
                  <div class="strength-progress" :style="{ width: passwordStrength + '%' }"></div>
                </div>
                <p class="strength-text">Kekuatan kata sandi: {{ passwordStrength }}%</p>
              </div>

              <!-- Password Requirements -->
              <div class="password-requirements">
                <p class="requirements-title">Persyaratan kata sandi:</p>
                <ul class="requirements-list">
                  <li :class="{ met: passwordRequirements.hasLength }">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Minimal 8 karakter
                  </li>
                  <li :class="{ met: passwordRequirements.hasUppercase }">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Huruf kapital (A-Z)
                  </li>
                  <li :class="{ met: passwordRequirements.hasLowercase }">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Huruf kecil (a-z)
                  </li>
                  <li :class="{ met: passwordRequirements.hasNumbers }">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Angka (0-9)
                  </li>
                </ul>
              </div>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
              <label for="confirmPassword" class="form-label">Konfirmasi Kata Sandi *</label>
              <div class="input-wrapper">
                <input
                  id="confirmPassword"
                  v-model="passwordData.confirmPassword"
                  type="password"
                  class="form-input"
                  placeholder="Masukkan ulang kata sandi baru"
                  :class="{ 'input-error': errors.confirmPassword }"
                />
              </div>
              <p v-if="errors.confirmPassword" class="error-message">{{ errors.confirmPassword }}</p>
            </div>
          </div>
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
            <span v-if="!isLoading">Simpan Perubahan</span>
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
.edit-profile-page {
  width: 100%;
  min-height: 100vh;
  background: var(--color-bg-light);
}

/* Header */
.profile-header {
  background: var(--color-white);
  border-bottom: 1px solid #e5e7eb;
  position: sticky;
  top: 0;
  z-index: 50;
}

.header-container {
  max-width: 900px;
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
  transition: all 0.2s ease;
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

/* Tabs */
.tabs-container {
  background: var(--color-white);
  border-bottom: 1px solid #e5e7eb;
  position: sticky;
  top: 60px;
  z-index: 40;
}

.tabs-header {
  max-width: 900px;
  margin: 0 auto;
  display: flex;
  padding: 0 1.5rem;
}

.tab-button {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  background: transparent;
  border: none;
  border-bottom: 3px solid transparent;
  color: var(--color-text-light);
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-button:hover {
  color: var(--color-text-dark);
}

.tab-button.active {
  color: var(--color-primary);
  border-bottom-color: var(--color-primary);
}

.tab-button svg {
  flex-shrink: 0;
}

/* Container */
.edit-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
}

/* Form */
.edit-form {
  background: var(--color-white);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.tab-content {
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Form Sections */
.form-section {
  margin-bottom: 2.5rem;
}

.form-section:last-of-type {
  margin-bottom: 0;
}

.form-section-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin-bottom: 1.5rem;
}

.form-section-title svg {
  color: var(--color-primary);
  flex-shrink: 0;
}

/* Form Groups */
.form-group {
  margin-bottom: 1.5rem;
}

.form-group.full-width {
  grid-column: 1 / -1;
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

.form-input {
  width: 100%;
  padding: 0.875rem 1rem;
  font-size: 0.9375rem;
  font-weight: 500;
  color: var(--color-text-dark);
  background: var(--color-bg-light);
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  transition: all 0.2s ease;
  font-family: inherit;
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
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-input.textarea {
  resize: vertical;
  min-height: 100px;
}

.error-message {
  font-size: 0.8125rem;
  color: #dc2626;
  margin-top: 0.375rem;
}

/* Password Strength */
.password-strength {
  margin-top: 1rem;
  margin-bottom: 1.25rem;
}

.strength-bar {
  height: 4px;
  background: #e5e7eb;
  border-radius: 2px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.strength-progress {
  height: 100%;
  background: linear-gradient(90deg, #dc2626 0%, #f97316 50%, #16a34a 100%);
  transition: width 0.3s ease;
}

.strength-text {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-text-light);
}

/* Password Requirements */
.password-requirements {
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 1rem;
  margin-top: 1rem;
}

.requirements-title {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.requirements-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.requirements-list li {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.875rem;
  color: #9ca3af;
  transition: color 0.2s ease;
}

.requirements-list li.met {
  color: #16a34a;
  font-weight: 600;
}

.requirements-list li svg {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
  color: currentColor;
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
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
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
  .edit-container {
    padding: 1rem;
  }

  .edit-form {
    padding: 1.5rem;
  }

  .header-container {
    padding: 0.75rem 1rem;
  }

  .header-title {
    font-size: 1.25rem;
  }

  .tabs-header {
    padding: 0 1rem;
  }

  .tab-button {
    padding: 0.875rem 1rem;
    font-size: 0.875rem;
    gap: 0.5rem;
  }

  .tab-button svg {
    width: 16px;
    height: 16px;
  }

  .form-actions {
    flex-direction: column;
  }
}

@media (max-width: 480px) {
  .edit-form {
    padding: 1rem;
  }

  .form-section-title {
    font-size: 1rem;
  }

  .form-label {
    font-size: 0.8125rem;
  }

  .form-input {
    font-size: 1rem;
    padding: 0.75rem 0.875rem;
  }

  .btn-cancel,
  .btn-submit {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
  }

  .tabs-header {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .tab-button {
    white-space: nowrap;
    flex-shrink: 0;
  }
}

</style>
