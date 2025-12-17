<div class="sidebar sidebar-fixed border-end" id="sidebar">
    <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
 {{-- <a href="{{ route('admin.types.index') }}" class="{{ request()->routeIs('admin.types.*') ? 'active' : '' }}">Types</a>
            <a href="{{ route('admin.places.index') }}" class="{{ request()->routeIs('admin.places.*') ? 'active' : '' }}">Places</a>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Users</a>
            <a href="{{ route('admin.logout') }}">Logout</a> --}}
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.types.index') }}">
                <i class="bi bi-person-gear nav-icon" style="font-size: 16px;"></i> Type
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.places.index') }}">
                <i class="bi bi-geo-alt nav-icon" style="font-size: 16px;"></i> Place
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.users.index') }}">
                <i class="bi bi-people nav-icon" style="font-size: 16px;"></i> User
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.logout') }}">
                <i class="bi bi-box-arrow-right nav-icon" style="font-size: 16px;"></i> Logout
            </a>
        </li>
        <li class="nav-item"></li>
            <a class="btn btn-primary float-end" href="{{ route('map') }}" target="_blank">View map</a>
        </li>
    </ul>

</div>
