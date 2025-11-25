<div
    id="sidebar-info"
    class="fixed inset-y-0 left-0 z-40 w-72 bg-gray-100 border-r border-gray-200 transform transition-transform duration-200 ease-in-out md:relative md:translate-x-0 md:w-64 md:flex-shrink-0"
    x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    x-cloak
>
    {{--sidebar info--}}
    <div class="flex flex-col justify-between h-full">
        {{-- sidebar top --}}
        <div id="sidebar-top" class="flex flex-col gap-6 p-4">
            {{-- logo --}}
            <a href="/app" class="flex items-center space-x-3">
                <img src="/img/infraread144.png" class="w-12" alt="">
                <span class="text-lg font-semibold text-gray-700 hidden md:block">InfraRead</span>
            </a>

            {{-- Sources - Categories --}}
            <ul class="space-y-2">
                <li class="hover:bg-primary hover:text-white @if (request()->is('app/admin/sources')) bg-gray-200 @endif rounded-md">
                    <a href="/app/admin/sources" class="w-full px-3 py-2 block">
                        <div class="flex space-x-4">
                            <x-ui.sources-icon classes="text-gray-400" />
                            <div>Sources</div>
                        </div>
                    </a>
                </li>
                <li class="hover:bg-primary hover:text-white @if (request()->is('app/admin/categories')) bg-gray-200 @endif rounded-md">
                    <a href="/app/admin/categories" class="w-full px-3 py-2 block">
                        <div class="flex space-x-4">
                            <x-ui.categories-icon classes="text-gray-400" />
                            <div>Categories</div>
                        </div>
                    </a>
                </li>

                <!-- API Section Divider -->
                <li class="pt-4 mt-4 border-t border-gray-300">
                    <div class="pb-2 px-3">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">API</span>
                    </div>
                </li>

                <li class="hover:bg-primary hover:text-white @if (request()->routeIs('admin.token.show')) bg-gray-200 @endif rounded-md">
                    <a href="{{ route('admin.token.show') }}" class="w-full px-3 py-2 block">
                        <div class="flex space-x-4">
                            <!-- Heroicons: identification (credential/token icon) -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                            </svg>
                            <div>API Tokens</div>
                        </div>
                    </a>
                </li>
                <li class="hover:bg-primary hover:text-white @if (request()->is('api-tester')) bg-gray-200 @endif rounded-md">
                    <a href="/api-tester" class="w-full px-3 py-2 block">
                        <div class="flex space-x-4">
                            <!-- Heroicons: beaker (for testing) -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                            <div>API Tester</div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>

        {{-- User --}}
        <div class="flex items-center justify-between w-full px-4 py-3 border-t border-gray-200">
            <span class="uppercase text-sm font-bold text-gray-600 truncate">{{ Auth::user()->name }}</span>
            <a href="/logout"
                class="px-3 py-2 text-xs uppercase bg-gray-300 rounded-lg hover:bg-primary hover:text-white">logout</a>
        </div>
    </div>
</div>
