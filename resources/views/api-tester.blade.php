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
            <h3 class="text-lg font-semibold">Post Mutations (Phase 2)</h3>
            
            <!-- Mark Single Post Read/Unread -->
            <form id="form-post-read-status" class="space-y-2">
                <h4 class="font-medium">Mark Post Read/Unread</h4>
                <div class="flex flex-wrap gap-4 items-end">
                    <label class="block text-sm">Post ID
                        <input name="post_id" class="mt-1 w-28 border rounded p-1" type="number" min="1" required>
                    </label>
                    <label class="block text-sm">Read Status
                        <select name="read" class="mt-1 w-32 border rounded p-1" required>
                            <option value="true">Mark Read</option>
                            <option value="false">Mark Unread</option>
                        </select>
                    </label>
                    <button class="bg-green-600 text-white px-4 py-1 rounded" type="submit">PATCH /api/v1/posts/:id/read-status</button>
                </div>
            </form>
            
            <!-- Bulk Mark Posts -->
            <form id="form-bulk-read-status" class="space-y-2">
                <h4 class="font-medium">Bulk Mark Posts Read/Unread</h4>
                <div class="flex flex-wrap gap-4 items-end">
                    <label class="block text-sm">Post IDs (comma-separated)
                        <input name="post_ids" class="mt-1 w-64 border rounded p-1" placeholder="1,2,3,4,5" required>
                    </label>
                    <label class="block text-sm">Read Status
                        <select name="read" class="mt-1 w-32 border rounded p-1" required>
                            <option value="true">Mark Read</option>
                            <option value="false">Mark Unread</option>
                        </select>
                    </label>
                    <button class="bg-green-600 text-white px-4 py-1 rounded" type="submit">PATCH /api/v1/posts/bulk-read-status</button>
                </div>
            </form>
            
            <!-- Mark All Posts -->
            <form id="form-mark-all-read" class="space-y-2">
                <h4 class="font-medium">Mark All Posts Read/Unread</h4>
                <div class="grid md:grid-cols-3 gap-4">
                    <label class="block text-sm">Read Status
                        <select name="read" class="mt-1 w-full border rounded p-1" required>
                            <option value="true">Mark Read</option>
                            <option value="false">Mark Unread</option>
                        </select>
                    </label>
                    <label class="block text-sm">Source ID (optional)
                        <input name="source_id" class="mt-1 w-full border rounded p-1" type="number" min="1">
                    </label>
                    <label class="block text-sm">Category ID (optional)
                        <input name="category_id" class="mt-1 w-full border rounded p-1" type="number" min="1">
                    </label>
                </div>
                <div class="flex flex-wrap gap-4 items-end">
                    <label class="block text-sm">Before Date (optional)
                        <input name="before_date" class="mt-1 w-40 border rounded p-1" type="date">
                    </label>
                    <button class="bg-green-600 text-white px-4 py-1 rounded" type="submit">PATCH /api/v1/posts/mark-all-read</button>
                </div>
            </form>
            
            <div class="text-xs text-gray-500" id="mutations-url"></div>
            <pre class="bg-gray-900 text-green-200 text-sm p-3 rounded overflow-auto max-h-64" id="mutations-output">(response)</pre>
        </div>

        <div class="bg-white shadow rounded p-4 space-y-4">
            <h3 class="text-lg font-semibold">Source Management (Phase 3)</h3>
            
            <!-- Create Source -->
            <form id="form-create-source" class="space-y-2">
                <h4 class="font-medium">Create Source</h4>
                <div class="grid md:grid-cols-2 gap-4">
                    <label class="block text-sm">URL (required)
                        <input name="url" class="mt-1 w-full border rounded p-1" placeholder="https://example.com/feed.xml" required>
                    </label>
                    <label class="block text-sm">Category ID (optional)
                        <input name="category_id" class="mt-1 w-full border rounded p-1" type="number" min="1">
                    </label>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <label class="block text-sm">Name (optional)
                        <input name="name" class="mt-1 w-full border rounded p-1" placeholder="Feed Name">
                    </label>
                    <label class="block text-sm">Description (optional)
                        <input name="description" class="mt-1 w-full border rounded p-1" placeholder="Feed Description">
                    </label>
                </div>
                <button class="bg-blue-600 text-white px-4 py-1 rounded" type="submit">POST /api/v1/sources</button>
            </form>
            
            <!-- Update Source -->
            <form id="form-update-source" class="space-y-2">
                <h4 class="font-medium">Update Source</h4>
                <div class="grid md:grid-cols-3 gap-4">
                    <label class="block text-sm">Source ID (required)
                        <input name="source_id" class="mt-1 w-full border rounded p-1" type="number" min="1" required>
                    </label>
                    <label class="block text-sm">Name
                        <input name="name" class="mt-1 w-full border rounded p-1" placeholder="New Name">
                    </label>
                    <label class="block text-sm">Description
                        <input name="description" class="mt-1 w-full border rounded p-1" placeholder="New Description">
                    </label>
                </div>
                <button class="bg-blue-600 text-white px-4 py-1 rounded" type="submit">PUT /api/v1/sources/:id</button>
            </form>
            
            <!-- Delete Source -->
            <form id="form-delete-source" class="space-y-2">
                <h4 class="font-medium">Delete Source</h4>
                <div class="flex flex-wrap gap-4 items-end">
                    <label class="block text-sm">Source ID
                        <input name="source_id" class="mt-1 w-28 border rounded p-1" type="number" min="1" required>
                    </label>
                    <button class="bg-red-600 text-white px-4 py-1 rounded" type="submit">DELETE /api/v1/sources/:id</button>
                </div>
            </form>
            
            <!-- Refresh Source -->
            <form id="form-refresh-source" class="space-y-2">
                <h4 class="font-medium">Refresh Source Posts</h4>
                <div class="flex flex-wrap gap-4 items-end">
                    <label class="block text-sm">Source ID
                        <input name="source_id" class="mt-1 w-28 border rounded p-1" type="number" min="1" required>
                    </label>
                    <button class="bg-purple-600 text-white px-4 py-1 rounded" type="submit">POST /api/v1/sources/:id/refresh</button>
                </div>
            </form>
            
            <div class="text-xs text-gray-500" id="sources-mgmt-url"></div>
            <pre class="bg-gray-900 text-green-200 text-sm p-3 rounded overflow-auto max-h-64" id="sources-mgmt-output">(response)</pre>
        </div>

        <div class="bg-white shadow rounded p-4 space-y-4">
            <h3 class="text-lg font-semibold">Category Management (Phase 3)</h3>
            
            <!-- Create Category -->
            <form id="form-create-category" class="space-y-2">
                <h4 class="font-medium">Create Category</h4>
                <div class="flex flex-wrap gap-4 items-end">
                    <label class="block text-sm">Description
                        <input name="description" class="mt-1 w-64 border rounded p-1" placeholder="Category Name" required>
                    </label>
                    <button class="bg-blue-600 text-white px-4 py-1 rounded" type="submit">POST /api/v1/categories</button>
                </div>
            </form>
            
            <!-- Update Category -->
            <form id="form-update-category" class="space-y-2">
                <h4 class="font-medium">Update Category</h4>
                <div class="flex flex-wrap gap-4 items-end">
                    <label class="block text-sm">Category ID
                        <input name="category_id" class="mt-1 w-28 border rounded p-1" type="number" min="1" required>
                    </label>
                    <label class="block text-sm">New Description
                        <input name="description" class="mt-1 w-64 border rounded p-1" placeholder="New Category Name" required>
                    </label>
                    <button class="bg-blue-600 text-white px-4 py-1 rounded" type="submit">PUT /api/v1/categories/:id</button>
                </div>
            </form>
            
            <!-- Delete Category -->
            <form id="form-delete-category" class="space-y-2">
                <h4 class="font-medium">Delete Category</h4>
                <div class="flex flex-wrap gap-4 items-end">
                    <label class="block text-sm">Category ID
                        <input name="category_id" class="mt-1 w-28 border rounded p-1" type="number" min="1" required>
                    </label>
                    <button class="bg-red-600 text-white px-4 py-1 rounded" type="submit">DELETE /api/v1/categories/:id</button>
                </div>
            </form>
            
            <div class="text-xs text-gray-500" id="categories-mgmt-url"></div>
            <pre class="bg-gray-900 text-green-200 text-sm p-3 rounded overflow-auto max-h-64" id="categories-mgmt-output">(response)</pre>
        </div>

        <div class="bg-white shadow rounded p-4 space-y-4">
            <h3 class="text-lg font-semibold">OPML Import/Export (Phase 3)</h3>
            
            <!-- Export OPML -->
            <div class="space-y-2">
                <h4 class="font-medium">Export OPML</h4>
                <button id="btn-export-opml" class="bg-green-600 text-white px-4 py-1 rounded">GET /api/v1/export-opml</button>
            </div>
            
            <!-- Preview OPML -->
            <form id="form-preview-opml" class="space-y-2">
                <h4 class="font-medium">Preview OPML Import</h4>
                <div class="flex flex-wrap gap-4 items-end">
                    <label class="block text-sm">OPML File
                        <input name="opml" class="mt-1 w-64 border rounded p-1" type="file" accept=".opml,.xml" required>
                    </label>
                    <button class="bg-yellow-600 text-white px-4 py-1 rounded" type="submit">POST /api/v1/preview-opml</button>
                </div>
            </form>
            
            <!-- Import OPML -->
            <form id="form-import-opml" class="space-y-2">
                <h4 class="font-medium">Import OPML</h4>
                <div class="flex flex-wrap gap-4 items-end">
                    <label class="block text-sm">OPML File
                        <input name="opml" class="mt-1 w-64 border rounded p-1" type="file" accept=".opml,.xml" required>
                    </label>
                    <label class="block text-sm">Mode
                        <select name="mode" class="mt-1 w-32 border rounded p-1">
                            <option value="replace">Replace</option>
                            <option value="merge">Merge</option>
                        </select>
                    </label>
                    <button class="bg-red-600 text-white px-4 py-1 rounded" type="submit">POST /api/v1/import-opml</button>
                </div>
            </form>
            
            <div class="text-xs text-gray-500" id="opml-url"></div>
            <pre class="bg-gray-900 text-green-200 text-sm p-3 rounded overflow-auto max-h-64" id="opml-output">(response)</pre>
        </div>

        <div class="bg-white shadow rounded p-4 space-y-4">
            <h3 class="text-lg font-semibold">Sources & Categories (Read-Only)</h3>
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

        // Post Mutations (Phase 2)
        q('#form-post-read-status').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const postId = fd.get('post_id');
            const read = fd.get('read') === 'true';
            const url = `/api/v1/posts/${postId}/read-status`;
            q('#mutations-url').textContent = url;
            q('#mutations-output').textContent = 'Loading...';
            try {
                const r = await fetch(url, {
                    method: 'PATCH',
                    headers: buildAuthHeaders({'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}),
                    body: JSON.stringify({read})
                });
                q('#mutations-output').textContent = fmt(await r.json());
            } catch(err) { q('#mutations-output').textContent = err; }
        });

        q('#form-bulk-read-status').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const postIds = fd.get('post_ids').split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id));
            const read = fd.get('read') === 'true';
            const url = '/api/v1/posts/bulk-read-status';
            q('#mutations-url').textContent = url;
            q('#mutations-output').textContent = 'Loading...';
            try {
                const r = await fetch(url, {
                    method: 'PATCH',
                    headers: buildAuthHeaders({'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}),
                    body: JSON.stringify({post_ids: postIds, read})
                });
                q('#mutations-output').textContent = fmt(await r.json());
            } catch(err) { q('#mutations-output').textContent = err; }
        });

        q('#form-mark-all-read').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const payload = {read: fd.get('read') === 'true'};
            if(fd.get('source_id')) payload.source_id = parseInt(fd.get('source_id'));
            if(fd.get('category_id')) payload.category_id = parseInt(fd.get('category_id'));
            if(fd.get('before_date')) payload.before_date = fd.get('before_date');
            const url = '/api/v1/posts/mark-all-read';
            q('#mutations-url').textContent = url;
            q('#mutations-output').textContent = 'Loading...';
            try {
                const r = await fetch(url, {
                    method: 'PATCH',
                    headers: buildAuthHeaders({'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}),
                    body: JSON.stringify(payload)
                });
                q('#mutations-output').textContent = fmt(await r.json());
            } catch(err) { q('#mutations-output').textContent = err; }
        });

        // Source Management (Phase 3)
        q('#form-create-source').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const payload = {url: fd.get('url')};
            if(fd.get('category_id')) payload.category_id = parseInt(fd.get('category_id'));
            if(fd.get('name')) payload.name = fd.get('name');
            if(fd.get('description')) payload.description = fd.get('description');
            const url = '/api/v1/sources';
            q('#sources-mgmt-url').textContent = url;
            q('#sources-mgmt-output').textContent = 'Loading...';
            try {
                const r = await fetch(url, {
                    method: 'POST',
                    headers: buildAuthHeaders({'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}),
                    body: JSON.stringify(payload)
                });
                q('#sources-mgmt-output').textContent = fmt(await r.json());
            } catch(err) { q('#sources-mgmt-output').textContent = err; }
        });

        q('#form-update-source').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const sourceId = fd.get('source_id');
            const payload = {};
            if(fd.get('name')) payload.name = fd.get('name');
            if(fd.get('description')) payload.description = fd.get('description');
            const url = `/api/v1/sources/${sourceId}`;
            q('#sources-mgmt-url').textContent = url;
            q('#sources-mgmt-output').textContent = 'Loading...';
            try {
                const r = await fetch(url, {
                    method: 'PUT',
                    headers: buildAuthHeaders({'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}),
                    body: JSON.stringify(payload)
                });
                q('#sources-mgmt-output').textContent = fmt(await r.json());
            } catch(err) { q('#sources-mgmt-output').textContent = err; }
        });

        q('#form-delete-source').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const sourceId = fd.get('source_id');
            const url = `/api/v1/sources/${sourceId}`;
            q('#sources-mgmt-url').textContent = url;
            q('#sources-mgmt-output').textContent = 'Loading...';
            try {
                const r = await fetch(url, {
                    method: 'DELETE',
                    headers: buildAuthHeaders({'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content})
                });
                q('#sources-mgmt-output').textContent = fmt(await r.json());
            } catch(err) { q('#sources-mgmt-output').textContent = err; }
        });

        q('#form-refresh-source').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const sourceId = fd.get('source_id');
            const url = `/api/v1/sources/${sourceId}/refresh`;
            q('#sources-mgmt-url').textContent = url;
            q('#sources-mgmt-output').textContent = 'Loading...';
            try {
                const r = await fetch(url, {
                    method: 'POST',
                    headers: buildAuthHeaders({'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}),
                    body: JSON.stringify({})
                });
                q('#sources-mgmt-output').textContent = fmt(await r.json());
            } catch(err) { q('#sources-mgmt-output').textContent = err; }
        });

        // Category Management (Phase 3)
        q('#form-create-category').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const payload = {description: fd.get('description')};
            const url = '/api/v1/categories';
            q('#categories-mgmt-url').textContent = url;
            q('#categories-mgmt-output').textContent = 'Loading...';
            try {
                const r = await fetch(url, {
                    method: 'POST',
                    headers: buildAuthHeaders({'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}),
                    body: JSON.stringify(payload)
                });
                q('#categories-mgmt-output').textContent = fmt(await r.json());
            } catch(err) { q('#categories-mgmt-output').textContent = err; }
        });

        q('#form-update-category').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const categoryId = fd.get('category_id');
            const payload = {description: fd.get('description')};
            const url = `/api/v1/categories/${categoryId}`;
            q('#categories-mgmt-url').textContent = url;
            q('#categories-mgmt-output').textContent = 'Loading...';
            try {
                const r = await fetch(url, {
                    method: 'PUT',
                    headers: buildAuthHeaders({'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}),
                    body: JSON.stringify(payload)
                });
                q('#categories-mgmt-output').textContent = fmt(await r.json());
            } catch(err) { q('#categories-mgmt-output').textContent = err; }
        });

        q('#form-delete-category').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const categoryId = fd.get('category_id');
            const url = `/api/v1/categories/${categoryId}`;
            q('#categories-mgmt-url').textContent = url;
            q('#categories-mgmt-output').textContent = 'Loading...';
            try {
                const r = await fetch(url, {
                    method: 'DELETE',
                    headers: buildAuthHeaders({'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content})
                });
                q('#categories-mgmt-output').textContent = fmt(await r.json());
            } catch(err) { q('#categories-mgmt-output').textContent = err; }
        });

        // OPML Operations (Phase 3)
        q('#btn-export-opml').addEventListener('click', async () => {
            q('#opml-url').textContent = '/api/v1/export-opml';
            q('#opml-output').textContent = 'Loading...';
            try {
                const r = await fetch('/api/v1/export-opml', baseGetOpts());
                q('#opml-output').textContent = fmt(await r.json());
            } catch(err) { q('#opml-output').textContent = err; }
        });

        q('#form-preview-opml').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const url = '/api/v1/preview-opml';
            q('#opml-url').textContent = url;
            q('#opml-output').textContent = 'Loading...';
            try {
                const r = await fetch(url, {
                    method: 'POST',
                    headers: buildAuthHeaders({'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}),
                    body: fd
                });
                q('#opml-output').textContent = fmt(await r.json());
            } catch(err) { q('#opml-output').textContent = err; }
        });

        q('#form-import-opml').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const url = '/api/v1/import-opml';
            q('#opml-url').textContent = url;
            q('#opml-output').textContent = 'Loading...';
            try {
                const r = await fetch(url, {
                    method: 'POST',
                    headers: buildAuthHeaders({'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}),
                    body: fd
                });
                q('#opml-output').textContent = fmt(await r.json());
            } catch(err) { q('#opml-output').textContent = err; }
        });
    </script>
</x-app-layout>
