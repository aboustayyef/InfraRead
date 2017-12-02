<template>
    <div>
        <post-details
            v-if="page == 'post details' " 
            :active_post="active_post" 
            v-on:closeWindow="closeDetailsView()">
        </post-details>
<!-- 
        <post-filters></post-filters>
-->
        <!-- List of Posts -->
        <div class="container" v-if="page == 'post list'">
            <div v-if="posts_loaded" class='row'>
                <ul>
                    <li v-for="post in filtered_posts">
                        <!-- When a user clicks on an area of the post, change the current post and mark it as read -->
                        <post-list-item 
                            :post="post"
                            v-on:show-post-details="showPostDetails(post)"
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
                page: 'post list',
                posts : [],
                posts_loaded : false,
                active_post : {},
                filter: null,
                filter: 'post.read == 0',
            };
        },
        created() {
            this.fetchPostList();
        },
        computed: {
            filtered_posts()
            {
                if (! this.filter ) { return this.posts }

                return this.posts.filter( (post) => {
                    return eval(this.filter);
                });

            }
        },
        methods: {
            fetchPostList() 
            {
                let request_string = 'api/posts';
                axios.get(request_string).then((res) => {
                    this.posts = res.data;
                    this.posts_loaded = true;
                    this.active_post = this.posts[0];
                });
            },
            showPostDetails(post){
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