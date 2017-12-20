<template>
    <div>
        <post-details
            v-if="posts_loaded"
            :page="page"
            :active_post="active_post"
            v-on:closeWindow="closeDetailsView()">
        </post-details>

        <!-- Level with breadcrumbs and settings -->
            <div class="container">
                <div class="level">
                    <nav class="breadcrumb has-arrow-separator level-left" style="margin-bottom:0" aria-label="breadcrumbs">
                      <ul>
                        <li><a href="/app/sources">Home</a></li>
                        <li class="is-active">&nbsp;
                            <span v-if="posts_description == 'All Posts'">
                            {{posts_description}}
                            </span>
                            <span v-else class="tag" >
                                {{posts_description}} &nbsp; 
                                <button class="delete is-small" @click="showallposts()"></button>
                            </span>

                        </li>
                      </ul>
                    </nav>
                    <form>
                        <div class="level-right">
                            <label class="ios7-switch">
                                <input
                                  type="checkbox"
                                  v-model="unread_only"
                                  true-value="true"
                                  false-value="false"
                                >
                                <span></span>
                                Unread Only <span v-if="unread_count>0">({{unread_count}})</span>
                                &nbsp;&nbsp;
                            </label>
                             <label class="ios7-switch">
                                <input
                                  type="checkbox"
                                  v-model="oldest_on_top"
                                  true-value="true"
                                  false-value="false"
                                >
                                <span></span>
                                Oldest On Top
                            </label>
                        </div>
                    </form>
                </div>
                <div class="level" v-if="unread_count > 0">
                    <div class="level-left">

                    </div>
                    <div class="level-right buttons">
                        <button class="button" v-show="!areyousure" @click="toggleAreYouSure()">Mark All Posts as Read</button>
                        <p v-show="areyousure" class="level-item">Are you sure?</p>
                        <a v-show="areyousure" class="level-item button is-danger" href="/markallread">Yes</a>
                        <a v-show="areyousure" class="level-item button" @click="toggleAreYouSure">No</a>
                    </div>
                </div>
            </div>
        <div class="container" v-show="page == 'post list'">
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
                    <li v-for="post in filtered_posts">
                        <!-- When a user clicks on an area of the post, change the current post and mark it as read -->
                        <post-list-item
                            :post="post"
                            v-on:show-post-details="showDetailsView(post)"
                            v-on:toggle-post-read="togglePostRead(post)"
                        ></post-list-item>
                        <hr>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props: [
            'refreshinterval'
        ],
        data() {
            return {
                page: window.page,    // used to know which view we're in: post list, post details or post filters
                posts_source: window.posts_source, // which XHR request to get posts
                posts_description: window.posts_description,
                posts : [], // the list of unfiltered posts
                posts_loaded : false,
                active_post : {}, // the posts which is in the post details view mode
                unread_only: "true", // default filter = unread posts
                last_fetch_posts: 0,
                all_sources:[],
                all_categories:[],
                oldest_on_top: "false", //location on list page, to remember when exiting details page
                areyousure: false,
            };
        },
        created() {
            // Start with local storage
            this.posts = JSON.parse(localStorage.getItem(this.posts_storage_key) || '[]');
            this.unread_only = localStorage.getItem('infraread-unread-only') || "true";
            this.oldest_on_top = localStorage.getItem('infraread-oldest-on-top') || "false";
            if (this.posts.length > 0) {
                this.posts_loaded = 'storage';
                this.active_post = this.posts[0];
            }
            this.fetchPostList();
            window.onfocus = this.autoRefreshPosts;
        },
        watch: {
            posts: function(){
                localStorage.setItem(this.posts_storage_key, JSON.stringify(this.posts));
            },
            unread_only: function(){
                localStorage.setItem('infraread-unread-only', this.unread_only);
            },
            oldest_on_top: function(){
                localStorage.setItem('infraread-oldest-on-top', this.oldest_on_top);
            },
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
                let posts_list = this.posts;
                if (this.unread_only == "true")
                {
                    posts_list = this.posts.filter((post)=>{
                        return post.read == 0
                    });
                }
                if (this.oldest_on_top == "true") {
                    return posts_list.reverse();
                }
                return posts_list;
            },
            showallposts()
            {
                window.location = "/";
            }
        },
        methods: {

            autoRefreshPosts()
            {
                //refresh posts when last fetch is older than this.refreshinterval
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
                });
            },

            toggleAreYouSure()
            {
                this.areyousure = !this.areyousure;
            },

            // Detail View

            showDetailsView(post){
                this.active_post = post;
                this.page = "post details";
            },
            closeDetailsView()
            {
                // mark post as read if unread
                if (this.active_post.read == 0) {
                    this.togglePostRead(this.active_post);
                }
                // change to list view
                this.page = 'post list';
            },
            togglePostRead(post)
            {
                post.read = 1 - post.read; // toggle between 0 and 1

                axios.patch('/api/posts/'+post.id, {read: post.read})
                .then((res) => {
                    // nothing
                }).catch((res) => {
                    console.log('there was a problem with updating post status');
                    post.read = 1 - post.read;;
                });
            },

            reverseOrder()
            {
                this.reverse = !this.reverse;
            },

            updateActivePost(post)
            {
                this.activepost = post;
                this.visible = ! this.visible;
            }
        }
    }
</script>