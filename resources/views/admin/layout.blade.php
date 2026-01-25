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
        [v-cloak], [x-cloak] {display: none}
    </style>
    <div id="app" class="min-h-screen" x-data="{ sidebarOpen: false }">
        <!-- Mobile top bar -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-white md:hidden">
            <div class="flex items-center space-x-3">
                <button x-on:click="sidebarOpen = !sidebarOpen" class="p-2 text-gray-600 border rounded-md" aria-label="Toggle navigation">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <a href="/app" class="flex items-center space-x-2">
                    <img src="/img/infraread144.png" class="w-8 h-8" alt="InfraRead logo">
                    <span class="text-sm font-semibold text-gray-700">InfraRead Admin</span>
                </a>
            </div>
            <a href="/logout" class="px-3 py-2 text-xs font-semibold uppercase bg-gray-100 rounded hover:bg-primary hover:text-white">logout</a>
        </div>

        <div class="flex min-h-screen bg-gray-50">
            <!-- Sidebar overlay (mobile) -->
            <div
                class="fixed inset-0 z-30 bg-black bg-opacity-30 md:hidden"
                x-show="sidebarOpen"
                x-on:click="sidebarOpen = false"
                x-transition.opacity
                x-cloak
            ></div>

            <!-- Sidebar -->
            <x-admin-sidebar />

            <!-- Main content -->
            <div id="content" class="flex-1 px-4 py-6 overflow-y-auto md:px-8 lg:px-12 md:py-10">
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
