<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Bản đồ Việt Nam - Thông tin thành phố / phường</title>

    <link rel="stylesheet" href="{{ asset('leaflet/dist/leaflet.css') }}" />
    {{-- <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" /> --}}
    <link rel="stylesheet" href="{{ asset('leaflet/dist/MarkerCluster.css') }}" />
    {{-- <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" /> --}}
    <link rel="stylesheet" href="{{ asset('leaflet/dist/MarkerCluster.Default.css') }}" />

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
            width: 340px;
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
    </style>
</head>

<body>

    <div id="map"></div>

    <!-- coordinate tooltip -->
    <div id="coordTooltip" class="coord-tooltip" style="display:none">Lat: -, Lng: -</div>
    <!-- coordinate tooltip (right) -->
    <div id="coordTooltipRight" class="coord-tooltip coord-tooltip-right" style="display:none">Lat: -, Lng: -</div>

    <!-- legend / filter UI -->
    <div id="placeFilter" class="place-filter">
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
            <label style="display:block; padding-top:10px">
                <input name="types_id" type="checkbox" value="{{ $t->id }}"><span style="font-size: 20px; font-family: Times;"> {{ $t->name }}</span>
                @if($t->icon)
                    <img src="{{ asset('icons/' . $t->icon) }}" class="icon">
                @endif
            </label><br />
        @endforeach
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
</body>

</html>
