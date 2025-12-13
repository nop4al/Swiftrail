<template>
  <div class="tracking-page">
    <!-- Header -->
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
          <router-link to="/profile" class="nav-link">Profile</router-link>
        </nav>
      </div>
    </header>

    <div class="tracking-container">

    <!-- Map Card -->
    <section class="card map-card">
      <div class="card-title">
        <svg class="icon" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 11 7 11s7-5.75 7-11c0-3.87-3.13-7-7-7zM12 11.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg>
        Live Tracking - {{ train.name }}
      </div>

      <div id="map" class="map-container" ref="mapContainer">
        <!-- Leaflet map will be rendered here -->
      </div>
    </section>

    <!-- Tourism Popup Modal -->
    <div v-if="showTourismModal" class="modal-overlay" @click="showTourismModal = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>{{ selectedStation?.name }}</h2>
          <button class="close-btn" @click="showTourismModal = false">×</button>
        </div>
        <div class="modal-body">
          <p class="station-info">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            Jadwal: {{ selectedStation?.scheduled }}
          </p>
          <h3>Rekomendasi Wisata Terdekat</h3>
          <div class="tourism-list">
            <div v-for="(place, index) in selectedStation?.tourism" :key="index" class="tourism-card">
              <div class="tourism-image">
                <img :src="place.image" :alt="place.name" />
                <div class="tourism-rating-badge">
                  <span class="rating-stars">★</span>
                  <span class="rating-number">{{ place.rating }}</span>
                </div>
              </div>
              <div class="tourism-info">
                <div class="tourism-header">
                  <h4>{{ place.name }}</h4>
                  <div class="tourism-rating-detail">
                    <span class="stars">
                      <svg v-for="i in 5" :key="i" class="star-icon" :class="{ filled: i <= Math.round(place.rating) }" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                      </svg>
                    </span>
                    <span class="review-count">({{ place.reviews }})</span>
                  </div>
                </div>
                
                <p class="tourism-category">{{ place.category }}</p>
                <p class="tourism-description">{{ place.description }}</p>
                
                <div class="tourism-details">
                  <div class="detail-item">
                    <svg class="detail-icon" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                    </svg>
                    <span>{{ place.address }}</span>
                  </div>
                  
                  <div class="detail-item">
                    <svg class="detail-icon" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M11.99 5V1h-1v4H8.01V1H7v4H5.5C4.12 5 3 6.13 3 7.5v12C3 20.87 4.12 22 5.5 22h13c1.38 0 2.5-1.13 2.5-2.5v-12C21 6.13 19.88 5 18.5 5H17V1h-1v4h-3.01V1h-1v4zm7.5 16H5.5V7h13v14z"/>
                    </svg>
                    <span>{{ place.hours }}</span>
                  </div>
                  
                  <div class="detail-item">
                    <svg class="detail-icon" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 1C6.48 1 2 5.48 2 11s4.48 10 10 10 10-4.48 10-10S17.52 1 12 1zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                    </svg>
                    <span>{{ place.price }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Info Card -->
    <section class="card info-card">
      <div class="info-header">
        <div class="info-left">
          <h2 class="train-name">{{ train.name }}</h2>
          <div class="train-details">
            <span class="train-number">KA {{ train.number }}</span>
            <span class="train-route">{{ train.from }} - {{ train.to }}</span>
          </div>
        </div>
        <button class="status-btn">Dalam Perjalanan</button>
      </div>

      <div class="progress-section">
        <div class="progress-row">
          <div class="station left">{{ train.from }}</div>
          <div class="station right">{{ train.to }}</div>
        </div>

        <div class="progress-track" aria-hidden="true">
          <div class="progress" :style="{ width: percent + '%' }"></div>
        </div>

        <div class="progress-meta">
          <div class="time left">{{ train.startTime }}</div>
          <div class="dist">{{ train.currentKm }} / {{ train.totalKm }} km</div>
          <div class="time right">{{ train.endTime }}</div>
        </div>
      </div>
    </section>

    <!-- Stats Grid -->
    <section class="stats-grid">
      <div class="stat-card speed">
        <div class="stat-icon-wrapper green">
          <div class="stat-icon">
            <svg viewBox="0 0 24 24"><path fill="currentColor" d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg>
          </div>
        </div>
        <div class="stat-content">
          <p class="stat-label">Kecepatan Saat Ini</p>
          <p class="stat-value">{{ train.speed }} <span class="stat-unit">km/h</span></p>
          <p class="stat-meta">Max: {{ train.maxSpeed }} km/h</p>
        </div>
      </div>

      <div class="stat-card occupancy">
        <div class="stat-icon-wrapper orange">
          <div class="stat-icon">
            <svg viewBox="0 0 24 24"><path fill="currentColor" d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zM2 20c0-2.2 4-4 10-4s10 1.8 10 4v2H2v-2z"/></svg>
          </div>
        </div>
        <div class="stat-content">
          <p class="stat-label">Okupansi</p>
          <p class="stat-value">{{ train.occupancy }}%</p>
          <p class="stat-meta">{{ train.occupancyLabel }}</p>
        </div>
      </div>

      <div class="stat-card delay">
        <div class="stat-icon-wrapper purple">
          <div class="stat-icon">
            <svg viewBox="0 0 24 24"><path fill="currentColor" d="M13 3h-2v10h2V3zm-2 14h2v4h-2v-4z"/></svg>
          </div>
        </div>
        <div class="stat-content">
          <p class="stat-label">Keterlambatan</p>
          <p class="stat-value" :class="{ late: train.delayMinutes > 0 }">
            {{ train.delayMinutes > 0 ? '+' + train.delayMinutes + ' menit' : 'On time' }}
          </p>
          <p class="stat-meta">{{ train.delayMinutes > 0 ? 'Terlambat' : 'Tepat Waktu' }}</p>
        </div>
      </div>

      <div class="stat-card distance">
        <div class="stat-icon-wrapper blue">
          <div class="stat-icon">
            <svg viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 14.5V7l4 3.5-4 6z"/></svg>
          </div>
        </div>
        <div class="stat-content">
          <p class="stat-label">Jarak Tempuh</p>
          <p class="stat-value">{{ train.currentKm }} <span class="stat-unit">km</span></p>
          <p class="stat-meta">dari {{ train.totalKm }} km</p>
        </div>
      </div>
    </section>

    <!-- Placeholder for additional content -->
    <!-- ...existing code... -->

    </div>
  </div>
</template>

<script setup>
import { reactive, computed, onMounted, ref } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const mapContainer = ref(null);
let map = null;
let markerGroup = null;
let routePolyline = null;

const showTourismModal = ref(false);
const selectedStation = ref(null);

const stations = [
  {
    name: 'Jakarta Kota',
    lat: -6.1071,
    lng: 106.8103,
    scheduled: '08:20',
    tourism: [
      {
        name: 'Kota Tua Jakarta',
        description: 'Kawasan bersejarah dengan arsitektur Belanda klasik',
        rating: 4.5,
        reviews: 2847,
        category: 'Tempat Bersejarah',
        address: 'Jalan Pintu Besar Utara, Jakarta Pusat',
        hours: '06:00 - 18:00 WIB',
        price: 'Gratis',
        image: 'https://images.unsplash.com/photo-1587086991415-4ec73e64e9d6?w=300&h=200&fit=crop'
      },
      {
        name: 'Museum Sejarah Jakarta',
        description: 'Museum lengkap dengan koleksi artefak sejarah Jakarta',
        rating: 4.6,
        reviews: 1523,
        category: 'Museum',
        address: 'Jalan Taman Fatahillah No. 2, Jakarta Pusat',
        hours: '09:00 - 17:00 WIB',
        price: 'Rp 20.000',
        image: 'https://images.unsplash.com/photo-1578654881529-145ea109b41f?w=300&h=200&fit=crop'
      },
      {
        name: 'Taman Impian Jaya Ancol',
        description: 'Kompleks hiburan dengan taman dan wahana menarik',
        rating: 4.4,
        reviews: 3421,
        category: 'Taman Hiburan',
        address: 'Jalan Lodan, Jakarta Utara',
        hours: '10:00 - 22:00 WIB',
        price: 'Rp 50.000 - 150.000',
        image: 'https://images.unsplash.com/photo-1564629238346-26ebbc4ec645?w=300&h=200&fit=crop'
      }
    ]
  },
  {
    name: 'Cirebon',
    lat: -6.7030,
    lng: 108.4456,
    scheduled: '10:43',
    arrivalTime: '10:46',
    tourism: [
      {
        name: 'Keraton Cirebon',
        description: 'Istana tradisional Cirebon dengan arsitektur unik',
        rating: 4.5,
        reviews: 892,
        category: 'Istana Bersejarah',
        address: 'Jalan Kasepuhan, Cirebon',
        hours: '08:00 - 17:00 WIB',
        price: 'Rp 15.000',
        image: 'https://images.unsplash.com/photo-1548013146-72976fba63b0?w=300&h=200&fit=crop'
      },
      {
        name: 'Batik Cirebon Traditional',
        description: 'Pusat batik dengan workshop dan toko batik tradisional',
        rating: 4.3,
        reviews: 634,
        category: 'Kerajinan Tangan',
        address: 'Jalan Sultan Agung, Cirebon',
        hours: '09:00 - 18:00 WIB',
        price: 'Rp 50.000 - 500.000',
        image: 'https://images.unsplash.com/photo-1548690312-e3fc14f6d674?w=300&h=200&fit=crop'
      },
      {
        name: 'Pantai Pelabuhan Ratu',
        description: 'Pantai indah dengan legenda mistis yang menarik',
        rating: 4.2,
        reviews: 456,
        category: 'Pantai',
        address: 'Pantai Pelabuhan, Cirebon',
        hours: '24 Jam',
        price: 'Gratis',
        image: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=300&h=200&fit=crop'
      }
    ]
  },
  {
    name: 'Pekalongan',
    lat: -6.8883,
    lng: 109.6798,
    scheduled: '12:05',
    arrivalTime: '12:07',
    tourism: [
      {
        name: 'Batik Coastal Walk',
        description: 'Destinasi batik tradisional dengan workshop interaktif',
        rating: 4.5,
        reviews: 1203,
        category: 'Destinasi Budaya',
        address: 'Jalan Gajah Mada, Pekalongan',
        hours: '08:00 - 17:00 WIB',
        price: 'Rp 25.000',
        image: 'https://images.unsplash.com/photo-1549144611-bde1e8e4bc5e?w=300&h=200&fit=crop'
      },
      {
        name: 'Museum Batik Indonesia',
        description: 'Museum yang menampilkan koleksi batik dari seluruh nusantara',
        rating: 4.4,
        reviews: 876,
        category: 'Museum',
        address: 'Jalan Oey Hay, Pekalongan',
        hours: '09:00 - 16:30 WIB',
        price: 'Rp 15.000',
        image: 'https://images.unsplash.com/photo-1564629238346-26ebbc4ec645?w=300&h=200&fit=crop'
      },
      {
        name: 'Pantai Pekalongan',
        description: 'Pantai yang cocok untuk berjalan-jalan santai',
        rating: 4.2,
        reviews: 543,
        category: 'Pantai',
        address: 'Pantai Pekalongan, Jawa Tengah',
        hours: '24 Jam',
        price: 'Gratis',
        image: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=300&h=200&fit=crop'
      }
    ]
  },
  {
    name: 'Semarang Tawang',
    lat: -6.9771,
    lng: 110.4137,
    scheduled: '13:09',
    arrivalTime: '13:12',
    tourism: [
      {
        name: 'Candi Borobudur',
        description: 'Candi Buddha terbesar di dunia dengan pemandangan spektakuler',
        rating: 4.8,
        reviews: 5231,
        category: 'Situs Budaya Dunia',
        address: 'Borobudur, Magelang',
        hours: '06:00 - 17:00 WIB',
        price: 'Rp 50.000',
        image: 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=300&h=200&fit=crop'
      },
      {
        name: 'Kota Lama Semarang',
        description: 'Wisata sejarah dengan arsitektur colonial bergaya Belanda',
        rating: 4.6,
        reviews: 2134,
        category: 'Tempat Bersejarah',
        address: 'Jalan Letjen Suprapto, Semarang',
        hours: '06:00 - 18:00 WIB',
        price: 'Gratis',
        image: 'https://images.unsplash.com/photo-1587086991415-4ec73e64e9d6?w=300&h=200&fit=crop'
      },
      {
        name: 'Taman Gajah Mada',
        description: 'Taman kota yang indah dengan fasilitas lengkap',
        rating: 4.3,
        reviews: 1087,
        category: 'Taman',
        address: 'Jalan Gajah Mada, Semarang',
        hours: '06:00 - 18:00 WIB',
        price: 'Gratis',
        image: 'https://images.unsplash.com/photo-1564629238346-26ebbc4ec645?w=300&h=200&fit=crop'
      }
    ]
  },
  {
    name: 'Bojonegoro',
    lat: -7.1600,
    lng: 111.8845,
    scheduled: '15:09',
    arrivalTime: '15:11',
    tourism: [
      {
        name: 'Taman Nasional Baluran',
        description: 'Taman nasional dengan pemandangan alam yang menakjubkan',
        rating: 4.6,
        reviews: 1645,
        category: 'Taman Nasional',
        address: 'Jalan Raya Bojonegoro, Banyuwangi',
        hours: '06:00 - 17:00 WIB',
        price: 'Rp 20.000',
        image: 'https://images.unsplash.com/photo-1469022563149-aa64dbd37dae?w=300&h=200&fit=crop'
      },
      {
        name: 'Makam Sunan Muria',
        description: 'Situs bersejarah Islam dengan arsitektur tradisional',
        rating: 4.3,
        reviews: 564,
        category: 'Situs Religius',
        address: 'Jalan Pendakian, Bojonegoro',
        hours: '06:00 - 18:00 WIB',
        price: 'Gratis',
        image: 'https://images.unsplash.com/photo-1548013146-72976fba63b0?w=300&h=200&fit=crop'
      },
      {
        name: 'Danau Bojonegoro',
        description: 'Danau alami yang cocok untuk rekreasi keluarga',
        rating: 4.2,
        reviews: 732,
        category: 'Danau',
        address: 'Jalan Pesisir Danau, Bojonegoro',
        hours: '24 Jam',
        price: 'Gratis',
        image: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=300&h=200&fit=crop'
      }
    ]
  },
  {
    name: 'Surabaya Pasar Turi',
    lat: -7.2197,
    lng: 112.7499,
    scheduled: '16:25',
    tourism: [
      {
        name: 'Museum Perang Surabaya',
        description: 'Museum yang menceritakan kisah perjuangan kemerdekaan',
        rating: 4.5,
        reviews: 1876,
        category: 'Museum Sejarah',
        address: 'Jalan Pemuda, Surabaya',
        hours: '09:00 - 17:00 WIB',
        price: 'Rp 20.000',
        image: 'https://images.unsplash.com/photo-1578654881529-145ea109b41f?w=300&h=200&fit=crop'
      },
      {
        name: 'Taman Bungkul',
        description: 'Taman modern dengan fasilitas olahraga dan rekreasi',
        rating: 4.6,
        reviews: 2543,
        category: 'Taman Rekreasi',
        address: 'Jalan Bungkul, Surabaya',
        hours: '06:00 - 22:00 WIB',
        price: 'Gratis',
        image: 'https://images.unsplash.com/photo-1564629238346-26ebbc4ec645?w=300&h=200&fit=crop'
      },
      {
        name: 'House of Sampoerna',
        description: 'Museum budaya dengan koleksi sejarah rokok Indonesia',
        rating: 4.4,
        reviews: 1234,
        category: 'Museum',
        address: 'Jalan Tunjungan, Surabaya',
        hours: '10:00 - 18:00 WIB',
        price: 'Rp 50.000',
        image: 'https://images.unsplash.com/photo-1549144611-bde1e8e4bc5e?w=300&h=200&fit=crop'
      }
    ]
  }
];

function openTourismModal(station) {
  selectedStation.value = station;
  showTourismModal.value = true;
}

const train = reactive({
  name: 'Argo Bromo Anggrek',
  number: '2',
  from: 'Jakarta Kota',
  to: 'Surabaya Pasar Turi',
  startTime: '08:20',
  endTime: '16:25',
  currentKm: 232,
  totalKm: 664,
  speed: 85,
  maxSpeed: 120,
  occupancy: 78,
  occupancyLabel: 'Ramai',
  delayMinutes: 5,
  status: 'Dalam Perjalanan'
});

const markerStation = reactive({
  name: 'Cirebon',
  scheduled: '10:43',
  lat: -6.7030,
  lng: 108.4456
});

// Sample route coordinates (Jakarta to Surabaya)
const routeCoordinates = [
  [-6.1071, 106.8103], // Jakarta Kota
  [-6.3, 107.2],
  [-6.5, 107.8],
  [-6.7030, 108.4456], // Cirebon
  [-6.8, 109.2],
  [-6.8883, 109.6798], // Pekalongan
  [-6.9, 109.9],
  [-6.9771, 110.4137], // Semarang Tawang
  [-7.0, 110.9],
  [-7.1, 111.4],
  [-7.1600, 111.8845], // Bojonegoro
  [-7.18, 112.2],
  [-7.2197, 112.7499] // Surabaya Pasar Turi
];

const percent = computed(() => Math.round((train.currentKm / train.totalKm) * 100));
const remainingKm = computed(() => Math.max(0, train.totalKm - train.currentKm));
train.remainingKm = remainingKm.value;

function initMap() {
  if (!mapContainer.value) return;

  // Create map centered on current train position
  map = L.map(mapContainer.value).setView(
    [markerStation.lat, markerStation.lng],
    8
  );

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19,
    tileSize: 256
  }).addTo(map);

  markerGroup = L.featureGroup().addTo(map);

  // Add route polyline
  routePolyline = L.polyline(routeCoordinates, {
    color: '#1675E7',
    weight: 3,
    opacity: 0.7,
    dashArray: '5, 10'
  }).addTo(map);

  // Add start marker (Jakarta)
  const jakartaMarker = L.circleMarker([routeCoordinates[0][0], routeCoordinates[0][1]], {
    radius: 8,
    fillColor: '#10B981',
    color: '#fff',
    weight: 2,
    opacity: 1,
    fillOpacity: 0.8
  })
    .bindPopup('<b>Jakarta Gambir</b><br>Keberangkatan<br><small style="color: var(--color-primary); cursor: pointer;">Klik marker untuk melihat wisata</small>')
    .on('click', function() {
      openTourismModal(stations[0]);
    })
    .addTo(map);

  // Add all station markers
  const stationCoordinates = [
    { coords: [-6.7030, 108.4456], name: 'Cirebon', idx: 1 },
    { coords: [-6.8883, 109.6798], name: 'Pekalongan', idx: 2 },
    { coords: [-6.9771, 110.4137], name: 'Semarang Tawang', idx: 3 },
    { coords: [-7.1600, 111.8845], name: 'Bojonegoro', idx: 4 }
  ];

  stationCoordinates.forEach((station) => {
    const stationData = stations[station.idx];
    const marker = L.circleMarker(station.coords, {
      radius: 7,
      fillColor: '#F59E0B',
      color: '#fff',
      weight: 2,
      opacity: 1,
      fillOpacity: 0.8
    })
      .bindPopup(`<b>${stationData.name}</b><br>Jadwal: ${stationData.scheduled}<br><small style="color: var(--color-primary); cursor: pointer;">Klik marker untuk melihat wisata</small>`)
      .on('click', function() {
        openTourismModal(stationData);
      })
      .addTo(map);
  });

  // End marker (Surabaya)
  L.circleMarker(
    [routeCoordinates[routeCoordinates.length - 1][0], routeCoordinates[routeCoordinates.length - 1][1]],
    {
      radius: 8,
      fillColor: '#EF4444',
      color: '#fff',
      weight: 2,
      opacity: 1,
      fillOpacity: 0.8
    }
  )
    .bindPopup('<b>Surabaya Pasar Turi</b><br>Tujuan<br><small style="color: var(--color-primary); cursor: pointer;">Klik marker untuk melihat wisata</small>')
    .on('click', function() {
      openTourismModal(stations[5]);
    })
    .addTo(map);

  // Add current train position marker
  const trainIcon = L.divIcon({
    html: `
      <div class="train-marker">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="24" height="24" rx="6" fill="#1675E7"/>
          <path d="M6 8C6 6.89543 6.89543 6 8 6H16C17.1046 6 18 6.89543 18 8V14C18 15.1046 17.1046 16 16 16H8C6.89543 16 6 15.1046 6 14V8Z" fill="white"/>
          <rect x="8" y="8" width="3" height="2" rx="0.5" fill="#1675E7"/>
          <rect x="13" y="8" width="3" height="2" rx="0.5" fill="#1675E7"/>
        </svg>
      </div>
    `,
    className: 'custom-train-icon',
    iconSize: [32, 32],
    iconAnchor: [16, 16],
    popupAnchor: [0, -16]
  });

  L.marker([markerStation.lat, markerStation.lng], { icon: trainIcon })
    .bindPopup(
      `<b>${markerStation.name}</b><br>Kecepatan: ${train.speed} km/h<br>Jadwal: ${markerStation.scheduled}`
    )
    .addTo(map);

  // Fit bounds to show entire route
  const bounds = L.latLngBounds(routeCoordinates);
  map.fitBounds(bounds, { padding: [50, 50] });
}

onMounted(() => {
  initMap();
});
</script>

<style scoped>
/* Header */
.header {
  background: var(--color-white);
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  position: sticky;
  top: 0;
  z-index: 50;
}

.header-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 80px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: inherit;
}

.logo-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
}

.logo-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.logo-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--color-primary);
  line-height: 1;
}

.logo-subtitle {
  font-size: 10px;
  font-weight: 600;
  color: var(--color-secondary);
  letter-spacing: 0.05em;
  line-height: 1;
}

.nav {
  display: flex;
  align-items: center;
  gap: 24px;
}

.nav-link {
  font-size: 14px;
  font-weight: 500;
  color: var(--color-text-light);
  text-decoration: none;
  transition: color 0.3s ease;
}

.nav-link:hover {
  color: var(--color-primary);
}

.nav-btn {
  padding: 10px 20px;
  background: var(--color-primary);
  color: var(--color-white);
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.nav-btn:hover {
  background: var(--color-primary-dark);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

/* Main Container */
.tracking-page {
  width: 100%;
  min-height: 100vh;
  background-color: var(--color-bg-light);
}

.tracking-container {
  max-width: 820px;
  margin: 0 auto;
  padding: 24px 16px 40px;
  font-family: 'Geist', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: var(--color-text-dark);
}

/* Card Styles */
.card {
  background: var(--color-white);
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 16px;
  border: 1px solid #e5e7eb;
}

.card.map-card {
  padding: 0;
  overflow: hidden;
}

/* Map Card */
.map-card .card-title {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--color-text-dark);
  font-weight: 600;
  font-size: 14px;
  padding: 16px;
  background: linear-gradient(135deg, #f0f7ff 0%, #f0f4ff 100%);
  border-bottom: 1px solid #e5e7eb;
  margin: 0;
}

.icon {
  width: 18px;
  height: 18px;
  color: var(--color-primary);
}

.map-placeholder {
  height: 220px;
  background: linear-gradient(135deg, #f0f4f8 0%, #d9e7f7 100%);
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.map-container {
  height: 320px;
  width: 100%;
  background: #f0f4f8;
  border-radius: 0 0 12px 12px;
}

/* Demo Marker */
.marker {
  position: absolute;
  transform: translate(-50%, -100%);
  display: flex;
  flex-direction: column;
  align-items: center;
}

.marker-bubble {
  background: var(--color-white);
  border-radius: 8px;
  padding: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  text-align: center;
  font-size: 12px;
  color: var(--color-text-dark);
  font-weight: 600;
}

.marker-pin {
  width: 12px;
  height: 12px;
  background: var(--color-primary);
  border-radius: 3px;
  margin-top: 6px;
  transform: rotate(45deg);
  box-shadow: 0 2px 8px rgba(22, 117, 231, 0.3);
}

/* Info Card */
.info-card {
  padding: 20px;
}

.info-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 20px;
}

.info-left {
  flex: 1;
}

.train-name {
  font-size: 20px;
  font-weight: 700;
  color: var(--color-text-dark);
  margin: 0 0 8px 0;
}

.train-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.train-number {
  font-size: 12px;
  color: var(--color-text-light);
  font-weight: 500;
}

.train-route {
  font-size: 13px;
  color: var(--color-primary);
  font-weight: 600;
}

.status-btn {
  padding: 10px 20px;
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: var(--color-white);
  border: none;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.3);
}

.status-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(22, 117, 231, 0.4);
}

/* Progress Section */
.progress-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.progress-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  color: var(--color-text-light);
  font-weight: 500;
}

.progress-track {
  height: 8px;
  background: #e5e7eb;
  border-radius: 99px;
  position: relative;
  overflow: hidden;
}

.progress {
  height: 100%;
  background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
  border-radius: 99px;
  transition: width 0.5s ease;
  box-shadow: 0 0 8px rgba(22, 117, 231, 0.3);
}

.progress-meta {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: var(--color-text-light);
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.stat-card {
  display: flex;
  gap: 16px;
  align-items: flex-start;
  padding: 20px;
  border-radius: 16px;
  background: var(--color-white);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border: 1px solid #f0f0f0;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
}

.stat-card.speed::before {
  background: linear-gradient(90deg, #10B981, #34D399);
}

.stat-card.occupancy::before {
  background: linear-gradient(90deg, #F59E0B, #FBBF24);
}

.stat-card.delay::before {
  background: linear-gradient(90deg, #8B5CF6, #A78BFA);
}

.stat-card.distance::before {
  background: linear-gradient(90deg, #3B82F6, #60A5FA);
}

.stat-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
  transform: translateY(-2px);
}

.stat-icon-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 64px;
  height: 64px;
  border-radius: 16px;
  flex-shrink: 0;
}

.stat-icon-wrapper.green {
  background: linear-gradient(135deg, rgba(52, 211, 153, 0.15), rgba(16, 185, 129, 0.1));
}

.stat-icon-wrapper.orange {
  background: linear-gradient(135deg, rgba(251, 191, 36, 0.15), rgba(245, 158, 11, 0.1));
}

.stat-icon-wrapper.purple {
  background: linear-gradient(135deg, rgba(167, 139, 250, 0.15), rgba(139, 92, 246, 0.1));
}

.stat-icon-wrapper.blue {
  background: linear-gradient(135deg, rgba(96, 165, 250, 0.15), rgba(59, 130, 246, 0.1));
}

.stat-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-white);
  font-weight: 600;
}

.stat-icon-wrapper.green .stat-icon {
  color: #10B981;
}

.stat-icon-wrapper.orange .stat-icon {
  color: #F59E0B;
}

.stat-icon-wrapper.purple .stat-icon {
  color: #8B5CF6;
}

.stat-icon-wrapper.blue .stat-icon {
  color: #3B82F6;
}

.stat-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-label {
  font-size: 12px;
  font-weight: 500;
  color: var(--color-text-light);
  margin: 0;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: var(--color-text-dark);
  margin: 0;
  line-height: 1.2;
}

.stat-value.late {
  color: #EF4444;
}

.stat-unit {
  font-size: 14px;
  font-weight: 500;
  color: var(--color-text-light);
  margin-left: 4px;
}

.stat-meta {
  font-size: 12px;
  color: var(--color-text-light);
  margin: 0;
}

/* Tourism Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease;
  padding: 20px;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.modal-content {
  background: var(--color-white);
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  border-radius: 20px;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  animation: slideIn 0.3s ease;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: var(--color-white);
  border-bottom: none;
}

.modal-header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
}

.close-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: var(--color-white);
  font-size: 32px;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  flex-shrink: 0;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 20px 24px;
}

.station-info {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--color-text-light);
  font-size: 14px;
  margin: 0 0 20px 0;
  padding: 12px;
  background: linear-gradient(135deg, rgba(22, 117, 231, 0.05), rgba(34, 197, 94, 0.05));
  border-radius: 8px;
  border-left: 4px solid var(--color-primary);
}

.station-info svg {
  width: 16px;
  height: 16px;
  color: var(--color-primary);
}

.modal-body h3 {
  font-size: 16px;
  font-weight: 600;
  color: var(--color-text-dark);
  margin: 0 0 16px 0;
}

.tourism-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.tourism-card {
  display: flex;
  flex-direction: column;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s ease;
  background: var(--color-white);
}

.tourism-card:hover {
  border-color: var(--color-primary);
  box-shadow: 0 4px 16px rgba(22, 117, 231, 0.12);
  transform: translateY(-2px);
}

.tourism-image {
  position: relative;
  width: 100%;
  height: 200px;
  overflow: hidden;
  background: #f0f4f8;
}

.tourism-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.tourism-card:hover .tourism-image img {
  transform: scale(1.05);
}

.tourism-rating-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  display: flex;
  align-items: center;
  gap: 4px;
  background: rgba(255, 255, 255, 0.95);
  padding: 6px 10px;
  border-radius: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  font-weight: 600;
  font-size: 13px;
}

.rating-stars {
  color: #F59E0B;
  font-size: 14px;
}

.rating-number {
  color: var(--color-text-dark);
}

.tourism-info {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.tourism-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.tourism-header h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: var(--color-text-dark);
  flex: 1;
}

.tourism-rating-detail {
  display: flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
}

.stars {
  display: flex;
  gap: 2px;
}

.star-icon {
  width: 14px;
  height: 14px;
  fill: #e5e7eb;
}

.star-icon.filled {
  fill: #F59E0B;
}

.review-count {
  font-size: 12px;
  color: var(--color-text-light);
}

.tourism-category {
  margin: 0;
  font-size: 12px;
  color: var(--color-primary);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.tourism-description {
  margin: 0;
  font-size: 13px;
  color: var(--color-text-light);
  line-height: 1.5;
}

.tourism-details {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding-top: 12px;
  border-top: 1px solid #f0f0f0;
}

.detail-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  font-size: 13px;
  color: var(--color-text-light);
  line-height: 1.4;
}

.detail-icon {
  width: 18px;
  height: 18px;
  min-width: 18px;
  color: var(--color-primary);
  margin-top: 2px;
}

/* Responsive */
@media (max-width: 768px) {
  .header-container {
    padding: 0 16px;
    height: 70px;
  }

  .tracking-container {
    padding: 16px 12px 40px;
  }

  .card {
    padding: 12px;
    margin-bottom: 12px;
  }

  .map-placeholder {
    height: 180px;
  }

  .map-container {
    height: 250px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .train-name {
    font-size: 16px;
  }

  .nav {
    gap: 12px;
  }

  .nav-link {
    font-size: 13px;
  }

  .nav-btn {
    padding: 8px 16px;
    font-size: 13px;
  }

  /* Modal responsive */
  .modal-overlay {
    padding: 16px;
    align-items: center;
    justify-content: center;
  }

  .modal-content {
    max-width: 90vw;
    max-height: 85vh;
    border-radius: 16px;
  }

  .modal-header {
    padding: 16px 20px;
  }

  .modal-header h2 {
    font-size: 18px;
  }

  .modal-body {
    padding: 16px 20px;
  }

  .tourism-card {
    display: flex;
    flex-direction: column;
  }

  .tourism-image {
    height: 160px;
  }

  .tourism-info {
    padding: 14px;
  }

  .tourism-header h4 {
    font-size: 14px;
  }

  .tourism-description {
    font-size: 12px;
  }

  .detail-item {
    font-size: 12px;
  }

  .detail-icon {
    width: 16px;
    height: 16px;
  }
}

@media (max-width: 480px) {
  .logo-text {
    display: none;
  }

  .header-container {
    padding: 0 12px;
  }

  .nav {
    gap: 8px;
  }

  .nav-link {
    display: none;
  }

  .nav-btn {
    padding: 6px 12px;
    font-size: 12px;
  }

  .map-container {
    height: 200px;
  }
}
</style>

<style>
/* Leaflet map overrides */
.map-container .leaflet-control-attribution {
  font-size: 10px;
}

.train-marker {
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%,
  100% {
    filter: drop-shadow(0 2px 4px rgba(22, 117, 231, 0.3));
  }
  50% {
    filter: drop-shadow(0 2px 8px rgba(22, 117, 231, 0.6));
  }
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Leaflet popup styling */
.leaflet-popup-content {
  font-family: 'Geist', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
  font-size: 13px !important;
  line-height: 1.4 !important;
}

.leaflet-popup-content b {
  color: #1f2937;
  font-weight: 600;
}

.leaflet-popup-content small {
  display: block;
  margin-top: 6px;
  color: #1675E7;
  font-size: 11px;
}

.leaflet-popup-content-wrapper {
  border-radius: 8px !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

.leaflet-popup-tip {
  background-color: white !important;
}

.leaflet-container a.leaflet-popup-close-button {
  width: 26px !important;
  height: 26px !important;
  font-size: 22px !important;
  line-height: 26px !important;
}
</style>