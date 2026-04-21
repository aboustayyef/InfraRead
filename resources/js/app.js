import './bootstrap';
import 'alpinejs';
import Vue from 'vue';

// Import the API client
import api from './api/client.js';
import App from './components/App.vue';
import ApiClientTest from './components/ApiClientTest.vue';
import AdminSources from './components/AdminSources.vue';
import AdminCategories from './components/AdminCategories.vue';

window.Vue = Vue;

// Make API client available globally
window.api = api;

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('app', App);
Vue.component('api-client-test', ApiClientTest);
Vue.component('admin-sources', AdminSources);
Vue.component('admin-categories', AdminCategories);

const app = new Vue({
    el: '#app'
});
