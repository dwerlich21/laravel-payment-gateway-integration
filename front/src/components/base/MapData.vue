<template>
  <div class="map-container">
    <div
      ref="mapContainer"
      style="width: 100%; height: 100%;"
    />
    <div
      v-if="clickedPoint"
      class="map-info"
    >
      <h3>Ponto Selecionado</h3>
      <p><strong>Latitude:</strong> {{ clickedPoint.lat }}</p>
      <p><strong>Longitude:</strong> {{ clickedPoint.lng }}</p>
    </div>
  </div>
</template>

<script setup>
/* eslint-disable */
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Estado do componente
const mapContainer = ref(null); // Referência para o elemento do mapa
const map = ref(null); // Instância do mapa
const clickedPoint = ref(null); // Coordenadas do ponto clicado
const currentMarker = ref(null); // Marcador atual no mapa

// Emits
const emit = defineEmits(['point-selected']); // Emite evento quando um ponto é selecionado

// Props
const props = defineProps({
    center: {
        type: Array,
        default: () => [-23.5505, -46.6333] // São Paulo, Brasil como centro padrão
    },
    zoom: {
        type: Number,
        default: 12
    }
});

// Inicializa o mapa quando o componente é montado
onMounted(() => {
    initMap();
});

// Limpa o mapa antes de destruir o componente
onBeforeUnmount(() => {
    if (map.value) {
        map.value.remove();
    }
});

// Observa mudanças no centro do mapa
watch(() => props.center, (newCenter) => {
    if (map.value) {
        map.value.setView(newCenter, props.zoom);
    }
}, { deep: true });

// Inicializa o mapa Leaflet
function initMap() {
    if (mapContainer.value) {
        // Cria a instância do mapa
        map.value = L.map(mapContainer.value).setView(props.center, props.zoom);

        // Adiciona camada de tiles OpenStreetMap (gratuito)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map.value);

        // Adiciona evento de clique ao mapa
        map.value.on('click', handleMapClick);
    }
}

// Função para lidar com o clique no mapa
function handleMapClick(e) {
    const { lat, lng } = e.latlng;

    // Arredonda para 6 casas decimais (precisão suficiente)
    const latitude = parseFloat(lat.toFixed(6));
    const longitude = parseFloat(lng.toFixed(6));

    // Atualiza o estado com as coordenadas clicadas
    clickedPoint.value = { lat: latitude, lng: longitude };

    // Emite evento com as coordenadas
    emit('point-selected', clickedPoint.value);

    // Remove marcador atual se existir
    if (currentMarker.value) {
        map.value.removeLayer(currentMarker.value);
    }

    // Adiciona novo marcador na posição clicada
    currentMarker.value = L.marker([latitude, longitude]).addTo(map.value);
    currentMarker.value.bindPopup(`
        <div class="custom-popup">
            <h3>Localização Selecionada</h3>
            <p><strong>Latitude:</strong> ${latitude}</p>
            <p><strong>Longitude:</strong> ${longitude}</p>
        </div>
    `).openPopup();
}
</script>

<style scoped>
.map-container {
    position: relative;
    width: 100%;
    height: 600px; /* Altura fixa para o exemplo */
}

.map-info {
    position: absolute;
    bottom: 20px;
    left: 20px;
    background-color: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    max-width: 300px;
    z-index: 1000;
}

/* Estilos para popups do Leaflet (injetados globalmente) */
:global(.custom-popup h3) {
    margin: 0 0 10px 0;
    color: #333;
}

:global(.custom-popup p) {
    margin: 5px 0;
}

:global(.leaflet-popup-content-wrapper) {
    border-radius: 8px;
    padding: 5px;
}
</style>