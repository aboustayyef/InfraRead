<template>
    <div>
        <post-details
            v-if="page == 'post details' " 
            :active_post="active_post" 
            v-on:closeWindow="closeDetailsView()">
        </post-details>


        <source-selector 
            v-if="page == 'source selector'"
            :sources = "all_sources"
            :categories = "all_categories"
            v-on:showAllPosts="showAllPosts()"
            v-on:showBySource="showSource(...arguments)"
            v-on:showByCategory="showCategory(...arguments)"
            v-on:closeWindow="hideSourceSelector()">
        </source-selector>

       
        <!-- Read / Unread Tabs -->
        <div class="level" v-if="page == 'post list'">
            <div class="container">
                <div class="tabs is-boxed is-right">
                  <ul>
                    <li :class="{'is-active': this.unread_only}" @click="unread_only = true"> <a>Unread</a></li>
                    <li :class="{'is-active': !this.unread_only}" @click="unread_only = false"> <a>All Posts</a></li>
                  </ul>
                </div>
            </div>
        </div>

        <!-- Breadcrumbs -->
        <div class="level" v-if="page == 'post list'">
            <div class="container">
                <nav class="breadcrumb has-arrow-separator" aria-label="breadcrumbs">
                  <ul>
                    <li><a @click="showSourceSelector()">Home</a></li>
                    <li class="is-active"><a href="#">{{posts_description}}</a></li>
                  </ul>
                </nav>
            </div>
        </div>
        <div class="container" v-if="page == 'post list'">
            
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
                page: 'post list',    // used to know which view we're in: post list, post details or post filters
                posts_source: '/api/posts', // which XHR request to get posts
                posts_description: 'All Posts', 
                posts : [], // the list of unfiltered posts
                posts_loaded : false, 
                active_post : {}, // the posts which is in the post details view mode
                unread_only: true, // default filter = unread posts
                all_sources:[],
                all_categories:[]
            };
        },
        created() {
            this.getAllSources();
            this.getAllCategories();
            this.fetchPostList();
        },
        computed: {
            filtered_posts()
            {
                if (this.unread_only) 
                { 
                    return this.posts.filter((post)=>{
                        return post.read == 0
                    });
                }
                return this.posts;
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
            getAllSources() 
            {
                axios.get('/api/source').then((res) => {
                    this.all_sources = res.data;
                });
            },
            getAllCategories() 
            {
                axios.get('/api/category').then((res) => {
                    this.all_categories = res.data;
                });
            },

            // Changing Source
            
            showAllPosts(){
                this.posts_source = '/api/posts' ;
                this.posts_description = 'All Posts" ';
                this.fetchPostList();
                this.page = "post list";
            },
            showSource(source){
                this.posts_source = '/api/postsBySource/' + source.id;
                this.posts_description = 'Posts By " ' + source.name + ' "';
                this.fetchPostList();
                this.page = "post list";
            },
            showCategory(category){
                this.posts_source = '/api/postsByCategory/' + category.id;
                this.posts_description = 'Posts By " ' + category.description + ' "';
                this.fetchPostList();
                this.page = "post list";
            },

            // Source Selector
            showSourceSelector(){
                this.page = "source selector";
            },
            hideSourceSelector(){
                this.page = "post list";
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

                // update list of posts
                this.fetchPostList();
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

            updateActivePost(post)
            {
                this.activepost = post;
                this.visible = ! this.visible;
            }
        }
    }
</script>