import axios from 'axios'

// Setup base URL - auto-detect IP address untuk akses cross-device
// Jika akses dari IP apapun, akan otomatis ke backend di IP yang sama
const getApiBaseURL = () => {
  // Check if environment variable is set (untuk production atau custom setup)
  if (import.meta.env.VITE_API_URL) {
    return import.meta.env.VITE_API_URL
  }
  
  // Auto-detect untuk development: gunakan IP yang sama seperti frontend
  // dengan port backend Laravel (default 8000)
  const hostname = window.location.hostname
  const protocol = window.location.protocol
  
  // Jika localhost/127.0.0.1, gunakan path relatif
  if (hostname === 'localhost' || hostname === '127.0.0.1') {
    return '/api/v1'
  }
  
  // Jika IP address lain, construct full URL ke backend
  return `${protocol}//${hostname}:8000/api/v1`
}

const apiBaseURL = getApiBaseURL()

// Create axios instance
const axiosInstance = axios.create({
  baseURL: apiBaseURL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  timeout: 30000 // 30 seconds timeout
})

// Request interceptor - tambah token jika ada
axiosInstance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor - handle errors globally
axiosInstance.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    // Handle 401 Unauthorized - only redirect if user was trying to access protected resource
    // Don't redirect for public endpoints like /promos, /stations, /destinations
    if (error.response?.status === 401) {
      const publicEndpoints = ['/promos', '/stations', '/destinations', '/info', '/health']
      const requestUrl = error.config?.url || ''
      
      // Check if this is a public endpoint
      const isPublicEndpoint = publicEndpoints.some(endpoint => requestUrl.includes(endpoint))
      
      // Only redirect to login if:
      // 1. It's NOT a public endpoint (i.e., it's a protected endpoint)
      // 2. AND user has a token (meaning they thought they were authenticated)
      const hasToken = !!localStorage.getItem('auth_token')
      
      if (!isPublicEndpoint && hasToken) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
        window.location.href = '/login'
        return Promise.reject(new Error('Sesi Anda telah berakhir. Silakan login kembali.'))
      }
      
      // For public endpoints or guests trying protected endpoints, just reject the promise
      // Let the component handle the error
      return Promise.reject(new Error('Anda tidak memiliki akses ke resource ini.'))
    }

    // Handle 403 Forbidden
    if (error.response?.status === 403) {
      return Promise.reject(new Error('Anda tidak memiliki izin untuk mengakses resource ini.'))
    }

    // Handle 404 Not Found
    if (error.response?.status === 404) {
      return Promise.reject(new Error('Resource tidak ditemukan.'))
    }

    // Handle 409 Conflict (duplikasi kursi misalnya)
    if (error.response?.status === 409) {
      return Promise.reject(error.response.data || new Error('Data konflik terjadi.'))
    }

    // Handle 422 Validation Error
    if (error.response?.status === 422) {
      return Promise.reject(error.response.data || new Error('Data validasi gagal.'))
    }

    // Handle 500+ Server Error
    if (error.response?.status >= 500) {
      return Promise.reject(new Error('Kesalahan server terjadi. Silakan coba lagi nanti.'))
    }

    // Handle network error (no response from server)
    if (!error.response) {
      if (error.code === 'ECONNABORTED') {
        return Promise.reject(new Error('Permintaan timeout. Silakan coba lagi.'))
      }
      return Promise.reject(new Error('Gagal terhubung ke server. Periksa koneksi internet Anda.'))
    }

    return Promise.reject(error)
  }
)

export default axiosInstance
