<template>
    <div class="admin-sources">
        <!-- Header -->
        <div class="flex justify-between mb-6">
            <h2 class="text-4xl font-bold text-gray-600">Sources</h2>
            <div class="flex space-x-4">
                <button
                    @click="exportOpml"
                    class="inline-block rounded-md px-3 py-2 bg-red-50 hover:bg-red-200 text-red-700"
                    :disabled="loading"
                >
                    ↓ Download OPML
                </button>
                <button
                    @click="openCreateModal"
                    class="hover:text-white hover:bg-primary bg-white text-primary px-4 py-2 border border-primary rounded-md"
                    :disabled="loading"
                >
                    + Add Source
                </button>
            </div>
        </div>

        <!-- Filter and Search -->
        <div class="flex flex-wrap w-full max-w-2xl items-center mb-6 space-x-4">
            <input
                v-model="searchString"
                type="text"
                placeholder="Filter Sources"
                class="ir_input flex-1"
            >
            <select v-model="filterCategory" class="ir_input">
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
                class="flex justify-between items-start bg-white rounded-md hover:shadow-sm"
            >
                <div class="flex-1 p-4 cursor-pointer hover:bg-gray-50" @click="editSource(source)">
                    <div class="text-primary font-semibold text-xl tracking-wider">{{ source.name }}</div>
                    <div class="text-gray-700">{{ source.description }}</div>
                    <div class="text-gray-400">
                        {{ source.category ? source.category.description : 'No Category' }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ source.fetcher_source }}
                    </div>
                </div>
                <div class="flex space-x-2 p-4">
                    <button
                        @click.stop="refreshSource(source)"
                        class="text-blue-600 hover:text-blue-800 text-sm"
                        :disabled="source.refreshing"
                    >
                        {{ source.refreshing ? 'Refreshing...' : 'Refresh' }}
                    </button>
                    <button
                        @click.stop="deleteSource(source)"
                        class="text-red-600 hover:text-red-800 text-sm"
                        :disabled="loading"
                    >
                        Delete
                    </button>
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
        <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">
                    {{ showCreateModal ? 'Add New Source' : 'Edit Source' }}
                </h3>

                <!-- Error Message in Modal -->
                <div v-if="modalError" class="bg-red-50 border border-red-200 rounded-md p-3 mb-4">
                    <p class="text-red-700 text-sm">{{ modalError }}</p>
                </div>

                <form @submit.prevent="saveSource">
                    <div class="space-y-4">
                        <!-- URL Analysis Section (only for create) -->
                        <div v-if="showCreateModal" class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h4 class="text-sm font-medium text-blue-900 mb-2">Auto-Discover RSS Feed</h4>
                            <div class="flex gap-2">
                                <input
                                    v-model="websiteUrl"
                                    type="url"
                                    placeholder="https://example.com"
                                    class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                >
                                <button
                                    type="button"
                                    @click="analyzeUrl"
                                    :disabled="analyzing || !websiteUrl"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Website or Feed URL</label>
                            <input
                                v-model="sourceForm.url"
                                type="url"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="https://example.com"
                                required
                            >
                            <p class="text-xs text-gray-500 mt-1">Website URL (preferred) or direct RSS feed URL</p>
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

        openCreateModal() {
            this.modalError = null; // Clear any previous modal errors
            this.showCreateModal = true;
        },

        editSource(source) {
            this.sourceForm = {
                id: source.id,
                name: source.name,
                url: source.rss_url,
                description: source.description,
                category_id: source.category ? source.category.id : ''
            };
            this.showEditModal = true;
        },

        async saveSource() {
            this.saving = true;
            this.modalError = null; // Clear previous modal errors

            try {
                if (this.showCreateModal) {
                    await window.api.createSource(this.sourceForm);
                    this.showSuccessMessage('Source created successfully!');
                } else {
                    await window.api.updateSource(this.sourceForm.id, this.sourceForm);
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
                    // Send the original website URL to the API, not the RSS feed URL
                    this.sourceForm.url = this.websiteUrl;
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
