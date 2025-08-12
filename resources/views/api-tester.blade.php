@php($title = 'API Tester')
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">API Tester (Temporary)</h2>
    </x-slot>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow rounded p-4 space-y-2">
            <h3 class="text-lg font-semibold">Auth Token</h3>
            <p class="text-xs text-gray-600">Paste a Sanctum personal access token (Bearer) to have it sent with requests. Stored only in this page's memory.</p>
            <input id="apitoken" type="password" placeholder="paste token here" class="w-full border rounded p-2 font-mono text-xs" autocomplete="off">
            <div class="flex items-center gap-2 text-xs text-gray-500"><button id="toggle-token-visibility" type="button" class="underline">show</button><span id="token-status">No token set</span></div>
        </div>
        <div class="bg-white shadow rounded p-4 space-y-4">
            <h3 class="text-lg font-semibold">Posts</h3>
            <form id="form-posts" class="space-y-2">
                <div class="grid md:grid-cols-4 gap-4">
                    <label class="block text-sm">Include
                        <input name="include" class="mt-1 w-full border rounded p-1" placeholder="source,category">
                    </label>
                    <label class="block text-sm">Filter Read
                        <select name="filter_read" class="mt-1 w-full border rounded p-1">
                            <option value="">-- any --</option>
                            <option value="0">Unread (0)</option>
                            <option value="1">Read (1)</option>
                        </select>
                    </label>
                    <label class="block text-sm">Filter Source ID
                        <input name="filter_source" class="mt-1 w-full border rounded p-1" type="number" min="1">
                    </label>
                    <label class="block text-sm">Filter Category ID
                        <input name="filter_category" class="mt-1 w-full border rounded p-1" type="number" min="1">
                    </label>
                </div>
                <div class="grid md:grid-cols-4 gap-4">
                    <label class="block text-sm">Sort
                        <select name="sort" class="mt-1 w-full border rounded p-1">
                            <option value="-posted_at">Newest First (-posted_at)</option>
                            <option value="posted_at">Oldest First (posted_at)</option>
                        </select>
                    </label>
                    <label class="block text-sm">Page Size
                        <input name="page_size" class="mt-1 w-full border rounded p-1" type="number" value="20" min="1" max="200">
                    </label>
                    <label class="block text-sm">Page
                        <input name="page" class="mt-1 w-full border rounded p-1" type="number" value="1" min="1">
                    </label>
                </div>
                <button class="bg-indigo-600 text-white px-4 py-1 rounded" type="submit">GET /api/v1/posts</button>
            </form>
            <div class="text-xs text-gray-500" id="posts-url"></div>
            <pre class="bg-gray-900 text-green-200 text-sm p-3 rounded overflow-auto max-h-80" id="posts-output">(response)</pre>
        </div>

        <div class="bg-white shadow rounded p-4 space-y-4">
            <h3 class="text-lg font-semibold">Post Detail + Summary</h3>
            <form id="form-post-show" class="flex flex-wrap gap-4 items-end">
                <label class="block text-sm">Post ID
                    <input name="post_id" class="mt-1 w-28 border rounded p-1" type="number" min="1" required>
                </label>
                <label class="block text-sm">Include
                    <input name="include" class="mt-1 w-48 border rounded p-1" placeholder="source,category">
                </label>
                <button class="bg-indigo-600 text-white px-4 py-1 rounded" type="submit">GET /api/v1/posts/:id</button>
            </form>
            <div class="text-xs text-gray-500" id="post-url"></div>
            <pre class="bg-gray-900 text-green-200 text-sm p-3 rounded overflow-auto max-h-72" id="post-output">(response)</pre>

            <form id="form-post-summary" class="flex flex-wrap gap-4 items-end">
                <label class="block text-sm">Post ID
                    <input name="post_id" class="mt-1 w-28 border rounded p-1" type="number" min="1" required>
                </label>
                <label class="block text-sm">Sentences
                    <input name="sentences" class="mt-1 w-24 border rounded p-1" type="number" value="2" min="1" max="10">
                </label>
                <button class="bg-purple-600 text-white px-4 py-1 rounded" type="submit">POST /api/v1/posts/:id/summary</button>
            </form>
            <div class="text-xs text-gray-500" id="summary-url"></div>
            <pre class="bg-gray-900 text-green-200 text-sm p-3 rounded overflow-auto max-h-56" id="summary-output">(response)</pre>
        </div>

        <div class="bg-white shadow rounded p-4 space-y-4">
            <h3 class="text-lg font-semibold">Sources & Categories</h3>
            <div class="flex flex-wrap gap-4">
                <button id="btn-sources" class="bg-indigo-600 text-white px-4 py-1 rounded">GET /api/v1/sources?include=category</button>
                <button id="btn-categories" class="bg-indigo-600 text-white px-4 py-1 rounded">GET /api/v1/categories</button>
            </div>
            <pre class="bg-gray-900 text-green-200 text-sm p-3 rounded overflow-auto max-h-72" id="meta-output">(response)</pre>
        </div>

        <div class="text-center text-xs text-gray-400 pt-4">Temporary internal tool - remove later.</div>
    </div>

    <script>
        function q(sel){return document.querySelector(sel);}    
        function fmt(obj){return JSON.stringify(obj, null, 2);} 
        function buildPostsUrl(params){
            const usp = new URLSearchParams();
            if(params.include) usp.set('include', params.include);
            if(params.filter_read!=='' && params.filter_read!=null) usp.set('filter[read]', params.filter_read);
            if(params.filter_source) usp.set('filter[source]', params.filter_source);
            if(params.filter_category) usp.set('filter[category]', params.filter_category);
            if(params.sort) usp.set('sort', params.sort);
            if(params.page_size) usp.set('page.size', params.page_size);
            if(params.page) usp.set('page', params.page);
            return '/api/v1/posts' + (usp.toString() ? ('?'+usp.toString()) : '');
        }
        let bearerToken = '';
        function buildAuthHeaders(base={}){
            const h = Object.assign({'Accept':'application/json'}, base);
            if(bearerToken){ h['Authorization'] = 'Bearer '+bearerToken; }
            return h;
        }
        const baseGetOpts = () => ({headers: buildAuthHeaders(), credentials:'same-origin'});

        // Token input handling
        const tokenInput = q('#apitoken');
        const tokenStatus = q('#token-status');
        const toggleVis = q('#toggle-token-visibility');
        toggleVis.addEventListener('click', () => {
            if(tokenInput.type === 'password'){ tokenInput.type='text'; toggleVis.textContent='hide'; }
            else { tokenInput.type='password'; toggleVis.textContent='show'; }
        });
        tokenInput.addEventListener('input', () => {
            bearerToken = tokenInput.value.trim();
            tokenStatus.textContent = bearerToken ? 'Token set ('+bearerToken.slice(0,8)+'...)' : 'No token set';
        });

        // List posts
        q('#form-posts').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const params = Object.fromEntries(fd.entries());
            const url = buildPostsUrl(params);
            q('#posts-url').textContent = url;
            q('#posts-output').textContent = 'Loading...';
            try {
                const r = await fetch(url, baseGetOpts());
                const j = await r.json();
                q('#posts-output').textContent = fmt(j);
            } catch(err){ q('#posts-output').textContent = err; }
        });

        // Show single post
        q('#form-post-show').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const id = fd.get('post_id');
            const include = fd.get('include');
            const url = '/api/v1/posts/' + id + (include ? ('?include='+encodeURIComponent(include)) : '');
            q('#post-url').textContent = url;
            q('#post-output').textContent = 'Loading...';
            try {
                const r = await fetch(url, baseGetOpts());
                const j = await r.json();
                q('#post-output').textContent = fmt(j);
            } catch(err){ q('#post-output').textContent = err; }
        });

        // Summary
        q('#form-post-summary').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const id = fd.get('post_id');
            const sentences = fd.get('sentences');
            const url = '/api/v1/posts/' + id + '/summary';
            q('#summary-url').textContent = url;
            q('#summary-output').textContent = 'Loading...';
            try {
                const r = await fetch(url, {method:'POST', credentials:'same-origin', headers: buildAuthHeaders({'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}), body: JSON.stringify({sentences: parseInt(sentences)})});
                const j = await r.json();
                q('#summary-output').textContent = fmt(j);
            } catch(err){ q('#summary-output').textContent = err; }
        });

        // Sources & Categories
        q('#btn-sources').addEventListener('click', async () => {
            q('#meta-output').textContent = 'Loading sources...';
            try { const r = await fetch('/api/v1/sources?include=category', baseGetOpts()); q('#meta-output').textContent = fmt(await r.json()); } catch(e){ q('#meta-output').textContent = e; }
        });
        q('#btn-categories').addEventListener('click', async () => {
            q('#meta-output').textContent = 'Loading categories...';
            try { const r = await fetch('/api/v1/categories', baseGetOpts()); q('#meta-output').textContent = fmt(await r.json()); } catch(e){ q('#meta-output').textContent = e; }
        });
    </script>
</x-app-layout>
