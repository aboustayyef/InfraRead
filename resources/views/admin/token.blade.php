@extends('admin.layout')

@section('content')
<div class="max-w-3xl mx-auto py-6 px-4 space-y-6">
        @if(session('generated_token'))
            <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded">
                <p class="font-semibold">New Token Generated ({{ session('token_name') }})</p>
                <p class="text-xs mt-1">Copy it now – it will not be shown again:</p>
                <pre class="mt-2 p-2 bg-gray-900 text-green-200 text-sm rounded overflow-auto">{{ session('generated_token') }}</pre>
            </div>
        @endif
        @if(session('revoked'))
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded">
                Token "{{ session('revoked') }}" revoked.
            </div>
        @endif

        <div class="bg-white shadow rounded p-6 space-y-4">
            <h3 class="text-lg font-semibold">Generate / Regenerate Token</h3>
            <form method="POST" action="{{ route('admin.token.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Token Name</label>
                    <input type="text" name="name" value="dev" class="mt-1 w-64 border rounded p-2" />
                    <p class="text-xs text-gray-500 mt-1">If a token with this name exists it will be replaced.</p>
                </div>
                <button class="bg-indigo-600 text-white px-4 py-2 rounded">Generate Token</button>
            </form>
        </div>

        <div class="bg-white shadow rounded p-6 space-y-4">
            <h3 class="text-lg font-semibold">Existing Tokens</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b"><th class="py-1">Name</th><th class="py-1">Last Used</th><th class="py-1">Created</th><th class="py-1"></th></tr>
                </thead>
                <tbody>
                @forelse(auth()->user()->tokens as $token)
                    <tr class="border-b">
                        <td class="py-1 pr-2">{{ $token->name }}</td>
                        <td class="py-1 pr-2 text-xs">{{ optional($token->last_used_at)->diffForHumans() ?? '—' }}</td>
                        <td class="py-1 pr-2 text-xs">{{ $token->created_at->diffForHumans() }}</td>
                        <td class="py-1 text-right">
                            <form method="POST" action="{{ route('admin.token.destroy') }}" onsubmit="return confirm('Revoke this token?');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="name" value="{{ $token->name }}" />
                                <button class="text-red-600 hover:underline">Revoke</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-500 text-xs">No tokens yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="text-xs text-gray-500">
            Use the generated value as: <code>Authorization: Bearer &lt;token&gt;</code>
        </div>
    </div>
@stop
