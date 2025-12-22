// JavaScript chính cho bản đồ Việt Nam (Leaflet, cluster, filter, popup, label đặc biệt)
// Được tách ra từ vietnam.blade.php để dễ bảo trì

// Đảm bảo chỉ chạy khi DOM đã sẵn sàng
// window.addEventListener('DOMContentLoaded', function() {
      // URL to GeoJSON. Place your file under public/geojson/vn_geo.json
        // var geojsonUrl = "{{ asset('geojson/vn_geo.json') }}";
        var geojsonUrl = window.geojsonUrl;

        // initialize map centered on Quảng Ngãi (Quảng Ngãi city area)
        var map = L.map('map').setView([14.805588, 108.958969], 13);

        // base layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            // attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // info control
        var info = L.control({
            position: 'topright'
        });

        info.onAdd = function(map) {
            this._div = L.DomUtil.create('div', 'info');
            this.update();
            return this._div;
        };

        info.update = function(props) {
            this._div.innerHTML = '<h4>Thông tin hành chính</h4>' + (props ? formatSelectedProps(props) :
            'một khu vực');
        };

        info.addTo(map);

        // style for polygons (thin, light borders)
        function style(feature) {
            return {
                weight: 0.8, // thin border
                opacity: 0.9,
                color: '#cfcfcf', // light gray
                fillOpacity: 0, // transparent fill
                fillColor: 'transparent'
            };
        }

        // highlight on hover (slightly stronger but still subtle)
        function highlightFeature(e) {
            var layer = e.target;

            layer.setStyle({
                weight: 1.6,
                color: '#8f8f8f',
                fillOpacity: 0
            });

            if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
                layer.bringToFront();
            }

            info.update(layer.feature.properties);
        }

        function resetHighlight(e) {
            geojsonLayer.resetStyle(e.target);
            info.update();
        }

        function onEachFeature(feature, layer) {
            // bind popup with selected properties only (Tên, Tên cũ)
            var props = feature.properties || {};
            var popupContent = formatSelectedProps(props, true);
            // prevent Leaflet from auto-panning when opening the popup
            layer.bindPopup(popupContent, { autoPan: false });

            layer.on({
                mouseover: highlightFeature,
                mouseout: resetHighlight,
                click: function (e) {
                    // Log the clicked latitude/longitude for this feature
                    console.log('Feature clicked at', 'e.latlng');
                    // show coordinates in UI tooltip (left)
                    showCoords(e.latlng);
                    // also show coordinates in bottom-right tooltip
                    showCoordsRight(e.latlng);
                    // Open the popup but do not pan the map
                    this.openPopup();
                }
            });
        }

        // Replace generic formatter with a selector that shows only the desired fields
        function formatSelectedProps(props, asHtml) {
            // common property keys to try
            var name = props.name || props.NAME || props.ten || props.TEN || props.name_1 || props.NAME_1 || '';
            var oldName = props.oldName || props.old_name || props.old || props.oldname || props['old name'] || '';

            var lines = [];
            if (name) lines.push('<strong>Tên mới:</strong> ' + escapeHtml(String(name)));
            if (oldName) lines.push('<strong>Tên cũ:</strong> ' + escapeHtml(String(oldName)));
            if (lines.length === 0) lines.push('<em>Không có thông tin tên</em>');

            if (asHtml) return '<div style="max-height:200px;overflow:auto;">' + lines.join('<br/>') + '</div>';
            return lines.join('<br/>');
        }

        function escapeHtml(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>\"]/g, function(m) {
                return map[m];
            });
        }

        // show coordinates in the floating left tooltip
        function showCoords(latlng) {
            try {
                var t = document.getElementById('coordTooltip');
                if (!t) return;
                t.style.display = 'block';
                var latText = (latlng && (latlng.lat || latlng.lat === 0)) ? latlng.lat.toFixed(6) : '-';
                var lngText = (latlng && (latlng.lng || latlng.lng === 0)) ? latlng.lng.toFixed(6) : '-';
                t.textContent = 'Lat: ' + latText + ', Lng: ' + lngText;
            } catch (e) {
                console.warn('showCoords error', e);
            }
        }

        // show coordinates in the bottom-right tooltip (multi-line)
        function showCoordsRight(latlng) {
            try {
                var t = document.getElementById('coordTooltipRight');
                if (!t) return;
                t.style.display = 'block';
                var latText = (latlng && (latlng.lat || latlng.lat === 0)) ? latlng.lat.toFixed(6) : '-';
                var lngText = (latlng && (latlng.lng || latlng.lng === 0)) ? latlng.lng.toFixed(6) : '-';
                t.textContent = 'Lat: ' + latText + '\nLng: ' + lngText;
            } catch (e) {
                console.warn('showCoordsRight error', e);
            }
        }

        var geojsonLayer = L.geoJSON(null, {
            style: style,
            onEachFeature: onEachFeature
        }).addTo(map);

        // cluster group
        var markersCluster = L.markerClusterGroup();
        map.addLayer(markersCluster);

        // base path for icons (public/icons)
        // var iconsBase = "{{ asset('icons') }}";
        var iconsBase = window.iconsBase;

        // mapping of place_type id => icon filename (from server-side $filterTypes)
        // var typeIcons = @json(isset($filterTypes) ? $filterTypes->pluck('icon', 'id') : []);
        var typeIcons = window.typeIcons || {};

        // icon factory using public/icons SVGs; prefer per-place `p.icon` when provided
        function iconByType(typeOrId) {
            var defaultUrl = iconsBase + '/marker-green.svg';
            var url = defaultUrl;

            // if numeric id provided, look up in typeIcons mapping
            if (typeOrId !== null && typeOrId !== undefined && String(typeOrId).match(/^\d+$/)) {
                var id = String(typeOrId);
                if (typeIcons && typeIcons[id]) {
                    url = iconsBase + '/' + typeIcons[id];
                }
                return L.icon({
                    iconUrl: url,
                    iconSize: [32, 48],
                    iconAnchor: [16, 48],
                    popupAnchor: [0, -40]
                });
            }

            return L.icon({
                iconUrl: url,
                iconSize: [32, 48],
                iconAnchor: [16, 48],
                popupAnchor: [0, -40]
            });
        }

        // choose icon for a place object; prefer p.icon (filename stored in DB), then type_id mapping
        function iconForPlace(p) {
            try {
                if (p && p.icon) {
                    return L.icon({
                        iconUrl: iconsBase + '/' + p.icon,
                        iconSize: [32, 48],
                        iconAnchor: [16, 48],
                        popupAnchor: [0, -40]
                    });
                }
            } catch (e) {
                console.warn('iconForPlace error', e);
            }

            // prefer type_id (numeric) returned by API
            var typeId = (p && (p.type_id || p.typeId || p.type_id === 0)) ? p.type_id : null;
            if (typeId) return iconByType(typeId);

            // fallback to type_name or legacy type
            var t = (p && p.type_name) ? p.type_name : (p && p.type) ? p.type : null;
            return iconByType(t);
        }

        var loadedPlaces = [];

        // Routing globals
        var routingControl = null;
        var userLat = null, userLng = null;
        var originMarker = null;
        var pickOriginMode = false;
        var pickCaptureHandler = null; // for capture-phase click listener
        // multi-select routing (start/end)
        var markersById = {}; // placeId => marker
        var selectedStart = null; // {id, name, lat, lng}
        var selectedEnd = null;
        var startOverlay = null, endOverlay = null;

        // Enable pick-origin mode using a capture-phase click listener so clicks on markers are also captured
        function enablePickOriginMode() {
            if (pickOriginMode) return;
            pickOriginMode = true;
            var btn = document.getElementById('btnPickOrigin');
            if (btn) {
                btn.textContent = 'Đang chọn - Click vào bản đồ';
                btn.style.marginTop = '10px';
            }
            // handler receives native MouseEvent; convert to latlng
            pickCaptureHandler = function(ev) {
                try {
                    // compute latlng from mouse event
                    var latlng = map.mouseEventToLatLng(ev);
                    if (latlng) {
                        setOrigin(latlng.lat, latlng.lng, 'Gốc (được chọn)');
                    }
                } catch (err) { console.warn('pickCaptureHandler error', err); }
                // disable pick mode after one selection
                disablePickOriginMode();
                // stop propagation to avoid opening popups after selection
                try { ev.stopPropagation(); } catch(e) {}
            };
            // use capture phase so we get the event before Leaflet/markers can stop propagation
            map.getContainer().addEventListener('click', pickCaptureHandler, true);
        }

        function disablePickOriginMode() {
            if (!pickOriginMode) return;
            pickOriginMode = false;
            var btn = document.getElementById('btnPickOrigin');
            if (btn) {
                btn.textContent = 'Chọn điểm xuất phát';
                btn.style.marginTop = '';
            }
            try {
                if (pickCaptureHandler) {
                    map.getContainer().removeEventListener('click', pickCaptureHandler, true);
                }
            } catch (e) { console.warn('disablePickOriginMode remove listener', e); }
            pickCaptureHandler = null;
        }

        function setOrigin(lat, lng, label) {
            userLat = parseFloat(lat);
            userLng = parseFloat(lng);
            if (originMarker) map.removeLayer(originMarker);
            originMarker = L.marker([userLat, userLng], {
                title: label || 'Origin',
                opacity: 0.95
            }).addTo(map).bindTooltip(label || 'Origin').openTooltip();
        }

        // clear any existing routing control
        function clearRoute() {
            if (routingControl) {
                try { map.removeControl(routingControl); } catch (e) { console.warn('clearRoute removeControl', e); }
                routingControl = null;
            }
        }

        // route from current origin (userLat,userLng) to provided lat/lng
        function routeToTarget(lat, lng) {
            if (userLat === null || userLng === null) {
                alert('Vui lòng chọn điểm xuất phát (Dùng vị trí của tôi hoặc Chọn điểm xuất phát) trước khi tìm đường.');
                return;
            }
            clearRoute();
            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(userLat, userLng),
                    L.latLng(parseFloat(lat), parseFloat(lng))
                ],
                router: L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1' }),
                lineOptions: { styles: [{ color: 'blue', opacity: 0.85, weight: 5 }] },
                addWaypoints: false,
                fitSelectedRoute: true,
                showAlternatives: false,
                createMarker: function(i, wp, nWps) {
                    return L.marker(wp.latLng, { draggable: false });
                }
            }).addTo(map);
        }

        function updateSelectionUI() {
            document.getElementById('selStartName').textContent = selectedStart ? (selectedStart.name || selectedStart.id) : '—';
            document.getElementById('selEndName').textContent = selectedEnd ? (selectedEnd.name || selectedEnd.id) : '—';
            // draw small overlays for start/end
            if (startOverlay) { try { map.removeLayer(startOverlay); } catch(e){} startOverlay = null; }
            if (endOverlay) { try { map.removeLayer(endOverlay); } catch(e){} endOverlay = null; }
            if (selectedStart) {
                startOverlay = L.circleMarker([selectedStart.lat, selectedStart.lng], {radius:8, color:'#0b84ff', fillColor:'#0b84ff', fillOpacity:0.9}).addTo(map);
            }
            if (selectedEnd) {
                endOverlay = L.circleMarker([selectedEnd.lat, selectedEnd.lng], {radius:8, color:'#ff3b30', fillColor:'#ff3b30', fillOpacity:0.9}).addTo(map);
            }
        }

        function setStartByPlace(place) {
            selectedStart = { id: place.id, name: place.name, lat: parseFloat(place.lat), lng: parseFloat(place.lng) };
            updateSelectionUI();
        }

        function setEndByPlace(place) {
            selectedEnd =
            updateSelectionUI();
        }

        function clearSelections() {
            selectedStart = null; selectedEnd = null; updateSelectionUI(); clearRoute();
        }

        function swapSelections() {
            var tmp = selectedStart; selectedStart = selectedEnd; selectedEnd = tmp; updateSelectionUI();
        }

        function routeBetweenSelected() {
            if (!selectedStart || !selectedEnd) {
                alert('Vui lòng chọn cả điểm bắt đầu và kết thúc');
                return;
            }
            routeBetweenCoords(selectedStart.lat, selectedStart.lng, selectedEnd.lat, selectedEnd.lng);
        }

        function routeBetweenCoords(slat, slng, dlat, dlng) {
            clearRoute();
            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(parseFloat(slat), parseFloat(slng)),
                    L.latLng(parseFloat(dlat), parseFloat(dlng))
                ],
                router: L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1' }),
                lineOptions: { styles: [{ color: 'green', opacity: 0.85, weight: 5 }] },
                addWaypoints: false,
                fitSelectedRoute: true,
                showAlternatives: false,
                createMarker: function(i, wp, nWps) {
                    return L.marker(wp.latLng, { draggable: false });
                }
            }).addTo(map);
        }

        function loadPlaces() {
            fetch('/api/places')
                .then(r => r.json())
                .then(data => {
                    loadedPlaces = data;
                    // console.log('Loaded places:', loadedPlaces, loadedPlaces.length);
                    renderPlaces();
                }).catch(err => console.error('Load places failed', err));
        }

        function renderPlaces() {
            markersCluster.clearLayers();
            var checked = Array.from(document.querySelectorAll('#placeFilter input[type=checkbox]:checked')).map(function(
            i) {
                return i.value;
            });
            var filterActive = checked.length > 0;
            loadedPlaces.forEach(function(p) {
                var lat = parseFloat(p.lat);
                var lng = parseFloat(p.lng);
                if (isNaN(lat) || isNaN(lng)) return;
                // if filter is active, require type_id match; otherwise show all
                if (filterActive) {
                    // if place has no type_id, still show it
                    if (p.type_id && checked.indexOf(String(p.type_id)) === -1) return; // filtered out by type_id
                }
                var m = L.marker([lat, lng], {
                    icon: iconForPlace(p)
                });
                // keep reference for selecting start/end later
                if (p.id !== undefined && p.id !== null) {
                    markersById[String(p.id)] = m;
                    m._placeId = p.id;
                    m._place = p;
                }
                m.bindPopup(popupForPlace(p), {
                    autoPan: false
                });
                m.on('click', function(e) {
                    // if user is in pick-origin mode and clicks a marker, treat it as origin selection
                    if (pickOriginMode) {
                        try {
                            setOrigin(e.latlng.lat, e.latlng.lng, 'Gốc (được chọn)');
                            pickOriginMode = false;
                            var pickBtn = document.getElementById('btnPickOrigin');
                            if (pickBtn) pickBtn.textContent = 'Chọn điểm xuất phát';
                            // give brief feedback
                            try { e.target.closePopup && e.target.closePopup(); } catch(e){}
                        } catch(err) { console.warn('Error setting origin from marker click', err); }
                        return;
                    }
                    showCoords(e.latlng);
                    console.log('Place clicked:', 'p.id, e.latlng');
                });

                // when popup opens, attach click handler for route button inside popup
                m.on('popupopen', function(e) {
                    try {
                        var popupEl = e.popup.getElement();
                        if (!popupEl) return;
                        var btn = popupEl.querySelector('.btn-route');
                        if (btn) {
                            btn.addEventListener('click', function(ev) {
                                ev.preventDefault();
                                var lat = parseFloat(this.dataset.lat);
                                var lng = parseFloat(this.dataset.lng);
                                // If user hasn't set origin yet, prompt to use locate
                                if (userLat === null || userLng === null) {
                                    // try to get browser location first
                                    map.locate({ setView: false, watch: false });
                                }
                                routeToTarget(lat, lng);
                            });
                        }
                        // attach set start / set end handlers for all matching buttons
                        var btnStarts = popupEl.querySelectorAll('.btn-set-start');
                        btnStarts.forEach(function(bs) {
                            bs.addEventListener('click', function(ev) {
                                ev.preventDefault();
                                var pid = this.dataset.id;
                                var lat = parseFloat(this.dataset.lat);
                                var lng = parseFloat(this.dataset.lng);
                                var name = this.dataset.name || '';
                                if (!isNaN(lat) && !isNaN(lng)) {
                                    setStartByPlace({ id: pid, name: name, lat: lat, lng: lng });
                                } else if (pid && markersById[pid] && markersById[pid]._place) {
                                    setStartByPlace(markersById[pid]._place);
                                }
                                try { map.closePopup(); } catch(e) {}
                            });
                        });
                        var btnEnds = popupEl.querySelectorAll('.btn-set-end');
                        btnEnds.forEach(function(be) {
                            be.addEventListener('click', function(ev) {
                                ev.preventDefault();
                                var pid = this.dataset.id;
                                var lat = parseFloat(this.dataset.lat);
                                var lng = parseFloat(this.dataset.lng);
                                var name = this.dataset.name || '';
                                if (!isNaN(lat) && !isNaN(lng)) {
                                    setEndByPlace({ id: pid, name: name, lat: lat, lng: lng });
                                } else if (pid && markersById[pid] && markersById[pid]._place) {
                                    setEndByPlace(markersById[pid]._place);
                                }
                                try { map.closePopup(); } catch(e) {}
                            });
                        });
                    } catch (e) { console.warn('popupopen route attach error', e); }
                });

                markersCluster.addLayer(m);
            });
        }

        // build popup HTML for a place including thumbnail, time, description
         function popupForPlace(p) {
             var parts = [];
             try {
                 if (p.thumbnail) {
                     var url = (String(p.thumbnail).match(/^https?:\/\//)) ? p.thumbnail : (p.thumbnail);
                     parts.push('<div style="text-align:center;margin-bottom:6px;"><img src="' + url +
                         '" style="max-width:230px;max-height:140px;border-radius:6px;display:block;margin:0 auto;"/></div>'
                         );
                 }
 
                 parts.push('<div style="font-weight:600;margin-bottom:4px;">' + escapeHtml(p.name || '') + '</div>');
 
                 if (p.time) parts.push('<div style="color:#666;margin-bottom:4px;">Thời gian: <em>' + escapeHtml(p.time) +
                     '</em></div>');
 
                 if (p.address) parts.push('<div>Địa chỉ: ' + escapeHtml(p.address) + '</div>');
 
                 if (p.description) parts.push('<div style="margin-top:6px;color:#333;">Mô tả: ' + escapeHtml(p.description) +
                     '</div>');
 
                 if (p.phone) parts.push('<div style="margin-top:6px;font-size:13px;">Tel: ' + escapeHtml(p.phone) +
                     '</div>');
 
                 // add route button
                 if (p.lat && p.lng) {
                     parts.push('<div style="margin-top:8px;text-align:center;display:flex;gap:6px;justify-content:center;">');
                     parts.push('<button class="btn-route btn btn-sm" data-lat="' + escapeHtml(String(p.lat)) + '" data-lng="' + escapeHtml(String(p.lng)) + '">Đi tới đây</button>');
                    //  parts.push('<button class="btn-set-start btn btn-sm" data-id="' + escapeHtml(String(p.id)) + '" data-lat="' + escapeHtml(String(p.lat)) + '" data-lng="' + escapeHtml(String(p.lng)) + '" data-name="' + escapeHtml(String(p.name || '')) + '">Đặt làm bắt đầu</button>');
                    //  parts.push('<button class="btn-set-end btn btn-sm" data-id="' + escapeHtml(String(p.id)) + '" data-lat="' + escapeHtml(String(p.lat)) + '" data-lng="' + escapeHtml(String(p.lng)) + '" data-name="' + escapeHtml(String(p.name || '')) + '">Đặt làm kết thúc</button>');
                     parts.push('</div>');
                 }
 
             } catch (e) {
                 console.warn('popupForPlace error', e);
             }
             return '<div style="max-width:260px;line-height:1.25;">' + parts.join('') + '</div>';
         }

        // filter checkbox events
        document.querySelectorAll('#placeFilter input[type=checkbox]').forEach(function(ch) {
            ch.addEventListener('change', function() {
                renderPlaces();
            });
        });

        // locate button and pick-origin handlers
        document.getElementById('btnLocateMe').addEventListener('click', function() {
            map.locate({ setView: true, maxZoom: 16 });
        });

        document.getElementById('btnPickOrigin').addEventListener('click', function() {
            // toggle enhanced pick-origin mode
            if (pickOriginMode) disablePickOriginMode(); else enablePickOriginMode();
        });

        document.getElementById('btnClearRoute').addEventListener('click', function() {
            clearRoute();
            if (originMarker) { try { map.removeLayer(originMarker); } catch (e) {} originMarker = null; }
            userLat = userLng = null;
            pickOriginMode = false;
            document.getElementById('btnPickOrigin').textContent = 'Chọn điểm xuất phát';
        });

        // when browser returns location found
        map.on('locationfound', function(e) {
            try {
                setOrigin(e.latlng.lat, e.latlng.lng, 'Vị trí của bạn');
                // if pickOriginMode was set, disable it
                pickOriginMode = false;
                document.getElementById('btnPickOrigin').textContent = 'Chọn điểm xuất phát';
            } catch (err) { console.warn('locationfound handler', err); }
        });

        // map click: show coords (normal behavior). When using capture-based pickOriginMode, selection handled by capture listener.
        map.on('click', function(e) {
            console.log('Map clicked at', e.latlng);
            showCoords(e.latlng);
        });

        // initial load
        loadPlaces();

        // load GeoJSON
        fetch(geojsonUrl).then(function(res) {
            if (!res.ok) throw new Error('Không thể tải GeoJSON: ' + res.status);
            return res.json();
        }).then(function(data) {
            geojsonLayer.addData(data);
            // Hiển thị label đặc biệt cho Hoàng Sa/Trường Sa
            if (data && data.features && Array.isArray(data.features)) {
                data.features.forEach(function(f) {
                    var props = f.properties || {};
                    var name = (props.name || props.NAME || props.ten || props.TEN || '').toLowerCase();
                    if (name.includes('hoàng sa') || name.includes('trường sa')) {
                        // Tìm tâm của polygon/multipolygon
                        var latlng = null;
                        if (f.geometry && f.geometry.type && f.geometry.coordinates) {
                            try {
                                var coords = f.geometry.coordinates;
                                if (f.geometry.type === 'Polygon') {
                                    latlng = getPolygonCenter(coords[0]);
                                } else if (f.geometry.type === 'MultiPolygon') {
                                    latlng = getPolygonCenter(coords[0][0]);
                                }
                            } catch (e) { latlng = null; }
                        }
                        if (latlng) {
                            L.tooltip({
                                permanent: true,
                                direction: 'center',
                                className: 'special-label',
                                interactive: false
                            })
                            .setContent(props.name || props.NAME || props.ten || props.TEN)
                            .setLatLng(latlng)
                            .addTo(map);
                        }
                    }
                });
            }
        }).catch(function(err) {
            console.error(err);
            alert('Lỗi khi tải dữ liệu bản đồ. Vui lòng kiểm tra đường dẫn geojson.');
        });

        // Hàm lấy tâm polygon đơn giản (trung bình các điểm)
        function getPolygonCenter(coords) {
            var lat = 0, lng = 0, n = coords.length;
            coords.forEach(function(pt) { lat += pt[1]; lng += pt[0]; });
            return L.latLng(lat/n, lng/n);
        }
// });
