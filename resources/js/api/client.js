/**
 * Infraread API Client
 *
 * Unified HTTP client for interacting with the Infraread API.
 * Handles authentication, error handling, retry logic, and provides
 * a clean interface for all API operations.
 *
 * Teaching Note: This client abstracts away the complexity of HTTP requests
 * and provides a consistent interface for the frontend. It handles:
 * - Authentication (both session and token-based)
 * - Error handling and user-friendly error messages
 * - Request/response transformation
 * - Retry logic for network issues
 * - Rate limiting awareness
 */

class InfrareadAPI {
    constructor() {
        this.baseURL = '/api/v1';
        this.token = null;
        this.retryAttempts = 3;
        this.retryDelay = 1000; // 1 second

        // Initialize authentication
        this.initializeAuth();
    }

    /**
     * Initialize authentication by using the token from Laravel
     */
    async initializeAuth() {
        console.log('🔄 Initializing API authentication...');

        // First check for token provided by Laravel
        if (window.Laravel && window.Laravel.apiToken) {
            this.token = window.Laravel.apiToken;

            // Check if this looks like a pre-configured token (longer) or auto-generated (shorter)
            if (this.token.length > 80) {
                console.log('✅ Using pre-configured API token from .env');
            } else {
                console.log('✅ Using auto-generated API token');
            }
            return;
        }

        // Fallback to stored token
        const storedToken = localStorage.getItem('infraread_api_token');
        if (storedToken) {
            console.log('✅ Found stored token');
            this.token = storedToken;
            return;
        }

        console.warn('❌ No API token available');
    }

    /**
     * Make an HTTP request to the API
     * Handles authentication, retries, and error processing
     */
    async request(method, endpoint, data = null, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        console.log(`📡 API Request: ${method} ${url}`, data ? { data } : '');

        const config = {
            method: method.toUpperCase(),
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            credentials: 'include', // Changed from 'same-origin' to 'include' for better cookie handling
            ...options
        };

        // Force fresh data reads for API GET requests.
        if (config.method === 'GET') {
            config.cache = 'no-store';
        }

        // Add authentication
        if (this.token) {
            config.headers['Authorization'] = `Bearer ${this.token}`;
            console.log('🔑 Using Bearer token authentication');
        } else {
            // Fallback to CSRF token for session-based requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.Laravel?.csrfToken;
            if (csrfToken) {
                config.headers['X-CSRF-TOKEN'] = csrfToken;
                console.log('🔑 Using CSRF token for session authentication');
            }
        }

        // Add request body for non-GET requests
        if (data && ['POST', 'PUT', 'PATCH'].includes(config.method)) {
            config.body = JSON.stringify(data);
        }

        // Implement retry logic
        for (let attempt = 1; attempt <= this.retryAttempts; attempt++) {
            try {
                const response = await fetch(url, config);

                // Handle authentication errors
                if (response.status === 401) {
                    console.warn('Authentication failed - token may be invalid');
                    this.token = null;
                    localStorage.removeItem('infraread_api_token');
                    // Don't retry automatically - user should refresh page to get new token
                }

                // Handle rate limiting
                if (response.status === 429) {
                    const retryAfter = response.headers.get('Retry-After') || this.retryDelay / 1000;
                    await this.delay(retryAfter * 1000);
                    continue;
                }

                // Handle other errors
                if (!response.ok) {
                    const errorData = await this.parseErrorResponse(response);
                    throw new APIError(errorData.message || `HTTP ${response.status}`, response.status, errorData);
                }

                // Success - parse and return response
                return await this.parseResponse(response);

            } catch (error) {
                if (error instanceof APIError) {
                    throw error; // Re-throw API errors immediately
                }

                // Network or other errors - retry if we have attempts left
                if (attempt < this.retryAttempts) {
                    console.warn(`Request attempt ${attempt} failed, retrying...`, error.message);
                    await this.delay(this.retryDelay * attempt); // Exponential backoff
                    continue;
                }

                // Final attempt failed
                throw new APIError(
                    `Network error after ${this.retryAttempts} attempts: ${error.message}`,
                    0,
                    { originalError: error }
                );
            }
        }
    }

    /**
     * Parse API response, handling both JSON and text responses
     */
    async parseResponse(response) {
        const contentType = response.headers.get('content-type');

        if (contentType && contentType.includes('application/json')) {
            return await response.json();
        }

        return await response.text();
    }

    /**
     * Parse error response and extract meaningful error information
     */
    async parseErrorResponse(response) {
        try {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            }
            return { message: await response.text() };
        } catch (error) {
            return { message: `HTTP ${response.status} ${response.statusText}` };
        }
    }

    /**
     * Utility method for delays
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // ===== API METHODS =====

    /**
     * Posts API
     */
    async getPosts(filters = {}) {
        const params = new URLSearchParams();

        // Handle common filters
        if (filters.read !== undefined) {
            params.append('filter[read]', filters.read);
        }
        if (filters.source) {
            params.append('filter[source]', filters.source);
        }
        if (filters.category) {
            params.append('filter[category]', filters.category);
        }
        if (filters.since) {
            params.append('filter[since]', filters.since);
        }
        if (filters.include) {
            params.append('include', Array.isArray(filters.include) ? filters.include.join(',') : filters.include);
        }
        // Remove any page.size or pagination params

        const query = params.toString();
        const endpoint = query ? `/posts?${query}` : '/posts';

        return await this.request('GET', endpoint);
    }

    async getPost(id, include = null) {
        const params = include ? `?include=${Array.isArray(include) ? include.join(',') : include}` : '';
        return await this.request('GET', `/posts/${id}${params}`);
    }

    async cachePostMarkdown(id) {
        return await this.request('POST', `/posts/${id}/cache-markdown`);
    }

    async markPostRead(id, read = true) {
        return await this.request('PATCH', `/posts/${id}/read-status`, { read });
    }

    async bulkMarkRead(postIds, read = true) {
        return await this.request('PATCH', '/posts/bulk-read-status', {
            post_ids: postIds,
            read
        });
    }

    async markAllRead(filters = {}) {
        return await this.request('PATCH', '/posts/mark-all-read', filters);
    }

    async generateSummary(postId, sentences = 2) {
        return await this.request('POST', `/posts/${postId}/summary`, { sentences });
    }

    /**
     * Sources API
     */
    async getSources(include = null) {
        const params = include ? `?include=${Array.isArray(include) ? include.join(',') : include}` : '';
        return await this.request('GET', `/sources${params}`);
    }

    async getSource(id, include = null) {
        const params = include ? `?include=${Array.isArray(include) ? include.join(',') : include}` : '';
        return await this.request('GET', `/sources/${id}${params}`);
    }

    async createSource(data) {
        return await this.request('POST', '/sources', data);
    }

    async updateSource(id, data) {
        return await this.request('PUT', `/sources/${id}`, data);
    }

    async deleteSource(id) {
        return await this.request('DELETE', `/sources/${id}`);
    }

    async refreshSource(id) {
        return await this.request('POST', `/sources/${id}/refresh`);
    }

    /**
     * Categories API
     */
    async getCategories(include = null) {
        const params = include ? `?include=${Array.isArray(include) ? include.join(',') : include}` : '';
        return await this.request('GET', `/categories${params}`);
    }

    async getCategory(id, include = null) {
        const params = include ? `?include=${Array.isArray(include) ? include.join(',') : include}` : '';
        return await this.request('GET', `/categories/${id}${params}`);
    }

    async createCategory(data) {
        return await this.request('POST', '/categories', data);
    }

    async updateCategory(id, data) {
        return await this.request('PUT', `/categories/${id}`, data);
    }

    async deleteCategory(id) {
        return await this.request('DELETE', `/categories/${id}`);
    }

    /**
     * Jobs API
     */
    async refreshSourceJob(sourceId) {
        return await this.request('POST', `/jobs/sources/${sourceId}/refresh`);
    }

    async generateSummaryJob(postId, sentences = 2) {
        return await this.request('POST', `/jobs/posts/${postId}/summary`, { sentences });
    }

    async getSummaryStatus(cacheKey) {
        return await this.request('GET', `/jobs/summary/${cacheKey}/status`);
    }

    async getQueueStatus() {
        return await this.request('GET', '/jobs/queue/status');
    }

    /**
     * URL Analysis API
     */
    async analyzeUrl(url) {
        const params = new URLSearchParams({ url });
        // Note: This endpoint is not in the v1 namespace yet, so we use absolute path
        const fullUrl = `/api/urlanalyze?${params.toString()}`;

        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        const response = await fetch(fullUrl, {
            method: 'GET',
            headers,
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return await response.json();
    }

    /**
     * OPML API
     */
    async exportOpml() {
        return await this.request('GET', '/export-opml');
    }

    async previewOpml(file) {
        const formData = new FormData();
        formData.append('opml_file', file);

        return await this.request('POST', '/preview-opml', formData, {
            headers: {} // Let browser set Content-Type for FormData
        });
    }

    async importOpml(file, mode = 'merge') {
        const formData = new FormData();
        formData.append('opml_file', file);
        formData.append('mode', mode);

        return await this.request('POST', '/import-opml', formData, {
            headers: {} // Let browser set Content-Type for FormData
        });
    }

    /**
     * Metrics API
     */
    async getCrawlStatus() {
        return await this.request('GET', '/metrics/crawl-status');
    }

    async getSystemStats() {
        return await this.request('GET', '/metrics/system');
    }

    async getSourcesHealth() {
        return await this.request('GET', '/metrics/sources-health');
    }

    async getRecentActivity() {
        return await this.request('GET', '/metrics/recent-activity');
    }

    async getSourceMetrics(sourceId) {
        return await this.request('GET', `/metrics/sources/${sourceId}`);
    }
}

/**
 * Custom error class for API errors
 * Provides structured error information for better error handling
 */
class APIError extends Error {
    constructor(message, status = 0, data = {}) {
        super(message);
        this.name = 'APIError';
        this.status = status;
        this.data = data;
    }

    /**
     * Get user-friendly error message
     */
    getUserMessage() {
        if (this.status === 0) {
            return 'Network connection error. Please check your internet connection.';
        }

        if (this.status === 401) {
            return 'Authentication required. Please log in again.';
        }

        if (this.status === 403) {
            return 'Access denied. You do not have permission for this action.';
        }

        if (this.status === 404) {
            return 'The requested resource was not found.';
        }

        if (this.status === 422) {
            return this.data.message || 'Invalid data provided.';
        }

        if (this.status === 429) {
            return 'Too many requests. Please wait a moment and try again.';
        }

        if (this.status >= 500) {
            return 'Server error. Please try again later.';
        }

        return this.message || 'An unexpected error occurred.';
    }
}

// Export for use in Vue components
export { InfrareadAPI, APIError };

// Create a global instance for immediate use
window.InfrareadAPI = InfrareadAPI;
window.APIError = APIError;

// Create and export a default instance
const api = new InfrareadAPI();
export default api;
