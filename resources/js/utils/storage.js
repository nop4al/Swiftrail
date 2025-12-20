/**
 * Storage Utilities untuk mengelola localStorage dan sessionStorage
 * Digunakan untuk persist data across components dalam booking flow
 */

const BOOKING_KEY = 'swiftrail_booking'
const AUTH_KEY = 'auth_token'
const USER_KEY = 'swiftrail_user'
const SEARCH_KEY = 'swiftrail_search'

// ============= BOOKING DATA =============

/**
 * Simpan booking session yang lengkap
 */
export const saveBookingSession = (bookingData) => {
  try {
    sessionStorage.setItem(BOOKING_KEY, JSON.stringify({
      ...getBookingSession(),
      ...bookingData,
      updatedAt: new Date().toISOString()
    }))
  } catch (error) {
    console.error('Error saving booking session:', error)
  }
}

/**
 * Ambil booking session
 */
export const getBookingSession = () => {
  try {
    const data = sessionStorage.getItem(BOOKING_KEY)
    return data ? JSON.parse(data) : {}
  } catch (error) {
    console.error('Error reading booking session:', error)
    return {}
  }
}

/**
 * Clear booking session
 */
export const clearBookingSession = () => {
  try {
    sessionStorage.removeItem(BOOKING_KEY)
  } catch (error) {
    console.error('Error clearing booking session:', error)
  }
}

/**
 * Simpan schedule yang dipilih
 */
export const saveSelectedSchedule = (schedule) => {
  saveBookingSession({
    selectedSchedule: schedule,
    scheduleId: schedule?.id
  })
}

/**
 * Ambil schedule yang dipilih
 */
export const getSelectedSchedule = () => {
  return getBookingSession().selectedSchedule || null
}

/**
 * Simpan selected seats
 */
export const saveSelectedSeats = (seats) => {
  saveBookingSession({
    selectedSeats: seats,
    seatCount: seats?.length || 0
  })
}

/**
 * Ambil selected seats
 */
export const getSelectedSeats = () => {
  return getBookingSession().selectedSeats || []
}

/**
 * Simpan passenger data
 */
export const savePassengerData = (passengers) => {
  saveBookingSession({
    passengers: passengers
  })
}

/**
 * Ambil passenger data
 */
export const getPassengerData = () => {
  return getBookingSession().passengers || []
}

/**
 * Simpan order summary (di Checkout)
 */
export const saveOrderSummary = (order) => {
  saveBookingSession({
    orderSummary: order,
    totalPrice: order?.totalPrice,
    bookingId: order?.bookingId
  })
}

/**
 * Ambil order summary
 */
export const getOrderSummary = () => {
  return getBookingSession().orderSummary || null
}

/**
 * Simpan payment data
 */
export const savePaymentData = (payment) => {
  saveBookingSession({
    paymentData: payment,
    paymentMethod: payment?.paymentMethod
  })
}

/**
 * Ambil payment data
 */
export const getPaymentData = () => {
  return getBookingSession().paymentData || null
}

// ============= AUTH DATA =============

/**
 * Simpan auth token
 */
export const saveAuthToken = (token) => {
  try {
    localStorage.setItem(AUTH_KEY, token)
  } catch (error) {
    console.error('Error saving auth token:', error)
  }
}

/**
 * Ambil auth token
 */
export const getAuthToken = () => {
  try {
    return localStorage.getItem(AUTH_KEY)
  } catch (error) {
    console.error('Error reading auth token:', error)
    return null
  }
}

/**
 * Remove auth token
 */
export const removeAuthToken = () => {
  try {
    localStorage.removeItem(AUTH_KEY)
  } catch (error) {
    console.error('Error removing auth token:', error)
  }
}

// ============= USER DATA =============

/**
 * Simpan user data
 */
export const saveUserData = (user) => {
  try {
    localStorage.setItem(USER_KEY, JSON.stringify(user))
  } catch (error) {
    console.error('Error saving user data:', error)
  }
}

/**
 * Ambil user data
 */
export const getUserData = () => {
  try {
    const data = localStorage.getItem(USER_KEY)
    return data ? JSON.parse(data) : null
  } catch (error) {
    console.error('Error reading user data:', error)
    return null
  }
}

/**
 * Remove user data
 */
export const removeUserData = () => {
  try {
    localStorage.removeItem(USER_KEY)
  } catch (error) {
    console.error('Error removing user data:', error)
  }
}

// ============= SEARCH HISTORY =============

/**
 * Simpan search history
 */
export const saveSearchHistory = (search) => {
  try {
    sessionStorage.setItem(SEARCH_KEY, JSON.stringify(search))
  } catch (error) {
    console.error('Error saving search history:', error)
  }
}

/**
 * Ambil search history
 */
export const getSearchHistory = () => {
  try {
    const data = sessionStorage.getItem(SEARCH_KEY)
    return data ? JSON.parse(data) : null
  } catch (error) {
    console.error('Error reading search history:', error)
    return null
  }
}

// ============= CLEAR ALL =============

/**
 * Clear semua data pada session (saat checkout/payment selesai)
 */
export const clearAllSessionData = () => {
  try {
    sessionStorage.removeItem(BOOKING_KEY)
    sessionStorage.removeItem(SEARCH_KEY)
  } catch (error) {
    console.error('Error clearing session data:', error)
  }
}

/**
 * Clear semua data (logout)
 */
export const clearAllData = () => {
  try {
    localStorage.removeItem(AUTH_KEY)
    localStorage.removeItem(USER_KEY)
    sessionStorage.removeItem(BOOKING_KEY)
    sessionStorage.removeItem(SEARCH_KEY)
  } catch (error) {
    console.error('Error clearing all data:', error)
  }
}

/**
 * Check apakah ada booking session yang aktif
 */
export const hasActiveBookingSession = () => {
  const session = getBookingSession()
  return Object.keys(session).length > 0 && !isSessionExpired(session)
}

/**
 * Check apakah session sudah expired (lebih dari 30 menit)
 */
export const isSessionExpired = (session) => {
  if (!session.updatedAt) return false
  const updatedTime = new Date(session.updatedAt).getTime()
  const currentTime = new Date().getTime()
  const thirtyMinutes = 30 * 60 * 1000
  return currentTime - updatedTime > thirtyMinutes
}
