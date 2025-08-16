@php($title = 'API Tester')
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">API Testing Interface</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Authentication Section -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Authentication</h3>
                <span id="token-status" class="text-sm text-gray-500">No token set</span>
            </div>
            <div class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bearer Token</label>
                    <input id="apitoken" type="password" placeholder="Paste your Sanctum personal access token here"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" autocomplete="off">
                </div>
                <button id="toggle-token-visibility" type="button"
                        class="px-4 py-2 text-sm font-medium text-indigo-600 bg-white border border-indigo-600 rounded-md hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Show
                </button>
            </div>
            <p class="mt-2 text-sm text-gray-500">Token is stored only in this page's memory and will be included in all API requests.</p>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <button class="api-tab active whitespace-nowrap py-4 px-1 border-b-2 border-indigo-500 font-medium text-sm text-indigo-600" data-tab="posts">
                        Posts API
                    </button>
                    <button class="api-tab whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="mutations">
                        Post Mutations
                    </button>
                    <button class="api-tab whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="sources">
                        Sources
                    </button>
                    <button class="api-tab whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="categories">
                        Categories
                    </button>
                    <button class="api-tab whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="search">
                        Search
                    </button>
                    <button class="api-tab whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="jobs">
                        Jobs
                    </button>
                    <button class="api-tab whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="metrics">
                        Metrics
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Posts Tab -->
                <div id="tab-posts" class="tab-content">
                    <div class="space-y-8">
                        <!-- Get Posts -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Get Posts</h3>
                            <form id="form-posts" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Include</label>
                                        <input name="include" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="source,category">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter Read</label>
                                        <select name="filter_read" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">-- any --</option>
                                            <option value="0">Unread (0)</option>
                                            <option value="1">Read (1)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Source ID</label>
                                        <input name="filter_source" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" type="number" min="1">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Category ID</label>
                                        <input name="filter_category" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" type="number" min="1">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort</label>
                                        <select name="sort" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="-posted_at">Newest First</option>
                                            <option value="posted_at">Oldest First</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Page Size</label>
                                        <input name="page_size" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" type="number" value="20" min="1" max="200">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Page</label>
                                        <input name="page" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" type="number" value="1" min="1">
                                    </div>
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <span class="font-mono text-xs mr-2">GET</span> /api/v1/posts
                                </button>
                            </form>
                            <div class="mt-4">
                                <div class="text-xs text-gray-500 mb-2" id="posts-url"></div>
                                <pre class="bg-gray-900 text-green-400 text-sm p-4 rounded-md overflow-auto max-h-80 border" id="posts-output">Response will appear here...</pre>
                            </div>
                        </div>

                        <!-- Get Single Post -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Get Single Post</h3>
                            <form id="form-post-show" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Post ID</label>
                                        <input name="post_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" type="number" min="1" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Include</label>
                                        <input name="include" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="source,category">
                                    </div>
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <span class="font-mono text-xs mr-2">GET</span> /api/v1/posts/:id
                                </button>
                            </form>
                            <div class="mt-4">
                                <div class="text-xs text-gray-500 mb-2" id="post-url"></div>
                                <pre class="bg-gray-900 text-green-400 text-sm p-4 rounded-md overflow-auto max-h-80 border" id="post-output">Response will appear here...</pre>
                            </div>
                        </div>

                        <!-- Generate Summary -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Generate Summary</h3>
                            <form id="form-post-summary" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Post ID</label>
                                        <input name="post_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" type="number" min="1" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Sentences</label>
                                        <input name="sentences" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" type="number" value="2" min="1" max="10">
                                    </div>
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    <span class="font-mono text-xs mr-2">POST</span> /api/v1/posts/:id/summary
                                </button>
                            </form>
                            <div class="mt-4">
                                <div class="text-xs text-gray-500 mb-2" id="summary-url"></div>
                                <pre class="bg-gray-900 text-green-400 text-sm p-4 rounded-md overflow-auto max-h-80 border" id="summary-output">Response will appear here...</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Post Mutations Tab -->
                <div id="tab-mutations" class="tab-content hidden">
                    <div class="space-y-8">
                        <!-- Mark Single Post -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Mark Single Post Read/Unread</h3>
                            <form id="form-post-read-status" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Post ID</label>
                                        <input name="post_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500" type="number" min="1" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Read Status</label>
                                        <select name="read" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500" required>
                                            <option value="true">Mark Read</option>
                                            <option value="false">Mark Unread</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <span class="font-mono text-xs mr-2">PATCH</span> /api/v1/posts/:id/read-status
                                </button>
                            </form>
                        </div>

                        <!-- Bulk Mark Posts -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Bulk Mark Posts Read/Unread</h3>
                            <form id="form-bulk-read-status" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Post IDs</label>
                                        <input name="post_ids" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500" placeholder="1,2,3,4,5" required>
                                        <p class="text-xs text-gray-500 mt-1">Comma-separated list of post IDs</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Read Status</label>
                                        <select name="read" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500" required>
                                            <option value="true">Mark Read</option>
                                            <option value="false">Mark Unread</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <span class="font-mono text-xs mr-2">PATCH</span> /api/v1/posts/bulk-read-status
                                </button>
                            </form>
                        </div>

                        <!-- Mark All Posts -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Mark All Posts Read/Unread</h3>
                            <form id="form-mark-all-read" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Read Status</label>
                                        <select name="read" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500" required>
                                            <option value="true">Mark Read</option>
                                            <option value="false">Mark Unread</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Source ID (optional)</label>
                                        <input name="source_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500" type="number" min="1">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Category ID (optional)</label>
                                        <input name="category_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500" type="number" min="1">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Before Date (optional)</label>
                                        <input name="before_date" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500" type="date">
                                    </div>
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <span class="font-mono text-xs mr-2">PATCH</span> /api/v1/posts/mark-all-read
                                </button>
                            </form>
                        </div>

                        <!-- Response Section -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Response</h3>
                            <div class="text-xs text-gray-500 mb-2" id="mutations-url"></div>
                            <pre class="bg-gray-900 text-green-400 text-sm p-4 rounded-md overflow-auto max-h-80 border" id="mutations-output">Response will appear here...</pre>
                        </div>
                    </div>
                </div>

                <!-- Sources Tab -->
                <div id="tab-sources" class="tab-content hidden">
                    <div class="space-y-8">
                        <!-- Get Sources -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Get Sources</h3>
                            <form id="form-sources" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Include</label>
                                        <input name="include" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="category">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Category ID</label>
                                        <input name="filter_category" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" type="number" min="1">
                                    </div>
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <span class="font-mono text-xs mr-2">GET</span> /api/v1/sources
                                </button>
                            </form>
                            <div class="mt-4">
                                <div class="text-xs text-gray-500 mb-2" id="sources-url"></div>
                                <pre class="bg-gray-900 text-green-400 text-sm p-4 rounded-md overflow-auto max-h-80 border" id="sources-output">Response will appear here...</pre>
                            </div>
                        </div>

                        <!-- Create Source -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Create Source</h3>
                            <form id="form-create-source" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">URL *</label>
                                        <input name="url" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="https://example.com/feed.xml" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Category ID</label>
                                        <input name="category_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" type="number" min="1">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                        <input name="name" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Feed Name">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                        <input name="description" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Feed Description">
                                    </div>
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <span class="font-mono text-xs mr-2">POST</span> /api/v1/sources
                                </button>
                            </form>
                        </div>

                        <!-- Other Source Operations -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Update Source</h4>
                                <form id="form-update-source" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Source ID</label>
                                        <input name="source_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" type="number" min="1" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                        <input name="name" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="New name">
                                    </div>
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <span class="font-mono text-xs mr-2">PUT</span> Update
                                    </button>
                                </form>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Delete Source</h4>
                                <form id="form-delete-source" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Source ID</label>
                                        <input name="source_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500" type="number" min="1" required>
                                    </div>
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <span class="font-mono text-xs mr-2">DELETE</span> Delete
                                    </button>
                                </form>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Refresh Source</h4>
                                <form id="form-refresh-source" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Source ID</label>
                                        <input name="source_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500" type="number" min="1" required>
                                    </div>
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                        <span class="font-mono text-xs mr-2">POST</span> Refresh
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Response Section -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Response</h3>
                            <div class="text-xs text-gray-500 mb-2" id="sources-mgmt-url"></div>
                            <pre class="bg-gray-900 text-green-400 text-sm p-4 rounded-md overflow-auto max-h-80 border" id="sources-mgmt-output">Response will appear here...</pre>
                        </div>
                    </div>
                </div>

                <!-- Categories Tab -->
                <div id="tab-categories" class="tab-content hidden">
                    <div class="space-y-8">
                        <!-- Get Categories -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Get Categories</h3>
                            <form id="form-categories" class="space-y-4">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <span class="font-mono text-xs mr-2">GET</span> /api/v1/categories
                                </button>
                            </form>
                            <div class="mt-4">
                                <div class="text-xs text-gray-500 mb-2" id="categories-url"></div>
                                <pre class="bg-gray-900 text-green-400 text-sm p-4 rounded-md overflow-auto max-h-80 border" id="categories-output">Response will appear here...</pre>
                            </div>
                        </div>

                        <!-- Category Operations -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Create Category</h4>
                                <form id="form-create-category" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                        <input name="description" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Category Name" required>
                                    </div>
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <span class="font-mono text-xs mr-2">POST</span> Create
                                    </button>
                                </form>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Update Category</h4>
                                <form id="form-update-category" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Category ID</label>
                                        <input name="category_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" type="number" min="1" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">New Description</label>
                                        <input name="description" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="New Name" required>
                                    </div>
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <span class="font-mono text-xs mr-2">PUT</span> Update
                                    </button>
                                </form>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Delete Category</h4>
                                <form id="form-delete-category" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Category ID</label>
                                        <input name="category_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500" type="number" min="1" required>
                                    </div>
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <span class="font-mono text-xs mr-2">DELETE</span> Delete
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Response Section -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Response</h3>
                            <div class="text-xs text-gray-500 mb-2" id="categories-mgmt-url"></div>
                            <pre class="bg-gray-900 text-green-400 text-sm p-4 rounded-md overflow-auto max-h-80 border" id="categories-mgmt-output">Response will appear here...</pre>
                        </div>
                    </div>
                </div>

                <!-- Search Tab -->
                <div id="tab-search" class="tab-content hidden">
                    <div class="space-y-8">
                        <!-- Search Posts -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Search Posts</h3>
                            <form id="form-search" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Query *</label>
                                        <input name="q" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" placeholder="Search terms" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Include</label>
                                        <input name="include" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" placeholder="source,category">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Source ID</label>
                                        <input name="filter_source" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" type="number" min="1">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Category ID</label>
                                        <input name="filter_category" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" type="number" min="1">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Read Status</label>
                                        <select name="filter_read" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500">
                                            <option value="">-- any --</option>
                                            <option value="0">Unread</option>
                                            <option value="1">Read</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    <span class="font-mono text-xs mr-2">GET</span> /api/v1/search
                                </button>
                            </form>
                            <div class="mt-4">
                                <div class="text-xs text-gray-500 mb-2" id="search-url"></div>
                                <pre class="bg-gray-900 text-green-400 text-sm p-4 rounded-md overflow-auto max-h-80 border" id="search-output">Response will appear here...</pre>
                            </div>
                        </div>

                        <!-- OPML Operations -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">OPML Operations</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="space-y-4">
                                    <h4 class="text-md font-semibold text-gray-900">Export OPML</h4>
                                    <button id="btn-export-opml" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <span class="font-mono text-xs mr-2">GET</span> Export OPML
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <h4 class="text-md font-semibold text-gray-900">Preview OPML</h4>
                                    <form id="form-preview-opml">
                                        <input name="opml" class="w-full mb-2 border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500" type="file" accept=".opml,.xml" required>
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                            <span class="font-mono text-xs mr-2">POST</span> Preview
                                        </button>
                                    </form>
                                </div>

                                <div class="space-y-4">
                                    <h4 class="text-md font-semibold text-gray-900">Import OPML</h4>
                                    <form id="form-import-opml">
                                        <input name="opml" class="w-full mb-2 border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500" type="file" accept=".opml,.xml" required>
                                        <select name="mode" class="w-full mb-2 border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                            <option value="replace">Replace</option>
                                            <option value="merge">Merge</option>
                                        </select>
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            <span class="font-mono text-xs mr-2">POST</span> Import
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="text-xs text-gray-500 mb-2" id="opml-url"></div>
                                <pre class="bg-gray-900 text-green-400 text-sm p-4 rounded-md overflow-auto max-h-80 border" id="opml-output">Response will appear here...</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jobs Tab -->
                <div id="tab-jobs" class="tab-content hidden">
                    <div class="space-y-8">
                        <!-- Refresh Source Job -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Refresh Source Job</h3>
                            <form id="form-refresh-job" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Source ID</label>
                                    <input name="source_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" type="number" min="1" required>
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <span class="font-mono text-xs mr-2">POST</span> /api/v1/jobs/sources/{id}/refresh
                                </button>
                            </form>
                        </div>

                        <!-- Generate Summary Job -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Generate Summary Job</h3>
                            <form id="form-summary-job" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Post ID</label>
                                        <input name="post_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500" type="number" min="1" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Sentences</label>
                                        <input name="sentences" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500" type="number" min="1" max="10" value="3">
                                    </div>
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    <span class="font-mono text-xs mr-2">POST</span> /api/v1/jobs/posts/{id}/summary
                                </button>
                            </form>
                        </div>

                        <!-- Job Status Checks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Summary Status</h4>
                                <form id="form-summary-status" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Cache Key</label>
                                        <input name="cache_key" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500" placeholder="summary_post_123_3" required>
                                    </div>
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                        <span class="font-mono text-xs mr-2">GET</span> Check Status
                                    </button>
                                </form>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Queue Status</h4>
                                <div class="space-y-4">
                                    <button id="btn-queue-status" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                        <span class="font-mono text-xs mr-2">GET</span> Queue Status
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Response Section -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Response</h3>
                            <div class="text-xs text-gray-500 mb-2" id="jobs-url"></div>
                            <pre class="bg-gray-900 text-green-400 text-sm p-4 rounded-md overflow-auto max-h-80 border" id="jobs-output">Response will appear here...</pre>
                        </div>
                    </div>
                </div>

                <!-- Metrics Tab -->
                <div id="tab-metrics" class="tab-content hidden">
                    <div class="space-y-8">
                        <!-- Source Metrics -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Source Metrics</h3>
                            <form id="form-source-metrics" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Source ID</label>
                                    <input name="source_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500" type="number" min="1" required>
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                    <span class="font-mono text-xs mr-2">GET</span> /api/v1/metrics/sources/{id}
                                </button>
                            </form>
                        </div>

                        <!-- System Metrics -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">System Stats</h4>
                                <button id="btn-system-stats" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-cyan-600 hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                                    <span class="font-mono text-xs mr-2">GET</span> System Stats
                                </button>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Sources Health</h4>
                                <button id="btn-sources-health" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500">
                                    <span class="font-mono text-xs mr-2">GET</span> Sources Health
                                </button>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Recent Activity</h4>
                                <button id="btn-recent-activity" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-violet-600 hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500">
                                    <span class="font-mono text-xs mr-2">GET</span> Recent Activity
                                </button>
                            </div>
                        </div>

                        <!-- Response Section -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Response</h3>
                            <div class="text-xs text-gray-500 mb-2" id="metrics-url"></div>
                            <pre class="bg-gray-900 text-green-400 text-sm p-4 rounded-md overflow-auto max-h-80 border" id="metrics-output">Response will appear here...</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const q = s => document.querySelector(s);
        const qa = s => document.querySelectorAll(s);
        const fmt = o => JSON.stringify(o, null, 2);

        let authToken = '';

        // Tab functionality
        qa('.api-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const tabName = tab.dataset.tab;

                // Update tab styles
                qa('.api-tab').forEach(t => {
                    t.classList.remove('border-indigo-500', 'text-indigo-600');
                    t.classList.add('border-transparent', 'text-gray-500');
                });
                tab.classList.remove('border-transparent', 'text-gray-500');
                tab.classList.add('border-indigo-500', 'text-indigo-600');

                // Show/hide content
                qa('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });
                q(`#tab-${tabName}`).classList.remove('hidden');
            });
        });

        // Token management
        q('#apitoken').addEventListener('input', e => {
            authToken = e.target.value.trim();
            q('#token-status').textContent = authToken ? 'Token set' : 'No token set';
        });

        q('#toggle-token-visibility').addEventListener('click', () => {
            const input = q('#apitoken');
            const btn = q('#toggle-token-visibility');
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = 'Hide';
            } else {
                input.type = 'password';
                btn.textContent = 'Show';
            }
        });

        // API Request helper
        async function apiRequest(method, url, data = null, outputElement = null, urlElement = null) {
            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };

            if (authToken) {
                headers['Authorization'] = `Bearer ${authToken}`;
            }

            const options = {
                method,
                headers
            };

            if (data) {
                if (data instanceof FormData) {
                    // Don't set Content-Type for FormData, let browser set it
                } else {
                    headers['Content-Type'] = 'application/json';
                    options.body = JSON.stringify(data);
                }
                if (data instanceof FormData) {
                    options.body = data;
                }
            }

            if (urlElement) urlElement.textContent = url;
            if (outputElement) outputElement.textContent = 'Loading...';

            try {
                const response = await fetch(url, options);
                const result = await response.json();
                if (outputElement) outputElement.textContent = fmt(result);
                return result;
            } catch (error) {
                const errorMsg = `Error: ${error.message}`;
                if (outputElement) outputElement.textContent = errorMsg;
                console.error('API Error:', error);
            }
        }

        // Posts API event handlers
        q('#form-posts').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const params = new URLSearchParams();

            for (const [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }

            const url = `/api/v1/posts${params.toString() ? '?' + params.toString() : ''}`;
            await apiRequest('GET', url, null, q('#posts-output'), q('#posts-url'));
        });

        q('#form-post-show').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const postId = formData.get('post_id');
            const include = formData.get('include');

            const params = new URLSearchParams();
            if (include) params.append('include', include);

            const url = `/api/v1/posts/${postId}${params.toString() ? '?' + params.toString() : ''}`;
            await apiRequest('GET', url, null, q('#post-output'), q('#post-url'));
        });

        q('#form-post-summary').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const postId = formData.get('post_id');
            const sentences = formData.get('sentences') || 2;

            const url = `/api/v1/posts/${postId}/summary`;
            await apiRequest('POST', url, { sentences: parseInt(sentences) }, q('#summary-output'), q('#summary-url'));
        });

        // Mutations API event handlers
        q('#form-post-read-status').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const postId = formData.get('post_id');
            const read = formData.get('read') === 'true';

            const url = `/api/v1/posts/${postId}/read-status`;
            await apiRequest('PATCH', url, { read }, q('#mutations-output'), q('#mutations-url'));
        });

        q('#form-bulk-read-status').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const postIds = formData.get('post_ids').split(',').map(id => parseInt(id.trim()));
            const read = formData.get('read') === 'true';

            const url = '/api/v1/posts/bulk-read-status';
            await apiRequest('PATCH', url, { post_ids: postIds, read }, q('#mutations-output'), q('#mutations-url'));
        });

        q('#form-mark-all-read').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = { read: formData.get('read') === 'true' };

            if (formData.get('source_id')) data.source_id = parseInt(formData.get('source_id'));
            if (formData.get('category_id')) data.category_id = parseInt(formData.get('category_id'));
            if (formData.get('before_date')) data.before_date = formData.get('before_date');

            const url = '/api/v1/posts/mark-all-read';
            await apiRequest('PATCH', url, data, q('#mutations-output'), q('#mutations-url'));
        });

        // Sources API event handlers
        q('#form-sources').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const params = new URLSearchParams();

            for (const [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }

            const url = `/api/v1/sources${params.toString() ? '?' + params.toString() : ''}`;
            await apiRequest('GET', url, null, q('#sources-output'), q('#sources-url'));
        });

        q('#form-create-source').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = { url: formData.get('url') };

            if (formData.get('name')) data.name = formData.get('name');
            if (formData.get('description')) data.description = formData.get('description');
            if (formData.get('category_id')) data.category_id = parseInt(formData.get('category_id'));

            const url = '/api/v1/sources';
            await apiRequest('POST', url, data, q('#sources-mgmt-output'), q('#sources-mgmt-url'));
        });

        q('#form-update-source').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const sourceId = formData.get('source_id');
            const data = {};

            if (formData.get('name')) data.name = formData.get('name');

            const url = `/api/v1/sources/${sourceId}`;
            await apiRequest('PUT', url, data, q('#sources-mgmt-output'), q('#sources-mgmt-url'));
        });

        q('#form-delete-source').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const sourceId = formData.get('source_id');

            const url = `/api/v1/sources/${sourceId}`;
            await apiRequest('DELETE', url, null, q('#sources-mgmt-output'), q('#sources-mgmt-url'));
        });

        q('#form-refresh-source').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const sourceId = formData.get('source_id');

            const url = `/api/v1/sources/${sourceId}/refresh`;
            await apiRequest('POST', url, null, q('#sources-mgmt-output'), q('#sources-mgmt-url'));
        });

        // Categories API event handlers
        q('#form-categories').addEventListener('submit', async e => {
            e.preventDefault();
            const url = '/api/v1/categories';
            await apiRequest('GET', url, null, q('#categories-output'), q('#categories-url'));
        });

        q('#form-create-category').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = { description: formData.get('description') };

            const url = '/api/v1/categories';
            await apiRequest('POST', url, data, q('#categories-mgmt-output'), q('#categories-mgmt-url'));
        });

        q('#form-update-category').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const categoryId = formData.get('category_id');
            const data = { description: formData.get('description') };

            const url = `/api/v1/categories/${categoryId}`;
            await apiRequest('PUT', url, data, q('#categories-mgmt-output'), q('#categories-mgmt-url'));
        });

        q('#form-delete-category').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const categoryId = formData.get('category_id');

            const url = `/api/v1/categories/${categoryId}`;
            await apiRequest('DELETE', url, null, q('#categories-mgmt-output'), q('#categories-mgmt-url'));
        });

        // Search API event handlers
        q('#form-search').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const params = new URLSearchParams();

            for (const [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }

            const url = `/api/v1/search?${params.toString()}`;
            await apiRequest('GET', url, null, q('#search-output'), q('#search-url'));
        });

        // OPML event handlers
        q('#btn-export-opml').addEventListener('click', async () => {
            const url = '/api/v1/export-opml';
            await apiRequest('GET', url, null, q('#opml-output'), q('#opml-url'));
        });

        q('#form-preview-opml').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);

            const url = '/api/v1/preview-opml';
            await apiRequest('POST', url, formData, q('#opml-output'), q('#opml-url'));
        });

        q('#form-import-opml').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);

            const url = '/api/v1/import-opml';
            await apiRequest('POST', url, formData, q('#opml-output'), q('#opml-url'));
        });

        // Jobs API event handlers
        q('#form-refresh-job').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const sourceId = formData.get('source_id');

            const url = `/api/v1/jobs/sources/${sourceId}/refresh`;
            await apiRequest('POST', url, null, q('#jobs-output'), q('#jobs-url'));
        });

        q('#form-summary-job').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const postId = formData.get('post_id');
            const sentences = parseInt(formData.get('sentences'));

            const url = `/api/v1/jobs/posts/${postId}/summary`;
            await apiRequest('POST', url, { sentences }, q('#jobs-output'), q('#jobs-url'));
        });

        q('#form-summary-status').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const cacheKey = formData.get('cache_key');

            const url = `/api/v1/jobs/summary-status/${cacheKey}`;
            await apiRequest('GET', url, null, q('#jobs-output'), q('#jobs-url'));
        });

        q('#btn-queue-status').addEventListener('click', async () => {
            const url = '/api/v1/jobs/queue-status';
            await apiRequest('GET', url, null, q('#jobs-output'), q('#jobs-url'));
        });

        // Metrics API event handlers
        q('#form-source-metrics').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const sourceId = formData.get('source_id');

            const url = `/api/v1/metrics/sources/${sourceId}`;
            await apiRequest('GET', url, null, q('#metrics-output'), q('#metrics-url'));
        });

        q('#btn-system-stats').addEventListener('click', async () => {
            const url = '/api/v1/metrics/system';
            await apiRequest('GET', url, null, q('#metrics-output'), q('#metrics-url'));
        });

        q('#btn-sources-health').addEventListener('click', async () => {
            const url = '/api/v1/metrics/sources-health';
            await apiRequest('GET', url, null, q('#metrics-output'), q('#metrics-url'));
        });

        q('#btn-recent-activity').addEventListener('click', async () => {
            const url = '/api/v1/metrics/recent-activity';
            await apiRequest('GET', url, null, q('#metrics-output'), q('#metrics-url'));
        });
    </script>
</x-app-layout>
