<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const searchQuery = ref('')
const expandedIndex = ref(null)

const faqItems = ref([
  {
    category: 'Pemesanan Tiket',
    items: [
      {
        question: 'Bagaimana cara memesan tiket kereta di SwiftRail?',
        answer: 'Untuk memesan tiket, cukup buka aplikasi, masukkan stasiun asal dan tujuan, pilih tanggal perjalanan, pilih jadwal kereta yang diinginkan, pilih seat, masukkan data penumpang, dan lakukan pembayaran. Tiket akan dikirim ke email Anda setelah pembayaran berhasil.'
      },
      {
        question: 'Berapa lama waktu pemesanan berlaku sebelum harus membayar?',
        answer: 'Pemesanan akan berlaku selama 15 menit. Jika Anda tidak menyelesaikan pembayaran dalam waktu tersebut, tiket akan kembali ke sistem dan dapat dipesan oleh penumpang lain.'
      },
      {
        question: 'Dapatkah saya mengubah jadwal kereta setelah membayar?',
        answer: 'Ya, Anda dapat mengubah jadwal kereta dengan menghubungi customer service kami atau melalui menu "Ubah Jadwal" di aplikasi. Perubahan akan dikenakan biaya perubahan tiket sesuai kebijakan kami.'
      },
      {
        question: 'Apakah ada diskon untuk pemesanan massal?',
        answer: 'Ya, kami menyediakan diskon khusus untuk pemesanan massal (minimal 10 tiket). Silakan hubungi tim corporate kami melalui email corporate@swiftrail.id untuk informasi lebih lanjut.'
      },
      {
        question: 'Berapa batas maksimal tiket yang bisa dipesan sekaligus?',
        answer: 'Batas maksimal pemesanan tiket sekaligus adalah 9 tiket per transaksi. Jika ingin memesan lebih banyak, Anda perlu melakukan pemesanan terpisah.'
      }
    ]
  },
  {
    category: 'Pembayaran',
    items: [
      {
        question: 'Metode pembayaran apa saja yang tersedia?',
        answer: 'Kami menerima berbagai metode pembayaran termasuk transfer bank, kartu kredit/debit, e-wallet (GCash, OVO, Dana), dan QRIS. Semua metode pembayaran aman dan terenkripsi.'
      },
      {
        question: 'Apakah ada biaya tambahan untuk pembayaran menggunakan kartu kredit?',
        answer: 'Tidak ada biaya admin tambahan. Harga yang terlihat di aplikasi sudah termasuk semua biaya. Namun, biaya cicilan (jika ada) akan dikenakan sesuai kebijakan bank penerbit kartu Anda.'
      },
      {
        question: 'Bagaimana jika pembayaran saya gagal?',
        answer: 'Jika pembayaran gagal, saldo Anda akan dikembalikan otomatis dalam 24 jam kerja. Anda dapat mencoba melakukan pembayaran ulang atau menghubungi customer service untuk bantuan.'
      },
      {
        question: 'Apakah ada batasan minimum pembelian?',
        answer: 'Tidak ada batasan minimum pembelian. Anda bisa membeli 1 tiket atau lebih sesuai dengan kebutuhan Anda.'
      },
      {
        question: 'Bagaimana dengan pembayaran cicilan?',
        answer: 'Pembayaran cicilan tersedia untuk kartu kredit tertentu melalui program cicilan 0% dari mitra bank kami. Pilihan cicilan akan muncul saat Anda memilih metode pembayaran.'
      }
    ]
  },
  {
    category: 'Program Loyalitas',
    items: [
      {
        question: 'Apa itu SwiftRail Loyalty Program?',
        answer: 'SwiftRail Loyalty Program adalah program reward kami yang memberikan poin untuk setiap transaksi Anda. Poin dapat ditukarkan dengan diskon, tiket gratis, atau hadiah menarik lainnya.'
      },
      {
        question: 'Bagaimana cara mendapatkan poin loyalitas?',
        answer: 'Anda mendapatkan poin setiap kali melakukan pembelian tiket atau transaksi di SwiftRail. Besaran poin tergantung dari nilai pembelian Anda. Member yang lebih tinggi tier-nya akan mendapatkan bonus poin yang lebih banyak.'
      },
      {
        question: 'Apa saja tier membership yang tersedia?',
        answer: 'Tersedia 3 tier membership: Silver (untuk member baru), Gold (setelah mengumpulkan 5.000 poin), dan Platinum (setelah mengumpulkan 15.000 poin). Setiap tier memberikan benefit dan bonus yang berbeda.'
      },
      {
        question: 'Bagaimana cara menukar poin dengan reward?',
        answer: 'Anda dapat melihat daftar reward yang tersedia di menu "Loyalty" di aplikasi. Pilih reward yang ingin Anda tukar, dan poin Anda akan langsung dikurangkan. Reward akan dikirim ke akun Anda.'
      },
      {
        question: 'Berapa lama poin loyalty berlaku?',
        answer: 'Poin loyalty berlaku seumur hidup selama akun Anda masih aktif. Namun, jika akun Anda tidak aktif selama 2 tahun, poin dapat hangus sesuai dengan syarat dan ketentuan kami.'
      }
    ]
  },
  {
    category: 'Pembatalan & Pengembalian Dana',
    items: [
      {
        question: 'Bagaimana cara membatalkan tiket saya?',
        answer: 'Anda dapat membatalkan tiket melalui menu "Tiket Saya" di aplikasi, lalu pilih tiket yang ingin dibatalkan dan klik "Batalkan Tiket". Pembatalan harus dilakukan minimal 3 jam sebelum keberangkatan.'
      },
      {
        question: 'Berapa biaya pembatalan tiket?',
        answer: 'Biaya pembatalan tergantung waktu pembatalan: Batalkan 3-24 jam sebelum keberangkatan akan dikenakan 25% dari harga tiket. Batalkan > 24 jam sebelum keberangkatan gratis tidak ada biaya pembatalan.'
      },
      {
        question: 'Berapa lama proses pengembalian dana?',
        answer: 'Pengembalian dana biasanya diproses dalam 3-5 hari kerja. Untuk pembayaran via transfer bank, akan lebih cepat masuk ke rekening Anda, sedangkan untuk e-wallet akan langsung masuk ke saldo e-wallet Anda.'
      },
      {
        question: 'Bagaimana jika saya kehilangan tiket?',
        answer: 'Tiket digital tidak bisa hilang karena tersimpan di akun Anda. Anda dapat mengakses tiket kapan saja melalui aplikasi atau email. Jika Anda lupa password, gunakan fitur "Lupa Password" untuk reset.'
      },
      {
        question: 'Dapatkah tiket ditransfer ke orang lain?',
        answer: 'Tidak, tiket tidak dapat ditransfer. Namun, Anda dapat membatalkan tiket dan orang lain dapat memesan tiket yang sama dengan cara normal.'
      }
    ]
  },
  {
    category: 'Akun & Keamanan',
    items: [
      {
        question: 'Bagaimana cara membuat akun SwiftRail?',
        answer: 'Buka aplikasi atau website SwiftRail, klik "Daftar", masukkan email dan password, isi data pribadi Anda, dan konfirmasi email Anda. Akun Anda siap digunakan!'
      },
      {
        question: 'Bagaimana jika saya lupa password?',
        answer: 'Klik "Lupa Password" di halaman login, masukkan email Anda, dan kami akan mengirim link untuk reset password. Ikuti instruksi di email untuk membuat password baru.'
      },
      {
        question: 'Apakah data saya aman?',
        answer: 'Ya, data pribadi Anda dilindungi dengan enkripsi tingkat bank. Kami tidak akan pernah membagikan data Anda kepada pihak ketiga tanpa persetujuan Anda.'
      },
      {
        question: 'Bagaimana cara mengubah data pribadi saya?',
        answer: 'Masuk ke menu "Profil", klik "Edit Profil", ubah data yang ingin diubah, dan klik "Simpan Perubahan". Data akan diupdate secara otomatis.'
      },
      {
        question: 'Bagaimana cara menghapus akun saya?',
        answer: 'Hubungi customer service kami melalui email support@swiftrail.id dengan subjek "Permintaan Penghapusan Akun" dan kami akan memproses permintaan Anda.'
      }
    ]
  },
  {
    category: 'Perjalanan & Keberangkatan',
    items: [
      {
        question: 'Kapan saya harus tiba di stasiun?',
        answer: 'Kami merekomendasikan Anda tiba di stasiun minimal 30 menit sebelum waktu keberangkatan. Namun, periksa di halaman detail tiket untuk informasi lebih lanjut tentang periode check-in.'
      },
      {
        question: 'Apa saja yang boleh saya bawa ke kereta?',
        answer: 'Anda dapat membawa barang bawaan sesuai dengan kapasitas bagasi kereta. Umumnya setiap penumpang diizinkan membawa 1-2 barang bawaan. Barang berbahaya dan cairan terlarang tidak diperbolehkan.'
      },
      {
        question: 'Bagaimana jika saya ketinggalan kereta?',
        answer: 'Jika Anda ketinggalan kereta, tiket Anda tidak dapat digunakan. Anda perlu membatalkan tiket tersebut dan membeli tiket baru untuk jadwal kereta berikutnya.'
      },
      {
        question: 'Apakah ada akomodasi khusus untuk difabel?',
        answer: 'Ya, kami menyediakan akomodasi khusus untuk penumpang dengan kebutuhan khusus. Hubungi customer service sebelum perjalanan untuk mengatur akomodasi yang Anda butuhkan.'
      },
      {
        question: 'Bagaimana jika kereta terlambat atau dibatalkan?',
        answer: 'Jika kereta terlambat atau dibatalkan, kami akan mengirimkan notifikasi ke email dan aplikasi Anda. Anda dapat memilih untuk tidak melakukan perjalanan dan mendapatkan pengembalian dana penuh atau ganti tiket jadwal lain.'
      }
    ]
  },
  {
    category: 'Masalah Teknis',
    items: [
      {
        question: 'Aplikasi saya tidak bisa login',
        answer: 'Pastikan Anda menggunakan email dan password yang benar. Coba clear cache aplikasi, restart aplikasi, atau reinstall aplikasi. Jika masalah masih terjadi, hubungi customer service kami.'
      },
      {
        question: 'Mengapa tiket saya tidak muncul?',
        answer: 'Tiket biasanya muncul dalam beberapa menit setelah pembayaran berhasil. Coba refresh aplikasi atau login kembali. Periksa email Anda untuk konfirmasi pembayaran. Jika masih tidak muncul, hubungi support kami.'
      },
      {
        question: 'Bagaimana jika ada masalah saat proses pembayaran?',
        answer: 'Jangan refresh halaman atau tutup aplikasi selama proses pembayaran. Tunggu hingga transaksi selesai. Periksa riwayat transaksi Anda untuk memastikan pembayaran berhasil atau gagal.'
      },
      {
        question: 'Bagaimana cara menghubungi customer service?',
        answer: `Email: support@swiftrail.id

Telepon: 1500-SWIFT (09874)

Live Chat: Di aplikasi (jam 8 pagi - 8 malam)

Media sosial: @SwiftRailID`
      }
    ]
  }
])

const filteredFaqItems = computed(() => {
  if (!searchQuery.value.trim()) {
    return faqItems.value
  }

  const query = searchQuery.value.toLowerCase()
  return faqItems.value.map(category => ({
    ...category,
    items: category.items.filter(item =>
      item.question.toLowerCase().includes(query) ||
      item.answer.toLowerCase().includes(query)
    )
  })).filter(category => category.items.length > 0)
})

const toggleExpand = (index) => {
  expandedIndex.value = expandedIndex.value === index ? null : index
}

const clearSearch = () => {
  searchQuery.value = ''
  expandedIndex.value = null
}
</script>

<template>
  <div class="faq-page">
    <!-- Header -->
    <header class="faq-header">
      <div class="header-container">
        <div class="header-content">
          <h1 class="header-title">Pusat Bantuan</h1>
          <p class="header-subtitle">Temukan jawaban dan solusi untuk semua pertanyaan Anda</p>
        </div>
      </div>
    </header>

    <!-- Search Box -->
    <div class="search-section">
      <div class="search-container">
        <div class="search-input-wrapper">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
            <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            class="search-input"
            placeholder="Cari pertanyaan Anda..."
          />
          <button v-if="searchQuery" @click="clearSearch" class="clear-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- FAQ Content -->
    <div class="faq-content">
      <div class="faq-container">
        <div v-if="filteredFaqItems.length > 0" class="faq-sections">
          <div v-for="(category, catIndex) in filteredFaqItems" :key="catIndex" class="faq-category">
            <h2 class="category-title">{{ category.category }}</h2>
            
            <div class="faq-accordion">
              <div
                v-for="(item, itemIndex) in category.items"
                :key="itemIndex"
                class="accordion-item"
                :class="{ 'is-expanded': expandedIndex === `${catIndex}-${itemIndex}` }"
              >
                <button
                  class="accordion-header"
                  @click="toggleExpand(`${catIndex}-${itemIndex}`)"
                >
                  <div class="header-content">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="expand-icon">
                      <path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="question-text">{{ item.question }}</span>
                  </div>
                </button>

                <Transition name="accordion">
                  <div v-show="expandedIndex === `${catIndex}-${itemIndex}`" class="accordion-body">
                    <div class="answer-content">
                      {{ item.answer }}
                    </div>
                  </div>
                </Transition>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="empty-state">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" opacity="0.3"/>
            <path d="M12 7V13M12 17H12.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity="0.3"/>
          </svg>
          <h3 class="empty-title">Tidak ada hasil</h3>
          <p class="empty-text">Kami tidak menemukan pertanyaan yang cocok dengan pencarian Anda</p>
          <button @click="clearSearch" class="btn-reset">Hapus Filter</button>
        </div>
      </div>

      <!-- Contact Section -->
      <div class="contact-section">
        <h3 class="contact-title">Masih ada pertanyaan?</h3>
        <p class="contact-text">Hubungi tim customer service kami yang siap membantu 24/7</p>
        
        <div class="contact-methods">
          <a href="mailto:support@swiftrail.id" class="contact-method">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
              <path d="M2 6L12 13L22 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>support@swiftrail.id</span>
          </a>

          <a href="tel:1500874" class="contact-method">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
            <span>1500-SWIFT (09874)</span>
          </a>

          <div class="contact-method">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
              <path d="M8 12H16M12 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>Live Chat (8am - 8pm)</span>
          </div>
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
  --color-success: #10B981;
  --color-error: #EF4444;
}

.faq-page {
  width: 100%;
  min-height: 100vh;
  background: var(--color-bg-light);
  padding-top: 64px;
  padding-bottom: 80px;
}

/* Header */
.faq-header {
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

/* Search Section */
.search-section {
  background: var(--color-white);
  padding: 2rem 1.5rem;
  border-bottom: 1px solid var(--color-border);
  position: sticky;
  top: 64px;
  z-index: 40;
}

.search-container {
  max-width: 900px;
  margin: 0 auto;
}

.search-input-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: var(--color-bg-light);
  border: 2px solid var(--color-border);
  border-radius: 12px;
  padding: 0.75rem 1rem;
  transition: all 0.3s ease;
}

.search-input-wrapper:focus-within {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(22, 117, 231, 0.1);
}

.search-input-wrapper svg {
  color: var(--color-text-light);
  flex-shrink: 0;
}

.search-input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 1rem;
  color: var(--color-text-dark);
  outline: none;
}

.search-input::placeholder {
  color: var(--color-text-light);
}

.clear-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  background: none;
  border: none;
  color: var(--color-text-light);
  cursor: pointer;
  transition: color 0.2s ease;
}

.clear-btn:hover {
  color: var(--color-text-dark);
}

/* FAQ Content */
.faq-content {
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
}

.faq-container {
  background: var(--color-white);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.faq-sections {
  padding: 2rem;
}

.faq-category {
  margin-bottom: 3rem;
}

.faq-category:last-child {
  margin-bottom: 0;
}

.category-title {
  font-size: 1.375rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid var(--color-primary);
  display: inline-block;
}

.faq-accordion {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.accordion-item {
  border: 1px solid var(--color-border);
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.accordion-item:hover {
  border-color: var(--color-primary);
}

.accordion-item.is-expanded {
  border-color: var(--color-primary);
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.15);
}

.accordion-header {
  width: 100%;
  padding: 1.25rem 1.5rem;
  background: var(--color-white);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
  text-align: left;
}

.accordion-item.is-expanded .accordion-header {
  background: rgba(22, 117, 231, 0.05);
}

.header-content {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  width: 100%;
}

.expand-icon {
  color: var(--color-primary);
  flex-shrink: 0;
  margin-top: 0.125rem;
  transition: transform 0.3s ease;
}

.accordion-item.is-expanded .expand-icon {
  transform: rotate(90deg);
}

.question-text {
  font-size: 1.0625rem;
  font-weight: 600;
  color: var(--color-text-dark);
  line-height: 1.4;
}

.accordion-body {
  padding: 1.5rem;
  background: rgba(249, 250, 251, 0.5);
  border-top: 1px solid var(--color-border);
}

.answer-content {
  color: var(--color-text-light);
  line-height: 1.6;
  font-size: 0.9375rem;
}

/* Accordion Transition */
.accordion-enter-active,
.accordion-leave-active {
  transition: all 0.3s ease;
}

.accordion-enter-from {
  opacity: 0;
  max-height: 0;
}

.accordion-leave-to {
  opacity: 0;
  max-height: 0;
}

/* Empty State */
.empty-state {
  padding: 4rem 2rem;
  text-align: center;
}

.empty-state svg {
  color: var(--color-text-light);
  margin: 0 auto 1.5rem;
}

.empty-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin-bottom: 0.5rem;
}

.empty-text {
  color: var(--color-text-light);
  margin-bottom: 1.5rem;
  font-size: 0.9375rem;
}

.btn-reset {
  padding: 0.75rem 1.5rem;
  background: var(--color-primary);
  color: var(--color-white);
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9375rem;
}

.btn-reset:hover {
  background: #1565D8;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

/* Contact Section */
.contact-section {
  margin-top: 3rem;
  padding: 2rem;
  background: linear-gradient(135deg, rgba(22, 117, 231, 0.08) 0%, rgba(22, 117, 231, 0.04) 100%);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  text-align: center;
}

.contact-title {
  font-size: 1.375rem;
  font-weight: 700;
  color: var(--color-text-dark);
  margin-bottom: 0.5rem;
}

.contact-text {
  color: var(--color-text-light);
  margin-bottom: 1.5rem;
  font-size: 0.9375rem;
}

.contact-methods {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  align-items: center;
}

.contact-method {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  background: var(--color-white);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  text-decoration: none;
  color: var(--color-primary);
  font-weight: 600;
  font-size: 0.9375rem;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 280px;
}

.contact-method:hover {
  background: var(--color-primary);
  color: var(--color-white);
  border-color: var(--color-primary);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

.contact-method svg {
  flex-shrink: 0;
}

/* Responsive */
@media (max-width: 768px) {
  .faq-page {
    padding-top: 64px;
  }

  .faq-header {
    padding: 2rem 1rem;
  }

  .header-title {
    font-size: 1.5rem;
  }

  .header-subtitle {
    font-size: 1rem;
  }

  .search-section {
    padding: 1.5rem 1rem;
  }

  .faq-content {
    padding: 1.5rem 1rem;
  }

  .faq-container {
    border-radius: 12px;
  }

  .faq-sections {
    padding: 1.5rem 1rem;
  }

  .faq-category {
    margin-bottom: 2rem;
  }

  .category-title {
    font-size: 1.125rem;
    margin-bottom: 1rem;
  }

  .accordion-header {
    padding: 1rem;
  }

  .question-text {
    font-size: 0.9375rem;
  }

  .accordion-body {
    padding: 1rem;
  }

  .answer-content {
    font-size: 0.875rem;
  }

  .contact-section {
    padding: 1.5rem 1rem;
    margin-top: 2rem;
  }

  .contact-method {
    min-width: unset;
    width: 100%;
  }

  .empty-state {
    padding: 2rem 1rem;
  }

  .expand-icon {
    width: 20px;
    height: 20px;
  }
}

@media (max-width: 480px) {
  .faq-header {
    padding: 1.5rem 1rem;
  }

  .header-title {
    font-size: 1.25rem;
  }

  .header-subtitle {
    font-size: 0.875rem;
  }

  .faq-sections {
    padding: 1rem;
  }

  .faq-category {
    margin-bottom: 1.5rem;
  }

  .accordion-header {
    padding: 0.875rem;
    gap: 0.75rem;
  }

  .question-text {
    font-size: 0.875rem;
  }

  .category-title {
    font-size: 1rem;
  }

  .contact-methods {
    gap: 0.75rem;
  }

  .contact-method {
    font-size: 0.8125rem;
    padding: 0.75rem 1rem;
  }
}
</style>
