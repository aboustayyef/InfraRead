<div id="sidebar-info" class="w-1/5  bg-gray-100 min-h-screen flex-shrink-0 sticky top-0 self-start border-b-0 border-r border-gray-200">
    {{--sidebar info--}}
    <div class="md:justify-between md:h-full md:flex md:flex-col">
        {{-- sidebar top --}}
    <div id="sidebar-top" class="flex flex-col h-48 md:justify-between">
            {{-- logo --}}
            <a href="/app"><img src="/img/infraread144.png" class="w-12 ml-4 mt-4" alt=""></a>

            {{-- Sources - Categories --}}
            <ul class="mt-4">
                <li
                    class="hover:bg-primary hover:text-white @if (request()->routeIs('admin.source.index')) bg-gray-200 @endif">
                    <a href="/admin/source" class="w-full pl-4 py-2 block">
                        <div class="flex space-x-4">
                            <x-ui.sources-icon classes="text-gray-400" />
                            <div>Sources</div>
                        </div>
                    </a>
                </li>
                <li
                    class="hover:bg-primary hover:text-white @if (request()->routeIs('admin.category.index')) bg-gray-200 @endif">
                    <a href="/admin/category" class="w-full pl-4 py-2 block">
                        <div class="flex space-x-4">
                            <x-ui.categories-icon classes="text-gray-400" />
                            <div>Categories</div>
                        </div>
                    </a>
                </li>
                <li
                    class="hover:bg-primary hover:text-white @if (request()->routeIs('admin.muted.index')) bg-gray-200 @endif">
                    <a href="/admin/muted" class="w-full pl-4 py-2 block">
                        <div class="flex space-x-4">
                            <x-ui.mute-icon classes="text-gray-400" />
                            <div>Muted Phrases</div>
                        </div>
                    </a>
                </li>
                <li class="hover:bg-primary hover:text-white @if (request()->routeIs('admin.token.show')) bg-gray-200 @endif">
                    <a href="{{ route('admin.token.show') }}" class="w-full pl-4 py-2 block">
                        <div class="flex space-x-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M4 2a2 2 0 00-2 2v3a2 2 0 002 2h1v2a3 3 0 006 0v-2h1a2 2 0 002-2V4a2 2 0 00-2-2H4zm4 7h4V4H4v3h4v2z"/></svg>
                            <div>API Tokens</div>
                        </div>
                    </a>
                </li>
            </ul>

        </div>
    </div>
    {{-- User --}}
    <div class="flex w-full items-center justify-between md:absolute p-4 bottom-0 left-0">
        <span class="uppercase text-sm font-bold text-gray-600">{{ Auth::user()->name }}</span>
        <a href="/logout"
            class="px-3 py-2 ml-2 text-xs uppercase bg-gray-300 rounded-lg hover:bg-primary hover:text-white">logout</a>
    </div>
</div>
