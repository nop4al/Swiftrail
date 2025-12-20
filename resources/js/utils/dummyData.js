export const dummyTrains = [
  // ===== 16 DESEMBER 2025 =====
  
  // SBY → GMB
  { id: 1, name: 'Argo Bromo Anggrek', number: 'KA1', status: 'available', departure: '09:10', arrival: '17:15', duration: '8h 5m', fromStation: 'Surabaya Pasar Turi (SBY)', toStation: 'Gambir (GMB)', date: '2025-12-16', stops: [{ station: 'Surabaya Pasar Turi', arrive: '08:00', depart: '09:10' }, { station: 'Bojonegoro', arrive: '10:16', depart: '10:21' }, { station: 'Semarang Tawang', arrive: '12:02', depart: '12:17' }, { station: 'Pekalongan', arrive: '13:17', depart: '13:22' }, { station: 'Cirebon', arrive: '14:40', depart: '14:45' }, { station: 'Gambir', arrive: '17:15', depart: '-' }], classes: [{ type: 'executive', name: 'Executive', seats: 50, available: 12, price: 450000 }, { type: 'economy', name: 'Economy', seats: 200, available: 65, price: 250000 }] },
  
  // GMB → SBY
  { id: 2, name: 'Argo Bromo Anggrek', number: 'KA2', status: 'available', departure: '08:20', arrival: '16:25', duration: '8h 5m', fromStation: 'Gambir (GMB)', toStation: 'Surabaya Pasar Turi (SBY)', date: '2025-12-16', stops: [{ station: 'Gambir', arrive: '08:00', depart: '08:20' }, { station: 'Cirebon', arrive: '10:01', depart: '10:46' }, { station: 'Pekalongan', arrive: '12:02', depart: '12:07' }, { station: 'Semarang Tawang', arrive: '13:07', depart: '13:12' }, { station: 'Bojonegoro', arrive: '15:06', depart: '15:11' }, { station: 'Surabaya Pasar Turi', arrive: '16:25', depart: '-' }], classes: [{ type: 'executive', name: 'Executive', seats: 50, available: 8, price: 450000 }, { type: 'economy', name: 'Economy', seats: 200, available: 78, price: 250000 }] },

  // GMB → SMG
  { id: 3, name: 'Argo Parahyangan', number: 'AP1', status: 'available', departure: '08:00', arrival: '12:17', duration: '4h 17m', fromStation: 'Gambir (GMB)', toStation: 'Semarang Tawang (SMG)', date: '2025-12-16', stops: [{ station: 'Gambir', arrive: '08:00', depart: '08:00' }, { station: 'Cirebon', arrive: '09:40', depart: '09:45' }, { station: 'Pekalongan', arrive: '10:55', depart: '11:00' }, { station: 'Semarang Tawang', arrive: '12:17', depart: '-' }], classes: [{ type: 'executive', name: 'Executive', seats: 40, available: 15, price: 320000 }, { type: 'economy', name: 'Economy', seats: 180, available: 72, price: 180000 }] },

  // SMG → GMB
  { id: 4, name: 'Argo Parahyangan', number: 'AP2', status: 'available', departure: '13:00', arrival: '17:17', duration: '4h 17m', fromStation: 'Semarang Tawang (SMG)', toStation: 'Gambir (GMB)', date: '2025-12-16', stops: [{ station: 'Semarang Tawang', arrive: '13:00', depart: '13:00' }, { station: 'Pekalongan', arrive: '14:12', depart: '14:17' }, { station: 'Cirebon', arrive: '15:27', depart: '15:32' }, { station: 'Gambir', arrive: '17:17', depart: '-' }], classes: [{ type: 'executive', name: 'Executive', seats: 40, available: 10, price: 320000 }, { type: 'economy', name: 'Economy', seats: 180, available: 85, price: 180000 }] },

  // SBY → SMG
  { id: 5, name: 'Ekspres Utara', number: 'EU1', status: 'available', departure: '10:21', arrival: '12:02', duration: '1h 41m', fromStation: 'Surabaya Pasar Turi (SBY)', toStation: 'Semarang Tawang (SMG)', date: '2025-12-16', stops: [{ station: 'Surabaya Pasar Turi', arrive: '10:00', depart: '10:21' }, { station: 'Bojonegoro', arrive: '10:16', depart: '10:21' }, { station: 'Semarang Tawang', arrive: '12:02', depart: '-' }], classes: [{ type: 'economy', name: 'Economy', seats: 200, available: 95, price: 220000 }] },

  // SMG → SBY
  { id: 6, name: 'Ekspres Utara', number: 'EU2', status: 'available', departure: '13:07', arrival: '16:30', duration: '3h 23m', fromStation: 'Semarang Tawang (SMG)', toStation: 'Surabaya Pasar Turi (SBY)', date: '2025-12-16', stops: [{ station: 'Semarang Tawang', arrive: '13:07', depart: '13:07' }, { station: 'Bojonegoro', arrive: '15:06', depart: '15:11' }, { station: 'Surabaya Pasar Turi', arrive: '16:30', depart: '-' }], classes: [{ type: 'economy', name: 'Economy', seats: 200, available: 110, price: 220000 }] },

  // SBY → GMB (malam)
  { id: 7, name: 'Argo Bromo Anggrek', number: 'KA3', status: 'available', departure: '21:15', arrival: '05:20', duration: '8h 5m', fromStation: 'Surabaya Pasar Turi (SBY)', toStation: 'Gambir (GMB)', date: '2025-12-16', stops: [{ station: 'Surabaya Pasar Turi', arrive: '20:00', depart: '21:15' }, { station: 'Bojonegoro', arrive: '22:21', depart: '22:26' }, { station: 'Semarang Tawang', arrive: '00:17', depart: '00:22' }, { station: 'Pekalongan', arrive: '01:22', depart: '01:27' }, { station: 'Cirebon', arrive: '02:45', depart: '02:50' }, { station: 'Gambir', arrive: '05:20', depart: '-' }], classes: [{ type: 'compartment', name: 'Compartment', seats: 40, available: 15, price: 380000 }, { type: 'economy', name: 'Economy', seats: 200, available: 92, price: 220000 }] },

  // GMB → SBY (malam)
  { id: 8, name: 'Argo Bromo Anggrek', number: 'KA4', status: 'available', departure: '20:30', arrival: '04:35', duration: '8h 5m', fromStation: 'Gambir (GMB)', toStation: 'Surabaya Pasar Turi (SBY)', date: '2025-12-16', stops: [{ station: 'Gambir', arrive: '19:00', depart: '20:30' }, { station: 'Cirebon', arrive: '22:51', depart: '22:56' }, { station: 'Pekalongan', arrive: '00:12', depart: '00:17' }, { station: 'Semarang Tawang', arrive: '01:17', depart: '01:22' }, { station: 'Bojonegoro', arrive: '03:16', depart: '03:21' }, { station: 'Surabaya Pasar Turi', arrive: '04:35', depart: '-' }], classes: [{ type: 'compartment', name: 'Compartment', seats: 40, available: 5, price: 380000 }, { type: 'economy', name: 'Economy', seats: 200, available: 110, price: 220000 }] },

  // ===== 17 DESEMBER 2025 =====

  // SBY → GMB
  { id: 9, name: 'Argo Bromo Anggrek', number: 'KA1', status: 'available', departure: '09:10', arrival: '17:15', duration: '8h 5m', fromStation: 'Surabaya Pasar Turi (SBY)', toStation: 'Gambir (GMB)', date: '2025-12-17', stops: [{ station: 'Surabaya Pasar Turi', arrive: '08:00', depart: '09:10' }, { station: 'Bojonegoro', arrive: '10:16', depart: '10:21' }, { station: 'Semarang Tawang', arrive: '12:02', depart: '12:17' }, { station: 'Pekalongan', arrive: '13:17', depart: '13:22' }, { station: 'Cirebon', arrive: '14:40', depart: '14:45' }, { station: 'Gambir', arrive: '17:15', depart: '-' }], classes: [{ type: 'executive', name: 'Executive', seats: 50, available: 12, price: 450000 }, { type: 'economy', name: 'Economy', seats: 200, available: 65, price: 250000 }] },

  // GMB → SBY
  { id: 10, name: 'Argo Bromo Anggrek', number: 'KA2', status: 'available', departure: '08:20', arrival: '16:25', duration: '8h 5m', fromStation: 'Gambir (GMB)', toStation: 'Surabaya Pasar Turi (SBY)', date: '2025-12-17', stops: [{ station: 'Gambir', arrive: '08:00', depart: '08:20' }, { station: 'Cirebon', arrive: '10:01', depart: '10:46' }, { station: 'Pekalongan', arrive: '12:02', depart: '12:07' }, { station: 'Semarang Tawang', arrive: '13:07', depart: '13:12' }, { station: 'Bojonegoro', arrive: '15:06', depart: '15:11' }, { station: 'Surabaya Pasar Turi', arrive: '16:25', depart: '-' }], classes: [{ type: 'executive', name: 'Executive', seats: 50, available: 8, price: 450000 }, { type: 'economy', name: 'Economy', seats: 200, available: 78, price: 250000 }] },

  // GMB → SMG
  { id: 11, name: 'Argo Parahyangan', number: 'AP1', status: 'available', departure: '08:00', arrival: '12:17', duration: '4h 17m', fromStation: 'Gambir (GMB)', toStation: 'Semarang Tawang (SMG)', date: '2025-12-17', stops: [{ station: 'Gambir', arrive: '08:00', depart: '08:00' }, { station: 'Cirebon', arrive: '09:40', depart: '09:45' }, { station: 'Pekalongan', arrive: '10:55', depart: '11:00' }, { station: 'Semarang Tawang', arrive: '12:17', depart: '-' }], classes: [{ type: 'executive', name: 'Executive', seats: 40, available: 15, price: 320000 }, { type: 'economy', name: 'Economy', seats: 180, available: 72, price: 180000 }] },

  // SMG → GMB
  { id: 12, name: 'Argo Parahyangan', number: 'AP2', status: 'available', departure: '13:00', arrival: '17:17', duration: '4h 17m', fromStation: 'Semarang Tawang (SMG)', toStation: 'Gambir (GMB)', date: '2025-12-17', stops: [{ station: 'Semarang Tawang', arrive: '13:00', depart: '13:00' }, { station: 'Pekalongan', arrive: '14:12', depart: '14:17' }, { station: 'Cirebon', arrive: '15:27', depart: '15:32' }, { station: 'Gambir', arrive: '17:17', depart: '-' }], classes: [{ type: 'executive', name: 'Executive', seats: 40, available: 10, price: 320000 }, { type: 'economy', name: 'Economy', seats: 180, available: 85, price: 180000 }] },

  // SBY → SMG
  { id: 13, name: 'Ekspres Utara', number: 'EU1', status: 'available', departure: '10:21', arrival: '12:02', duration: '1h 41m', fromStation: 'Surabaya Pasar Turi (SBY)', toStation: 'Semarang Tawang (SMG)', date: '2025-12-17', stops: [{ station: 'Surabaya Pasar Turi', arrive: '10:00', depart: '10:21' }, { station: 'Bojonegoro', arrive: '10:16', depart: '10:21' }, { station: 'Semarang Tawang', arrive: '12:02', depart: '-' }], classes: [{ type: 'economy', name: 'Economy', seats: 200, available: 95, price: 220000 }] },

  // SMG → SBY
  { id: 14, name: 'Ekspres Utara', number: 'EU2', status: 'available', departure: '13:07', arrival: '16:30', duration: '3h 23m', fromStation: 'Semarang Tawang (SMG)', toStation: 'Surabaya Pasar Turi (SBY)', date: '2025-12-17', stops: [{ station: 'Semarang Tawang', arrive: '13:07', depart: '13:07' }, { station: 'Bojonegoro', arrive: '15:06', depart: '15:11' }, { station: 'Surabaya Pasar Turi', arrive: '16:30', depart: '-' }], classes: [{ type: 'economy', name: 'Economy', seats: 200, available: 110, price: 220000 }] },

  // SBY → GMB (malam)
  { id: 15, name: 'Argo Bromo Anggrek', number: 'KA3', status: 'available', departure: '21:15', arrival: '05:20', duration: '8h 5m', fromStation: 'Surabaya Pasar Turi (SBY)', toStation: 'Gambir (GMB)', date: '2025-12-17', stops: [{ station: 'Surabaya Pasar Turi', arrive: '20:00', depart: '21:15' }, { station: 'Bojonegoro', arrive: '22:21', depart: '22:26' }, { station: 'Semarang Tawang', arrive: '00:17', depart: '00:22' }, { station: 'Pekalongan', arrive: '01:22', depart: '01:27' }, { station: 'Cirebon', arrive: '02:45', depart: '02:50' }, { station: 'Gambir', arrive: '05:20', depart: '-' }], classes: [{ type: 'compartment', name: 'Compartment', seats: 40, available: 15, price: 380000 }, { type: 'economy', name: 'Economy', seats: 200, available: 92, price: 220000 }] },

  // GMR → SPT (malam)
  { id: 16, name: 'Argo Bromo Anggrek', number: 'KA4', status: 'available', departure: '20:30', arrival: '04:35', duration: '8h 5m', fromStation: 'Gambir (GMR)', toStation: 'Surabaya Pasar Turi (SPT)', date: '2025-12-17', stops: [{ station: 'Gambir', arrive: '19:00', depart: '20:30' }, { station: 'Cirebon', arrive: '22:51', depart: '22:56' }, { station: 'Pekalongan', arrive: '00:12', depart: '00:17' }, { station: 'Semarang Tawang', arrive: '01:17', depart: '01:22' }, { station: 'Bojonegoro', arrive: '03:16', depart: '03:21' }, { station: 'Surabaya Pasar Turi', arrive: '04:35', depart: '-' }], classes: [{ type: 'compartment', name: 'Compartment', seats: 40, available: 5, price: 380000 }, { type: 'economy', name: 'Economy', seats: 200, available: 110, price: 220000 }] }
]

// Filter trains by route and date
export const getTrainsByRoute = (fromCode, toCode, date) => {
  return dummyTrains.filter(train => {
    const trainFromCode = train.fromStation.match(/\(([^)]+)\)/)?.[1]
    const trainToCode = train.toStation.match(/\(([^)]+)\)/)?.[1]
    return trainFromCode === fromCode && trainToCode === toCode && train.date === date
  })
}

// Get available seats by class
export const getAvailableSeatsByClass = (scheduleId, classType) => {
  const train = dummyTrains.find(t => t.id === scheduleId)
  if (!train) return 0
  const classInfo = train.classes.find(c => c.type === classType)
  return classInfo ? classInfo.available : 0
}

// Format price to Indonesian Rupiah
export const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price)
}

// Format date to Indonesian locale
export const formatDate = (dateString) => {
  const date = new Date(dateString + 'T00:00:00')
  return date.toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Dummy seats data
export const dummySeats = {
  'executive': [
    { row: 1, seats: ['1A', '1B', '1C', '1D'] },
    { row: 2, seats: ['2A', '2B', '2C', '2D'] },
    { row: 3, seats: ['3A', '3B', '3C', '3D'] },
    { row: 4, seats: ['4A', '4B', '4C', '4D'] },
    { row: 5, seats: ['5A', '5B', '5C', '5D'] }
  ],
  'economy': [
    { row: 1, seats: ['1A', '1B', '1C', '1D', '1E', '1F'] },
    { row: 2, seats: ['2A', '2B', '2C', '2D', '2E', '2F'] },
    { row: 3, seats: ['3A', '3B', '3C', '3D', '3E', '3F'] },
    { row: 4, seats: ['4A', '4B', '4C', '4D', '4E', '4F'] },
    { row: 5, seats: ['5A', '5B', '5C', '5D', '5E', '5F'] }
  ]
}

// Dummy payment methods
export const dummyPaymentMethods = [
  {
    id: 1,
    name: 'SwiftPay',
    code: 'swift-pay',
    type: 'digital-wallet',
    balance: 5000000,
    minBalance: 100000,
    icon: '💳'
  },
  {
    id: 2,
    name: 'QRIS',
    code: 'qris',
    type: 'qr-code',
    balance: null,
    minBalance: 0,
    icon: '📱'
  },
  {
    id: 3,
    name: 'Internet Banking',
    code: 'internet-banking',
    type: 'bank-transfer',
    balance: null,
    minBalance: 0,
    icon: '🏦'
  }
]

// Dummy order confirmation
export const dummyOrderConfirmation = {
  bookingId: 'BK20251216001',
  status: 'pending',
  trainName: 'Argo Bromo Anggrek',
  trainNumber: 'KA2',
  fromStation: 'Gambir (GMR)',
  toStation: 'Surabaya Pasar Turi (SPT)',
  departureDate: '2025-12-16',
  departureTime: '08:20',
  arrivalTime: '16:25',
  duration: '8h 5m',
  passengers: [
    {
      id: 1,
      name: 'John Doe',
      type: 'adult',
      seat: '1A'
    }
  ],
  totalPrice: 450000,
  bookingDate: new Date().toISOString().split('T')[0],
  paymentMethod: 'swift-pay'
}

// Dummy promos
export const dummyPromos = [
  {
    id: 1,
    code: 'PROMO2024',
    description: 'Diskon 20% untuk pembelian tiket',
    discount: 20,
    minPurchase: 500000,
    validUntil: '2025-12-31'
  },
  {
    id: 2,
    code: 'SWIFTDISKON',
    description: 'Cashback 10% dengan SwiftPay',
    discount: 10,
    minPurchase: 250000,
    validUntil: '2025-12-31'
  }
]
