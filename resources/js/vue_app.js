require('./bootstrap');

require('alpinejs');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('app', require('./components/v2/App.vue').default);
Vue.component('post', require('./components/v2/Post.vue').default);

const app = new Vue({
    el: '#app'
});