require('./bootstrap');

require('alpinejs');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('app', require('./components/App.vue').default);
Vue.component('header-settings', require('./components/HeaderSettings.vue').default);
Vue.component('unread-count', require('./components/UnreadCount.vue').default);
Vue.component('bottom-nav', require('./components/BottomNav.vue').default);
Vue.component('post-details', require('./components/PostDetails.vue').default);
Vue.component('post-list-item', require('./components/PostListItem.vue').default);
Vue.component('empty-post-list-item', require('./components/EmptyPostListItem.vue').default);
const app = new Vue({
    el: '#app'
});