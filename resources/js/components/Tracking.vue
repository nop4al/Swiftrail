<script setup>
import { reactive, computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const route = useRoute();
const mapContainer = ref(null);
let map = null;
let markerGroup = null;
let routePolyline = null;

const showTourismModal = ref(false);
const selectedStation = ref(null);
const showTourismData = ref(false);

const stations = ref([]);

function openTourismModal(station) {
  console.log('Opening tourism modal for station:', station);
  selectedStation.value = station;
  showTourismModal.value = true;
  showTourismData.value = false; // Show button first
  
  // Debug: log tourism data
  if (station?.station?.tourism) {
    console.log('Tourism data found:', station.station.tourism);
  } else {
    console.warn('No tourism data for this station');
  }
}

function checkDestinations() {
  showTourismData.value = true;
}

function testModal() {
  console.log('Test modal button clicked');
  if (stations.value.length > 1) {
    const testStation = stations.value[1]; // Cirebon
    console.log('Testing with station:', testStation);
    openTourismModal(testStation);
  } else {
    alert('No stations data available');
  }
}

const train = reactive({
  name: 'Loading...',
  number: '-',
  from: '-',
  to: '-',
  startTime: '--:--',
  endTime: '--:--',
  currentKm: 0,
  totalKm: 800,
  speed: 0,
  maxSpeed: 160,
  occupancy: 0,
  occupancyLabel: 'Unknown',
  delayMinutes: 0
});

const markerStation = reactive({});

// Route coordinates from API
const routeCoordinates = [];

// Calculate distance between two coordinates (haversine formula)
function calculateDistance(lat1, lng1, lat2, lng2) {
  const R = 6371; // Earth radius in km
  const dLat = ((lat2 - lat1) * Math.PI) / 180;
  const dLng = ((lng2 - lng1) * Math.PI) / 180;
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos((lat1 * Math.PI) / 180) *
      Math.cos((lat2 * Math.PI) / 180) *
      Math.sin(dLng / 2) *
      Math.sin(dLng / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
}

// Calculate total distance from all coordinates
function calculateTotalDistance(coords) {
  let total = 0;
  for (let i = 0; i < coords.length - 1; i++) {
    total += calculateDistance(
      coords[i][0],
      coords[i][1],
      coords[i + 1][0],
      coords[i + 1][1]
    );
  }
  return total;
}

// Parse time to minutes
function parseTimeToMinutes(timeStr) {
  if (!timeStr) return 0;
  const [hours, minutes] = timeStr.split(':').map(Number);
  return hours * 60 + minutes;
}

// Get current progress based on time
function updateTrainProgress() {
  if (stations.value.length === 0) return;

  const now = new Date();
  const currentTimeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
  const currentMinutes = parseTimeToMinutes(currentTimeStr);
  
  const startMinutes = parseTimeToMinutes(train.startTime);
  const endMinutes = parseTimeToMinutes(train.endTime);
  
  // Handle overnight routes
  let totalMinutes = endMinutes - startMinutes;
  if (totalMinutes < 0) totalMinutes += 24 * 60;
  
  let elapsedMinutes = currentMinutes - startMinutes;
  if (elapsedMinutes < 0) elapsedMinutes += 24 * 60;
  
  // If journey not started or already finished
  if (elapsedMinutes < 0) {
    train.currentKm = 0;
    train.speed = 0;
  } else if (elapsedMinutes > totalMinutes) {
    train.currentKm = train.totalKm;
    train.speed = 0;
  } else {
    // Calculate progress based on time
    const progress = elapsedMinutes / totalMinutes;
    train.currentKm = Math.round(train.totalKm * progress);
    train.speed = totalMinutes > 0 ? Math.round((train.totalKm / totalMinutes) * 60) : 0;
  }
}

const percent = computed(() => Math.round((train.currentKm / train.totalKm) * 100));
const remainingKm = computed(() => Math.max(0, train.totalKm - train.currentKm));
train.remainingKm = remainingKm.value;

async function fetchTrainData() {
  try {
    const trainCode = route.params.train_code;
    let scheduleId = route.query.schedule_id;
    const travelDate = route.query.date;
    
    // Fallback: parse from URL if needed
    if (!scheduleId) {
      const urlParams = new URLSearchParams(window.location.search);
      scheduleId = urlParams.get('schedule_id');
    }
    
    console.log('Tracking params:', { trainCode, scheduleId, travelDate });
    
    if (!trainCode) return;

    // If we have schedule_id from the booking, fetch data directly from booking
    let response;
    if (scheduleId) {
      console.log('Fetching booking data with schedule_id:', scheduleId);
      response = await fetch(`/api/v1/bookings/${scheduleId}`);
      
      // If booking endpoint fails, fall back to tracking endpoint
      if (!response.ok) {
        console.warn('Booking endpoint failed, falling back to tracking endpoint');
        response = await fetch(`/api/v1/tracking/${trainCode}`);
      }
    } else {
      // Fallback: fetch by train code
      console.log('Fetching tracking data with train code:', trainCode);
      response = await fetch(`/api/v1/tracking/${trainCode}`);
    }
    
    if (!response.ok) throw new Error('Failed to fetch train data');

    const data = await response.json();
    const trainData = data.data || data;
    
    console.log('Tracking data received:', trainData);

    // Fallback untuk parse response
    // Jika tidak ada `stops`, fetch dari tracking endpoint dengan train code
    if (!trainData.stops) {
      try {
        // Extract train code dari booking data atau dari params
        const code = trainData.train?.code || trainCode;
        if (code) {
          console.log('Fetching stops from tracking endpoint:', code);
          const trackingResponse = await fetch(`/api/v1/tracking/${code}`);
          
          if (trackingResponse.ok) {
            const trackingData = await trackingResponse.json();
            const trackData = trackingData.data || trackingData;
            if (trackData.stops && trackData.stops.length > 0) {
              trainData.stops = trackData.stops;
              console.log('Stops fetched from tracking endpoint:', trainData.stops.length);
            }
          }
        }
      } catch (err) {
        console.warn('Failed to fetch stops from tracking endpoint:', err);
      }
    }

    // Update train reactive object
    Object.assign(train, trainData);

    // Populate route coordinates dari stops atau dari API response
    if (trainData.stops && trainData.stops.length > 0) {
      routeCoordinates.length = 0;
      console.log('Processing stops:', trainData.stops);
      
      trainData.stops.forEach((stop, idx) => {
        const station = stop.station || stop;
        const lat = station.latitude || station.lat || parseFloat(station.latitude);
        const lng = station.longitude || station.lng || parseFloat(station.longitude);
        
        console.log(`Stop ${idx}: lat=${lat}, lng=${lng}, station:`, station);
        
        if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
          routeCoordinates.push([parseFloat(lat), parseFloat(lng)]);
        }
      });
      
      console.log('Final routeCoordinates:', routeCoordinates);
      stations.value = trainData.stops;
      
      // Calculate total distance from coordinates
      if (routeCoordinates.length > 1) {
        train.totalKm = Math.round(calculateTotalDistance(routeCoordinates));
        console.log('Calculated total distance:', train.totalKm, 'km');
      }
    } else {
      console.warn('No stops data in trainData:', trainData);
    }

    // Set current marker station
    if (trainData.station) {
      markerStation.name = trainData.station;
    }
    if (trainData.lat && trainData.lng) {
      markerStation.lat = trainData.lat;
      markerStation.lng = trainData.lng;
    }

    // Populate train data dari response
    // Handle both booking API format and tracking API format
    train.name = trainData.train?.name || trainData.name || 'Train';
    // Try multiple fields for train code
    const trainCodeFromData = trainData.train?.code || trainData.code || trainData.train_code || trainCode;
    train.number = trainCodeFromData || '';
    
    // Set from/to stations - handle both API formats
    if (trainData.stops && trainData.stops.length > 0) {
      // Format dari tracking API
      train.from = trainData.stops[0].station.name;
      train.to = trainData.stops[trainData.stops.length - 1].station.name;
      train.startTime = trainData.stops[0].departure_time || trainData.stops[0].arrival_time || '00:00';
      train.endTime = trainData.stops[trainData.stops.length - 1].arrival_time || trainData.stops[trainData.stops.length - 1].departure_time || '00:00';
    } else if (trainData.from_station && trainData.to_station) {
      // Format dari booking API
      train.from = trainData.from_station?.name || trainData.from_station || 'Unknown';
      train.to = trainData.to_station?.name || trainData.to_station || 'Unknown';
      train.startTime = trainData.schedule?.departure_time || '00:00';
      train.endTime = trainData.schedule?.arrival_time || '00:00';
    } else {
      train.from = 'Jakarta';
      train.to = 'Surabaya';
      train.startTime = '14:30';
      train.endTime = '22:00';
    }
    
    train.currentKm = trainData.overall_progress || 0;
    train.totalKm = trainData.total_distance || 800;
    train.speed = trainData.speed || 0;
    train.maxSpeed = 160; // Default max speed for trains
    train.occupancy = trainData.occupancy || 0;
    train.occupancyLabel = train.occupancy > 75 ? 'Penuh' : train.occupancy > 50 ? 'Agak Penuh' : train.occupancy > 25 ? 'Sedang' : 'Kosong';
    train.delayMinutes = trainData.delay_minutes || 0;
  } catch (error) {
    console.error('Error fetching train data:', error);
    train.name = 'Error Loading Train Data';
    train.from = 'Error';
    train.to = 'Error';
  }
}

function initMap() {
  if (!mapContainer.value) {
    console.error('Map container not found');
    return;
  }
  
  console.log('Initializing map. Route coordinates:', routeCoordinates);
  console.log('Stations:', stations.value);
  
  if (routeCoordinates.length === 0) {
    console.warn('No route coordinates available');
  }

  // Create map centered on current train position
  const centerLat = markerStation.lat || -6.2088;
  const centerLng = markerStation.lng || 106.8456;
  
  map = L.map(mapContainer.value).setView([centerLat, centerLng], 8);
  
  console.log('Map created with center:', [centerLat, centerLng]);

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19,
    tileSize: 256
  }).addTo(map);

  markerGroup = L.featureGroup().addTo(map);

  // Add route polyline if we have coordinates
  if (routeCoordinates.length > 1) {
    console.log('Adding route polylines');
    
    // Add background glow effect - orange/yellow color
    L.polyline(routeCoordinates, {
      color: '#F59E0B',
      weight: 12,
      opacity: 0.2,
      lineCap: 'round',
      lineJoin: 'round'
    }).addTo(map);

    // Add main route line - bold orange
    routePolyline = L.polyline(routeCoordinates, {
      color: '#F59E0B',
      weight: 6,
      opacity: 0.95,
      lineCap: 'round',
      lineJoin: 'round',
      className: 'route-line'
    }).addTo(map);

    // Add animated dashed line overlay - lighter orange
    L.polyline(routeCoordinates, {
      color: '#FCD34D',
      weight: 3,
      opacity: 0.7,
      dashArray: '10, 5',
      lineCap: 'round',
      lineJoin: 'round',
      className: 'route-line-animated'
    }).addTo(map);
    
    console.log('Route polylines added successfully');
  } else {
    console.warn('Not enough coordinates for polyline. Length:', routeCoordinates.length);
  }

  // Add markers for each station from API data
  if (stations.value && stations.value.length > 0) {
    console.log('Adding station markers:', stations.value.length);
    
    stations.value.forEach((stop, idx) => {
    const station = stop.station;
    let color = '#F59E0B'; // middle - orange
    
    if (idx === 0) color = '#10B981'; // start (green)
    if (idx === stations.value.length - 1) color = '#EF4444'; // end (red)

    console.log(`Adding marker ${idx}: ${station.name} at [${station.latitude}, ${station.longitude}]`);

    // Determine marker size based on position
    const markerRadius = idx === 0 || idx === stations.value.length - 1 ? 10 : 8;

    const marker = L.circleMarker([station.latitude, station.longitude], {
      radius: markerRadius,
      fillColor: color,
      color: '#fff',
      weight: 3,
      opacity: 1,
      fillOpacity: 0.9,
      interactive: true
    }).addTo(map);

    // Add click handler BEFORE adding to map
    marker.on('click', function(e) {
      console.log('Marker clicked:', station.name);
      console.log('Stop data:', stop);
      console.log('Has tourism:', stop.station?.tourism);
      L.DomEvent.stopPropagation(e);
      openTourismModal(stop);
    });

    // Add glow effect around marker
    L.circleMarker([station.latitude, station.longitude], {
      radius: markerRadius + 6,
      fillColor: 'transparent',
      color: color,
      weight: 1,
      opacity: 0.3,
      fillOpacity: 0,
      className: 'marker-glow'
    }).addTo(map);
    });
    
    console.log('Station markers added');
  } else {
    console.warn('No stations to add');
  }

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

  L.marker([markerStation.lat || -6.2088, markerStation.lng || 106.8456], { icon: trainIcon })
    .bindPopup(
      `<b>${markerStation.name || 'Current Position'}</b><br>Kecepatan: ${train.speed || 0} km/h`
    )
    .addTo(map);

  // Fit bounds to show entire route
  if (routeCoordinates.length > 1) {
    const bounds = L.latLngBounds(routeCoordinates);
    map.fitBounds(bounds, { padding: [50, 50] });
    console.log('Map bounds fitted');
  }
}

onMounted(async () => {
  await fetchTrainData();
  initMap();
  
  // Update progress every second
  updateTrainProgress();
  const updateInterval = setInterval(updateTrainProgress, 1000);
  
  // Cleanup on unmount
  return () => {
    clearInterval(updateInterval);
  };
});
</script>

<template>
  <div class="tracking-page">
    <div class="tracking-container">

    <!-- Map Card -->
    <section class="card map-card">
      <div class="card-title">
        <svg class="icon" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 11 7 11s7-5.75 7-11c0-3.87-3.13-7-7-7zM12 11.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg>
        Live Tracking - {{ train.name }}
        <button 
          class="test-modal-btn"
          @click="testModal"
          title="Test tourism modal"
        >
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
        </button>
      </div>

      <div id="map" class="map-container" ref="mapContainer">
        <!-- Leaflet map will be rendered here -->
      </div>
    </section>

    <!-- Tourism Popup Modal -->
    <div v-if="showTourismModal" class="modal-overlay" @click="showTourismModal = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <div>
            <h2>{{ selectedStation?.station?.name }}</h2>
            <div class="station-schedule-badge">
              <span v-if="selectedStation?.arrival_time" class="badge arrival">
                📍 Datang: {{ selectedStation.arrival_time }}
              </span>
              <span v-if="selectedStation?.departure_time" class="badge departure">
                🚂 Berangkat: {{ selectedStation.departure_time }}
              </span>
            </div>
          </div>
          <button class="close-btn" @click="showTourismModal = false">×</button>
        </div>
        <div class="modal-body">
          <div v-if="!showTourismData" class="check-destination-section">
            <p class="station-info">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
              Kode Stasiun: <strong>{{ selectedStation?.station?.code }}</strong>
            </p>
            <p class="station-location" style="color: #666; font-size: 13px; margin: 8px 0;">
              📍 Lokasi: {{ selectedStation?.station?.city || 'Indonesia' }}
            </p>
            <p class="description">Temukan destinasi wisata menarik di sekitar stasiun {{ selectedStation?.station?.name }}</p>
            <button class="btn-check-destination" @click="checkDestinations">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
              </svg>
              Cek Destinasi
            </button>
          </div>
          <div v-else class="tourism-section">
            <div class="station-info-detail">
              <p style="margin: 0 0 12px 0;">
                <strong>📍 {{ selectedStation?.station?.name }}</strong><br>
                <span style="color: #666; font-size: 12px;">
                  Datang: <strong style="color: #10B981;">{{ selectedStation?.arrival_time || '-' }}</strong> | 
                  Berangkat: <strong style="color: #F59E0B;">{{ selectedStation?.departure_time || '-' }}</strong>
                </span>
              </p>
            </div>
            <h3>Rekomendasi Wisata Terdekat</h3>
            <div class="tourism-list">
              <div v-if="!selectedStation?.station?.tourism || selectedStation.station.tourism.length === 0" class="no-data">
                <p>Tidak ada data wisata untuk stasiun ini</p>
              </div>
              <div v-for="(place, index) in selectedStation?.station?.tourism" :key="index" class="tourism-card">
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
                    <span>{{ place.phone }}</span>
                  </div>
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

      <!-- Test Tourism Modal Button -->
      <button 
        class="test-modal-btn-card"
        @click="testModal"
        title="Test tourism modal"
      >
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/></svg>
        Test Modal (Cirebon)
      </button>

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

      <div class="stat-card progress">
        <div class="stat-icon-wrapper blue">
          <div class="stat-icon">
            <svg viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5z"/></svg>
          </div>
        </div>
        <div class="stat-content">
          <p class="stat-label">Progress</p>
          <p class="stat-value">{{ percent }}%</p>
          <p class="stat-meta">Sisa: {{ remainingKm }} km</p>
        </div>
      </div>
    </section>

    <!-- Placeholder for additional content -->
    <!-- ...existing code... -->

    </div>
  </div>
</template>

<style scoped>
/* Card Styles */

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
  justify-content: space-between;
}

.test-modal-btn {
  width: 32px;
  height: 32px;
  padding: 0;
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.3);
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #10B981;
  flex-shrink: 0;
}

.test-modal-btn:hover {
  background: rgba(16, 185, 129, 0.2);
  border-color: #10B981;
  transform: scale(1.05);
}

.test-modal-btn svg {
  width: 18px;
  height: 18px;
}

.test-modal-btn-card {
  width: 100%;
  padding: 12px 16px;
  margin-bottom: 16px;
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
  border: 2px solid rgba(16, 185, 129, 0.3);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #10B981;
  font-weight: 600;
  font-size: 14px;
}

.test-modal-btn-card:hover {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(16, 185, 129, 0.1) 100%);
  border-color: #10B981;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}

.test-modal-btn-card svg {
  width: 20px;
  height: 20px;
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

.stat-card.progress::before {
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
  align-items: flex-start;
  padding: 20px 24px;
  background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
  color: var(--color-white);
  border-bottom: none;
}

.modal-header h2 {
  margin: 0 0 12px 0;
  font-size: 20px;
  font-weight: 700;
}

.station-schedule-badge {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-top: 8px;
}

.station-schedule-badge .badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
  background: rgba(255, 255, 255, 0.15);
}

.station-schedule-badge .badge.arrival {
  background-color: rgba(16, 185, 129, 0.25);
  color: #c6f6d5;
  border: 1px solid rgba(16, 185, 129, 0.4);
}

.station-schedule-badge .badge.departure {
  background-color: rgba(245, 158, 11, 0.25);
  color: #fef3c7;
  border: 1px solid rgba(245, 158, 11, 0.4);
}

.station-info-detail {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 14px;
  border-radius: 8px;
  margin-bottom: 16px;
  border-left: 4px solid var(--color-primary);
}

.station-info-detail p {
  margin: 4px 0;
  font-size: 13px;
  color: #333;
}

.station-info-detail strong {
  color: var(--color-primary);
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

/* Leaflet map overrides */
.map-container .leaflet-control-attribution {
  font-size: 10px;
}

.train-marker {
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
  animation: pulse 2s infinite;
}

.marker-glow {
  animation: markerPulse 2s infinite;
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

@keyframes markerPulse {
  0%,
  100% {
    opacity: 0.3;
  }
  50% {
    opacity: 0.6;
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

@keyframes dashSlide {
  0% {
    stroke-dashoffset: 0;
  }
  100% {
    stroke-dashoffset: -14;
  }
}

/* Route line animations */
.route-line {
  filter: drop-shadow(0 3px 6px rgba(245, 158, 11, 0.3));
}

.route-line-animated {
  animation: dashSlide 20s linear infinite;
  filter: drop-shadow(0 2px 4px rgba(252, 211, 77, 0.4));
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

.check-destination-section {
  text-align: center;
  padding: 20px 0;
}

.check-destination-section .description {
  color: #666;
  font-size: 14px;
  margin: 16px 0;
  line-height: 1.5;
}

.btn-check-destination {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 28px;
  background: linear-gradient(135deg, #1675E7 0%, #1565d8 100%);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(22, 117, 231, 0.3);
}

.btn-check-destination:hover {
  background: linear-gradient(135deg, #1565d8 0%, #1454c0 100%);
  box-shadow: 0 4px 12px rgba(22, 117, 231, 0.4);
  transform: translateY(-2px);
}

.btn-check-destination:active {
  transform: translateY(0);
}

.tourism-section h3 {
  margin: 16px 0 12px 0;
  font-size: 16px;
  color: #1675E7;
}
</style>