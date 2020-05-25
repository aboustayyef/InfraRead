<template>
    <div id="scrollable_body" :class="{'prevent-scrolling': page == 'post details'}">

        <!-- Content of a Single Post -->
        <!-- This view is hidden at the start (using: X-translate 100% to the right) -->
        <!-- It only comes into view (X-translate:0%) when the page kind is 'post details'  -->
        <post-details
            v-if="posts_loaded"
            :page="page"
            :active_post="active_post"
            :active_post_content="active_post_content"
        ></post-details>
        
        <!-- UI Element to show Unread Count -->
        <unread-count
            :page="page"
            :unread_count="unread_count">
        </unread-count>

        <!-- UI Element to show "saving" status when saving for later -->
        <div id="savinglater">
            <progress v-if="saving_later_status == 'saving'" class="progress is-primary" max="100">30%</progress>
            <span v-if="saving_later_status == 'success'">Saved!</span>
            <span v-if="saving_later_status == 'failure'">couldn't save!</span>
        </div>
        
        <!-- UI Element to show Close Button for Article (to return to list view) -->
        <bottom-nav
            v-on:closeWindow="closeDetailsView()"
            v-on:keyup.esc="closeDetailsView()"
            :page="page"
        ></bottom-nav>
        
        <!-- UI Collection of Toggle Settings Elements at the Top -->
        <header-settings
            :posts_description="posts_description"
            :unread_only="unread_only"
            v-on:UnreadOnlyToggle="ToggleUnreadOnly"
            :oldest_on_top="oldest_on_top"
            v-on:OldestOnTopToggle="ToggleOldestOnTop"
            :last_successful_crawl="last_successful_crawl"
        ></header-settings>
            
        <!-- List of Posts -->
        <div id="list_of_posts" class="container">
            
            <!-- If all posts are read, display 'there are no unread posts' message  -->
            <div class="row" v-if="filtered_posts.length == 0 && posts_loaded" >
                There are no unread posts... <a @click="unread_only = false">See All posts</a>
            </div>

            <div v-if="posts_loaded" class='row'>
                <ul>
                    <li class="post_list_item" v-for="(post,i) in filtered_posts" v-bind:key="post.id" >
                    <!-- When a user clicks on an area of the post, change the current post and mark it as read -->
                        <post-list-item 
                            :index="i"
                            :post="post"
                            v-on:show-post-details="showDetailsView(post)"
                            v-on:toggle-post-read="togglePostRead(post)"
                            :keyboard_navigation_active="keyboard_navigation_active"
                            :keyboard_navigation_index="keyboard_navigation_index"
                        ></post-list-item>
                        <hr v-if="!(unread_only && post.read)">
                    </li>
                </ul>
            </div>
            <div v-else>
                <!-- show placeholder until posts load -->
                <ul>
                    <li v-for="n in 6" :key="n">
                        <empty-post-list-item></empty-post-list-item>
                        <hr>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
<script>
import { setTimeout } from 'timers';
    export default {
        props: [
            'refreshinterval','last_successful_crawl'
        ],
        data() {
            return {
                page: window.page,    // used to know which view we're in: options: 'post list', 'post details' or 'post filters'
                posts_source: window.posts_source, // which XHR request to get posts
                posts_description: window.posts_description,
                posts : [], // the list of unfiltered posts
                posts_loaded : false,
                active_post : {}, // the posts which is in the post details view mode
                active_post_content: 'loading...',
                unread_only: true, // default filter = unread posts
                last_fetch_posts: 0,
                all_sources:[],
                all_categories:[],
                oldest_on_top: true, //location on list page, to remember when exiting details page
                areyousure: false,
                keyboard_navigation_active:false,
                keyboard_navigation_index:0,
                saving_later_status: 'nothing', // options: 'nothing', 'saving' , 'success' , 'failure'
            };
        },
        created() {

            this.fetchPostList();

            // Capture Keyboard input
            document.addEventListener('keydown', this.handleKeyboardInput);
            
        },
        computed: {
           unread_count()
            {
                if (this.posts_loaded == true) {
                    return this.posts.filter((post) => {return post.read == 0}).length;
                }
                return 0;
            },
            filtered_posts()
            {
                if (this.posts_loaded) {
                    let posts_copy = this.posts.slice(); //used slice() because reverse() mutates original array
                    if (this.oldest_on_top) {
                        posts_copy.reverse(); 
                    }
                    if (this.unread_only) {
                        posts_copy = posts_copy.filter((post) => !post.read);
                    } 
                    return posts_copy;
                }
                return false;
            },
        },
        methods: {
            handleKeyboardInput(e) {
                // console.log(` ${e.code}`);
                // escape key 
                if(e.code == 'Escape' ) {
                    // exits details view if there
                    if ( this.page == "post details" ) {
                        this.closeDetailsView();
                    }
                    //otherwise deactivates keyboard navigation
                    else {
                        this.keyboard_navigation_active = false;
                    }
                }

                // L (log distances and dimensions for figuring out scrolling)
                if (e.code == 'KeyL') {
                    let all_posts_window = document.getElementById('scrollable_body');
                    let height_of_all_posts_window = all_posts_window.offsetHeight;
                    const height_of_post_list_item = document.getElementsByClassName('post_list_item').item(0).offsetHeight;
                    let highlighted_item = document.getElementsByClassName('highlighted').item(0);
                    let scroll_amount = window.pageYOffset;
                    let viewport_height = window.innerHeight;
                    let highlighted_item_distance_from_top = highlighted_item.offsetTop;
                    let highlighted_item_height = highlighted_item.offsetHeight;
                    let highlighted_item_distance_from_top_of_viewport = highlighted_item_distance_from_top - scroll_amount + 109; 
                    let highlighted_item_distance_to_bottom_of_viewport = viewport_height - highlighted_item_distance_from_top_of_viewport - highlighted_item_height;
                    console.table({
                        "page height" : height_of_all_posts_window,
                        "scroll amount" : scroll_amount,
                        "Viewport height" : viewport_height,
                        "Highlighted Item Height": highlighted_item_height,
                        "Highlighted Item Distance From Top" : highlighted_item_distance_from_top,
                        "Highlighted Item Distance From Top Of Viewport" : highlighted_item_distance_from_top_of_viewport,
                        "Highlighted Item Distance to Bottom Of Viewport": highlighted_item_distance_to_bottom_of_viewport
                    });
                }

                // J (move down the posts)
                if (e.code == 'KeyJ') {
                    if (this.page == 'post details') {
                        // use J for scrolling down if in details view
                        document.getElementById('details-area').scrollBy(0,200);
                    } else {
                        // if keyboard navigation is not active, turn it on
                        // and reset position of highlight                    
                        if (!this.keyboard_navigation_active) {
                            this.keyboard_navigation_active = true;
                            this.keyboard_navigation_index = 0;
                        // otherwise augment by 1
                        } else {
                            if (this.keyboard_navigation_index < this.filtered_posts.length - 1) {
                                this.keyboard_navigation_index += 1;
                                this.make_sure_highlighted_item_stays_visible();
                            } 
                        }
                    }
                }
                // K (move up the posts)
                if (e.code == 'KeyK') {
                    if (this.page == 'post details') {
                        // use K for scrolling up if in details view
                        document.getElementById('details-area').scrollBy(0,-200);
                    } else {
                        // if keyboard navigation is not active, turn it on
                        // and reset position of highlight                    
                        if (!this.keyboard_navigation_active) {
                            this.keyboard_navigation_active = true;
                            this.keyboard_navigation_index = 0;
                        // otherwise augment by 1
                        } else {
                        if (this.keyboard_navigation_index > 0) {
                            this.keyboard_navigation_index -= 1;
                            this.make_sure_highlighted_item_stays_visible();
                        } 
                        }
                    }
                } 
                // O or Enter (open highlighted post)
                if ( (e.code == 'KeyO' || e.code == 'KeyEnter') && this.keyboard_navigation_active) {
                    // open highlighted post if we're in list mode                    
                    if (this.page == "post list") {
                        this.showDetailsView(this.filtered_posts[this.keyboard_navigation_index]);
                    // Navigate to url if we're in details mode
                    }else{
                        window.open(this.filtered_posts[this.keyboard_navigation_index].url, '_blank');
                    }
                }
                // R or E (Mark Post as read)
                if ((e.code == 'KeyR' || e.code == 'KeyE') && this.keyboard_navigation_active) {
                    this.togglePostRead(this.filtered_posts[this.keyboard_navigation_index]);
                    if (this.page == "post details") {
                       this.closeDetailsView(); 
                    }
                }
                if (e.code == 'KeyS'){
                    if (this.page == "post list" && this.keyboard_navigation_active) {
                        this.saveforlater(this.filtered_posts[this.keyboard_navigation_index].url);
                    // Navigate to url if we're in details mode
                    }else if (this.page =="post details"){
                        this.saveforlater(this.active_post.url);
                    }
                }
            },
            NavigateToNthPost(n){
                //
            },
            ToggleUnreadOnly() {
                this.unread_only = !this.unread_only;
            },
            ToggleOldestOnTop(d) {
                this.oldest_on_top = d;
            },
            autoRefreshPosts() {
                //refresh posts when last fetch is older than this.refreshinterval
                console.log('refreshing posts');
                if((Date.now() - this.last_fetch_posts) > (this.refreshinterval * 60000)) {
                    this.fetchPostList();
                }
            },
            fetchPostList() {
                axios.get(this.posts_source).then((res) => {
                    this.posts = res.data;
                    this.active_post = this.posts[0];
                    this.last_fetch_posts = Date.now();
                    this.updateDocumentTitle();
                    this.posts_loaded = true;
                });
            },
            updateDocumentTitle() {
                if (this.unread_count > 0) {
                    document.title = `(${this.unread_count}) InfraRead`;
                } else {
                    document.title = `InfraRead`;
                }
            },
            showDetailsView(post){
                document.getElementById('details-area').scrollTo(0,0);
                this.active_post = post;
                this.page = "post details";
                axios.get('/api/postContentById/' + this.active_post.id).then((res) => {
                    this.active_post_content=res.data.content;
                });
            },
            closeDetailsView() {
                console.log('called');
                // mark post as read if unread
                if (this.active_post.read == 0) {
                    this.togglePostRead(this.active_post);
                }
                this.active_post_content="loading...";
                // change to list view
                this.page = 'post list';
            },
            togglePostRead(post) {
                post.read = 1 - post.read ; // toggle between 0 and 1
                this.updateDocumentTitle() ;
                axios.patch('/api/posts/'+post.id, {read: post.read})
                .then((res) => {
                    //nothing
                }).catch((res) => {
                    console.log('there was a problem with updating post status');
                    post.read = 1 - post.read;
                });
            },
            saveforlater(url){
                this.saving_later_status = 'saving';
                axios.get('/app/readlater?url='+encodeURI(url)).
                then((res) => {
                    if (res.data.bookmark_id || res.data.status == 1) {
                        this.saving_later_status = 'success';
                        // remove success message after 2 seconds
                        setTimeout((t) => {
                           this.saving_later_status = 'nothing'; 
                        }, 2000);
                    } else {
                        this.saving_later_status = 'failure';
                    }
                }).catch((res) => {
                    // nothing
                });
            },
            updateActivePost(post) {
                this.activepost = post;
                this.visible = ! this.visible;
            },
            make_sure_highlighted_item_stays_visible(){
                // Set the variables for dimensions and offsets
                let all_posts_window = document.getElementById('scrollable_body');
                let height_of_all_posts_window = all_posts_window.offsetHeight;
                const height_of_post_list_item = document.getElementsByClassName('post_list_item').item(0).offsetHeight;
                let highlighted_item = document.getElementsByClassName('post_list_item').item(this.keyboard_navigation_index);
                let scroll_amount = window.pageYOffset;
                let viewport_height = window.innerHeight;
                let highlighted_item_distance_from_top = highlighted_item.offsetTop;
                let highlighted_item_height = highlighted_item.offsetHeight;
                let highlighted_item_distance_from_top_of_viewport = highlighted_item_distance_from_top - scroll_amount + 109; 
                let highlighted_item_distance_to_bottom_of_viewport = viewport_height - highlighted_item_distance_from_top_of_viewport - highlighted_item_height;

                let margin = 120;

                // if highlighted item is below the viewport, scroll up
                if (highlighted_item_distance_to_bottom_of_viewport < 0) {
                    window.scrollBy(0, - highlighted_item_distance_to_bottom_of_viewport + margin);
                }
                console.log(highlighted_item_distance_from_top_of_viewport);
                if (highlighted_item_distance_from_top_of_viewport < 0) {
                    window.scrollBy(0,highlighted_item_distance_from_top_of_viewport - margin);
                }

            }

        }
    }
</script>
<style scoped>
    .prevent-scrolling{
        height:100vh;
        overflow-y:hidden;
    }
    #savinglater{
        z-index: 300; 
        position: fixed;
        right: 1em;
        top: 0;
        padding: 3px;
        color:grey;
        font-size: 12px;
    }
    .progress{
        height:5px;
    }
</style>