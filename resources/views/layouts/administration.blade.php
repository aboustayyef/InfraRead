<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>InfraRead</title>

    <!-- Styles -->
    <link href="https://fonts.googleapis.com/css?family=Bebas+Neue|Catamaran:400,600&display=swap" rel="stylesheet"> 
    <link href="{{ mix('css/admin.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        @include('admin.partials.navigation')
        @include('admin.partials.navtabs')
        {{--  Message --}}
        @if(session()->has('message'))
            <br>
            <div class="container">
                <div class="alert alert-info">
                    {{session()->get('message')}}
                </div>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="{{ mix('js/admin.js') }}"></script>
    <!-- Extra Scripts -->
    @yield('extra_scripts')
</body>
</html>
