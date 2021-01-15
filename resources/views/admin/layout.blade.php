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
</head>
<body>
    <style>
        [v-cloak] {display: none}
    </style>
    <div id="app">
        @if(session()->has('message'))
            <br>
            <div class="container">
                <div class="alert alert-info">
                    {{session()->get('message')}}
                </div>
            </div>
        @endif 
        <div class="flex">
            {{-- Sidebar --}}
            <div id="sidebar-info" class="bg-gray-100 w-1/8 w-64 min-h-screen fixed">
                {{--sidebar info--}}
                <div class="py-20 pl-10 pr-20 flex flex-col justify-between h-full">
                    {{-- sidebar top --}}
                    <div id="sidebar-top" class="flex flex-col h-48 justify-between">
                        {{-- logo --}}
                        <a href="/app/v2"><img src="/img/infraread144.png" class="w-12" alt=""></a>
                        <ul>
                            <li class="flex @if(strstr(request()->path(), 'admin/source')) font-bold @else font-light text-gray-400 @endif">
                                <a href="/admin/source">Sources</a>
                                <a href="{{route('admin.source.create')}}" class="block bg-gray-200 px-3 ml-2 text-gray-400 hover:text-white hover:bg-primary rounded-md">+</a>
                            </li>
                            <li class="mt-4 flex @if(strstr(request()->path(), 'admin/category')) font-bold @else font-light text-gray-400 @endif">
                                <a href="/admin/category">Categories</a>   
                                <a href="{{route('admin.category.create')}}" class="block bg-gray-200 px-3 ml-2 text-gray-400 hover:text-white hover:bg-primary rounded-md">+</a>
                            </li> 
                        </ul>
                    </div>
                    {{-- User --}}
                    <div class="absolute bottom-8 flex items-center">
                        {{ Auth::user()->name }} 
                        <a href="/logout" class=" text-xs uppercase ml-2 px-3 py-1 rounded-lg bg-gray-300 hover:bg-primary hover:text-white">logout</a>
                    </div>
                </div>
            </div>

            <div id="content" class="mx-auto mt-20 w-full max-w-7xl pb-24">
                @yield('content')
            </div>
        </div>

        
    </div>

    <!-- Scripts -->
    <script src="{{ mix('js/admin.js') }}"></script>
    <!-- Extra Scripts -->
    @yield('extra_scripts')
    @livewireScripts
</body>
</html>
