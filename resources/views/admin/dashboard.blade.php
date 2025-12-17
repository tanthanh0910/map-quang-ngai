<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <style>
        :root {
            --bg: #f5f7fb;
            --card: #ffffff;
            --muted: #6b7280;
            --accent: #2563eb
        }

        * {
            box-sizing: border-box
        }

        body {
            font-family: Inter, ui-sans-serif, system-ui, Arial, Helvetica, sans-serif;
            background: var(--bg);
            margin: 0;
            color: #111
        }

        .app {
            display: flex;
            min-height: 100vh
        }

        .sidebar {
            width: 220px;
            background: white;
            color: #fff;
            padding: 20px 14px;
            flex: 0 0 220px
        }

        .brand {
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 18px;
            color: black;
        }

        .sidebar a {
            display: block;
            color: black;
            padding: 8px 10px;
            border-radius: 6px;
            text-decoration: none;
            margin-bottom: 6px
        }

        .sidebar a:hover {
            background: rgb(253 233 216);
        }

        .sidebar a.active {
            background: rgb(253 233 216);
            font-weight: 600
        }

        .main {
            flex: 1;
            padding: 18px
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px
        }

        .topbar .title {
            font-size: 20px;
            font-weight: 700
        }

        .card {
            background: var(--card);
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
        }

        .toolbar {
            display: flex;
            gap: 8px;
            align-items: center
        }

        .btn {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 6px;
            background: var(--accent);
            color: #fff;
            text-decoration: none;
            border: none;
            cursor: pointer
        }

        .btn.secondary {
            background: #e6eefc;
            color: var(--accent)
        }

        .search input {
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #e6eefc
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            text-align: left
        }

        th {
            font-weight: 600;
            color: var(--muted);
            font-size: 13px
        }

        .form-row {
            display: flex;
            gap: 12px;
            margin-bottom: 12px
        }

        label {
            display: block;
            font-size: 13px;
            margin-bottom: 6px;
            color: var(--muted)
        }

        input[type=text],
        input[type=number],
        textarea,
        select {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #e6eefc
        }

        .actions form {
            display: inline
        }

        footer {
            margin-top: 24px;
            color: var(--muted);
            font-size: 13px
        }

        @media (max-width:800px) {
            .sidebar {
                display: none
            }

            .main {
                padding: 12px
            }
        }
    </style>
</head>

<body>
    <div class="app">
        <aside class="sidebar">
            <a href="{{ route('admin.types.index') }}"
                class="{{ request()->routeIs('admin.types.*') ? 'active' : '' }}">Types</a>
            <a href="{{ route('admin.places.index') }}"
                class="{{ request()->routeIs('admin.places.*') ? 'active' : '' }}">Places</a>
            <a href="{{ route('admin.users.index') }}"
                class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Users</a>
            <a href="{{ route('admin.logout') }}">Logout</a>
        </aside>
        <main class="main">
            <div class="topbar">
                <div class="title">@yield('title', 'Dashboard')</div>
                <div class="toolbar">
                    <a class="btn secondary" href="{{ route('map') }}" target="_blank">View map</a>
                </div>
            </div>

            <div class="card">
                @if (session('status'))
                    <div style="margin-bottom:12px;color:green">{{ session('status') }}</div>
                @endif
                @yield('content')
            </div>

            <footer>
                &copy; {{ date('Y') }} Map Quang Ngai
            </footer>
        </main>
    </div>
</body>

</html>
