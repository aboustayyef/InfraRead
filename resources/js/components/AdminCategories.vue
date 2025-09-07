<template>
    <div class="bg-white">
        <div class="px-4 py-5 sm:p-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 space-y-4 md:space-y-0">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Categories Management</h3>
                    <p class="mt-1 text-sm text-gray-500">Manage RSS feed categories</p>
                </div>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 self-start"
                >
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Category
                </button>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                <p class="mt-2 text-sm text-gray-500">Loading categories...</p>
            </div>

            <!-- Categories List -->
            <div v-else class="flex flex-col">
                <!-- Desktop Table -->
                <div class="hidden md:block -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Category
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Sources Count
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Created
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="category in categories" :key="category.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ category.description }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ category.sources_count || 0 }} sources
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(category.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button
                                                @click="editCategory(category)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-4"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="deleteCategory(category)"
                                                class="text-red-600 hover:text-red-900"
                                                :class="{ 'opacity-50 cursor-not-allowed': deleting === category.id }"
                                                :disabled="deleting === category.id"
                                            >
                                                {{ deleting === category.id ? 'Deleting...' : 'Delete' }}
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Mobile Card Layout -->
                <div class="md:hidden space-y-4">
                    <div v-for="category in categories" :key="category.id" class="bg-white rounded-lg shadow border border-gray-200 p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">
                                    {{ category.description }}
                                </h4>
                                <div class="flex flex-col space-y-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 self-start">
                                        {{ category.sources_count || 0 }} sources
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        Created {{ formatDate(category.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 pt-3 border-t border-gray-100">
                            <button
                                @click="editCategory(category)"
                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteCategory(category)"
                                class="text-red-600 hover:text-red-900 text-sm font-medium"
                                :class="{ 'opacity-50 cursor-not-allowed': deleting === category.id }"
                                :disabled="deleting === category.id"
                            >
                                {{ deleting === category.id ? 'Deleting...' : 'Delete' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!loading && categories.length === 0" class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No categories</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new category.</p>
                <div class="mt-6">
                    <button
                        @click="openCreateModal"
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Category
                    </button>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal"></div>

                <!-- Modal panel -->
                <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full mx-4">
                    <form @submit.prevent="saveCategory">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="w-full mt-3 text-center sm:mt-0 sm:text-left">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                            {{ isEditing ? 'Edit Category' : 'Add New Category' }}
                                        </h3>
                                        <button type="button" @click="closeModal" class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Category Description Field -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                        <input
                                            v-model="categoryForm.description"
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                            placeholder="Enter category description"
                                            required
                                            :class="{ 'border-red-500': errors.description }"
                                        />
                                        <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
                                        <p class="text-xs text-gray-500 mt-1">A descriptive name for this category (e.g., "Tech News", "Politics")</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Actions -->
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col-reverse sm:flex-row sm:justify-end space-y-reverse space-y-2 sm:space-y-0 sm:space-x-3">
                            <button
                                type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm"
                                :class="{ 'opacity-50 cursor-not-allowed': saving }"
                                :disabled="saving"
                            >
                                {{ saving ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
                            </button>
                            <button
                                type="button"
                                @click="closeModal"
                                class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <div v-if="message" class="fixed top-4 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg v-if="message.type === 'success'" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900">{{ message.text }}</p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="message = null" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdminCategories',
    data() {
        return {
            categories: [],
            loading: false,
            saving: false,
            deleting: null,
            showModal: false,
            isEditing: false,
            categoryForm: {
                description: ''
            },
            editingCategory: null,
            errors: {},
            message: null
        };
    },
    mounted() {
        this.loadCategories();
    },
    methods: {
        async loadCategories() {
            this.loading = true;
            console.log('🔄 Loading categories...');
            try {
                console.log('📡 Making API request for categories');
                const response = await window.api.getCategories();
                console.log('✅ API response received:', response);
                this.categories = response.data;
                console.log('📊 Categories loaded:', this.categories.length, 'categories');
            } catch (error) {
                console.error('❌ Failed to load categories:', error);
                this.showMessage('Failed to load categories', 'error');
            } finally {
                this.loading = false;
            }
        },

        openCreateModal() {
            this.isEditing = false;
            this.editingCategory = null;
            this.categoryForm = {
                description: ''
            };
            this.errors = {};
            this.showModal = true;
        },

        editCategory(category) {
            this.isEditing = true;
            this.editingCategory = category;
            this.categoryForm = {
                description: category.description
            };
            this.errors = {};
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.isEditing = false;
            this.editingCategory = null;
            this.categoryForm = {
                description: ''
            };
            this.errors = {};
        },

        async saveCategory() {
            this.saving = true;
            this.errors = {};

            try {
                let response;
                if (this.isEditing) {
                    response = await window.api.updateCategory(this.editingCategory.id, this.categoryForm);
                } else {
                    response = await window.api.createCategory(this.categoryForm);
                }

                this.showMessage(response.message || 'Category saved successfully', 'success');
                this.closeModal();
                this.loadCategories();
            } catch (error) {
                console.error('Failed to save category:', error);

                if (error.status === 422) {
                    this.errors = error.data.errors || {};
                } else {
                    this.showMessage('Failed to save category', 'error');
                }
            } finally {
                this.saving = false;
            }
        },

        async deleteCategory(category) {
            if (!confirm(`Are you sure you want to delete "${category.description}"?\n\nIf this category has sources, they will be moved to "Uncategorized".`)) {
                return;
            }

            this.deleting = category.id;

            try {
                const response = await window.api.deleteCategory(category.id);

                let message = response.message || 'Category deleted successfully';
                if (response.data && response.data.sources_moved > 0) {
                    message += ` (${response.data.sources_moved} sources moved to ${response.data.moved_to_category})`;
                }

                this.showMessage(message, 'success');
                this.loadCategories();
            } catch (error) {
                console.error('Failed to delete category:', error);
                this.showMessage('Failed to delete category', 'error');
            } finally {
                this.deleting = null;
            }
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        },

        showMessage(text, type = 'success') {
            this.message = { text, type };
            setTimeout(() => {
                this.message = null;
            }, 5000);
        }
    }
};
</script>

<style scoped>
/* Custom styles if needed */
</style>
