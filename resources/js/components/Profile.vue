<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import ProfilePhotoUpload from './ProfilePhotoUpload.vue'

const router = useRouter()

// User menu
const showUserMenu = ref(false)

// Active Tab State
const activeTab = ref('profil') // 'profil', 'loyalty'
const activeEditTab = ref('biodata') // For edit profile: 'biodata', 'password'
const isEditMode = ref(false) // Toggle between view and edit mode

// User data
const userProfile = ref({
  first_name: '',
  last_name: '',
  email: '',
  gender: '',
  phone: '',
  identityNumber: '',
  address: '',
  city: ''
})

// Form data for edit mode
const formData = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  identityNumber: '',
  address: '',
  identityType: 'KTP',
  city: '',
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
const errorMessage = ref('')
const errors = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  address: '',
  identityNumber: '',
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

// Photo upload modal state
const showPhotoUploadModal = ref(false)
const photoUploadLoading = ref(false)
const photoUploadError = ref('')
const selectedPhotoFile = ref(null)
const photoPreviewUrl = ref(null)
const photoFileInput = ref(null)

// Loyalty data
const userLoyalty = ref({})

// Fetch user profile from API
const fetchUserProfile = async () => {
  try {
    isLoading.value = true
    const token = localStorage.getItem('auth_token')
    const profileFromStorage = localStorage.getItem('userProfile')
    
    if (!token) {
      // Jika ada data di localStorage, gunakan itu dulu
      if (profileFromStorage) {
        try {
          userProfile.value = JSON.parse(profileFromStorage)
          return
        } catch (e) {
          console.error('Error parsing stored profile:', e)
        }
      }
      router.push('/login')
      return
    }

    const response = await fetch('/api/v1/auth/profile', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })

    if (!response.ok) {
      if (response.status === 401) {
        localStorage.removeItem('authToken')
        // Jika ada data di localStorage, gunakan itu sambil redirect
        if (profileFromStorage) {
          try {
            userProfile.value = JSON.parse(profileFromStorage)
          } catch (e) {
            console.error('Error parsing stored profile:', e)
          }
        }
        // Tunggu sebentar baru redirect
        setTimeout(() => {
          router.push('/login')
        }, 500)
        return
      }
      // Jika ada error lain, gunakan data dari localStorage jika tersedia
      if (profileFromStorage) {
        try {
          userProfile.value = JSON.parse(profileFromStorage)
          console.warn('Using cached profile data due to API error')
          return
        } catch (e) {
          console.error('Error parsing stored profile:', e)
        }
      }
      throw new Error(`HTTP ${response.status}`)
    }

    const data = await response.json()
    
    if (data.success && data.data) {
      userProfile.value = {
        id: data.data.id,
        user_id: data.data.user_id,
        first_name: data.data.first_name,
        last_name: data.data.last_name,
        email: data.data.email,
        gender: data.data.gender,
        phone: data.data.phone || '',
        identityNumber: data.data.identityNumber || '',
        address: data.data.address || '',
        city: data.data.city || '',
        avatar: data.data.avatar || null
      }
      
      // Update form data
      formData.first_name = userProfile.value.first_name
      formData.last_name = userProfile.value.last_name
      formData.email = userProfile.value.email
      formData.phone = userProfile.value.phone
      formData.identityNumber = userProfile.value.identityNumber
      formData.address = userProfile.value.address
      formData.city = userProfile.value.city
    }
  } catch (error) {
    console.error('Error fetching profile:', error)
    errorMessage.value = 'Gagal memuat profil. Silakan coba lagi.'
  } finally {
    isLoading.value = false
  }
}

// Load profile on component mount
onMounted(() => {
  fetchUserProfile()
  fetchUserLoyalty()
})

// Handle photo update from ProfilePhotoUpload component
const handlePhotoUpdated = (photoData) => {
  // Update userProfile dengan avatar baru
  if (photoData.avatar) {
    userProfile.value.avatar = photoData.avatar
  } else {
    userProfile.value.avatar = null
  }
}

// Photo upload modal functions
const openPhotoUploadModal = () => {
  showPhotoUploadModal.value = true
  photoUploadError.value = ''
  selectedPhotoFile.value = null
  // Set preview dengan current avatar jika ada
  if (userProfile.value?.avatar) {
    photoPreviewUrl.value = `/${userProfile.value.avatar}`
  } else {
    photoPreviewUrl.value = null
  }
}

const closePhotoUploadModal = () => {
  showPhotoUploadModal.value = false
  photoUploadError.value = ''
  selectedPhotoFile.value = null
  photoPreviewUrl.value = null
  if (photoFileInput.value) {
    photoFileInput.value.value = ''
  }
}

const triggerPhotoFileInput = () => {
  photoFileInput.value?.click()
}

const handlePhotoFileSelect = (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  // Validasi tipe file
  const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']
  if (!validTypes.includes(file.type)) {
    photoUploadError.value = 'Format file harus JPEG, PNG, JPG, atau GIF'
    return
  }

  // Validasi ukuran (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    photoUploadError.value = 'Ukuran file maksimal 2MB'
    return
  }

  selectedPhotoFile.value = file
  photoUploadError.value = ''

  // Tampilkan preview
  const reader = new FileReader()
  reader.onload = (e) => {
    photoPreviewUrl.value = e.target?.result
  }
  reader.readAsDataURL(file)
}

const uploadPhotoFromModal = async () => {
  if (!selectedPhotoFile.value) {
    photoUploadError.value = 'Pilih foto terlebih dahulu'
    return
  }

  try {
    photoUploadLoading.value = true
    photoUploadError.value = ''

    const token = localStorage.getItem('auth_token')
    const formData = new FormData()
    formData.append('avatar', selectedPhotoFile.value)

    const response = await fetch('/api/v1/profile/photo', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: formData
    })

    const data = await response.json()

    if (!response.ok) {
      if (data.errors && data.errors.avatar) {
        photoUploadError.value = data.errors.avatar[0]
      } else {
        photoUploadError.value = data.message || 'Gagal mengunggah foto'
      }
      return
    }

    // Update profile dengan avatar baru
    userProfile.value.avatar = data.data.avatar

    // Update localStorage untuk sync ke header
    localStorage.setItem('userProfile', JSON.stringify(userProfile.value))

    // Dispatch custom event untuk notify Header component (storage event hanya untuk tab lain)
    window.dispatchEvent(new Event('profile-updated'))

    // Close modal
    closePhotoUploadModal()

    // Show success message
    successMessage.value = 'Foto profil berhasil diperbarui!'
    showSuccess.value = true
    setTimeout(() => {
      showSuccess.value = false
    }, 3000)
  } catch (error) {
    photoUploadError.value = error.message || 'Terjadi kesalahan saat mengunggah foto'
  } finally {
    photoUploadLoading.value = false
  }
}

// Function to get gradient based on membership level
const getMembershipGradient = (level) => {
  const gradients = {
    'Silver': 'linear-gradient(135deg, #64748B 0%, #94A3B8 100%)',
    'Gold': 'linear-gradient(135deg, #D97706 0%, #F59E0B 100%)',
    'Platinum': 'linear-gradient(135deg, #7C3AED 0%, #A855F7 100%)'
  }
  return gradients[level] || gradients['Silver']
}

// Function to get progress bar color based on level
const getProgressBarColor = (level) => {
  const colors = {
    'Silver': 'rgba(100, 116, 139, 0.8)',
    'Gold': 'rgba(217, 119, 6, 0.8)',
    'Platinum': 'rgba(124, 58, 237, 0.8)'
  }
  return colors[level] || colors['Silver']
}

// Function to get icon and color based on activity
const getActivityIcon = (activity) => {
  const iconConfig = {
    'KRL': { type: 'train', color: '#3B82F6', bgGradient: 'linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%)' },
    'LRT': { type: 'train', color: '#3B82F6', bgGradient: 'linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%)' },
    'Kereta Jarak Jauh': { type: 'train', color: '#3B82F6', bgGradient: 'linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%)' },
    'Bonus sign up': { type: 'gift', color: '#EC4899', bgGradient: 'linear-gradient(135deg, #fce7f3 0%, #fdf2f8 100%)' },
    'sign up': { type: 'gift', color: '#EC4899', bgGradient: 'linear-gradient(135deg, #fce7f3 0%, #fdf2f8 100%)' }
  }
  
  for (const [key, config] of Object.entries(iconConfig)) {
    if (activity.includes(key)) {
      return config
    }
  }
  
  return { type: 'train', color: '#3B82F6', bgGradient: 'linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%)' }
}

// Function to get SVG icon based on type
const getIconSVG = (type) => {
  const icons = {
    'train': 'M8 9h8V7H8v2zm13-2v10c0 1.1-.9 2-2 2h-1v2h-2v-2H8v2H6v-2H5c-1.1 0-2-.9-2-2V7c0-1.1.9-2 2-2h14c1.1 0 2 .9 2 2zm-2 10H5V7h14v10zm-4-3H9v-2h8v2z',
    'gift': 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z'
  }
  return icons[type] || icons['train']
}

const membershipLevels = ref([])

const pointsHistory = ref([])

const showLogoutConfirm = ref(false)

// Loyalty data - Update the existing userLoyalty
userLoyalty.value = {
  totalPoints: 0,
  currentLevel: 'Silver',
  status: 'Member Baru',
  progress: 0,
  currentPoints: 0,
  nextLevelPoints: 5000,
}

const loyaltyRewards = ref([])

const myLoyaltyRewards = ref([])

// Fetch user loyalty data (unified endpoint)
const fetchUserLoyalty = async () => {
  try {
    const token = localStorage.getItem('authToken')
    if (!token) return

    const response = await fetch('/api/v1/loyalty', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })

    const data = await response.json()
    if (data.success && data.data) {
      // Set user loyalty info
      userLoyalty.value = {
        totalPoints: data.data.loyalty.total_points,
        currentLevel: data.data.loyalty.tier.name,
        status: `Member ${data.data.loyalty.tier.name}`,
        progress: calculateLoyaltyProgress(data.data.loyalty.total_points, data.data.loyalty.tier.min_points),
        currentPoints: data.data.loyalty.total_points,
        nextLevelPoints: getNextLevelPoints(data.data.loyalty.tier.min_points),
      }

      // Set points history from transactions
      pointsHistory.value = data.data.transactions || []

      // Set available rewards
      loyaltyRewards.value = data.data.rewards || []

      // Set my claimed rewards
      myLoyaltyRewards.value = data.data.my_rewards || []

      // Set membership levels data
      membershipLevels.value = [
        {
          name: 'Silver',
          benefits: [
            'Dapatkan poin untuk setiap transaksi',
            'Akses ke reward terbatas',
            'Notifikasi promo eksklusif',
          ]
        },
        {
          name: 'Gold',
          benefits: [
            'Bonus 50% lebih banyak poin',
            'Akses ke semua reward',
            'Priority customer service',
            'Birthday special offer',
          ]
        },
        {
          name: 'Platinum',
          benefits: [
            'Bonus 100% lebih banyak poin',
            'Akses eksklusif ke event VIP',
            'Dedicated account manager',
            'Priority booking & support',
            'Exclusive gifts & rewards',
          ]
        },
      ]
    }
  } catch (error) {
    console.error('Error fetching loyalty:', error)
  }
}

// Helper functions
const calculateLoyaltyProgress = (currentPoints, minPointsCurrentTier) => {
  const nextTierPoints = getNextLevelPoints(minPointsCurrentTier)
  if (nextTierPoints === minPointsCurrentTier) return 100
  return Math.round(((currentPoints - minPointsCurrentTier) / (nextTierPoints - minPointsCurrentTier)) * 100)
}

const getNextLevelPoints = (currentMinPoints) => {
  if (currentMinPoints === 0) return 5000
  if (currentMinPoints === 5000) return 15000
  return 15000
}

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

  if (!formData.first_name.trim()) {
    errors.first_name = 'Nama depan harus diisi'
    isValid = false
  } else if (formData.first_name.trim().length < 2) {
    errors.first_name = 'Nama depan minimal 2 karakter'
    isValid = false
  }

  if (!formData.last_name.trim()) {
    errors.last_name = 'Nama belakang harus diisi'
    isValid = false
  } else if (formData.last_name.trim().length < 2) {
    errors.last_name = 'Nama belakang minimal 2 karakter'
    isValid = false
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!formData.email.trim()) {
    errors.email = 'Email harus diisi'
    isValid = false
  } else if (!emailRegex.test(formData.email)) {
    errors.email = 'Format email tidak valid'
    isValid = false
  }

  const phoneRegex = /^(\+62|62|0)[0-9]{9,12}$/
  if (!formData.phone.trim() || formData.phone === '-') {
    errors.phone = 'Nomor telepon harus diisi'
    isValid = false
  } else if (!phoneRegex.test(formData.phone.replace(/\s|-/g, ''))) {
    errors.phone = 'Nomor telepon tidak valid'
    isValid = false
  }

  if (!formData.address.trim() || formData.address === '-') {
    errors.address = 'Alamat harus diisi'
    isValid = false
  } else if (formData.address.trim().length < 10) {
    errors.address = 'Alamat minimal 10 karakter'
    isValid = false
  }

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

  if (!passwordData.currentPassword) {
    errors.currentPassword = 'Kata sandi saat ini harus diisi'
    isValid = false
  }

  if (!passwordData.newPassword) {
    errors.newPassword = 'Kata sandi baru harus diisi'
    isValid = false
  } else if (!passwordRequirements.value.hasLength || !passwordRequirements.value.hasUppercase || 
             !passwordRequirements.value.hasLowercase || !passwordRequirements.value.hasNumbers) {
    errors.newPassword = 'Kata sandi tidak memenuhi persyaratan'
    isValid = false
  }

  if (!passwordData.confirmPassword) {
    errors.confirmPassword = 'Konfirmasi kata sandi harus diisi'
    isValid = false
  } else if (passwordData.newPassword !== passwordData.confirmPassword) {
    errors.confirmPassword = 'Kata sandi tidak cocok'
    isValid = false
  }

  return isValid
}

// Submit handler for edit form
const handleEditSubmit = async () => {
  const isValid = activeEditTab.value === 'biodata' ? validateBiodata() : validatePassword()
  
  if (!isValid) {
    return
  }

  isLoading.value = true
  try {
    const token = localStorage.getItem('auth_token')
    const method = activeEditTab.value === 'biodata' ? 'PUT' : 'POST'
    const endpoint = activeEditTab.value === 'biodata' ? '/api/v1/auth/profile' : '/api/v1/auth/change-password'
    
    const payload = activeEditTab.value === 'biodata' 
      ? {
          first_name: formData.first_name,
          last_name: formData.last_name,
          email: formData.email,
          phone: formData.phone,
          identityNumber: formData.identityNumber,
          address: formData.address,
          city: formData.city
        }
      : {
          current_password: passwordData.currentPassword,
          new_password: passwordData.newPassword
        }

    const response = await fetch(endpoint, {
      method: method,
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    })

    const data = await response.json()

    if (!response.ok) {
      // Handle validation errors
      if (response.status === 422 && data.errors) {
        Object.keys(data.errors).forEach(field => {
          errors[field] = data.errors[field][0]
        })
      } else {
        throw new Error(data.message || 'Gagal menyimpan perubahan')
      }
      return
    }

    // Update user profile if biodata
    if (activeEditTab.value === 'biodata') {
      userProfile.value.first_name = formData.first_name
      userProfile.value.last_name = formData.last_name
      userProfile.value.email = formData.email
      userProfile.value.phone = formData.phone
      userProfile.value.identityNumber = formData.identityNumber
      userProfile.value.address = formData.address
      userProfile.value.city = formData.city
      
      // Save updated profile to localStorage
      localStorage.setItem('userProfile', JSON.stringify(userProfile.value))
    }
    
    const message = activeEditTab.value === 'biodata' 
      ? 'Data pribadi berhasil diperbarui!' 
      : 'Kata sandi berhasil diubah!'
    
    successMessage.value = message
    showSuccess.value = true

    if (activeEditTab.value === 'password') {
      passwordData.currentPassword = ''
      passwordData.newPassword = ''
      passwordData.confirmPassword = ''
      passwordStrength.value = 0
    }

    setTimeout(() => {
      isEditMode.value = false
      activeTab.value = 'profil'
    }, 2000)
  } catch (error) {
    console.error('Error updating profile:', error)
    successMessage.value = error.message || 'Terjadi kesalahan saat menyimpan'
    showSuccess.value = true
  } finally {
    isLoading.value = false
  }
}

const handleLogout = () => {
  localStorage.removeItem('authToken')
  localStorage.removeItem('userData')
  localStorage.removeItem('userProfile')
  showUserMenu.value = false
  // Trigger event untuk update header dan component lain
  window.dispatchEvent(new Event('auth-changed'))
  router.push('/login')
}

const startEditProfile = (tab = 'biodata') => {
  formData.first_name = userProfile.value.first_name
  formData.last_name = userProfile.value.last_name
  formData.email = userProfile.value.email
  formData.phone = userProfile.value.phone
  formData.identityNumber = userProfile.value.identityNumber
  formData.address = userProfile.value.address
  formData.city = userProfile.value.city
  activeEditTab.value = tab
  isEditMode.value = true
  // Clear errors on edit start
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
  // Scroll to top
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const cancelEdit = () => {
  isEditMode.value = false
  activeTab.value = 'profil'
  activeEditTab.value = 'biodata'
  // Clear errors on cancel
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
  // Scroll to top
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}
</script>

<template>
  <div class="profile-page" v-if="!isEditMode">
    <!-- Header -->
    <header class="header">
      <div class="header-container">
        <div class="logo">
          <div class="logo-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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
        
        <nav class="nav">
          <router-link to="/" class="nav-link">Home</router-link>
          <a href="#" class="nav-link">Tickets</a>
          
          <!-- User Profile Menu -->
          <div class="user-menu-wrapper">
            <button @click="showUserMenu = !showUserMenu" class="user-menu-btn">
              <div class="user-avatar" :style="userProfile?.avatar ? { backgroundImage: `url(/${userProfile.avatar})`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}">
                <span v-if="!userProfile?.avatar">{{ userProfile?.first_name?.charAt(0) }}{{ userProfile?.last_name?.charAt(0) }}</span>
              </div>
              <span class="user-name">{{ userProfile?.first_name }} {{ userProfile?.last_name }}</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
            
            <!-- Dropdown menu -->
            <div v-if="showUserMenu" class="dropdown-menu">
              <button @click="handleLogout" class="dropdown-item logout">Keluar</button>
            </div>
          </div>
        </nav>
      </div>
    </header>

    <div class="profile-container">
      <!-- Main Content Card -->
      <div class="main-content-card">
        <!-- Profile Tab Content -->
        <div v-if="activeTab === 'profil'" class="tab-content profile-content">
          <!-- Header Background -->
          <div class="profile-header-bg"></div>

          <!-- Profile Header Content -->
          <div class="profile-header-content">
            <!-- Avatar and Text Row -->
            <div class="profile-avatar-text">
              <!-- Avatar with Edit Icon -->
              <div class="avatar-wrapper">
                <div class="avatar" :style="userProfile?.avatar ? { backgroundImage: `url(/${userProfile.avatar})`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}">
                  <span v-if="!userProfile?.avatar">{{ userProfile.initials }}</span>
                </div>
                <!-- Edit Photo Button (Floating) -->
                <button class="avatar-edit-btn" @click="openPhotoUploadModal" title="Edit Foto Profil">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/>
                    <path d="M20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                  </svg>
                </button>
              </div>

              <!-- Profile Info -->
              <div class="profile-info-text">
                <h1 class="profile-name">{{ userProfile.first_name }} {{ userProfile.last_name }}</h1>
                <p class="profile-email">{{ userProfile.email }}</p>
                <p class="profile-id">ID: {{ userProfile.user_id }}</p>
              </div>

              <!-- Edit Button -->
              <button class="edit-btn" @click="startEditProfile">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M11 4H4C2.89543 4 2 4.89543 2 6V20C2 21.1046 2.89543 22 4 22H18C19.1046 22 20 21.1046 20 20V13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M18.5 2.5C19.3284 1.67157 20.6716 1.67157 21.5 2.5C22.3284 3.32843 22.3284 4.67157 21.5 5.5L11 16H7V12L18.5 2.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Edit Profil
              </button>
            </div>
          </div>

          <!-- Info Grid -->
          <div class="info-grid">
            <div class="info-card">
              <label class="info-label">NOMOR TELEPON</label>
              <p class="info-value">{{ userProfile.phone }}</p>
            </div>
            <div class="info-card">
              <label class="info-label">IDENTITAS (KTP)</label>
              <p class="info-value">{{ userProfile.identityNumber }}</p>
            </div>
            <div class="info-card">
              <label class="info-label">DOMISILI</label>
              <p class="info-value">{{ userProfile.address }}</p>
            </div>
          </div>

          <!-- Security Section -->
          <div class="security-section">
            <div class="security-content">
              <h3 class="security-title">Keamanan Akun</h3>
              <p class="security-text">Kata sandi terakhir diubah 30 hari yang lalu.</p>
            </div>
            <button class="update-password-btn" @click="startEditProfile('password')">
              Ubah Kata Sandi
            </button>
          </div>
        </div>

        <!-- Loyalty Tab Content -->
        <div v-if="activeTab === 'loyalty'" class="tab-content loyalty-content">
          <!-- Status Card -->
          <div class="loyalty-status-card" :style="{ background: getMembershipGradient(userLoyalty.currentLevel) }">
            <div class="status-header">
              <div class="status-info">
                <div class="status-badge">{{ userLoyalty.currentLevel }}</div>
                <h3 class="status-title">{{ userLoyalty.status }}</h3>
                <p class="status-subtitle">Poin Loyalitas Kamu</p>
              </div>
              <div class="status-points-display">
                <div class="points-number">{{ userLoyalty.totalPoints }}</div>
                <div class="points-label">POIN</div>
              </div>
            </div>

            <!-- Progress Bar -->
            <div class="progress-section">
              <div class="progress-header-text">
                <span>Progres ke {{ userLoyalty.currentLevel === 'Silver' ? 'Gold' : userLoyalty.currentLevel === 'Gold' ? 'Platinum' : 'Platinum' }}</span>
                <span class="progress-percentage">{{ userLoyalty.progress }}%</span>
              </div>
              <div class="progress-bar">
                <div class="progress-fill" :style="{ width: userLoyalty.progress + '%', background: getProgressBarColor(userLoyalty.currentLevel) }"></div>
              </div>
              <div class="progress-info">
                <div class="progress-detail">
                  <span class="progress-label">Poin Sekarang</span>
                  <span class="progress-value">{{ userLoyalty.currentPoints }}</span>
                </div>
                <div class="progress-detail">
                  <span class="progress-label">Poin Berikutnya</span>
                  <span class="progress-value">{{ userLoyalty.nextLevelPoints }}</span>
                </div>
                <div class="progress-detail">
                  <span class="progress-label">Sisa Poin</span>
                  <span class="progress-value">{{ userLoyalty.nextLevelPoints - userLoyalty.currentPoints }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Membership Levels Grid -->
          <h3 class="membership-section-title">Tingkatan Membership</h3>
          <div class="membership-grid">
            <div v-for="level in membershipLevels" :key="level.name" class="membership-card" :class="{ 'is-current': level.name === userLoyalty.currentLevel }">
              <!-- Current Badge -->
              <div v-if="level.name === userLoyalty.currentLevel" class="current-badge">LEVEL KAMU</div>
              
              <!-- Card Header with Icon -->
              <div class="card-header">
                <div class="icon-wrapper" :class="level.name.toLowerCase()">
                  <svg width="36" height="36" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2L15.09 10H23.5L17.23 14.46L19.88 22.5L12 18.15L4.12 22.5L6.77 14.46L0.5 10H9L12 2Z"/>
                  </svg>
                </div>
              </div>
              
              <!-- Card Body -->
              <div class="card-body">
                <h4 class="membership-name">{{ level.name }}</h4>
                <ul class="membership-benefits">
                  <li v-for="(benefit, idx) in level.benefits" :key="idx">
                    <span class="benefit-icon">✓</span>
                    {{ benefit }}
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Points History -->
          <div class="points-history">
            <h3 class="history-title">Riwayat Poin</h3>
            <div class="history-container">
              <div v-if="pointsHistory.length > 0" class="history-list">
                <div v-for="(item, idx) in pointsHistory" :key="idx" class="history-item">
                  <div class="history-item-left">
                    <div class="history-icon" :style="{ background: getActivityIcon(item.activity).bgGradient, color: getActivityIcon(item.activity).color }">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path :d="getIconSVG(getActivityIcon(item.activity).type)"/>
                      </svg>
                    </div>
                    <div class="history-info">
                      <p class="history-activity">{{ item.activity }}</p>
                      <p class="history-date">{{ item.date }}</p>
                    </div>
                  </div>
                  <div class="history-points-badge" :style="{ background: getActivityIcon(item.activity).bgGradient, color: getActivityIcon(item.activity).color }">
                    {{ item.points }}
                  </div>
                </div>
              </div>
              <div v-else class="empty-history">
                <p>Belum ada riwayat poin</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <div class="sidebar">
        <!-- Menu Items -->
        <div class="menu-list">
          <button class="menu-item" :class="{ active: activeTab === 'profil' }" @click="activeTab = 'profil'">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/>
              <path d="M6 20c0-3.3 2.69-6 6-6s6 2.7 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Profil Saya
          </button>
          <button class="menu-item" :class="{ active: activeTab === 'loyalty' }" @click="activeTab = 'loyalty'">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 1L15.09 8.26H23L17.55 12.25L19.64 19.52L12 15.5L4.36 19.52L6.45 12.25L1 8.26H8.91L12 1Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
            </svg>
            Loyalty
          </button>
        </div>
      </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <Transition name="modal">
      <div v-if="showLogoutConfirm" class="modal-overlay" @click="showLogoutConfirm = false">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Konfirmasi Logout</h3>
            <button class="modal-close" @click="showLogoutConfirm = false">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </button>
          </div>
          <div class="modal-body">
            <p>Apakah Anda yakin ingin keluar dari akun ini?</p>
          </div>
          <div class="modal-footer">
            <button class="btn-cancel" @click="showLogoutConfirm = false">Batal</button>
            <button class="btn-confirm" @click="handleLogout">Ya, Keluar</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>

  <!-- Edit Profile Mode -->
  <div class="edit-profile-page" v-if="isEditMode">
    <!-- Header -->
    <header class="profile-header">
      <div class="header-container">
        <button class="back-btn" @click="cancelEdit">
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
          :class="{ active: activeEditTab === 'biodata' }"
          @click="activeEditTab = 'biodata'"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Biodata Diri
        </button>
        <button
          class="tab-button"
          :class="{ active: activeEditTab === 'password' }"
          @click="activeEditTab = 'password'"
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
      <form @submit.prevent="handleEditSubmit" class="edit-form">
        <!-- Biodata Tab -->
        <div v-show="activeEditTab === 'biodata'" class="tab-content">
          <!-- Personal Data Section -->
          <div class="form-section">
            <h3 class="form-section-title">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Data Pribadi
            </h3>

            <div class="form-group-row">
              <div class="form-group">
                <label for="firstName" class="form-label">Nama Depan *</label>
                <div class="input-wrapper">
                  <input
                    id="firstName"
                    v-model="formData.first_name"
                    type="text"
                    class="form-input"
                    placeholder="Masukkan nama depan"
                    :class="{ 'input-error': errors.first_name }"
                  />
                </div>
                <p v-if="errors.first_name" class="error-message">{{ errors.first_name }}</p>
              </div>

              <div class="form-group">
                <label for="lastName" class="form-label">Nama Belakang *</label>
                <div class="input-wrapper">
                  <input
                    id="lastName"
                    v-model="formData.last_name"
                    type="text"
                    class="form-input"
                    placeholder="Masukkan nama belakang"
                    :class="{ 'input-error': errors.last_name }"
                  />
                </div>
                <p v-if="errors.last_name" class="error-message">{{ errors.last_name }}</p>
              </div>
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
        <div v-show="activeEditTab === 'password'" class="tab-content">
          <div class="form-section">
            <h3 class="form-section-title">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              Keamanan Akun
            </h3>

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

              <div class="password-strength">
                <div class="strength-bar">
                  <div class="strength-progress" :style="{ width: passwordStrength + '%' }"></div>
                </div>
                <p class="strength-text">Kekuatan kata sandi: {{ passwordStrength }}%</p>
              </div>

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
          <button type="button" class="btn-cancel" @click="cancelEdit" :disabled="isLoading">
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

  <!-- Photo Upload Modal -->
  <Transition name="fade-modal">
    <div v-if="showPhotoUploadModal" class="photo-upload-modal-overlay" @click="closePhotoUploadModal">
      <div class="photo-upload-modal" @click.stop>
        <!-- Modal Header -->
        <div class="modal-header">
          <h2 class="modal-title">Unggah Foto Profil</h2>
          <button class="modal-close-btn" @click="closePhotoUploadModal">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>

        <!-- Modal Content -->
        <div class="modal-content">
          <!-- Preview -->
          <div class="photo-preview-container">
            <div class="photo-preview">
              <img v-if="photoPreviewUrl" :src="photoPreviewUrl" alt="Preview" />
              <div v-else class="photo-placeholder">
                <div class="placeholder-initials">
                  {{ userProfile?.first_name?.charAt(0) }}{{ userProfile?.last_name?.charAt(0) }}
                </div>
              </div>
            </div>
          </div>

          <!-- File Info -->
          <div v-if="selectedPhotoFile" class="file-info">
            <p class="file-name">{{ selectedPhotoFile.name }}</p>
            <p class="file-size">{{ (selectedPhotoFile.size / 1024).toFixed(2) }} KB</p>
          </div>

          <!-- Error Message -->
          <div v-if="photoUploadError" class="alert alert-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
              <path d="M12 8V12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              <circle cx="12" cy="16" r="1" fill="currentColor"/>
            </svg>
            {{ photoUploadError }}
          </div>

          <!-- Requirements -->
          <div class="file-requirements">
            <p class="requirements-title">Persyaratan:</p>
            <ul class="requirements-list">
              <li>Format: JPEG, PNG, JPG, GIF</li>
              <li>Ukuran: Max 2MB</li>
              <li>Dimensi: Min 200x200px</li>
            </ul>
          </div>
        </div>

        <!-- Modal Actions -->
        <div class="modal-actions">
          <button 
            type="button"
            class="btn-select-file"
            @click="triggerPhotoFileInput"
            :disabled="photoUploadLoading"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M21 15V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M17 8L12 3L7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M12 3V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            {{ selectedPhotoFile ? 'Pilih Foto Lain' : 'Pilih Foto' }}
          </button>

          <div class="modal-button-group">
            <button 
              type="button"
              class="btn-cancel"
              @click="closePhotoUploadModal"
              :disabled="photoUploadLoading"
            >
              Batal
            </button>
            <button 
              type="button"
              class="btn-upload"
              @click="uploadPhotoFromModal"
              :disabled="!selectedPhotoFile || photoUploadLoading"
            >
              {{ photoUploadLoading ? 'Mengunggah...' : 'Unggah' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>

  <!-- Hidden file input -->
  <input
    ref="photoFileInput"
    type="file"
    accept="image/*"
    style="display: none"
    @change="handlePhotoFileSelect"
  />
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

.nav-btn {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--color-white);
  background: var(--color-primary);
  padding: 0.625rem 1.5rem;
  border-radius: 8px;
}

.nav-btn:hover {
  background: var(--color-primary-dark);
  transform: translateY(-1px);
}

/* User Menu */
.user-menu-wrapper {
  position: relative;
}

.user-menu-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 1rem;
  background: #F3F4F6;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.user-menu-btn:hover {
  background: #E5E7EB;
  border-color: #D1D5DB;
}

.user-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: var(--color-primary);
  color: white;
  border-radius: 50%;
  font-weight: 600;
  font-size: 0.875rem;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-dark);
  max-width: 100px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 0.5rem;
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  min-width: 180px;
  z-index: 1000;
}

.dropdown-item {
  display: block;
  width: 100%;
  padding: 0.75rem 1rem;
  text-align: left;
  color: var(--color-text-dark);
  font-size: 0.875rem;
  border: none;
  background: none;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.dropdown-item:hover {
  background-color: #F3F4F6;
}

.dropdown-item.logout {
  color: #EF4444;
  border-top: 1px solid #E5E7EB;
}

.dropdown-item.logout:hover {
  background-color: #FEE2E2;
}

.profile-container {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 2rem;
  padding: 2rem 1.5rem;
}

/* Main Content Card */
.main-content-card {
  background: var(--color-white);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
}

/* Tab Navigation */
.tab-navigation {
  display: flex;
  gap: 0;
  border-bottom: 2px solid #e5e7eb;
  padding: 0 2rem;
  background: var(--color-white);
}

.tab-btn {
  flex: 1;
  padding: 1.25rem 1rem;
  background: none;
  border: none;
  border-bottom: 3px solid transparent;
  color: var(--color-text-light);
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  margin-bottom: -2px;
}

.tab-btn:hover {
  color: var(--color-primary);
}

.tab-btn.active {
  color: var(--color-primary);
  border-bottom-color: var(--color-primary);
}

/* Tab Content */
.tab-content {
  flex: 1;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Profile Content */
.profile-content {
  display: flex;
  flex-direction: column;
}

/* Tickets Content */
.tickets-content {
  padding: 2rem;
}

.ticket-tabs {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 2rem;
}

.ticket-tab {
  padding: 0.625rem 1.25rem;
  background: var(--color-bg-light);
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  color: var(--color-text-dark);
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.ticket-tab:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.ticket-tab.active {
  background: var(--color-primary);
  color: var(--color-white);
  border-color: var(--color-primary);
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.empty-icon {
  color: var(--color-text-light);
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin-bottom: 0.5rem;
}

.empty-description {
  color: var(--color-text-light);
  font-size: 0.9375rem;
  margin-bottom: 1.5rem;
}

.empty-link {
  padding: 0.75rem 1.75rem;
  background: var(--color-primary);
  color: var(--color-white);
  border-radius: 8px;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.empty-link:hover {
  background: var(--color-primary-dark);
  transform: translateY(-2px);
}

/* Loyalty Content */
.loyalty-content {
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.loyalty-status-card {
  color: var(--color-white);
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  position: relative;
  overflow: hidden;
}

.loyalty-status-card::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle at 30% 70%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
  pointer-events: none;
}

.status-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2.5rem;
  position: relative;
  z-index: 1;
}

.status-info {
  flex: 1;
}

.status-badge {
  display: inline-block;
  background: rgba(255, 255, 255, 0.25);
  color: var(--color-white);
  padding: 0.375rem 0.875rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.75rem;
  backdrop-filter: blur(10px);
}

.status-title {
  font-size: 1.625rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.status-subtitle {
  font-size: 0.875rem;
  opacity: 0.9;
}

.status-points-display {
  text-align: right;
  background: rgba(255, 255, 255, 0.15);
  padding: 1.25rem 1.5rem;
  border-radius: 12px;
  backdrop-filter: blur(10px);
  min-width: 130px;
}

.points-number {
  font-size: 2.25rem;
  font-weight: 800;
  line-height: 1;
  margin-bottom: 0.375rem;
}

.points-label {
  font-size: 0.75rem;
  font-weight: 600;
  opacity: 0.9;
  letter-spacing: 0.1em;
}

.progress-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  position: relative;
  z-index: 1;
}

.progress-header-text {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9375rem;
  font-weight: 600;
}

.progress-percentage {
  background: rgba(255, 255, 255, 0.25);
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.8125rem;
  font-weight: 700;
}

.progress-bar {
  height: 12px;
  background: rgba(255, 255, 255, 0.25);
  border-radius: 6px;
  overflow: hidden;
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

.progress-fill {
  height: 100%;
  border-radius: 6px;
  transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.progress-info {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-top: 0.5rem;
}

.progress-detail {
  background: rgba(255, 255, 255, 0.1);
  padding: 0.875rem;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
  backdrop-filter: blur(10px);
}

.progress-label {
  font-size: 0.75rem;
  font-weight: 600;
  opacity: 0.85;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.progress-value {
  font-size: 1.125rem;
  font-weight: 700;
}

.membership-section-title {
  font-size: 1.375rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin-bottom: 1.75rem;
  margin-top: 1rem;
}

.membership-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.75rem;
  margin-bottom: 3rem;
}

.membership-card {
  position: relative;
  background: var(--color-white);
  border: 2px solid #e5e7eb;
  border-radius: 16px;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
}

.membership-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, currentColor 0%, currentColor 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.membership-card.silver::before {
  background: linear-gradient(90deg, #64748B 0%, #94A3B8 100%);
}

.membership-card.gold::before {
  background: linear-gradient(90deg, #D97706 0%, #F59E0B 100%);
}

.membership-card.platinum::before {
  background: linear-gradient(90deg, #7C3AED 0%, #A855F7 100%);
}

.membership-card:nth-child(1) {
  --card-color: #64748B;
}

.membership-card:nth-child(2) {
  --card-color: #D97706;
}

.membership-card:nth-child(3) {
  --card-color: #7C3AED;
}

.membership-card.is-current {
  border-color: var(--card-color);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12), 0 0 0 1px var(--card-color);
  transform: scale(1.05);
}

.membership-card.is-current::before {
  opacity: 1;
}

.membership-card:hover:not(.is-current) {
  transform: translateY(-6px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
  border-color: var(--card-color);
}

.membership-card:hover::before {
  opacity: 1;
}

.current-badge {
  position: absolute;
  top: -1px;
  right: -1px;
  background: linear-gradient(135deg, var(--card-color) 0%, var(--card-color) 100%);
  color: var(--color-white);
  padding: 0.5rem 1rem;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  border-radius: 0 16px 0 8px;
  z-index: 10;
}

.card-header {
  padding: 1.5rem 1.5rem 1rem;
  display: flex;
  justify-content: center;
  align-items: center;
}

.icon-wrapper {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0;
  transition: all 0.3s ease;
}

.icon-wrapper.silver {
  background: linear-gradient(135deg, #64748B15 0%, #94A3B815 100%);
  color: #64748B;
}

.icon-wrapper.gold {
  background: linear-gradient(135deg, #D9770615 0%, #F59E0B15 100%);
  color: #D97706;
}

.icon-wrapper.platinum {
  background: linear-gradient(135deg, #7C3AED15 0%, #A855F715 100%);
  color: #7C3AED;
}

.membership-card:hover .icon-wrapper {
  transform: scale(1.1) rotate(5deg);
}

.icon-wrapper svg {
  width: 36px;
  height: 36px;
}

.card-body {
  padding: 0.875rem 1.25rem 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.membership-name {
  font-size: 1.125rem;
  font-weight: 700;
  margin-bottom: 0.875rem;
  background: linear-gradient(135deg, var(--card-color) 0%, var(--card-color) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.membership-benefits {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  flex: 1;
}

.membership-benefits li {
  font-size: 0.8rem;
  color: var(--color-text-light);
  line-height: 1.4;
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
}

.benefit-icon {
  flex-shrink: 0;
  font-weight: 700;
  color: var(--card-color);
  font-size: 1rem;
  margin-top: -2px;
}

.points-history {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  margin-top: 2rem;
}

.history-title {
  font-size: 1.375rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin: 0;
}

.history-container {
  background: var(--color-white);
  border: 1.5px solid #e5e7eb;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.history-list {
  display: flex;
  flex-direction: column;
}

.history-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  transition: all 0.2s ease;
  border-bottom: 1px solid #f3f4f6;
}

.history-item:last-child {
  border-bottom: none;
}

.history-item:hover {
  background: #fafafa;
}

.history-item-left {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
}

.history-icon {
  flex-shrink: 0;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
}

.history-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  min-width: 0;
}

.history-activity {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--color-text-dark);
  margin: 0;
  line-height: 1.3;
}

.history-date {
  font-size: 0.8125rem;
  color: var(--color-text-light);
  margin: 0;
}

.history-points-badge {
  flex-shrink: 0;
  padding: 0.625rem 1rem;
  color: var(--color-primary);
  font-size: 0.95rem;
  font-weight: 700;
  border-radius: 10px;
  text-align: center;
  min-width: 80px;
}

.empty-history {
  padding: 3rem 1.5rem;
  text-align: center;
  color: var(--color-text-light);
}

.empty-history p {
  margin: 0;
  font-size: 0.95rem;
}

/* Profile Card */
.profile-card {
  background: var(--color-white);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.profile-header-bg {
  height: 120px;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  position: relative;
}

.profile-header-bg::after {
  content: '';
  position: absolute;
  inset: 0;
  background-image: 
    radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(9, 216, 227, 0.15) 0%, transparent 40%);
}

.profile-header-content {
  padding: 2rem;
  display: flex;
  flex-direction: row;
  align-items: flex-start;
  margin-top: -35px;
  position: relative;
  z-index: 1;
  width: 100%;
  box-sizing: border-box;
  gap: 2rem;
}

.profile-avatar-text {
  display: flex;
  align-items: flex-start;
  gap: 1.5rem;
  width: 100%;
  position: relative;
}

.avatar {
  width: 100px;
  height: 100px;
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  border: 4px solid var(--color-white);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: 700;
  color: var(--color-white);
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.avatar-wrapper {
  position: relative;
  width: fit-content;
}

.avatar-edit-btn {
  position: absolute;
  bottom: -8px;
  right: -8px;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #0066cc;
  border: 3px solid white;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 102, 204, 0.3);
  padding: 0;
}

.avatar-edit-btn:hover {
  background: #0052a3;
  box-shadow: 0 4px 12px rgba(0, 102, 204, 0.5);
  transform: scale(1.1);
}

.avatar-edit-btn:active {
  transform: scale(0.95);
}

.profile-info-text {
  flex: 1;
  min-width: 0;
  margin-top: 0;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
}

.profile-name {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin-bottom: 0;
  margin-top: 0.5rem;
}

.profile-email {
  font-size: 0.9375rem;
  color: var(--color-text-light);
  margin-bottom: 0;
  margin-top: 0;
}

.profile-id {
  font-size: 0.8125rem;
  color: var(--color-primary);
  font-weight: 600;
  margin-top: 0;
}

.edit-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  color: var(--color-white);
  border: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
  flex-shrink: 0;
  white-space: nowrap;
  margin-top: 1.5rem;
}

.edit-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(22, 117, 231, 0.3);
}

/* Info Grid */
.info-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  padding: 2rem;
  border-bottom: 1px solid #e5e7eb;
}

.info-card {
  background: var(--color-bg-light);
  padding: 1.25rem;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.info-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-light);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.75rem;
  display: block;
}

.info-value {
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-text-dark);
}

/* Security Section */
.security-section {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem 2rem;
  background: #eff6ff;
  border-left: 4px solid var(--color-primary);
}

.security-content {
  flex: 1;
}

.security-title {
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-primary);
  margin-bottom: 0.25rem;
}

.security-text {
  font-size: 0.875rem;
  color: var(--color-text-light);
}

.update-password-btn {
  padding: 0.625rem 1rem;
  background: var(--color-white);
  color: var(--color-primary);
  border: 1px solid var(--color-primary);
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.update-password-btn:hover {
  background: var(--color-primary);
  color: var(--color-white);
}

/* Sidebar */
.sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.balance-card {
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  color: var(--color-white);
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.2);
}

.balance-label {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  opacity: 0.9;
  margin-bottom: 0.75rem;
}

.balance-amount {
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
}

.balance-actions {
  display: flex;
  gap: 0.75rem;
}

.balance-btn {
  flex: 1;
  padding: 0.625rem;
  background: rgba(255, 255, 255, 0.2);
  color: var(--color-white);
  border: 1px solid rgba(255, 255, 255, 0.4);
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.75rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.balance-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.6);
}

/* Menu List */
.menu-list {
  background: var(--color-white);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  background: transparent;
  border: none;
  border-bottom: 1px solid #e5e7eb;
  color: var(--color-text-dark);
  font-weight: 500;
  font-size: 0.9375rem;
  cursor: pointer;
  transition: all 0.2s ease;
  width: 100%;
  text-align: left;
}

.menu-item:last-child {
  border-bottom: none;
}

.menu-item:hover {
  background: var(--color-bg-light);
  color: var(--color-primary);
}

.menu-item.active {
  background: rgba(22, 117, 231, 0.05);
  color: var(--color-primary);
  font-weight: 600;
}

.menu-item.logout {
  color: #f97316;
}

.menu-item.logout:hover {
  color: #ea580c;
  background: #fff7ed;
}

.menu-item svg {
  flex-shrink: 0;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal-content {
  background: var(--color-white);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  max-width: 420px;
  width: 100%;
  overflow: hidden;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-text-dark);
}

.modal-close {
  background: none;
  border: none;
  color: var(--color-text-light);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-close:hover {
  color: var(--color-primary);
}

.modal-body {
  padding: 1.5rem;
}

.modal-body p {
  font-size: 0.9375rem;
  color: var(--color-text-dark);
  line-height: 1.6;
}

.modal-footer {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.btn-cancel,
.btn-confirm {
  flex: 1;
  padding: 0.75rem 1.25rem;
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

.btn-cancel:hover {
  border-color: var(--color-text-dark);
  background: #f3f4f6;
}

.btn-confirm {
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  color: var(--color-white);
}

.btn-confirm:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(22, 117, 231, 0.3);
}

/* Modal Animation */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

/* Responsive */
@media (max-width: 1024px) {
  .profile-container {
    grid-template-columns: 1fr;
  }

  .sidebar {
    grid-template-columns: repeat(2, 1fr);
  }

  .info-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .membership-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .header-container {
    padding: 0.75rem 1rem;
  }

  .logo-title {
    font-size: 1.1rem;
  }

  .nav {
    gap: 0.5rem;
  }

  .nav-link {
    padding: 0.4rem 0.75rem;
    font-size: 0.875rem;
  }

  .nav-btn {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
  }
}

@media (max-width: 768px) {
  .profile-page {
    padding-top: 56px;
  }

  .profile-header-content {
    flex-direction: column;
    align-items: stretch;
    margin-top: -50px;
    padding: 1.5rem 1rem;
    gap: 1rem;
  }

  .profile-avatar-text {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 1rem;
  }

  .avatar {
    width: 80px;
    height: 80px;
    font-size: 1.5rem;
  }

  .profile-info-text {
    width: 100%;
  }

  .profile-name {
    font-size: 0.9rem;
  }

  .edit-btn {
    width: 100%;
  }

  .info-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .security-section {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }

  .update-password-btn {
    width: 100%;
  }

  .membership-grid {
    grid-template-columns: 1fr;
  }

  .history-header,
  .history-row {
    grid-template-columns: 0.8fr 1.2fr 0.8fr;
  }

  .tab-navigation {
    padding: 0 1rem;
  }

  .tab-btn {
    padding: 1rem 0.75rem;
    font-size: 0.875rem;
  }

  .tickets-content,
  .loyalty-content {
    padding: 1.5rem;
  }

  .balance-card {
    padding: 1.5rem;
  }

  .balance-label {
    font-size: 0.7rem;
  }

  .balance-amount {
    font-size: 1.5rem;
  }

  .balance-actions {
    gap: 0.5rem;
  }

  .balance-btn {
    font-size: 0.7rem;
    padding: 0.5rem;
  }

  .menu-item {
    padding: 1rem;
    font-size: 0.875rem;
    gap: 0.75rem;
  }

  .menu-item svg {
    width: 18px;
    height: 18px;
  }

  .header-container {
    padding: 0.75rem 1rem;
  }

  .logo-title {
    font-size: 1rem;
  }

  .logo-subtitle {
    font-size: 0.5rem;
  }

  .nav {
    gap: 0.5rem;
  }

  .nav-link {
    padding: 0.4rem 0.5rem;
    font-size: 0.8rem;
  }

  .nav-btn {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
  }
}

@media (max-width: 480px) {
  .profile-page {
    padding: 52px 0.75rem 1rem;
  }

  .profile-card {
    border-radius: 12px;
  }

  .profile-header-bg {
    height: 100px;
  }

  .profile-header-content {
    flex-direction: column;
    align-items: stretch;
    padding: 1.25rem 0.75rem;
    margin-top: -50px;
    gap: 1rem;
  }

  .profile-avatar-text {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 0.75rem;
  }

  .avatar {
    width: 70px;
    height: 70px;
    font-size: 1.25rem;
  }

  .profile-name {
    font-size: 0.8rem;
  }

  .profile-email {
    font-size: 0.8rem;
  }

  .profile-id {
    font-size: 0.75rem;
  }

  .edit-btn {
    width: 100%;
    font-size: 0.8rem;
    padding: 0.625rem 1rem;
  }

  .info-grid {
    padding: 1rem 0.75rem;
    grid-template-columns: 1fr;
  }

  .info-label {
    font-size: 0.7rem;
  }

  .info-value {
    font-size: 0.9rem;
  }

  .security-section {
    padding: 1rem;
    border-left: 3px solid var(--color-primary);
  }

  .security-title {
    font-size: 0.9rem;
  }

  .security-text {
    font-size: 0.8rem;
  }

  .update-password-btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
  }

  .tab-navigation {
    padding: 0 0.75rem;
  }

  .tab-btn {
    padding: 0.875rem 0.5rem;
    font-size: 0.75rem;
  }

  .tickets-content,
  .loyalty-content {
    padding: 1rem 0.75rem;
  }

  .ticket-tabs {
    gap: 0.5rem;
    margin-bottom: 1rem;
  }

  .ticket-tab {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
  }

  .empty-state {
    padding: 2rem 1rem;
  }

  .empty-icon {
    width: 48px;
    height: 48px;
  }

  .empty-title {
    font-size: 1rem;
  }

  .empty-description {
    font-size: 0.8rem;
  }

  .loyalty-status-card {
    padding: 1.25rem;
  }

  .status-title {
    font-size: 1.25rem;
  }

  .status-points {
    font-size: 1rem;
  }

  .membership-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .membership-card {
    padding: 1rem;
  }

  .membership-icon {
    font-size: 1.75rem;
  }

  .membership-name {
    font-size: 0.9rem;
  }

  .membership-benefits li {
    font-size: 0.75rem;
  }

  .history-title {
    font-size: 0.9rem;
  }

  .history-header,
  .history-row {
    grid-template-columns: 0.8fr 1fr 0.6fr;
    font-size: 0.75rem;
    padding: 0.75rem;
  }

  .header-container {
    padding: 0.5rem 0.75rem;
  }

  .logo {
    gap: 0.4rem;
  }

  .logo-icon {
    width: 24px;
    height: 24px;
  }

  .logo-title {
    font-size: 0.9rem;
  }

  .logo-subtitle {
    font-size: 0.5rem;
  }

  .nav {
    gap: 0.3rem;
  }

  .nav-link {
    display: none;
  }

  .nav-btn {
    padding: 0.4rem 0.6rem;
    font-size: 0.7rem;
  }
}

/* Edit Profile Page Styles */
.edit-profile-page {
  width: 100%;
  min-height: 100vh;
  background: var(--color-bg-light);
}

.profile-header {
  background: var(--color-white);
  border-bottom: 1px solid #e5e7eb;
  position: sticky;
  top: 0;
  z-index: 50;
}

.profile-header .header-container {
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

.tabs-container {
  background: var(--color-white);
  border-bottom: 1px solid #e5e7eb;
  position: sticky;
  top: 60px;
  z-index: 40;
  display: block;
  visibility: visible;
  width: 100%;
}

.tabs-header {
  max-width: 900px;
  margin: 0 auto;
  display: flex;
  padding: 0 1.5rem;
  width: 100%;
  visibility: visible;
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
  visibility: visible;
  opacity: 1;
  display: inline-flex;
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

.edit-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
  min-height: 60vh;
  display: block;
  visibility: visible;
}

.edit-form {
  background: var(--color-white);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  display: block;
  width: 100%;
  visibility: visible;
}

.tab-content {
  animation: fadeInEdit 0.2s ease;
  display: block;
  width: 100%;
  visibility: visible;
  opacity: 1;
  min-height: 400px;
}

@keyframes fadeInEdit {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.form-section {
  margin-bottom: 2.5rem;
  display: block;
  width: 100%;
  visibility: visible;
  opacity: 1;
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

.form-group {
  margin-bottom: 1.5rem;
  display: block;
  width: 100%;
  visibility: visible;
  opacity: 1;
}

.form-group-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

@media (max-width: 640px) {
  .form-group-row {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}

.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 0.625rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  visibility: visible;
  opacity: 1;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  visibility: visible;
  opacity: 1;
}

.form-input {
  width: 100%;
  padding: 0.875rem 1rem;
  font-size: 0.9375rem;
  font-weight: 500;
  color: var(--color-text-dark);
  background: var(--color-white);
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  transition: all 0.2s ease;
  font-family: inherit;
  display: block;
  visibility: visible;
  opacity: 1;
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
  animation: spinEdit 0.6s linear infinite;
}

@keyframes spinEdit {
  to {
    transform: rotate(360deg);
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .edit-container {
    padding: 1rem;
  }

  .edit-form {
    padding: 1.5rem;
  }

  .profile-header .header-container {
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

/* Photo Upload Modal Styles */
.photo-upload-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.photo-upload-modal {
  background: white;
  border-radius: 12px;
  width: 95%;
  max-width: 360px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #e0e0e0;
  flex-shrink: 0;
}

.modal-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0;
}

.modal-close-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  color: #666;
  transition: color 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-close-btn:hover {
  color: #1a1a1a;
}

.modal-content {
  padding: 1rem 1.25rem;
  overflow-y: auto;
  flex: 1;
}

.photo-preview-container {
  margin-bottom: 1rem;
  display: flex;
  justify-content: center;
}

.photo-preview {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  overflow: hidden;
  background: #e0e0e0;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 3px solid #0066cc;
}

.photo-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.photo-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
}

.placeholder-initials {
  font-size: 40px;
  font-weight: 600;
  color: white;
}

.file-info {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.file-name {
  font-size: 14px;
  font-weight: 500;
  color: #1a1a1a;
  margin: 0 0 0.25rem 0;
  word-break: break-all;
}

.file-size {
  font-size: 12px;
  color: #666;
  margin: 0;
}

.alert {
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
}

.alert-error {
  background: #ffebee;
  color: #c62828;
  border: 1px solid #ef5350;
}

.file-requirements {
  background: #f0f4ff;
  padding: 12px;
  border-radius: 8px;
  border-left: 4px solid #0066cc;
  margin-bottom: 1rem;
}

.requirements-title {
  font-size: 12px;
  font-weight: 600;
  color: #666;
  text-transform: uppercase;
  margin: 0 0 8px 0;
  letter-spacing: 0.5px;
}

.requirements-list {
  list-style: none;
  padding: 0;
  margin: 0;
  font-size: 13px;
  color: #666;
}

.requirements-list li {
  padding: 4px 0;
  padding-left: 20px;
  position: relative;
}

.requirements-list li:before {
  content: '✓';
  position: absolute;
  left: 0;
  color: #0066cc;
  font-weight: bold;
}

.modal-actions {
  padding: 1rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 10px;
  border-top: 1px solid #e0e0e0;
  flex-shrink: 0;
  background: white;
}

.btn-select-file {
  width: 100%;
  padding: 10px 14px;
  background: #f0f0f0;
  color: #1a1a1a;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.btn-select-file:hover:not(:disabled) {
  background: #e0e0e0;
  border-color: #d0d0d0;
}

.btn-select-file:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.modal-button-group {
  display: flex;
  gap: 12px;
}

.btn-cancel {
  flex: 1;
  padding: 10px 14px;
  background: #f0f0f0;
  color: #1a1a1a;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancel:hover:not(:disabled) {
  background: #e0e0e0;
}

.btn-cancel:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-upload {
  flex: 1;
  padding: 10px 14px;
  background: #0066cc;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-upload:hover:not(:disabled) {
  background: #0052a3;
  box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3);
}

.btn-upload:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.fade-modal-enter-active,
.fade-modal-leave-active {
  transition: opacity 0.3s ease;
}

.fade-modal-enter-from,
.fade-modal-leave-to {
  opacity: 0;
}

.fade-modal-enter-to,
.fade-modal-leave-from {
  opacity: 1;
}

</style>
