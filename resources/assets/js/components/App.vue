<template>
    <div>
        <post-details
            v-if="page == 'post details' " 
            :active_post="active_post"
            v-on:closeWindow="closeDetailsView()">
        </post-details>
       
        <!-- Read / Unread Tabs -->
        <div class="row" v-if="page == 'post list'">
            <div class="container">
                <div class="tabs is-boxed is-right">
                  <ul>
                    <li :class="{'is-active': this.unread_only}" @click="unread_only = true"> <a>Unread ({{unread_count}})</a></li>
                    <li :class="{'is-active': !this.unread_only}" @click="unread_only = false"> <a>All Posts</a></li>
                  </ul>
                </div>
            </div>
        </div>

        <!-- Breadcrumbs -->
            <div class="container">
                <div class="level">
                    <nav class="breadcrumb has-arrow-separator" aria-label="breadcrumbs">
                      <ul>
                        <li><a @click="showSourceSelector()">Home</a></li>
                        <li class="is-active"><a href="#">{{posts_description}}</a></li>
                      </ul>
                    </nav>
                    <button v-if=" this.reverse" class="button is-right" @click="reverseOrder()">&uarr; Oldest On Top</button>
                    <button v-if=" !this.reverse" class="button is-right" @click="reverseOrder()">&darr; Newest on Top</button>
                </div>
            </div>
        <div class="container" v-show="page == 'post list'">
            <div class="row" v-if="filtered_posts.length == 0 && posts_loaded">
                There are no unread posts... <a @click="unread_only = false">See All posts</a>
            </div>
            <div v-if="posts_loaded" class='row'>
                <ul>
                    <li v-for="post in filtered_posts">
                        <!-- When a user clicks on an area of the post, change the current post and mark it as read -->
                        <post-list-item 

                            :post="post"
                        
                            v-on:show-post-details="showDetailsView(post)"
                            v-on:toggle-post-read="togglePostRead(post)"
                            v-on:show-source="showSource(post.source)"
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
        data() {
            return {
                page: window.page,    // used to know which view we're in: post list, post details or post filters
                posts_source: window.posts_source, // which XHR request to get posts
                posts_description: window.posts_description, 
                posts : [], // the list of unfiltered posts
                posts_loaded : false, 
                active_post : {}, // the posts which is in the post details view mode
                unread_only: true, // default filter = unread posts
                all_sources:[],
                all_categories:[],
                reverse: false, //location on list page, to remember when exiting details page
            };
        },
        created() {
            this.fetchPostList();
        },
        computed: {
            unread_count()
            {
                return this.posts.filter((post) => {return post.read == 0}).length;
            },
            filtered_posts()
            {
                let posts_list = this.posts;
                if (this.unread_only) 
                { 
                    posts_list = this.posts.filter((post)=>{
                        return post.read == 0
                    });
                }
                if (this.reverse) {
                    return posts_list.reverse();
                }
                return posts_list;
            }
        },
        methods: {
            fetchPostList() 
            {
                axios.get(this.posts_source).then((res) => {
                    this.posts = res.data;
                    this.posts_loaded = true;
                    this.active_post = this.posts[0];
                });
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

                axios.patch('api/posts/'+post.id, {read: post.read})
                .then((res) => {
                    this.fetchPostList();
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