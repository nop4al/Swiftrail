<script setup>
import { ref } from 'vue'

const lastUpdated = '16 Desember 2025'

const sections = ref([
  {
    title: '1. Penerimaan Syarat dan Ketentuan',
    content: 'Dengan mengakses dan menggunakan aplikasi atau website SwiftRail ("Platform"), Anda menyetujui untuk terikat oleh Syarat dan Ketentuan ini. Jika Anda tidak setuju dengan bagian mana pun dari syarat-syarat ini, mohon jangan menggunakan Platform kami.'
  },
  {
    title: '2. Layanan Kami',
    content: 'SwiftRail menyediakan platform pemesanan tiket kereta api, pembayaran, dan layanan terkait transportasi kereta. Kami bertindak sebagai perantara antara Anda dan operator kereta api resmi. Semua tiket yang dipesan melalui Platform adalah tiket resmi dari operator kereta api yang bersangkutan.'
  },
  {
    title: '3. Akun Pengguna',
    content: `Untuk menggunakan Platform, Anda harus membuat akun dengan memberikan informasi yang akurat dan lengkap. Anda bertanggung jawab untuk:
• Menjaga kerahasiaan password dan informasi akun Anda
• Semua aktivitas yang terjadi di akun Anda
• Segera memberitahu kami jika ada penggunaan tidak sah

Kami tidak bertanggung jawab atas penggunaan tidak sah akun Anda jika Anda gagal menjaga kerahasiaan informasi akun Anda.`
  },
  {
    title: '4. Pemesanan dan Pembayaran',
    content: `Dengan melakukan pemesanan melalui Platform:
• Anda menyatakan bahwa Anda memiliki kapasitas hukum untuk membuat kontrak
• Informasi penumpang yang Anda berikan adalah akurat dan lengkap
• Pembayaran harus diselesaikan dalam waktu yang ditentukan untuk mengamankan pemesanan
• Tiket akan dikeluarkan setelah pembayaran berhasil diproses

Kami berhak membatalkan pemesanan jika pembayaran tidak diterima dalam waktu yang ditentukan.`
  },
  {
    title: '5. Pembatalan dan Pengembalian Dana',
    content: `Kebijakan pembatalan kami adalah sebagai berikut:
• Pembatalan lebih dari 24 jam sebelum keberangkatan: 100% pengembalian dana
• Pembatalan 3-24 jam sebelum keberangkatan: 75% pengembalian dana (25% biaya pembatalan)
• Pembatalan kurang dari 3 jam sebelum keberangkatan: Tidak dapat dibatalkan

Pengembalian dana akan diproses dalam 3-5 hari kerja. Kami tidak bertanggung jawab atas keterlambatan pengembalian dana yang disebabkan oleh pihak ketiga (bank, e-wallet, dll).`
  },
  {
    title: '6. Batasan Tanggung Jawab',
    content: `SwiftRail tidak bertanggung jawab atas:
• Keterlambatan, pembatalan, atau perubahan jadwal kereta oleh operator kereta
• Kehilangan atau kerusakan barang bawaan Anda selama perjalanan
• Cedera atau kerugian yang terjadi selama atau sesudah perjalanan
• Gangguan atau ketidaktersediaan Platform

Tanggung jawab kami terbatas pada jumlah yang Anda bayarkan untuk layanan.`
  },
  {
    title: '7. Konten Pengguna',
    content: `Anda mengizinkan SwiftRail untuk menggunakan informasi yang Anda berikan untuk:
• Memproses pesanan dan pembayaran Anda
• Mengirim notifikasi dan pembaruan tentang perjalanan Anda
• Meningkatkan layanan kami
• Kepatuhan terhadap persyaratan hukum

Informasi pribadi Anda dilindungi sesuai dengan Kebijakan Privasi kami.`
  },
  {
    title: '8. Penggunaan Platform',
    content: `Anda setuju untuk tidak:
• Menggunakan Platform untuk tujuan ilegal atau melanggar hukum
• Mengirim konten yang berbahaya, menghina, atau melecehkan
• Mencoba mengakses sistem kami secara tidak sah
• Menggunakan bot atau scraper untuk mengumpulkan data dari Platform
• Melakukan aktivitas yang mengganggu operasi Platform`
  },
  {
    title: '9. Perubahan Syarat dan Ketentuan',
    content: 'SwiftRail berhak untuk mengubah Syarat dan Ketentuan ini kapan saja. Perubahan akan berlaku segera setelah dipublikasikan di Platform. Penggunaan Platform Anda yang berkelanjutan berarti Anda menerima perubahan-perubahan tersebut.'
  },
  {
    title: '10. Hukum yang Berlaku',
    content: 'Syarat dan Ketentuan ini diatur oleh hukum Republik Indonesia. Setiap sengketa akan diselesaikan di pengadilan yang berwenang di Indonesia.'
  },
  {
    title: '11. Kontak Kami',
    content: `Jika Anda memiliki pertanyaan tentang Syarat dan Ketentuan ini, silakan hubungi kami:

Email: legal@swiftrail.id
Telepon: 1500-SWIFT (09874)
Alamat: Jl. Transportation Hub No. 1, Jakarta, Indonesia`
  }
])
</script>

<template>
  <div class="terms-page">
    <!-- Header -->
    <header class="terms-header">
      <div class="header-container">
        <h1 class="header-title">Syarat dan Ketentuan</h1>
        <p class="header-subtitle">Harap baca dengan seksama sebelum menggunakan Platform kami</p>
      </div>
    </header>

    <!-- Content -->
    <div class="terms-content">
      <div class="terms-container">
        <div class="last-updated">
          <p>Terakhir diperbarui: <strong>{{ lastUpdated }}</strong></p>
        </div>

        <div class="sections">
          <section v-for="(section, index) in sections" :key="index" class="section">
            <h2 class="section-title">{{ section.title }}</h2>
            <p class="section-content" v-for="(line, i) in section.content.split('\n')" :key="i">
              {{ line }}
            </p>
          </section>
        </div>

        <div class="agreement-section">
          <div class="agreement-box">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/>
            </svg>
            <p>Dengan menggunakan Platform kami, Anda menyatakan bahwa Anda telah membaca dan menyetujui semua Syarat dan Ketentuan di atas.</p>
          </div>
        </div>

        <div class="footer-links">
          <router-link to="/kebijakan-privasi" class="link">← Kebijakan Privasi</router-link>
          <router-link to="/pusat-bantuan" class="link">Kembali ke Pusat Bantuan</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
* {
  --color-primary: #1675E7;
  --color-text-dark: #1F2937;
  --color-text-light: #6B7280;
  --color-bg-light: #F9FAFB;
  --color-white: #FFFFFF;
  --color-border: #E5E7EB;
}

.terms-page {
  width: 100%;
  min-height: 100vh;
  background: var(--color-bg-light);
  padding-top: 64px;
  padding-bottom: 80px;
}

/* Header */
.terms-header {
  background: linear-gradient(135deg, var(--color-primary) 0%, #1565D8 100%);
  color: var(--color-white);
  padding: 3rem 1.5rem;
  text-align: center;
}

.header-container {
  max-width: 900px;
  margin: 0 auto;
}

.header-title {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  line-height: 1.2;
}

.header-subtitle {
  font-size: 1.125rem;
  opacity: 0.9;
}

/* Content */
.terms-content {
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
}

.terms-container {
  background: var(--color-white);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.last-updated {
  padding: 1rem;
  background: rgba(22, 117, 231, 0.05);
  border-left: 4px solid var(--color-primary);
  border-radius: 6px;
  margin-bottom: 2rem;
}

.last-updated p {
  color: var(--color-text-light);
  font-size: 0.9375rem;
  margin: 0;
}

.sections {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  margin-bottom: 2rem;
}

.section {
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--color-border);
}

.section:last-child {
  border-bottom: none;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--color-primary);
  margin-bottom: 1rem;
}

.section-content {
  color: var(--color-text-light);
  line-height: 1.6;
  font-size: 0.9375rem;
  margin: 0.5rem 0;
  white-space: pre-wrap;
}

/* Agreement Section */
.agreement-section {
  margin: 2rem 0;
}

.agreement-box {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.5rem;
  background: rgba(16, 185, 129, 0.08);
  border: 1px solid rgba(16, 185, 129, 0.2);
  border-radius: 12px;
}

.agreement-box svg {
  color: #10B981;
  flex-shrink: 0;
  margin-top: 0.25rem;
}

.agreement-box p {
  color: var(--color-text-dark);
  font-size: 0.9375rem;
  line-height: 1.5;
  margin: 0;
}

/* Footer Links */
.footer-links {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid var(--color-border);
  flex-wrap: wrap;
}

.link {
  padding: 0.75rem 1.5rem;
  background: var(--color-primary);
  color: var(--color-white);
  text-decoration: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9375rem;
  transition: all 0.3s ease;
}

.link:hover {
  background: #1565D8;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
  .terms-header {
    padding: 2rem 1rem;
  }

  .header-title {
    font-size: 1.5rem;
  }

  .header-subtitle {
    font-size: 1rem;
  }

  .terms-content {
    padding: 1.5rem 1rem;
  }

  .terms-container {
    padding: 1.5rem 1rem;
  }

  .sections {
    gap: 1.5rem;
  }

  .section {
    padding-bottom: 1rem;
  }

  .section-title {
    font-size: 1rem;
  }

  .section-content {
    font-size: 0.875rem;
  }

  .footer-links {
    flex-direction: column;
    gap: 0.75rem;
  }

  .link {
    width: 100%;
    text-align: center;
  }
}

@media (max-width: 480px) {
  .terms-header {
    padding: 1.5rem 1rem;
  }

  .header-title {
    font-size: 1.25rem;
  }

  .header-subtitle {
    font-size: 0.875rem;
  }

  .terms-container {
    padding: 1rem;
    border-radius: 12px;
  }

  .last-updated {
    padding: 0.75rem;
    margin-bottom: 1.5rem;
  }

  .last-updated p {
    font-size: 0.8125rem;
  }

  .section-title {
    font-size: 0.9375rem;
  }

  .section-content {
    font-size: 0.8125rem;
  }

  .agreement-box {
    padding: 1rem;
    gap: 0.75rem;
  }

  .agreement-box p {
    font-size: 0.8125rem;
  }
}
</style>
