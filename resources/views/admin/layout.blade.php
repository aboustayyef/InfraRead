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
    @livewireStyles
    @livewireScripts
    <script src="{{ mix('js/admin.js') }}"></script>
</head>
<body>
    <style>
        [v-cloak] {display: none}
    </style>
    <div id="app">
        <div class="p-0 lg:flex">
            <x-admin-sidebar />
            <div id="content" class="w-full px-12 pb-24 mt-20 lg:pl-72">
                @if(session()->has('message'))
                    <div class="p-4 bg-green-100 mb-4 flex justify-between items-center" x-data="{visible:true}" x-show="visible">
                        <div>{{session()->get('message')}}</div>
                        <div x-on:keydown.window.escape="visible = false" @click="visible = false" class="text-xl cursor-pointer" >&times;</div>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>


    </div>

    <!-- Scripts -->
    <!-- Extra Scripts -->
    @yield('extra_scripts')

</body>
</html>
