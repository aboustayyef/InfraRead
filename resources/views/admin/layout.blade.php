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
        
        <div class="p-4 lg:p-0 lg:flex">
            {{-- Sidebar --}}
            <div id="sidebar-info" class="w-full bg-gray-100 lg:w-64 lg:min-h-screen lg:fixed">
                {{--sidebar info--}}
                <div class="py-20 pl-10 pr-20 lg:justify-between lg:h-full lg:flex lg:flex-col">
                    {{-- sidebar top --}}
                    <div id="sidebar-top" class="flex flex-col h-48 lg:justify-between">
                        {{-- logo --}}
                        <a href="/app"><img src="/img/infraread144.png" class="w-12" alt=""></a>
                        <ul class="mt-4 lg:mt-0">
                            <li class="flex">
                                <a href="/admin/source">Sources</a>
                                <a href="{{route('admin.source.create')}}" class="block px-3 ml-2 text-gray-400 bg-gray-200 rounded-md hover:text-white hover:bg-primary">+</a>
                            </li>
                            <li class="flex mt-4">
                                <a href="/admin/category">Categories</a>   
                                <a href="{{route('admin.category.create')}}" class="block px-3 ml-2 text-gray-400 bg-gray-200 rounded-md hover:text-white hover:bg-primary">+</a>
                            </li> 
                        </ul>
                    </div>
                    {{-- User --}}
                    <div class="flex items-center lg:absolute bottom-8">
                        {{ Auth::user()->name }} 
                        <a href="/logout" class="px-3 py-1 ml-2 text-xs uppercase bg-gray-300 rounded-lg hover:bg-primary hover:text-white">logout</a>
                    </div>
                </div>
            </div>

            <div id="content" class="w-full max-w-4xl pb-24 mt-20 lg:pl-72">
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
