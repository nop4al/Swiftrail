<script setup>
import { ref } from 'vue'

const lastUpdated = '16 Desember 2025'

const sections = ref([
  {
    title: '1. Pengenalan',
    content: 'SwiftRail ("kami", "kami", atau "perusahaan kami") berkomitmen untuk melindungi privasi Anda. Kebijakan Privasi ini menjelaskan praktik privasi kami dan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi pribadi Anda.'
  },
  {
    title: '2. Informasi yang Kami Kumpulkan',
    content: `Kami mengumpulkan informasi dengan cara berikut:

A. Informasi yang Anda Berikan:
• Data pribadi (nama, email, nomor telepon, alamat, KTP)
• Data pembayaran (nomor kartu kredit, riwayat transaksi)
• Informasi penumpang (nama penumpang, gender, tempat duduk)
• Komunikasi dengan customer service

B. Informasi yang Dikumpulkan Secara Otomatis:
• Alamat IP dan informasi perangkat
• Cookies dan data penjelajahan
• Preferensi pengguna dan riwayat pencarian
• Lokasi geografis (jika Anda memberikan izin)`
  },
  {
    title: '3. Penggunaan Informasi',
    content: `Kami menggunakan informasi pribadi Anda untuk:
• Memproses pemesanan dan pembayaran tiket
• Mengirim tiket dan konfirmasi pemesanan
• Memberikan customer service dan dukungan teknis
• Mengirim notifikasi tentang jadwal, promosi, dan penawaran khusus
• Meningkatkan layanan dan pengalaman pengguna
• Mencegah fraud dan aktivitas ilegal
• Kepatuhan terhadap persyaratan hukum dan peraturan`
  },
  {
    title: '4. Berbagi Informasi',
    content: `Kami hanya membagikan informasi pribadi Anda dengan:
• Operator kereta api untuk keperluan pemesanan tiket
• Penyedia layanan pembayaran untuk memproses transaksi
• Agensi pemerintah jika diwajibkan oleh hukum
• Mitra bisnis kami yang membantu menyediakan layanan

Kami tidak akan pernah menjual atau menyewakan informasi pribadi Anda kepada pihak ketiga untuk tujuan pemasaran.`
  },
  {
    title: '5. Keamanan Data',
    content: `Kami menggunakan langkah-langkah keamanan tingkat industri untuk melindungi data Anda:
• Enkripsi SSL/TLS untuk transmisi data
• Penyimpanan data yang aman dengan kontrol akses
• Audit keamanan berkala
• Pembatasan akses ke informasi pribadi hanya untuk karyawan yang membutuhkan

Namun, tidak ada metode transmisi internet yang 100% aman. Kami tidak dapat menjamin keamanan data absolut.`
  },
  {
    title: '6. Retensi Data',
    content: `Kami menyimpan informasi pribadi Anda selama:
• Diperlukan untuk menyediakan layanan kepada Anda
• Untuk memenuhi kewajiban hukum dan peraturan
• Untuk menyelesaikan transaksi keuangan (sesuai regulasi perbankan)

Setelah periode ini, data akan dihapus atau di-anonimkan dengan aman.`
  },
  {
    title: '7. Hak Anda',
    content: `Anda memiliki hak untuk:
• Mengakses data pribadi yang kami miliki tentang Anda
• Memperbaiki data yang tidak akurat atau lengkap
• Menghapus data pribadi Anda (dengan batasan tertentu)
• Membatasi penggunaan data pribadi Anda
• Menolak pemasaran langsung

Untuk menggunakan hak-hak ini, silakan hubungi kami di privacy@swiftrail.id`
  },
  {
    title: '8. Cookies dan Teknologi Pelacakan',
    content: `Platform kami menggunakan cookies untuk:
• Mengingat preferensi login dan bahasa Anda
• Menganalisis cara pengguna menggunakan Platform
• Menyediakan iklan yang dipersonalisasi

Anda dapat mengatur browser Anda untuk menolak cookies, tetapi ini mungkin mempengaruhi fungsionalitas Platform.`
  },
  {
    title: '9. Pemasaran dan Komunikasi',
    content: `Kami dapat mengirim Anda email dan notifikasi push tentang:
• Promosi dan penawaran khusus
• Pembaruan fitur dan layanan baru
• Survei dan feedback

Anda dapat membatalkan langganan dari komunikasi pemasaran kapan saja melalui pengaturan akun atau dengan mengklik "Unsubscribe" di email.`
  },
  {
    title: '10. Privasi Anak-anak',
    content: 'Platform kami tidak ditujukan untuk anak-anak di bawah 13 tahun. Kami tidak secara sengaja mengumpulkan informasi pribadi dari anak-anak. Jika kami menyadari kami telah mengumpulkan data dari anak di bawah 13 tahun, kami akan segera menghapusnya.'
  },
  {
    title: '11. Tautan Eksternal',
    content: 'Platform kami mungkin berisi tautan ke website pihak ketiga. Kami tidak bertanggung jawab atas praktik privasi website eksternal. Kami sarankan Anda membaca kebijakan privasi mereka.'
  },
  {
    title: '12. Perubahan Kebijakan Privasi',
    content: 'Kami dapat memperbarui Kebijakan Privasi ini kapan saja. Perubahan akan berlaku segera setelah dipublikasikan. Penggunaan Platform Anda yang berkelanjutan berarti Anda menerima perubahan-perubahan tersebut.'
  },
  {
    title: '13. Kontak Kami',
    content: `Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini atau praktik privasi kami, silakan hubungi kami:

Email: privacy@swiftrail.id
Telepon: 1500-SWIFT (09874)
Alamat: Jl. Transportation Hub No. 1, Jakarta, Indonesia`
  }
])
</script>

<template>
  <div class="privacy-page">
    <!-- Header -->
    <header class="privacy-header">
      <div class="header-container">
        <h1 class="header-title">Kebijakan Privasi</h1>
        <p class="header-subtitle">Kami menghargai privasi Anda dan berkomitmen untuk melindunginya</p>
      </div>
    </header>

    <!-- Content -->
    <div class="privacy-content">
      <div class="privacy-container">
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

        <div class="protection-section">
          <div class="protection-box">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z" fill="currentColor"/>
            </svg>
            <p>Privasi Anda adalah prioritas kami. Informasi pribadi Anda dilindungi dengan enkripsi tingkat bank dan keamanan berlapis.</p>
          </div>
        </div>

        <div class="footer-links">
          <router-link to="/syarat-dan-ketentuan" class="link">← Syarat dan Ketentuan</router-link>
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

.privacy-page {
  width: 100%;
  min-height: 100vh;
  background: var(--color-bg-light);
  padding-top: 64px;
  padding-bottom: 80px;
}

/* Header */
.privacy-header {
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
.privacy-content {
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
}

.privacy-container {
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

/* Protection Section */
.protection-section {
  margin: 2rem 0;
}

.protection-box {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.5rem;
  background: rgba(168, 85, 247, 0.08);
  border: 1px solid rgba(168, 85, 247, 0.2);
  border-radius: 12px;
}

.protection-box svg {
  color: #A855F7;
  flex-shrink: 0;
  margin-top: 0.25rem;
}

.protection-box p {
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
  .privacy-header {
    padding: 2rem 1rem;
  }

  .header-title {
    font-size: 1.5rem;
  }

  .header-subtitle {
    font-size: 1rem;
  }

  .privacy-content {
    padding: 1.5rem 1rem;
  }

  .privacy-container {
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
  .privacy-header {
    padding: 1.5rem 1rem;
  }

  .header-title {
    font-size: 1.25rem;
  }

  .header-subtitle {
    font-size: 0.875rem;
  }

  .privacy-container {
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

  .protection-box {
    padding: 1rem;
    gap: 0.75rem;
  }

  .protection-box p {
    font-size: 0.8125rem;
  }
}
</style>
