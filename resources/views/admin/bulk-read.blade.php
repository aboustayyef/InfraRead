@php($title = 'Mark as Read Tools')
@extends('admin.layout')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-6 space-y-2">
        <h1 class="text-2xl font-semibold text-gray-900">Mark as Read</h1>
        <p class="text-sm text-gray-600">Use these bulk actions when you need to clear the backlog from the admin side. Each action asks for confirmation before running.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Mark everything</h2>
                <span class="text-xs uppercase tracking-wide text-red-600 font-semibold">Use carefully</span>
            </div>
            <p class="text-sm text-gray-600">Marks every unread post in the system as read.</p>
            <form method="POST" action="{{ route('vue.admin.mark-read.all') }}" onsubmit="return confirm('Mark ALL posts as read? This cannot be undone.');">
                @csrf
                <button type="submit" class="w-full inline-flex justify-center px-4 py-2 bg-primary text-white rounded-md shadow hover:bg-red-700 transition-colors">Mark all as read</button>
            </form>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Older than</h2>
                <span class="text-xs uppercase tracking-wide text-gray-500">By age</span>
            </div>
            <p class="text-sm text-gray-600">Mark unread posts that are older than the chosen number of days.</p>
            <form method="POST" action="{{ route('vue.admin.mark-read.older-than') }}" class="space-y-3" onsubmit="return confirm('Mark posts older than this threshold as read?');">
                @csrf
                <label class="block text-sm font-medium text-gray-700">Days ago</label>
                <input type="number" name="days" min="1" max="3650" value="{{ old('days', 7) }}" class="w-full border border-gray-300 rounded-md p-2 focus:border-primary focus:ring-primary">
                @error('days')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
                <button type="submit" class="w-full inline-flex justify-center px-4 py-2 bg-gray-900 text-white rounded-md shadow hover:bg-gray-800 transition-colors">Mark older items as read</button>
            </form>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-5 space-y-4 md:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Keep the latest, clear the rest</h2>
                <span class="text-xs uppercase tracking-wide text-gray-500">By count</span>
            </div>
            <p class="text-sm text-gray-600">Keep the newest N posts unread and mark everything older as read.</p>
            <form method="POST" action="{{ route('vue.admin.mark-read.except-latest') }}" class="space-y-3 md:flex md:items-end md:space-y-0 md:space-x-3" onsubmit="return confirm('Mark all but the most recent posts as read?');">
                @csrf
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700">Number to keep unread</label>
                    <input type="number" name="keep_latest" min="0" max="5000" value="{{ old('keep_latest', 20) }}" class="w-full border border-gray-300 rounded-md p-2 focus:border-primary focus:ring-primary">
                    @error('keep_latest')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:w-48">
                    <button type="submit" class="w-full inline-flex justify-center px-4 py-2 bg-gray-900 text-white rounded-md shadow hover:bg-gray-800 transition-colors">Mark others as read</button>
                </div>
            </form>
            <p class="text-xs text-gray-500">Example: enter 50 to keep the latest 50 posts unread and mark everything older as read.</p>
        </div>
    </div>
</div>
@endsection
