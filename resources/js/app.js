import { createApp } from 'vue'
import '../css/app.css'
import App from './App.vue'
import router from './router/index.js'
import axios from './config/axios'

const app = createApp(App)

// Setup axios sebagai global property untuk akses di semua component
app.config.globalProperties.$axios = axios

// Global error handler
app.config.errorHandler = (err, instance, info) => {
  console.error('Global error:', err)
  console.error('Error info:', info)
}

app.use(router)
app.mount('#app')
