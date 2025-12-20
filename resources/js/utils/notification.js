/**
 * Show success notification
 */
export const showSuccess = (message) => {
  if (typeof window !== 'undefined' && window.$notify) {
    window.$notify?.success?.({
      title: 'Sukses',
      message: message
    })
  } else {
    console.log('✓ Success:', message)
  }
}

/**
 * Show error notification
 */
export const showError = (error, title = 'Error') => {
  const errorMessage = error?.message || error?.toString() || 'Terjadi kesalahan'
  
  if (typeof window !== 'undefined' && window.$notify) {
    window.$notify?.error?.({
      title: title,
      message: errorMessage
    })
  } else {
    console.error('✗ Error:', title, '-', errorMessage)
  }
}

/**
 * Show warning notification
 */
export const showWarning = (message, title = 'Peringatan') => {
  if (typeof window !== 'undefined' && window.$notify) {
    window.$notify?.warning?.({
      title: title,
      message: message
    })
  } else {
    console.warn('⚠ Warning:', title, '-', message)
  }
}

/**
 * Show info notification
 */
export const showInfo = (message, title = 'Informasi') => {
  if (typeof window !== 'undefined' && window.$notify) {
    window.$notify?.info?.({
      title: title,
      message: message
    })
  } else {
    console.info('ℹ Info:', title, '-', message)
  }
}
