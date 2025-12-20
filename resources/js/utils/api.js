import axios from '@/config/axios'

// ============= ERROR HANDLING =============

const getErrorMessage = (error) => {
  if (error.response?.status === 404) {
    return new Error('Resource tidak ditemukan.')
  }
  if (error.response?.status === 401) {
    return new Error('Unauthorized. Silakan login kembali.')
  }
  if (error.response?.status === 500) {
    return new Error('Server error. Silakan coba lagi nanti.')
  }
  return new Error(error.response?.data?.message || error.message || 'Terjadi kesalahan')
}

// ============= SCHEDULE / JADWAL KERETA =============

/**
 * Cari jadwal kereta berdasarkan stasiun asal, tujuan, dan tanggal
 */
export const getSchedules = async (fromStation, toStation, date) => {
  try {
    const response = await axios.get('/schedules', {
      params: {
        from_station: fromStation,
        to_station: toStation,
        date: date,
        limit: 20
      }
    })
    return response.data
  } catch (error) {
    console.error('Error fetching schedules:', error)
    throw getErrorMessage(error)
  }
}

/**
 * Dapatkan detail jadwal kereta spesifik
 */
export const getScheduleDetail = async (scheduleId) => {
  try {
    const response = await axios.get(`/schedules/${scheduleId}`)
    return response.data
  } catch (error) {
    console.error('Error fetching schedule detail:', error)
    throw getErrorMessage(error)
  }
}

// ============= BOOKING / PEMESANAN =============

/**
 * Dapatkan daftar kursi yang tersedia dan sudah dipesan
 */
export const getAvailableSeats = async (scheduleId, trainClass) => {
  try {
    // Generate mock seat availability data
    // In production, this would fetch from backend
    const seatData = {}
    const rows = 10
    const cols = ['A', 'B', 'C', 'D']
    
    // Randomly mark some seats as reserved
    for (let i = 1; i <= rows; i++) {
      for (let col of cols) {
        const seatKey = `${i}${col}`
        // 30% chance of being reserved
        seatData[seatKey] = Math.random() > 0.7 ? 'reserved' : 'available'
      }
    }
    
    return { data: seatData, status: 200 }
  } catch (error) {
    console.error('Error fetching available seats:', error)
    throw getErrorMessage(error)
  }
}

/**
 * Buat pemesanan baru (reservasi kursi)
 */
export const createBooking = async (bookingData) => {
  try {
    const response = await axios.post('/bookings', {
      schedule_id: bookingData.scheduleId,
      user_id: bookingData.userId || 1,
      passenger_name: bookingData.passengerName,
      nik: bookingData.nik || null,
      passenger_type: bookingData.passengerType || 'Dewasa',
      seat_number: bookingData.seatNumber,
      class: bookingData.class,
      price: bookingData.price
    })
    return response.data
  } catch (error) {
    console.error('Error creating booking:', error)
    throw getErrorMessage(error)
  }
}

/**
 * Dapatkan detail pemesanan
 */
export const getBooking = async (bookingId) => {
  try {
    const response = await axios.get(`/bookings/${bookingId}`)
    return response.data
  } catch (error) {
    console.error('Error fetching booking:', error)
    throw getErrorMessage(error)
  }
}

/**
 * Batalkan pemesanan
 */
export const cancelBooking = async (bookingId) => {
  try {
    const response = await axios.delete(`/bookings/${bookingId}`)
    return response.data
  } catch (error) {
    console.error('Error canceling booking:', error)
    throw getErrorMessage(error)
  }
}

// ============= PAYMENT / PEMBAYARAN =============

/**
 * Dapatkan daftar metode pembayaran yang tersedia
 */
export const getPaymentMethods = async () => {
  try {
    const response = await axios.get('/payments/methods')
    return response.data
  } catch (error) {
    console.error('Error fetching payment methods:', error)
    throw getErrorMessage(error)
  }
}

/**
 * Dapatkan konfirmasi pesanan lengkap sebelum pembayaran
 */
export const getOrderConfirmation = async (bookingId) => {
  try {
    const response = await axios.get(`/payments/confirmation/${bookingId}`)
    return response.data
  } catch (error) {
    console.error('Error fetching order confirmation:', error)
    throw getErrorMessage(error)
  }
}

/**
 * Proses pembayaran
 */
export const processPayment = async (paymentData) => {
  try {
    const response = await axios.post('/payments/process', {
      booking_id: paymentData.bookingId,
      payment_method: paymentData.paymentMethod,
      amount: paymentData.amount,
      currency: 'IDR',
      card_token: paymentData.cardToken || null,
      bank_name: paymentData.bankName || null,
      customer_name: paymentData.customerName,
      customer_email: paymentData.customerEmail
    })
    return response.data
  } catch (error) {
    console.error('Error processing payment:', error)
    throw getErrorMessage(error)
  }
}

// ============= TRAINS / KERETA =============

/**
 * Dapatkan daftar semua kereta
 */
export const getTrains = async () => {
  try {
    const response = await axios.get('/trains')
    return response.data
  } catch (error) {
    console.error('Error fetching trains:', error)
    throw getErrorMessage(error)
  }
}

/**
 * Dapatkan detail kereta spesifik
 */
export const getTrainDetail = async (id) => {
  try {
    const response = await axios.get(`/trains/${id}`)
    return response.data
  } catch (error) {
    console.error('Error fetching train detail:', error)
    throw getErrorMessage(error)
  }
}

/**
 * Cari kereta berdasarkan kriteria
 */
export const searchTrains = async (params) => {
  try {
    // Extract station name from full name (e.g., 'Gambir (GMB)' -> 'Gambir')
    const fromStationName = params.fromStation.split('(')[0].trim()
    const toStationName = params.toStation.split('(')[0].trim()
    
    // Format date to YYYY-MM-DD
    const date = params.travelDate.split('T')[0]
    
    console.log('Searching trains with:', { fromStationName, toStationName, date })
    
    // Call API endpoint
    const response = await axios.post('/bookings/trains/available', {
      from_station: fromStationName,
      to_station: toStationName,
      date: date
    })
    
    console.log('Search API response:', response)
    
    // Transform API response to match expected format
    const trains = response.data?.data || []
    
    return {
      success: trains.length > 0,
      results: trains,
      error: trains.length === 0 ? 'Tidak ada kereta tersedia untuk rute dan tanggal yang dipilih' : null
    }
  } catch (error) {
    console.error('Error searching trains:', error)
    return {
      success: false,
      results: [],
      error: error.response?.data?.message || 'Terjadi kesalahan saat mencari kereta'
    }
  }
}

// ============= STATIONS / STASIUN =============

/**
 * Dapatkan daftar semua stasiun
 */
export const getStations = async () => {
  try {
    const response = await axios.get('/stations')
    return response.data
  } catch (error) {
    console.error('Error fetching stations:', error)
    throw getErrorMessage(error)
  }
}

/**
 * Dapatkan detail stasiun spesifik
 */
export const getStation = async (stationCode) => {
  try {
    const response = await axios.get(`/stations/${stationCode}`)
    return response.data
  } catch (error) {
    console.error('Error fetching station:', error)
    throw getErrorMessage(error)
  }
}

/**
 * Cari stasiun berdasarkan kota
 */
export const getStationsByCity = async (city) => {
  try {
    const response = await axios.get(`/stations/city/${city}`)
    return response.data
  } catch (error) {
    console.error('Error fetching stations by city:', error)
    throw getErrorMessage(error)
  }
}

/**
 * Fetch stasiun dari backend (backward compatibility)
 */
export const fetchStations = async () => {
  try {
    const response = await axios.get('/stations')
    console.log('Stations API response:', response)
    console.log('response.data:', response.data)
    console.log('response.data.data:', response.data?.data)
    
    // Handle different response formats
    let data = []
    if (Array.isArray(response.data)) {
      data = response.data
    } else if (response.data?.data && Array.isArray(response.data.data)) {
      data = response.data.data
    } else if (response.data?.success && Array.isArray(response.data.data)) {
      data = response.data.data
    }
    
    console.log('Final stations data:', data)
    return data
  } catch (error) {
    console.error('Error fetching stations:', error)
    return []
  }
}

// ============= PROMOS / PROMO =============

/**
 * Dapatkan daftar promo
 */
export const getPromos = async () => {
  try {
    const response = await axios.get('/promos')
    return response.data
  } catch (error) {
    console.error('Error fetching promos:', error)
    throw getErrorMessage(error)
  }
}

/**
 * Fetch promos dari backend (backward compatibility)
 */
export const fetchPromos = async () => {
  try {
    const response = await axios.get('/promos')
    return response.data.data || response.data || []
  } catch (error) {
    console.error('Error fetching promos:', error)
    return []
  }
}

/**
 * Dapatkan detail promo spesifik
 */
export const getPromo = async (promoId) => {
  try {
    const response = await axios.get(`/promos/${promoId}`)
    return response.data
  } catch (error) {
    console.error('Error fetching promo:', error)
    throw getErrorMessage(error)
  }
}

// ============= DESTINATIONS / DESTINASI =============

/**
 * Dapatkan daftar destinasi
 */
export const getDestinations = async () => {
  try {
    const response = await axios.get('/destinations')
    return response.data
  } catch (error) {
    console.error('Error fetching destinations:', error)
    throw getErrorMessage(error)
  }
}

/**
 * Fetch destinasi dari backend (backward compatibility)
 */
export const fetchDestinations = async () => {
  try {
    const response = await axios.get('/destinations')
    return response.data.data || response.data || []
  } catch (error) {
    console.error('Error fetching destinations:', error)
    return []
  }
}

// ============= APP INFO =============

/**
 * Get app info
 */
export const fetchAppInfo = async () => {
  try {
    const response = await axios.get('/info')
    return response.data || {}
  } catch (error) {
    console.error('Error fetching app info:', error)
    return {}
  }
}

/**
 * Health check
 */
export const healthCheck = async () => {
  try {
    const response = await axios.get('/health')
    return response.data
  } catch (error) {
    console.error('Error health check:', error)
    throw getErrorMessage(error)
  }
}

/**
 * Format tanggal
 */
export const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', { 
    month: 'long', 
    day: 'numeric', 
    year: 'numeric'
  })
}

/**
 * Format harga ke Rupiah
 */
export const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(price)
}

/**
 * Swap values
 */
export const swapValues = (value1, value2) => {
  return { from: value2, to: value1 }
}
