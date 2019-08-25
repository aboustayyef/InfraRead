<template>
    <div :class="{'prevent-scrolling': page == 'post details'}">
        <post-details
            v-if="posts_loaded"
            :page="page"
            :active_post="active_post"
        ></post-details>
        
        <unread-count
            :page="page"
            :unread_count="unread_count">
        </unread-count>
        <bottom-nav
            v-on:closeWindow="closeDetailsView()"
            v-on:keyup.esc="closeDetailsView()"
            :page="page"
        ></bottom-nav>
        
        <header-settings
            :posts_description="posts_description"
            :unread_only="unread_only"
            v-on:UnreadOnlyToggle="ToggleUnreadOnly"
            :oldest_on_top="oldest_on_top"
            v-on:OldestOnTopToggle="ToggleOldestOnTop"
            :last_successful_crawl="last_successful_crawl"

        ></header-settings>
            
        <div class="container">
            <div class="row" v-if="filtered_posts.length == 0 && posts_loaded">
                There are no unread posts... <a @click="unread_only = false">See All posts</a>
            </div>

            <article class="message is-warning" v-if="posts_loaded == 'storage'">
              <div class="message-body ">
                Updating posts...
              </div>
            </article>

            <div v-if="posts_loaded" class='row'>
                <ul>
                    <li v-for="(post,i) in filtered_posts" v-bind:key="post.id">
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
        </div>
    </div>
</template>
<script>
    export default {
        props: [
            'refreshinterval','last_successful_crawl'
        ],
        data() {
            return {
                page: window.page,    // used to know which view we're in: post list, post details or post filters
                posts_source: window.posts_source, // which XHR request to get posts
                posts_description: window.posts_description,
                posts : [], // the list of unfiltered posts
                posts_loaded : false,
                active_post : {}, // the posts which is in the post details view mode
                unread_only: true, // default filter = unread posts
                last_fetch_posts: 0,
                all_sources:[],
                all_categories:[],
                oldest_on_top: true, //location on list page, to remember when exiting details page
                areyousure: false,
                keyboard_navigation_active:false,
                keyboard_navigation_index:0,
            };
        },
        created() {
            // Start with local storage
            this.posts = JSON.parse(localStorage.getItem(this.posts_storage_key) || '[]');
            if (this.posts.length > 0) {
                this.posts_loaded = 'storage';
                this.active_post = this.posts[0];
            }
            this.fetchPostList();

            // Capture Keyboard input
            document.addEventListener('keydown', this.handleKeyboardInput);
            
        },
        watch: {
            posts: function(){
                localStorage.setItem(this.posts_storage_key, JSON.stringify(this.posts));
            }
        },
        computed: {

            posts_storage_key()
            {
               return 'feedreader-' + this.posts_source;
            },
            unread_count()
            {
                return this.posts.filter((post) => {return post.read == 0}).length;
            },
            filtered_posts()
            {
                let posts_copy = this.posts.slice(); //used slice() because reverse() mutates original array
                if (this.oldest_on_top) {
                    posts_copy.reverse(); 
                }
                if (this.unread_only) {
                    posts_copy = posts_copy.filter((post) => !post.read);
                } 
                return posts_copy;
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
                if (e.code == 'KeyI'){
                    if (this.page == "post list" && this.keyboard_navigation_active) {
                        this.savetoinstapaper(this.filtered_posts[this.keyboard_navigation_index].url);
                    // Navigate to url if we're in details mode
                    }else if (this.page =="post details"){
                        this.savetoinstapaper(this.active_post.url);
                    }
                }
            },
            NavigateToNthPost(n)
            {
                //
            },
            ToggleUnreadOnly()
            {
                this.unread_only = !this.unread_only;
            },

            ToggleOldestOnTop(d)
            {
                this.oldest_on_top = d;
            },

            autoRefreshPosts()
            {
                //refresh posts when last fetch is older than this.refreshinterval
                console.log('refreshing posts');
                if((Date.now() - this.last_fetch_posts) > (this.refreshinterval * 60000)) {
                    this.fetchPostList();
                }
            },

            fetchPostList()
            {
                axios.get(this.posts_source).then((res) => {
                    this.posts = res.data;
                    this.posts_loaded = 'server';
                    this.active_post = this.posts[0];
                    this.last_fetch_posts = Date.now();
                    this.updateDocumentTitle();
                });
            },

            updateDocumentTitle()
            {
                if (this.unread_count > 0) {
                    document.title = `(${this.unread_count}) InfraRead`;
                } else {
                    document.title = `InfraRead`;
                }
            },

            // Detail View

            showDetailsView(post){
                document.getElementById('details-area').scrollTop=0;
                this.active_post = post;
                this.page = "post details";
            },
            closeDetailsView()
            {
                console.log('called');
                // mark post as read if unread
                if (this.active_post.read == 0) {
                    this.togglePostRead(this.active_post);
                }
                // change to list view
                this.page = 'post list';
            },
            togglePostRead(post)
            {
                post.read = 1 - post.read ; // toggle between 0 and 1
                this.updateDocumentTitle() ;
                axios.patch('/api/posts/'+post.id, {read: post.read})
                .then((res) => {
                    // nothing
                }).catch((res) => {
                    console.log('there was a problem with updating post status');
                    post.read = 1 - post.read;
                });
            },
            savetoinstapaper(url){
                console.log('saving to instapaper: '+ url);
                axios.get('app/readlater?url='+encodeURI(url)).
                then((res) => {
                    if (res.data.bookmark_id) {
                        console.log('succesfully saved');
                    } else {
                        console.log('could not save');
                    }
                }).catch((res) => {
                    // nothing
                });
            },
            updateActivePost(post)
            {
                this.activepost = post;
                this.visible = ! this.visible;
            }
        }
    }
</script>
<style scoped>
    .prevent-scrolling{
        height:100vh;
        overflow-y:hidden;
    }
</style>