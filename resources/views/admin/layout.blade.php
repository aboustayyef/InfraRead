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
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        {{-- @include('admin.partials.navigation')
        @include('admin.partials.navtabs')
        {{--  Message --}}
        {{-- 
        @if(session()->has('message'))
            <br>
            <div class="container">
                <div class="alert alert-info">
                    {{session()->get('message')}}
                </div>
            </div>
        @endif --}}
        <div class="flex">
            {{-- Sidebar --}}
            <div class="bg-gray-100 w-1/8 w-64 min-h-screen">
                {{--sidebar info--}}
                <div class="py-20 pl-10 pr-20 flex flex-col justify-between h-full">
                    {{-- sidebar top --}}
                    <div class="flex flex-col h-40 justify-between">
                        {{-- logo --}}
                        <img src="/img/infraread144.png" class="w-12" alt="">
                        <ul>
                            <li class="@if(strstr(request()->path(), 'admin/source')) font-bold @else font-light text-gray-400 @endif"><a href="/admin/source">Sources</a></li>
                            <li class="mt-4 @if(strstr(request()->path(), 'admin/category')) font-bold @else font-light text-gray-400 @endif"><a href="/admin/category">Categories</a></li>    
                        </ul>
                    </div>
                    {{-- User --}}
                    <div class="flex items-center">
                        {{ Auth::user()->name }} 
                        <a href="/logout" class=" text-xs uppercase ml-2 px-3 py-1 rounded-lg bg-gray-300 hover:bg-red-500 hover:text-white">logout</a>
                    </div>
                </div>
            </div>

            <div class="flex-grow"></div>
        </div>

        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="{{ mix('js/admin.js') }}"></script>
    <!-- Extra Scripts -->
    @yield('extra_scripts')
</body>
</html>
