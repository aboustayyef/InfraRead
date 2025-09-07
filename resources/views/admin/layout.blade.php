<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Transfer Variables from PHP to Js --}}
    <script>
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            apiToken: '{{ $api_token ?? '' }}'
        }
    </script>

    <title>InfraRead</title>

    <!-- Styles -->
    <link href="https://fonts.googleapis.com/css?family=Bebas+Neue|Catamaran:400,600&display=swap" rel="stylesheet">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <style>
        [v-cloak] {display: none}
    </style>
    <div id="app" class="min-h-screen" x-data="{ sidebarOpen: false }">
        <!-- Mobile menu button -->
        <div class="md:hidden fixed top-0 left-0 z-40 w-full bg-white border-b border-gray-200 px-4 py-3">
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>

        <!-- Mobile overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" 
             class="fixed inset-0 z-30 bg-black bg-opacity-50 md:hidden"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <div class="flex min-h-screen">
            <x-admin-sidebar />
            <div id="content" class="flex-1 px-4 py-16 md:px-12 md:py-24 overflow-y-auto pt-20 md:pt-24">
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
    <script src="{{ mix('js/app.js') }}"></script>
    <!-- Extra Scripts -->
    @yield('extra_scripts')

</body>
</html>
