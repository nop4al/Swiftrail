<template>
  <!-- Footer hanya muncul jika sudah login atau di halaman Beranda, tapi tidak di Admin page -->
  <footer v-if="(isLoggedIn || isHomePage) && !isAdminPage" class="footer">
    <div class="footer-container">
      <div class="footer-brand">
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
        <p class="footer-desc">Solusi transportasi kereta api terlengkap di Indonesia.</p>
      </div>
      
      <div class="footer-links">
        <div class="footer-col">
          <h4>Perusahaan</h4>
          <a href="#">Tentang Kami</a>
          <a href="#">Karir</a>
          <a href="#">Blog</a>
          <a href="#">Kontak</a>
        </div>
        <div class="footer-col">
          <h4>Bantuan</h4>
          <router-link to="/pusat-bantuan">Pusat Bantuan</router-link>
          <router-link to="/syarat-dan-ketentuan">Syarat & Ketentuan</router-link>
          <router-link to="/kebijakan-privasi">Kebijakan Privasi</router-link>
          <a href="#">Kontak</a>
        </div>
      </div>
    </div>
    
    <div class="footer-bottom">
      <p>&copy; 2025 SwiftRail. All rights reserved.</p>
    </div>
  </footer>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const authToken = ref(localStorage.getItem('auth_token'))

// Check apakah sudah login (dari localStorage)
const isLoggedIn = computed(() => {
  return !!authToken.value
})

// Check apakah di halaman Beranda (home)
const isHomePage = computed(() => {
  return route.path === '/' || route.path === '/home'
})

// Check apakah di Admin page
const isAdminPage = computed(() => {
  return route.path === '/admin'
})

// Listen untuk perubahan storage
onMounted(() => {
  window.addEventListener('storage', (e) => {
    if (e.key === 'authToken') {
      authToken.value = e.newValue
    }
  })
  
  // Listen juga untuk perubahan dari halaman yang sama (custom event)
  window.addEventListener('auth-changed', (e) => {
    authToken.value = localStorage.getItem('auth_token')
  })
})
</script>

<style scoped>
.footer {
  background: #1a1a1a;
  color: white;
  padding: 4rem 0;
  margin-top: 4rem;
  border-top: 1px solid #333;
}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: grid;
  grid-template-columns: 1.5fr 2fr;
  gap: 4rem;
}

.footer-brand .logo {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin-bottom: 1rem;
}

.footer-brand .logo-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: rgba(22, 117, 231, 0.1);
  border-radius: 8px;
}

.footer-brand .logo-text {
  display: flex;
  flex-direction: column;
  line-height: 1.1;
}

.footer-brand .logo-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1675E7;
}

.footer-brand .logo-subtitle {
  font-size: 0.65rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.5);
  letter-spacing: 0.8px;
}

.footer-desc {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.6);
  line-height: 1.6;
}

.footer-links {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 2rem;
}

.footer-col h4 {
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 1rem;
  color: white;
}

.footer-col a {
  display: block;
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.6);
  text-decoration: none;
  margin-bottom: 0.6rem;
  transition: color 0.3s ease;
}

.footer-col a:hover {
  color: #1675E7;
}

.footer-bottom {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  text-align: center;
}

.footer-bottom p {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.4);
}

@media (max-width: 768px) {
  .footer-container {
    grid-template-columns: 1fr;
    gap: 2rem;
    padding: 0 1rem;
  }

  .footer-links {
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
  }

  .footer {
    padding: 3rem 0;
    margin-top: 2rem;
  }

  .footer-col h4 {
    font-size: 0.75rem;
    margin-bottom: 0.8rem;
  }

  .footer-col a {
    font-size: 0.85rem;
    margin-bottom: 0.5rem;
  }
}
</style>
