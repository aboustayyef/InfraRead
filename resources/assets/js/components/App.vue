<template>
    <div>
        <!-- <post-details 
            :activepost="activepost" 
            :visible="visible"
            v-on:toggle="visible = !visible">
        </post-details>
        
        <post-filters></post-filters>
-->
        <!-- List of Posts -->
        <div class="container">
            <div v-if="posts_loaded" class='row'>
                <ul>
                    <li v-for="post in posts">
                        <!-- When a user clicks on an area of the post, change the current post and mark it as read -->
                        <post-list-item 
                            :post="post"
                            v-on:toggleReadStatus="togglePostRead(post)"
                            v-on:show-post-details="showPostDetails(post)"
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
                posts : [],
                posts_loaded : false,
                active_post : {},
                filter: null,
                filter: 'post.category.id == 3',
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
                let request_string = 'api/postsByReadStatus';
                axios.get(request_string).then((res) => {
                    this.posts = res.data;
                    this.posts_loaded = true;
                    this.active_post = this.posts[0];
                });
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