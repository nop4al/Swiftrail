/**
 * Helper utilities untuk error handling, data formatting, dan validasi
 */

// ============= ERROR HANDLING =============

/**
 * Get user-friendly error message dari error object
 * Digunakan oleh axios interceptor dan API functions
 */
export const getErrorMessage = (error) => {
  // Network error
  if (!error.response) {
    if (error.code === 'ECONNABORTED') {
      return 'Request timeout - koneksi terlalu lama. Silakan coba lagi.'
    }
    return error.message || 'Koneksi ke server gagal. Periksa internet Anda.'
  }

  const status = error.response.status
  const data = error.response.data

  // Custom error messages berdasarkan status code
  const errorMap = {
    400: 'Data yang dikirim tidak valid',
    401: 'Anda belum login. Silakan login terlebih dahulu.',
    403: 'Anda tidak memiliki akses untuk melakukan aksi ini',
    404: 'Data yang dicari tidak ditemukan',
    409: data?.message || 'Konflik data - mungkin kursi sudah dipesan orang lain',
    422: data?.message || 'Data yang dikirim tidak lengkap atau tidak valid',
    429: 'Terlalu banyak request. Tunggu beberapa saat.',
    500: 'Terjadi kesalahan pada server. Tim kami sedang mengatasi.',
    503: 'Server sedang maintenance. Coba lagi nanti.'
  }

  // Return custom message atau generic message
  return errorMap[status] || data?.message || `Terjadi kesalahan (${status})`
}

/**
 * Parse error response
 */
export const parseErrorResponse = (error) => {
  if (!error.response) {
    return {
      message: getErrorMessage(error),
      status: 0,
      code: error.code
    }
  }

  return {
    message: getErrorMessage(error),
    status: error.response.status,
    errors: error.response.data?.errors || {},
    code: error.response.data?.code
  }
}

/**
 * Show user-friendly notification (dapat di-extend dengan toast library)
 */
export const showNotification = (message, type = 'info', duration = 3000) => {
  console.log(`[${type.toUpperCase()}] ${message}`)
  
  // Bisa di-extend dengan toast/snackbar library seperti:
  // - Vue Toastification
  // - Element Plus ElMessage
  // - Vuetify Snackbar
  
  // Contoh simple alert
  if (type === 'error') {
    console.error(message)
  }
}

/**
 * Show error notification
 */
export const showError = (error, context = '') => {
  const message = typeof error === 'string' ? error : getErrorMessage(error)
  showNotification(`${context ? context + ': ' : ''}${message}`, 'error')
}

/**
 * Show success notification
 */
export const showSuccess = (message) => {
  showNotification(message, 'success')
}

/**
 * Show warning notification
 */
export const showWarning = (message) => {
  showNotification(message, 'warning')
}

// ============= DATA FORMATTING =============

/**
 * Format tanggal ke format lokal Indonesia
 * Input: "2024-01-15" → Output: "15 Jan 2024" atau "15 Januari 2024"
 */
export const formatDate = (dateString, format = 'short') => {
  if (!dateString) return '-'
  
  try {
    const date = new Date(dateString + 'T00:00:00')
    
    if (format === 'full') {
      return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }
    
    return date.toLocaleDateString('id-ID', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  } catch (error) {
    console.error('Error formatting date:', error)
    return dateString
  }
}

/**
 * Format waktu ke format lokal
 * Input: "14:30:00" → Output: "14:30" atau "2:30 PM"
 */
export const formatTime = (timeString, format = '24h') => {
  if (!timeString) return '-'
  
  try {
    const [hours, minutes] = timeString.split(':').slice(0, 2)
    
    if (format === '12h') {
      const hour = parseInt(hours)
      const period = hour >= 12 ? 'PM' : 'AM'
      const displayHour = hour % 12 || 12
      return `${displayHour}:${minutes} ${period}`
    }
    
    return `${hours}:${minutes}`
  } catch (error) {
    console.error('Error formatting time:', error)
    return timeString
  }
}

/**
 * Format harga ke format Indonesia (Rp format)
 * Input: 150000 → Output: "Rp150.000" atau "Rp150.000,00"
 */
export const formatPrice = (price, showDecimal = false) => {
  if (price === null || price === undefined || price === '') return 'Rp0'
  
  try {
    const numPrice = parseFloat(price)
    
    if (isNaN(numPrice)) return 'Rp0'
    
    if (showDecimal) {
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 2
      }).format(numPrice)
    }
    
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0
    }).format(numPrice)
  } catch (error) {
    console.error('Error formatting price:', error)
    return `Rp${price}`
  }
}

/**
 * Format durasi waktu perjalanan
 * Input: 3.5 (jam) → Output: "3h 30m"
 */
export const formatDuration = (hours) => {
  if (!hours) return '-'
  
  const h = Math.floor(hours)
  const m = Math.round((hours - h) * 60)
  
  if (h === 0) return `${m}m`
  if (m === 0) return `${h}h`
  return `${h}h ${m}m`
}

/**
 * Swap value untuk dropdown
 */
export const swapValues = (current, alternative) => {
  return {
    swapped: alternative,
    current: current
  }
}

// ============= VALIDATION =============

/**
 * Validate email format
 */
export const isValidEmail = (email) => {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return regex.test(email)
}

/**
 * Validate Indonesian phone number
 */
export const isValidPhoneNumber = (phone) => {
  // Accept 08xx, +628xx, or 628xx format
  const regex = /^(?:\+62|62|0)8[0-9]{8,11}$/
  return regex.test(phone.replace(/\s/g, ''))
}

/**
 * Validate NIK (Nomor Identitas Kependudukan) - 16 digits
 */
export const isValidNIK = (nik) => {
  return /^\d{16}$/.test(nik)
}

/**
 * Validate seat number format
 */
export const isValidSeatNumber = (seat) => {
  // Format: 1A, 2B, 10C, etc.
  return /^[0-9]{1,2}[A-Z]$/.test(seat)
}

/**
 * Validate credit card number (basic Luhn algorithm)
 */
export const isValidCardNumber = (cardNumber) => {
  const cleaned = cardNumber.replace(/\D/g, '')
  
  if (cleaned.length < 13 || cleaned.length > 19) return false
  
  let sum = 0
  let isEven = false
  
  for (let i = cleaned.length - 1; i >= 0; i--) {
    let digit = parseInt(cleaned[i], 10)
    
    if (isEven) {
      digit *= 2
      if (digit > 9) {
        digit -= 9
      }
    }
    
    sum += digit
    isEven = !isEven
  }
  
  return sum % 10 === 0
}

/**
 * Validate required fields
 */
export const validateRequired = (fields) => {
  const errors = {}
  
  for (const [key, value] of Object.entries(fields)) {
    if (!value || (typeof value === 'string' && !value.trim())) {
      errors[key] = `${key} harus diisi`
    }
  }
  
  return {
    isValid: Object.keys(errors).length === 0,
    errors
  }
}

// ============= STRING UTILITIES =============

/**
 * Capitalize first letter
 */
export const capitalize = (str) => {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1)
}

/**
 * Truncate string
 */
export const truncate = (str, length = 50) => {
  if (!str || str.length <= length) return str
  return str.substring(0, length) + '...'
}

/**
 * Convert kebab-case atau snake_case ke readable text
 */
export const toReadableText = (text) => {
  return text
    .replace(/[-_]/g, ' ')
    .replace(/([a-z])([A-Z])/g, '$1 $2')
    .split(' ')
    .map(word => capitalize(word))
    .join(' ')
}

// ============= OBJECT UTILITIES =============

/**
 * Deep clone object
 */
export const deepClone = (obj) => {
  return JSON.parse(JSON.stringify(obj))
}

/**
 * Check if object is empty
 */
export const isEmpty = (obj) => {
  if (!obj) return true
  return Object.keys(obj).length === 0
}

/**
 * Merge objects
 */
export const mergeObjects = (target, ...sources) => {
  return sources.reduce((result, source) => {
    return { ...result, ...source }
  }, target)
}

/**
 * Search trains - legacy helper
 */
export const searchTrains = (params) => {
  console.log('Searching trains:', {
    from: params.fromStation,
    to: params.toStation,
    date: params.travelDate,
    type: params.activeTab
  })
}
