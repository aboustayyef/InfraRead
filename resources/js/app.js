require('./bootstrap');

require('alpinejs');

// Import the API client
import api from './api/client.js';

window.Vue = require('vue');

// Make API client available globally
window.api = api;

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('app', require('./components/App.vue').default);
Vue.component('api-client-test', require('./components/ApiClientTest.vue').default);
Vue.component('admin-sources', require('./components/AdminSources.vue').default);
Vue.component('admin-categories', require('./components/AdminCategories.vue').default);

const app = new Vue({
    el: '#app'
});
