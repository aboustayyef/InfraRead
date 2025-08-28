<template>
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
        <h3 class="text-lg font-semibold text-blue-800 mb-2">API Client Test</h3>

        <!-- Authentication Status -->
        <div class="mb-4">
            <h4 class="font-medium text-blue-700">Authentication Status:</h4>
            <div class="text-sm text-gray-600">
                <div v-if="authStatus.loading">Testing authentication...</div>
                <div v-else-if="authStatus.error" class="text-red-600">
                    Error: {{ authStatus.error }}
                </div>
                <div v-else-if="authStatus.data.overall_authenticated" class="text-green-600">
                    ✅ API Client Ready - Token Available
                    <br>
                    <span class="text-xs">Authentication: {{ getUserInfo().method }}</span>
                </div>
                <div v-else class="text-orange-600">
                    ❌ API Client Not Ready - No Token Available
                    <br>
                    <span class="text-xs">Please refresh the page to get a new token</span>
                </div>
            </div>
        </div>

        <!-- API Test Buttons -->
        <div class="space-x-2 mb-4">
            <button
                @click="testPosts"
                :disabled="loading"
                class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600 disabled:opacity-50"
            >
                Test Posts API
            </button>
            <button
                @click="testSources"
                :disabled="loading"
                class="px-3 py-1 bg-green-500 text-white rounded text-sm hover:bg-green-600 disabled:opacity-50"
            >
                Test Sources API
            </button>
            <button
                @click="testCategories"
                :disabled="loading"
                class="px-3 py-1 bg-purple-500 text-white rounded text-sm hover:bg-purple-600 disabled:opacity-50"
            >
                Test Categories API
            </button>
        </div>

        <!-- Results -->
        <div v-if="loading" class="text-blue-600">Loading...</div>

        <div v-if="lastResult" class="bg-gray-100 p-3 rounded text-xs">
            <h4 class="font-medium mb-2">Last API Call Result:</h4>
            <pre class="whitespace-pre-wrap">{{ JSON.stringify(lastResult, null, 2) }}</pre>
        </div>

        <div v-if="error" class="bg-red-100 border border-red-300 text-red-700 p-3 rounded text-sm mt-2">
            <strong>Error:</strong> {{ error }}
        </div>
    </div>
</template>

<script>
export default {
    name: 'ApiClientTest',
    data() {
        return {
            loading: false,
            error: null,
            lastResult: null,
            authStatus: {
                loading: true,
                error: null,
                data: null
            },
            hasToken: false
        };
    },

    async mounted() {
        await this.checkAuthentication();
        this.checkToken();
    },

    methods: {
        async checkAuthentication() {
            console.log('🔐 Checking authentication...');
            try {
                this.authStatus.loading = true;
                this.authStatus.error = null;

                // Simple check - if we have a token, we're authenticated
                if (window.api.token) {
                    console.log('✅ Token available, testing API call...');
                    // Test with a simple API call
                    const result = await window.api.getSources();
                    console.log('✅ API call successful:', result);

                    this.authStatus.data = {
                        overall_authenticated: true,
                        authentication: {
                            sanctum: {
                                authenticated: true,
                                user_email: 'Authenticated via token'
                            },
                            web: { authenticated: false }
                        },
                        recommended_action: 'api_ready'
                    };
                } else {
                    console.log('❌ No token available');
                    this.authStatus.data = {
                        overall_authenticated: false,
                        authentication: {
                            sanctum: { authenticated: false },
                            web: { authenticated: false }
                        },
                        recommended_action: 'needs_token'
                    };
                }
            } catch (error) {
                console.error('❌ Auth check failed:', error);
                this.authStatus.error = error.getUserMessage ? error.getUserMessage() : error.message;
                this.authStatus.data = {
                    overall_authenticated: false,
                    authentication: {
                        sanctum: { authenticated: false },
                        web: { authenticated: false }
                    },
                    recommended_action: 'api_error'
                };
            } finally {
                this.authStatus.loading = false;
            }
        },

        checkToken() {
            this.hasToken = !!window.api.token;
        },

        getUserInfo() {
            if (!this.authStatus.data || !this.authStatus.data.overall_authenticated) {
                return { method: 'None' };
            }
            return { method: 'Sanctum Token' };
        },

        async testPosts() {
            console.log('🧪 Testing Posts API...');
            try {
                this.loading = true;
                this.error = null;

                console.log('📡 Calling api.getPosts...');
                const result = await window.api.getPosts({ include: 'source,category' });
                console.log('✅ Posts API result:', result);

                this.lastResult = {
                    endpoint: 'GET /posts',
                    count: result.data?.length || 0,
                    sample: result.data?.slice(0, 2) || result
                };
            } catch (error) {
                console.error('❌ Posts API test failed:', error);
                this.error = error.getUserMessage ? error.getUserMessage() : error.message;
            } finally {
                this.loading = false;
            }
        },

        async testSources() {
            try {
                this.loading = true;
                this.error = null;

                const result = await window.api.getSources('category');
                this.lastResult = {
                    endpoint: 'GET /sources',
                    count: result.data?.length || 0,
                    sample: result.data?.slice(0, 2) || result
                };
            } catch (error) {
                this.error = error.getUserMessage();
                console.error('Sources API test failed:', error);
            } finally {
                this.loading = false;
            }
        },

        async testCategories() {
            try {
                this.loading = true;
                this.error = null;

                const result = await window.api.getCategories();
                this.lastResult = {
                    endpoint: 'GET /categories',
                    count: result.data?.length || 0,
                    sample: result.data || result
                };
            } catch (error) {
                this.error = error.getUserMessage();
                console.error('Categories API test failed:', error);
            } finally {
                this.loading = false;
            }
        }
    }
};
</script>
