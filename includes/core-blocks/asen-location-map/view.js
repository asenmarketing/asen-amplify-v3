// Leaflet map per block; coordinates are geocoded server-side and passed as data attributes.
// Leaflet (data-leaflet) and the tiles load only once a map is within 500px of the viewport.
const escape = (s) => s.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

let leaflet;
const loadLeaflet = (src) => (leaflet ??= typeof L !== 'undefined' ? Promise.resolve() : new Promise((resolve, reject) => {
  const script = document.createElement('script');
  script.src = src;
  script.onload = resolve;
  script.onerror = reject;
  document.head.append(script);
}));

const initMap = (el) => {
  const lat = parseFloat(el.dataset.lat);
  const lon = parseFloat(el.dataset.lon);

  const map = L.map(el).setView([lat, lon], 16);
  L.tileLayer(el.dataset.tiles, {
    maxZoom: parseInt(el.dataset.maxZoom, 10) || 19,
    attribution: el.dataset.attribution || '',
  }).addTo(map);

  const markerOptions = {};
  if (el.dataset.marker) {
    const w = parseInt(el.dataset.markerWidth, 10) || 40;
    const h = parseInt(el.dataset.markerHeight, 10) || 40;
    markerOptions.icon = L.icon({ iconUrl: el.dataset.marker, iconSize: [w, h], iconAnchor: [w / 2, h], popupAnchor: [0, -h] });
  }

  let popup = '';
  if (el.dataset.name) popup += '<span class="business-name">' + escape(el.dataset.name) + '</span><br>';
  popup += '<span class="address">' + escape(el.dataset.address) + '</span>';
  if (el.dataset.directions) popup += '<br><a class="directions-link" href="' + escape(el.dataset.directions) + '" target="_blank" rel="noopener">Get Directions</a>';

  L.marker([lat, lon], markerOptions).addTo(map).bindPopup(popup).openPopup();
};

const observer = new IntersectionObserver((entries) => {
  entries.forEach(({ isIntersecting, target }) => {
    if (!isIntersecting) return;
    observer.unobserve(target);
    loadLeaflet(target.dataset.leaflet).then(() => initMap(target));
  });
}, { rootMargin: '500px 0px' });

document.querySelectorAll('.asen-location-map .map[data-lat]').forEach((el) => observer.observe(el));
