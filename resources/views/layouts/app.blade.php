<!DOCTYPE html>
<html lang="en">

@include('layouts.head')

<body>
    @include('partials.sidebar')
    <div class="wrapper d-flex flex-column min-vh-100">
        @include('partials.header')
        <div class="body flex-grow-1">
            @yield('content')
        </div>
        <!-- @include('partials.footer') -->
    </div>
    @include('layouts.js')
</body>

</html>
