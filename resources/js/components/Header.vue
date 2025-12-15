<template>
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
        <router-link to="/" class="nav-link">Beranda</router-link>
        
        <!-- Jika sudah login: tampilkan Tiket Saya & Profile -->
        <template v-if="isLoggedIn">
          <router-link to="/ticket" class="nav-link">Tiket Saya</router-link>
          <router-link to="/profile" class="nav-link">Profile</router-link>
        </template>
        
        <!-- Jika belum login: tampilkan Login & Register -->
        <template v-else>
          <router-link to="/login" class="nav-link">Masuk</router-link>
          <router-link to="/register" class="nav-link nav-link-register">Daftar</router-link>
        </template>
      </nav>
    </div>
  </header>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { computed } from 'vue'

const router = useRouter()

// Check apakah sudah login dari localStorage
const isLoggedIn = computed(() => {
  return !!localStorage.getItem('authToken')
})
</script>

<style scoped>
.header {
  background: white;
  color: #1a1a1a;
  padding: 0.75rem 0;
  position: sticky;
  top: 0;
  z-index: 1000;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border-bottom: 1px solid #e5e7eb;
}

.header-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
  text-decoration: none;
  color: #1a1a1a;
  flex-shrink: 0;
}

.logo-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  background: #f0f4ff;
  border-radius: 6px;
}

.logo-text {
  display: flex;
  flex-direction: column;
  line-height: 1.1;
}

.logo-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1675E7;
  letter-spacing: -0.5px;
}

.logo-subtitle {
  font-size: 0.65rem;
  font-weight: 600;
  color: #6B7280;
  letter-spacing: 0.8px;
}

.nav {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.nav-link {
  color: #4B5563;
  text-decoration: none;
  font-weight: 500;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  position: relative;
  white-space: nowrap;
}

.nav-link:hover {
  color: #1675E7;
  background: #f0f4ff;
}

.nav-link.router-link-active {
  color: #1675E7;
  background: #f0f4ff;
  font-weight: 600;
}

.nav-link-register {
  background: #1675E7;
  color: white !important;
  font-weight: 600;
  padding: 0.4rem 1.2rem !important;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.nav-link-register:hover {
  background: #1563D1 !important;
  box-shadow: 0 2px 8px rgba(22, 117, 231, 0.3);
}

@media (max-width: 768px) {
  .header-container {
    padding: 0 1rem;
    flex-direction: column;
    gap: 0.75rem;
  }

  .nav {
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
  }

  .nav-link {
    font-size: 0.8rem;
    padding: 0.35rem 0.7rem;
  }

  .logo-title {
    font-size: 0.95rem;
  }

  .logo-subtitle {
    font-size: 0.6rem;
  }
}
</style>
