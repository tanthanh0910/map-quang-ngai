<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Bản đồ địa điểm Phường Đức Phổ - Tỉnh Quảng Ngãi - Thông tin thành phố / phường</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Xem bản đồ nhà hàng, khách sạn, bệnh viện, trường học, quán nhậu, cafe, chợ trên toàn Phường Đức Phổ, Tỉnh Quảng Ngãi. Tìm đường nhanh chóng, chính xác, hỗ trợ địa chỉ hành chính mới sau sáp nhập.">

    <link rel="stylesheet" href="{{ asset('leaflet/dist/leaflet.css') }}" />
    {{-- <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" /> --}}
    <link rel="stylesheet" href="{{ asset('leaflet/dist/MarkerCluster.css') }}" />
    {{-- <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" /> --}}
    <link rel="stylesheet" href="{{ asset('leaflet/dist/MarkerCluster.Default.css') }}" />
    <meta name="google-site-verification"
            content="mBOi32kuL--NbhuAqUUXyI42Mz7g2SfZgvSdGbJpAv8" />
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
        }

        #map {
            width: 100%;
            height: 100vh;
        }

        .info {
            padding: 6px 8px;
            font: 14px/16px Arial, Helvetica, sans-serif;
            background: white;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
        }

        /* Map control button styles */
        .map-btn {
            display: inline-block;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 13px;
            line-height: 1;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
            transition: transform .06s ease, box-shadow .12s ease, opacity .12s ease;
            color: #fff;
            background: #6c757d;
            /* default gray */
        }

        /* Specific styling for popup 'Đi tới đây' button */
        .btn-route {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 8px;
            background: linear-gradient(180deg, #06b6d4, #0891b2);
            color: #fff;
            border: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
            cursor: pointer;
            font-size: 13px;
            transition: transform .06s ease, box-shadow .12s ease, opacity .12s ease;
            min-width: 86px;
            text-align: center;
        }

        .btn-route:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.16);
        }

        .btn-route:active {
            transform: translateY(1px);
        }

        .btn-route:focus {
            outline: 2px solid rgba(2, 6, 23, 0.12);
            outline-offset: 2px;
        }

        .btn-route.btn-sm {
            padding: 5px 8px;
            font-size: 12px;
            border-radius: 6px;
            min-width: 72px;
        }

        /* spacing between popup action buttons */
        .leaflet-popup-content .btn-route+button {
            margin-left: 6px;
        }

        .map-btn:active {
            transform: translateY(1px);
        }

        .map-btn:focus {
            outline: 2px solid rgba(0, 0, 0, 0.12);
            outline-offset: 2px;
        }

        .map-btn--primary {
            background: linear-gradient(180deg, #2563eb, #1e40af);
        }

        .map-btn--accent {
            background: linear-gradient(180deg, #06b6d4, #0891b2);
        }

        .map-btn--danger {
            background: linear-gradient(180deg, #ef4444, #b91c1c);
        }

        /* small variant used in panel */
        .map-btn--sm {
            padding: 6px 8px;
            font-size: 13px;
            border-radius: 7px;
        }

        /* subtle text color for notes */
        .map-note {
            font-size: 12px;
            color: #ff8c00;
            margin-top: 6px;
        }

        /* coordinate tooltip */
        .coord-tooltip {
            position: fixed;
            left: 12px;
            bottom: 12px;
            background: rgba(255, 255, 255, 0.95);
            color: #222;
            padding: 6px 10px;
            border-radius: 4px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.15);
            font-size: 13px;
            font-family: Arial, Helvetica, sans-serif;
            z-index: 1000;
            pointer-events: auto;
        }

        /* right aligned tooltip */
        .coord-tooltip-right {
            left: auto;
            right: 12px;
            text-align: right;
            white-space: pre-line;
        }

        /* filter panel: full-height and wider, scrollable */
        #placeFilter.place-filter {
            position: fixed;
            left: 3px;
            top: 3px;
            bottom: 3px;
            width: 320px;
            height: calc(100vh - 8px);
            overflow-y: auto;
            padding: 12px;
            box-sizing: border-box;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 6px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.12);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
        }

        /* optional nicer thin scrollbar for WebKit browsers */
        #placeFilter.place-filter::-webkit-scrollbar {
            width: 8px;
        }

        #placeFilter.place-filter::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.12);
            border-radius: 4px;
        }

        #placeFilter.place-filter::-webkit-scrollbar-track {
            background: transparent;
        }

        .icon {
            width: 16px;
            height: 16px;
        }

        /* CSS cho label đặc biệt */
        .special-label {
            background: rgba(255, 255, 255, 0.95);
            color: black;
            font-weight: bold;
            font-size: 18px;
            border-radius: 8px;
            border: 2px solid black;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 4px 12px;
            text-shadow: 0 1px 2px #fff, 0 0 2px #fff;
        }


/* DESKTOP */
#placeFilter {
    position: fixed;
    left: 3px;
    top: 3px;
    bottom: 3px;
    width: 340px;
    height: calc(100vh - 8px);
    overflow-y: auto;
    padding: 12px;
    box-sizing: border-box;
    z-index: 1000;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 6px;
    box-shadow: 0 1px 6px rgba(0, 0, 0, 0.12);
    transition: transform 0.3s ease;
}

/* MOBILE */
@media (max-width: 768px) {
    #placeFilter {
        left: 0;
        top: 0;
        bottom: 0;
        height: 100vh;
        border-radius: 0;
        transform: translateX(-100%); /* ẨN HOÀN TOÀN */
    }

    #placeFilter.active {
        transform: translateX(0); /* HIỆN */
    }
}


.filter-toggle-btn {
    display: none;
        z-index: 2000;

}

/* Rescue button */
.rescue-trigger {
    display: block;
    width: 100%;
    margin: 10px 0 12px;
    padding: 10px 12px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(180deg, #ef4444, #b91c1c);
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0,0,0,0.12);
}
.rescue-trigger:hover { filter: brightness(1.05); }

/* Rescue modal */
.rescue-modal {
    position: fixed; inset: 0; z-index: 3000;
    display: flex; align-items: center; justify-content: center;
    font-family: Arial, Helvetica, sans-serif;
}
.rescue-modal__backdrop {
    position: absolute; inset: 0;
    background: rgba(0,0,0,0.45);
}
.rescue-modal__dialog {
    position: relative;
    width: min(520px, 92vw);
    max-height: 92vh;
    overflow-y: auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    padding: 18px 20px 16px;
}
.rescue-modal__header {
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 14px;
}
.rescue-modal__header h3 { margin: 0; font-size: 20px; color: #b91c1c; }
.rescue-modal__close {
    background: transparent; border: none; font-size: 26px; cursor: pointer; line-height: 1; color: #666;
}
.rescue-field { margin-bottom: 14px; }
.rescue-field > label {
    display: block; font-weight: 600; font-size: 14px; margin-bottom: 6px; color: #333;
}
.rescue-field > label .req { color: #dc2626; }
.rescue-field input[type=text],
.rescue-field input[type=tel],
.rescue-field textarea {
    width: 100%; box-sizing: border-box;
    padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;
    font-size: 14px; font-family: inherit;
}
.rescue-field textarea { resize: vertical; min-height: 70px; }
.rescue-field input[type=file] { font-size: 13px; }
.rescue-note { font-size: 12px; color: #ff8c00; margin-top: 6px; }

.rescue-tabs {
    display: inline-flex; gap: 6px; background: #f3f4f6;
    padding: 4px; border-radius: 8px; float: right; margin-top: -32px;
}
.rescue-tab {
    border: none; background: transparent;
    padding: 6px 12px; border-radius: 6px;
    font-size: 13px; cursor: pointer; color: #555;
    display: inline-flex; align-items: center; gap: 4px;
}
.rescue-tab.active { background: #fff; color: #111; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.rescue-btn-green {
    margin-top: 8px; width: 100%;
    padding: 10px 12px; border: none; border-radius: 8px;
    background: #22c55e; color: #fff; font-size: 14px; font-weight: 600;
    cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
}
.rescue-btn-green:hover { filter: brightness(1.03); }

.rescue-actions {
    display: flex; gap: 10px; justify-content: flex-end; margin-top: 8px;
}
.rescue-btn-cancel, .rescue-btn-submit {
    padding: 9px 16px; border: none; border-radius: 8px;
    font-size: 14px; cursor: pointer; font-weight: 600;
}
.rescue-btn-cancel { background: #e5e7eb; color: #111; }
.rescue-btn-submit { background: linear-gradient(180deg, #ef4444, #b91c1c); color: #fff; }
.rescue-btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
.rescue-alert {
    margin-bottom: 12px; padding: 8px 10px; border-radius: 6px;
    font-size: 13px; display: none;
}
.rescue-alert.ok { background: #dcfce7; color: #166534; display: block; }
.rescue-alert.err { background: #fee2e2; color: #991b1b; display: block; }

/* Rescue filter section */
.rescue-section {
    border: 1.5px solid #fecaca;
    background: #fff5f5;
    border-radius: 10px;
    padding: 10px 12px 12px;
    margin-bottom: 14px;
}
.rescue-section__title {
    display: flex; align-items: center; gap: 6px;
    color: #b91c1c; font-weight: 700; font-size: 17px;
    font-family: Times; margin-bottom: 8px;
}
.rescue-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 22px; height: 22px; padding: 0 7px;
    background: #dc2626; color: #fff;
    border-radius: 11px; font-size: 12px; font-weight: 700;
    font-family: Arial, Helvetica, sans-serif;
    margin-left: 4px;
}
.rescue-badge--zero { background: #9ca3af; }
.rescue-section label.rescue-check {
    display: flex; align-items: center; gap: 8px;
    font-size: 15px; cursor: pointer; padding: 6px 0;
}
.rescue-section label.rescue-check .icon { width: 18px; height: 18px; }

@media (max-width: 768px) {
    .filter-toggle-btn {
        display: flex;
        position: fixed;
        top: 14px;
        left: 14px;
        z-index: 2000; /* CAO HƠN LEAFLET */
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: none;
        background: #0d6efd;
        color: #fff;
        font-size: 22px;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
}


    </style>
</head>

<body>

    <div id="map"></div>

    <!-- coordinate tooltip -->
    {{-- <div id="coordTooltip" class="coord-tooltip" style="display:none">Lat: -, Lng: -</div> --}}
    <!-- coordinate tooltip (right) -->
    <div id="coordTooltipRight" class="coord-tooltip coord-tooltip-right" style="display:none">Lat: -, Lng: -</div>

    <!-- legend / filter UI -->
    <button id="btnToggleFilter" class="filter-toggle-btn">☰</button>

    <div id="placeFilter" class="place-filter">
        <div class="rescue-section">
            <div class="rescue-section__title">
                🆘 Yêu cầu cứu hộ
                <span id="rescueBadge" class="rescue-badge {{ $rescueCount == 0 ? 'rescue-badge--zero' : '' }}" title="Số điểm đang cần hỗ trợ">{{ $rescueCount }}</span>
            </div>
            @if(!empty($rescueType))
                <label class="rescue-check">
                    <input name="types_id" type="checkbox" value="{{ $rescueType->id }}">
                    <span>Hiển thị điểm cứu hộ trên bản đồ</span>
                    @if($rescueType->icon)
                        <img src="{{ asset('icons/' . $rescueType->icon) }}" class="icon">
                    @endif
                </label>
            @endif
            <button type="button" id="btnOpenRescue" class="rescue-trigger">🆘 Gửi cứu hộ</button>
        </div>

        <strong style="font-size: 20px; font-family: Times;">Chỉ đường tới địa điểm</strong><br />

        <!-- Routing controls: use my location, pick origin by clicking, clear route -->
        <div style="margin-top:10px;margin-bottom:12px;">
            <button id="btnLocateMe" class="map-btn map-btn--accent map-btn--sm" style="margin-right:8px">Dùng vị trí của tôi</button>
            <button id="btnPickOrigin" class="map-btn map-btn--primary map-btn--sm" style="margin-right:8px">Chọn điểm xuất phát</button>
            <div style="margin-top: 10px"><button id="btnClearRoute" class="map-btn map-btn--danger map-btn--sm">Xóa đường đi</button></div>
            <div class="map-note">Ghi chú: bấm "Chọn điểm xuất phát" rồi click vào bản đồ để đặt gốc.</div>
        </div>

        <strong style="font-size: 20px; font-family: Times;">Loại địa điểm</strong><br />
        @foreach ($filterTypes as $t)
            <label style="display:block; padding-top:10px;">
                <input name="types_id" type="checkbox" value="{{ $t->id }}"><span style="font-size: 20px; font-family: Times;"> {{ $t->name }}</span>
                @if($t->icon)
                    <img src="{{ asset('icons/' . $t->icon) }}" class="icon">
                @endif
            </label><br />
        @endforeach
        <div style="padding-bottom: 60px;">.......</div>
    </div>

    <!-- Rescue modal -->
    <div id="rescueModal" class="rescue-modal" style="display:none" aria-hidden="true">
        <div class="rescue-modal__backdrop" data-rescue-close></div>
        <div class="rescue-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="rescueTitle">
            <div class="rescue-modal__header">
                <h3 id="rescueTitle">Cứu hộ</h3>
                <button type="button" class="rescue-modal__close" data-rescue-close aria-label="Đóng">×</button>
            </div>

            <div id="rescueAlert" class="rescue-alert"></div>

            <form id="rescueForm" enctype="multipart/form-data" novalidate>
                <div class="rescue-field">
                    <label>Tên hộ <span class="req">*</span></label>
                    <input type="text" name="name" id="rescueName" required placeholder="Nhập tên hộ">
                </div>

                <div class="rescue-field">
                    <label>Vị trí <span class="req">*</span></label>
                    <div class="rescue-tabs" role="tablist">
                        <button type="button" class="rescue-tab active" data-tab="address">📍 Địa chỉ</button>
                        <button type="button" class="rescue-tab" data-tab="coord">🧭 Tọa độ</button>
                    </div>

                    <div class="rescue-tab-pane" data-pane="address">
                        <input type="text" id="rescueAddress" name="address" placeholder="Nhập địa chỉ">
                        <button type="button" id="rescueLocateMe" class="rescue-btn-green">📍 Lấy vị trí hiện tại</button>
                    </div>

                    <div class="rescue-tab-pane" data-pane="coord" style="display:none">
                        <input type="text" id="rescueCoord" placeholder="Nhập tọa độ (VD: 21.0285, 105.8542) hoặc link Google Maps">
                        <button type="button" id="rescueFromCoord" class="rescue-btn-green">📍 Lấy địa chỉ từ tọa độ</button>
                    </div>

                    <input type="hidden" name="lat" id="rescueLat">
                    <input type="hidden" name="lng" id="rescueLng">
                </div>

                <div class="rescue-field">
                    <label>Tình trạng</label>
                    <textarea name="description" rows="3" placeholder="mô tả tình trạng hiện tại của bạn"></textarea>
                </div>

                <div class="rescue-field">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" placeholder="0123456789 - 0123456789">
                </div>

                <div class="rescue-field">
                    <label>Hình ảnh</label>
                    <input type="file" name="thumbnail" accept="image/*" capture="environment">
                    <div class="rescue-note">Chụp hình ảnh hiện tại xung quanh bạn</div>
                </div>

                <div class="rescue-actions">
                    <button type="button" class="rescue-btn-cancel" data-rescue-close>Hủy</button>
                    <button type="submit" id="rescueSubmitBtn" class="rescue-btn-submit">Gửi cứu hộ</button>
                </div>
            </form>
        </div>
    </div>

    {{-- <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script> --}}
    <script src="{{ asset('leaflet/dist/leaflet194.js') }}"></script>
    {{-- <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script> --}}
    <script src="{{ asset('leaflet/dist/leaflet.markercluster.js') }}"></script>

    <!-- Leaflet Routing Machine (LRM) -->
    {{-- <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" /> --}}
    <link rel="stylesheet" href="{{ asset('leaflet/dist/leaflet-routing-machine.css') }}" />
    {{-- <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script> --}}
    <script src="{{ asset('leaflet/dist/leaflet-routing-machine.min.js') }}"></script>

    <script>
        window.typeIcons = @json(isset($filterTypes) ? $filterTypes->pluck('icon', 'id') : []);
    </script>
    <script>
        window.geojsonUrl = "{{ asset('geojson/vn_geo.json') }}";
        window.iconsBase = "{{ asset('icons') }}";
    </script>
    <script src="{{ asset('js-block/map.js') }}"></script>
    <script src="{{ asset('js-block/f12.js') }}"></script>
    <script>
        document.getElementById('btnToggleFilter').addEventListener('click', function () {
            document.getElementById('placeFilter').classList.toggle('active');
        });
        document.addEventListener('click', function (e) {
    const panel = document.getElementById('placeFilter');
    const btn = document.getElementById('btnToggleFilter');

    if (
        panel.classList.contains('active') &&
        !panel.contains(e.target) &&
        !btn.contains(e.target)
    ) {
        panel.classList.remove('active');
    }
});

    </script>

    <script>
    (function () {
        const modal     = document.getElementById('rescueModal');
        const openBtn   = document.getElementById('btnOpenRescue');
        const form      = document.getElementById('rescueForm');
        const alertBox  = document.getElementById('rescueAlert');
        const tabs      = modal.querySelectorAll('.rescue-tab');
        const panes     = modal.querySelectorAll('.rescue-tab-pane');
        const addrInput = document.getElementById('rescueAddress');
        const coordInput= document.getElementById('rescueCoord');
        const latInput  = document.getElementById('rescueLat');
        const lngInput  = document.getElementById('rescueLng');
        const submitBtn = document.getElementById('rescueSubmitBtn');

        function openModal() {
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        }
        function closeModal() {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            showAlert('', '');
        }
        function showAlert(msg, kind) {
            alertBox.textContent = msg || '';
            alertBox.className = 'rescue-alert' + (msg ? ' ' + kind : '');
        }

        openBtn.addEventListener('click', openModal);
        modal.querySelectorAll('[data-rescue-close]').forEach(el =>
            el.addEventListener('click', closeModal));
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') closeModal();
        });

        // tab switching
        tabs.forEach(t => t.addEventListener('click', function () {
            tabs.forEach(x => x.classList.remove('active'));
            t.classList.add('active');
            const target = t.getAttribute('data-tab');
            panes.forEach(p => {
                p.style.display = (p.getAttribute('data-pane') === target) ? '' : 'none';
            });
        }));

        // parse "lat,lng" or google maps link
        function parseCoords(raw) {
            if (!raw) return null;
            raw = String(raw).trim();
            // direct "lat, lng"
            let m = raw.match(/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/);
            if (m) return { lat: parseFloat(m[1]), lng: parseFloat(m[2]) };
            // google maps patterns: @lat,lng / q=lat,lng / !3dlat!4dlng / ll=lat,lng
            m = raw.match(/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/);
            if (m) return { lat: parseFloat(m[1]), lng: parseFloat(m[2]) };
            m = raw.match(/[?&](?:q|ll|destination)=(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/);
            if (m) return { lat: parseFloat(m[1]), lng: parseFloat(m[2]) };
            m = raw.match(/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/);
            if (m) return { lat: parseFloat(m[1]), lng: parseFloat(m[2]) };
            return null;
        }

        // use Nominatim for reverse/forward geocoding (OSM, free, rate-limited)
        async function reverseGeocode(lat, lng) {
            const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=vi`;
            const r = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!r.ok) throw new Error('reverse geocode failed');
            const j = await r.json();
            return j.display_name || '';
        }
        async function forwardGeocode(q) {
            const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(q)}&accept-language=vi&limit=1`;
            const r = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!r.ok) throw new Error('forward geocode failed');
            const j = await r.json();
            if (!Array.isArray(j) || !j.length) return null;
            return { lat: parseFloat(j[0].lat), lng: parseFloat(j[0].lon), display: j[0].display_name };
        }

        // Lấy vị trí hiện tại
        document.getElementById('rescueLocateMe').addEventListener('click', function () {
            if (!navigator.geolocation) {
                showAlert('Trình duyệt không hỗ trợ định vị.', 'err');
                return;
            }
            showAlert('Đang lấy vị trí...', 'ok');
            navigator.geolocation.getCurrentPosition(async function (pos) {
                const lat = pos.coords.latitude, lng = pos.coords.longitude;
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
                try {
                    const addr = await reverseGeocode(lat, lng);
                    if (addr) addrInput.value = addr;
                    showAlert('Đã lấy vị trí.', 'ok');
                } catch (e) {
                    showAlert('Đã lấy tọa độ nhưng không lấy được địa chỉ.', 'ok');
                }
            }, function (err) {
                showAlert('Không lấy được vị trí: ' + err.message, 'err');
            }, { enableHighAccuracy: true, timeout: 10000 });
        });

        // Lấy địa chỉ từ tọa độ / Google Maps link
        document.getElementById('rescueFromCoord').addEventListener('click', async function () {
            const parsed = parseCoords(coordInput.value);
            if (!parsed) {
                showAlert('Tọa độ hoặc link không hợp lệ.', 'err');
                return;
            }
            latInput.value = parsed.lat.toFixed(6);
            lngInput.value = parsed.lng.toFixed(6);
            showAlert('Đang tra địa chỉ...', 'ok');
            try {
                const addr = await reverseGeocode(parsed.lat, parsed.lng);
                if (addr) addrInput.value = addr;
                showAlert('Đã lấy địa chỉ từ tọa độ.', 'ok');
            } catch (e) {
                showAlert('Có tọa độ nhưng không tra được địa chỉ.', 'ok');
            }
        });

        // submit form
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            showAlert('', '');

            const name = form.name.value.trim();
            if (!name) { showAlert('Vui lòng nhập tên hộ.', 'err'); return; }

            // if lat/lng chưa có, thử parse từ coord input hoặc forward geocode từ address
            if (!latInput.value || !lngInput.value) {
                const parsed = parseCoords(coordInput.value);
                if (parsed) {
                    latInput.value = parsed.lat.toFixed(6);
                    lngInput.value = parsed.lng.toFixed(6);
                } else if (addrInput.value.trim()) {
                    showAlert('Đang xác định tọa độ từ địa chỉ...', 'ok');
                    try {
                        const g = await forwardGeocode(addrInput.value.trim());
                        if (g) { latInput.value = g.lat.toFixed(6); lngInput.value = g.lng.toFixed(6); }
                    } catch (err) { /* ignore */ }
                }
            }

            if (!latInput.value || !lngInput.value) {
                showAlert('Vui lòng cung cấp vị trí (địa chỉ hoặc tọa độ).', 'err');
                return;
            }

            submitBtn.disabled = true;
            showAlert('Đang gửi...', 'ok');

            try {
                const fd = new FormData(form);
                const r = await fetch('/api/places/support', {
                    method: 'POST',
                    body: fd,
                    headers: { 'Accept': 'application/json' }
                });
                const j = await r.json().catch(() => ({}));
                if (!r.ok) {
                    const msg = j.message || (j.errors ? Object.values(j.errors).flat().join(', ') : 'Gửi thất bại.');
                    showAlert(msg, 'err');
                } else {
                    showAlert('Đã gửi yêu cầu cứu hộ thành công!', 'ok');
                    form.reset();
                    latInput.value = ''; lngInput.value = '';
                    setTimeout(closeModal, 1500);
                }
            } catch (err) {
                showAlert('Lỗi mạng: ' + err.message, 'err');
            } finally {
                submitBtn.disabled = false;
            }
        });
    })();
    </script>
    

</body>

</html>
