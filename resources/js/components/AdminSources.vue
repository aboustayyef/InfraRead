<template>
    <div class="admin-sources">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:justify-between mb-6 space-y-4 md:space-y-0">
            <h2 class="text-2xl md:text-4xl font-bold text-gray-600">Sources</h2>
            <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-4">
                <button
                    @click="exportOpml"
                    class="inline-block rounded-md px-3 py-2 bg-yellow-50 hover:bg-yellow-200 text-gray-500 text-sm"
                    :disabled="loading"
                >
                    ↓ Download OPML
                </button>
                <button
                    @click="openCreateModal"
                    class="hover:text-white hover:bg-primary bg-white text-primary px-4 py-2 border border-primary rounded-md text-sm"
                    :disabled="loading"
                >
                    + Add Source
                </button>
            </div>
        </div>

        <!-- Filter and Search -->
        <div class="flex flex-col md:flex-row w-full max-w-none md:max-w-2xl items-stretch md:items-center mb-6 space-y-4 md:space-y-0 md:space-x-4">
            <input
                v-model="searchString"
                type="text"
                placeholder="Filter Sources"
                class="ir_input flex-1"
            >
            <select v-model="filterCategory" class="ir_input flex-none md:w-auto">
                <option value="">All Categories</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.description }}
                </option>
            </select>
        </div>

        <!-- Loading State -->
        <div v-if="loading && sources.length === 0" class="text-center py-8">
            <p class="text-gray-500">Loading sources...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
            <p class="text-red-700">{{ error }}</p>
            <button @click="loadSources" class="mt-2 text-red-600 hover:text-red-800 underline">
                Try Again
            </button>
        </div>

        <!-- Sources List -->
        <div class="space-y-2">
            <div
                v-for="source in filteredSources"
                :key="source.id"
                class="flex flex-col md:flex-row md:justify-between md:items-start bg-white rounded-md hover:shadow-sm"
            >
                <!-- Mobile Action Buttons - Top -->
                <div class="flex justify-between md:hidden p-4 border-b border-gray-100">
                    <div class="flex space-x-2">
                        <button
                            @click.stop="refreshSource(source)"
                            class="text-gray-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-50"
                            :disabled="source.refreshing"
                            title="Refresh source"
                        >
                            <svg v-if="source.refreshing" class="w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                        </button>
                        <button
                            @click.stop="editSource(source)"
                            class="text-gray-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-50"
                            :disabled="loading"
                            title="Edit source"
                        >
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </button>
                    </div>
                    <button
                        @click.stop="deleteSource(source)"
                        class="text-gray-600 hover:text-red-800 p-2 rounded-full hover:bg-red-50"
                        :disabled="loading"
                        title="Delete source"
                    >
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </div>

                <!-- Desktop Action Buttons - Left -->
                <div class="hidden md:flex space-x-2 p-4">
                    <button
                        @click.stop="refreshSource(source)"
                        class="text-gray-600 hover:text-blue-800 p-1 rounded-full hover:bg-blue-50"
                        :disabled="source.refreshing"
                        title="Refresh source"
                    >
                        <svg v-if="source.refreshing" class="w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </button>
                    <button
                        @click.stop="deleteSource(source)"
                        class="text-gray-600 hover:text-red-800 p-1 rounded-full hover:bg-red-50"
                        :disabled="loading"
                        title="Delete source"
                    >
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </div>
                
                <!-- Content -->
                <div class="flex-1 p-4 cursor-pointer hover:bg-gray-50" @click="editSource(source)">
                    <div class="flex flex-col md:flex-row md:space-x-6 space-y-2 md:space-y-0">
                        <div class="text-primary font-semibold text-lg md:text-xl tracking-wider">{{ source.name }}</div>
                        <div class="text-sm bg-gray-100 text-gray-400 rounded-full w-8 h-8 leading-8 flex items-center pb-1 justify-center align-middle self-start">{{ source.id }}</div>
                    </div>
                    <div class="text-gray-700 mt-1">{{ source.description }}</div>
                    <div class="text-gray-400 text-sm mt-1">
                        {{ source.category ? source.category.description : 'No Category' }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1 break-all">
                        {{ source.fetcher_source }}
                    </div>
                </div>

            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && filteredSources.length === 0" class="text-center py-8">
            <p class="text-gray-500">
                {{ searchString || filterCategory ? 'No sources match your filters.' : 'No sources found. Add your first source!' }}
            </p>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl p-4 md:p-6 w-full max-w-md max-h-screen overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ showCreateModal ? 'Add New Source' : 'Edit Source' }}
                    </h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Error Message in Modal -->
                <div v-if="modalError" class="bg-red-50 border border-red-200 rounded-md p-3 mb-4">
                    <p class="text-red-700 text-sm">{{ modalError }}</p>
                </div>

                <form @submit.prevent="saveSource">
                    <div class="space-y-4">
                        <!-- URL Analysis Section (only for create) -->
                        <div v-if="showCreateModal" class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h4 class="text-sm font-medium text-blue-900 mb-2">Auto-Discover RSS Feed</h4>
                            <div class="flex flex-col md:flex-row gap-2">
                                <input
                                    v-model="websiteUrl"
                                    type="url"
                                    placeholder="https://example.com"
                                    class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                                >
                                <button
                                    type="button"
                                    @click="analyzeUrl"
                                    :disabled="analyzing || !websiteUrl"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                                >
                                    {{ analyzing ? 'Analyzing...' : 'Analyze' }}
                                </button>
                            </div>
                            <p class="text-xs text-blue-700 mt-1">Enter a website URL to automatically discover the RSS feed and populate the fields below.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input
                                v-model="sourceForm.name"
                                type="text"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                required
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Website URL</label>
                            <input
                                v-model="sourceForm.url"
                                type="url"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="https://example.com"
                            >
                            <p class="text-xs text-gray-500 mt-1">The main website URL (optional)</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">RSS Feed URL</label>
                            <input
                                v-model="sourceForm.rss_url"
                                type="url"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="https://example.com/feed.xml"
                                required
                            >
                            <p class="text-xs text-gray-500 mt-1">The actual RSS/Atom feed URL (required)</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea
                                v-model="sourceForm.description"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                rows="3"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select
                                v-model="sourceForm.category_id"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">No Category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.description }}
                                </option>
                            </select>
                            <div class="mt-2">
                                <a
                                    href="/app/admin/categories"
                                    target="_blank"
                                    class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 hover:underline"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Manage Categories
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                            :disabled="saving"
                        >
                            {{ saving ? 'Saving...' : (showCreateModal ? 'Create' : 'Update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="fixed top-4 right-4 bg-green-50 border border-green-200 rounded-md p-4 z-50">
            <p class="text-green-700">{{ successMessage }}</p>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdminSources',
    data() {
        return {
            sources: [],
            categories: [],
            loading: false,
            saving: false,
            analyzing: false,
            websiteUrl: '',
            error: null, // For main page errors (loading sources, etc.)
            modalError: null, // For modal-specific errors (creating/editing sources)
            successMessage: null,
            searchString: '',
            filterCategory: '',
            showCreateModal: false,
            showEditModal: false,
            sourceForm: {
                id: null,
                name: '',
                url: '',
                rss_url: '',
                description: '',
                category_id: ''
            }
        }
    },
    computed: {
        filteredSources() {
            return this.sources.filter(source => {
                const matchesSearch = !this.searchString ||
                    source.name.toLowerCase().includes(this.searchString.toLowerCase()) ||
                    source.description.toLowerCase().includes(this.searchString.toLowerCase());

                const matchesCategory = !this.filterCategory ||
                    (source.category && source.category.id == this.filterCategory);

                return matchesSearch && matchesCategory;
            });
        }
    },
    mounted() {
        this.loadSources();
        this.loadCategories();

        // Refresh categories when window regains focus (user returns from category management)
        window.addEventListener('focus', this.handleWindowFocus);
    },
    beforeDestroy() {
        window.removeEventListener('focus', this.handleWindowFocus);
    },
    methods: {
        async loadSources() {
            this.loading = true;
            this.error = null;

            try {
                const response = await window.api.getSources('category');
                this.sources = response.data.map(source => ({
                    ...source,
                    refreshing: false
                }));
            } catch (error) {
                this.error = 'Failed to load sources. Please try again.';
                console.error('Error loading sources:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadCategories() {
            try {
                const response = await window.api.getCategories();
                this.categories = response.data;
            } catch (error) {
                console.error('Error loading categories:', error);
            }
        },

        handleWindowFocus() {
            // Refresh categories when user returns from category management page
            this.loadCategories();
        },

        openCreateModal() {
            this.modalError = null; // Clear any previous modal errors
            this.showCreateModal = true;
        },

        editSource(source) {
            this.sourceForm = {
                id: source.id,
                name: source.name,
                url: source.url || '',
                rss_url: source.rss_url || '',
                description: source.description,
                category_id: source.category ? source.category.id : ''
            };
            this.showEditModal = true;
        },

        async saveSource() {
            this.saving = true;
            this.modalError = null; // Clear previous modal errors

            try {
                // Transform the form data to match API expectations
                const apiData = {
                    ...this.sourceForm,
                    fetcher_source: this.sourceForm.rss_url,
                };
                delete apiData.rss_url; // Remove the frontend field name

                if (this.showCreateModal) {
                    await window.api.createSource(apiData);
                    this.showSuccessMessage('Source created successfully!');
                } else {
                    await window.api.updateSource(this.sourceForm.id, apiData);
                    this.showSuccessMessage('Source updated successfully!');
                }

                this.closeModal();
                this.loadSources();
            } catch (error) {
                console.error('Error saving source:', error);

                // Handle different types of errors
                if (error.status === 422 && error.data && error.data.errors) {
                    // Validation errors
                    const errorMessages = Object.values(error.data.errors).flat();
                    this.modalError = errorMessages.join(' ');
                } else if (error.data && error.data.message) {
                    // API error message
                    this.modalError = error.data.message;
                } else {
                    // Generic error
                    this.modalError = 'Failed to save source. Please check your input and try again.';
                }
            } finally {
                this.saving = false;
            }
        },

        async deleteSource(source) {
            if (!confirm(`Are you sure you want to delete "${source.name}"? This will also delete all posts from this source.`)) {
                return;
            }

            this.loading = true;

            try {
                await window.api.deleteSource(source.id);
                this.showSuccessMessage('Source deleted successfully!');
                this.loadSources();
            } catch (error) {
                this.error = 'Failed to delete source. Please try again.';
                console.error('Error deleting source:', error);
            } finally {
                this.loading = false;
            }
        },

        async refreshSource(source) {
            source.refreshing = true;

            try {
                await window.api.refreshSourceJob(source.id);
                this.showSuccessMessage(`Refresh job queued for "${source.name}"`);
            } catch (error) {
                this.error = 'Failed to queue refresh job. Please try again.';
                console.error('Error queuing refresh job:', error);
            } finally {
                source.refreshing = false;
            }
        },

        async exportOpml() {
            try {
                const response = await window.api.exportOpml();
                // Create download link
                const blob = new Blob([response.data.content], { type: 'application/xml' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = response.data.filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                this.showSuccessMessage('OPML file downloaded successfully!');
            } catch (error) {
                this.error = 'Failed to export OPML. Please try again.';
                console.error('Error exporting OPML:', error);
            }
        },

        async analyzeUrl() {
            if (!this.websiteUrl) return;

            this.analyzing = true;
            this.modalError = null;

            try {
                const response = await window.api.analyzeUrl(this.websiteUrl);

                if (response.status === 'error') {
                    this.modalError = response.error_messages.join(', ');
                    return;
                }

                // Populate the form with discovered data
                if (response.result) {
                    this.sourceForm.name = response.result.title || '';
                    // Set the website URL to the original URL entered
                    this.sourceForm.url = this.websiteUrl;
                    // Set the RSS feed URL to the discovered feed
                    this.sourceForm.rss_url = response.result.rss || '';
                    this.sourceForm.description = response.result.description || '';
                }

                this.showSuccessMessage('RSS feed discovered successfully!');

            } catch (error) {
                this.modalError = 'Failed to analyze URL. Please check the URL and try again.';
                console.error('Error analyzing URL:', error);
            } finally {
                this.analyzing = false;
            }
        },

        closeModal() {
            this.showCreateModal = false;
            this.showEditModal = false;
            this.websiteUrl = '';
            this.analyzing = false;
            this.modalError = null; // Clear modal errors when closing modal
            this.sourceForm = {
                id: null,
                name: '',
                url: '',
                rss_url: '',
                description: '',
                category_id: ''
            };
        },

        showSuccessMessage(message) {
            this.successMessage = message;
            setTimeout(() => {
                this.successMessage = null;
            }, 3000);
        }
    }
}
</script>

<style scoped>
.ir_input {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    outline: none;
}

.ir_input:focus {
    outline: 2px solid #3b82f6;
    border-color: #3b82f6;
}
</style>
