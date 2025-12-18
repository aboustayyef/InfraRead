@php($title = 'Admin Overview')
@extends('admin.layout')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500">Welcome back, {{ Auth::user()->name ?? 'Admin' }}.</p>
                <h1 class="text-2xl font-semibold text-gray-900 mt-1">InfraRead Admin</h1>
                <p class="text-gray-600 mt-2">Keep an eye on sources, categories, and API access from one place.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="/app/admin/sources" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-md shadow hover:bg-red-700 transition-colors">
                    Manage sources
                </a>
                <a href="{{ route('admin.token.show') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md hover:border-primary hover:text-primary transition-colors">
                    API tokens
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="/app/admin/sources" class="group block bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Sources</p>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['sources']) }}</div>
                    <p class="text-sm text-gray-500 mt-1">RSS/JSON feeds configured</p>
                </div>
                <div class="p-2 rounded-lg bg-primary bg-opacity-10 text-primary">
                    <x-ui.sources-icon classes="text-primary h-6 w-6" />
                </div>
            </div>
        </a>

        <a href="/app/admin/categories" class="group block bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Categories</p>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['categories']) }}</div>
                    <p class="text-sm text-gray-500 mt-1">Buckets to organize sources</p>
                </div>
                <div class="p-2 rounded-lg bg-primary bg-opacity-10 text-primary">
                    <x-ui.categories-icon classes="text-primary h-6 w-6" />
                </div>
            </div>
        </a>

        <a href="{{ route('admin.token.show') }}" class="group block bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">API Tokens</p>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['tokens']) }}</div>
                    <p class="text-sm text-gray-500 mt-1">Active tokens for integrations</p>
                </div>
                <div class="p-2 rounded-lg bg-primary bg-opacity-10 text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7a4.5 4.5 0 10-9 0v3.5M6.75 10.5A2.25 2.25 0 004.5 12.75v6A2.25 2.25 0 006.75 21h10.5A2.25 2.25 0 0019.5 18.75v-6a2.25 2.25 0 00-2.25-2.25h-10.5z" />
                    </svg>
                </div>
            </div>
        </a>

        <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Posts</p>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['posts']) }}</div>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ number_format($stats['unread_posts']) }} unread across sources
                    </p>
                </div>
                <div class="p-2 rounded-lg bg-primary bg-opacity-10 text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2.25-9.75V18A2.25 2.25 0 0115 20.25H6.75A2.25 2.25 0 014.5 18V6.75A2.25 2.25 0 016.75 4.5h5.25L17.25 8z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white border border-gray-100 rounded-xl shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Shortcuts</h2>
                <span class="text-xs uppercase tracking-wide text-gray-500">Navigation</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="/app/admin/sources" class="flex items-center justify-between px-4 py-3 border border-gray-200 rounded-lg hover:border-primary hover:shadow-sm transition">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Manage sources</p>
                        <p class="text-xs text-gray-500">Add, remove, or edit feed inputs.</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="/app/admin/categories" class="flex items-center justify-between px-4 py-3 border border-gray-200 rounded-lg hover:border-primary hover:shadow-sm transition">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Organize categories</p>
                        <p class="text-xs text-gray-500">Group sources and keep things tidy.</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="{{ route('admin.token.show') }}" class="flex items-center justify-between px-4 py-3 border border-gray-200 rounded-lg hover:border-primary hover:shadow-sm transition">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">API tokens</p>
                        <p class="text-xs text-gray-500">Generate or revoke access for clients.</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="/api-tester" class="flex items-center justify-between px-4 py-3 border border-gray-200 rounded-lg hover:border-primary hover:shadow-sm transition">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">API tester</p>
                        <p class="text-xs text-gray-500">Send sample requests with your tokens.</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">API Readiness</h2>
                <span class="text-xs uppercase tracking-wide text-gray-500">Status</span>
            </div>
            <ul class="space-y-3">
                <li class="flex items-start space-x-3">
                    <span class="mt-1 h-2 w-2 rounded-full bg-green-500"></span>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Token available</p>
                        <p class="text-xs text-gray-500">Use <code>Authorization: Bearer &lt;token&gt;</code> for API calls.</p>
                    </div>
                </li>
                <li class="flex items-start space-x-3">
                    <span class="mt-1 h-2 w-2 rounded-full bg-green-500"></span>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Endpoints ready</p>
                        <p class="text-xs text-gray-500">Manage feeds via the Sources and Categories tools.</p>
                    </div>
                </li>
                <li class="flex items-start space-x-3">
                    <span class="mt-1 h-2 w-2 rounded-full bg-green-500"></span>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Test locally</p>
                        <p class="text-xs text-gray-500">Hit the API tester to verify responses before shipping.</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
