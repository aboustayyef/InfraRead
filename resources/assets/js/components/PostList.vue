<template>
    <div class="container">
        <div class="tabs is-right is-boxed">
            <ul>
                <li :class="{'is-active':only_unread}" @click="only_unread = ! only_unread"><a>Unread Posts</a></li>
                <li :class="{'is-active':!only_unread}" @click="only_unread = ! only_unread"><a>All Posts</a></li>
            </ul>
        </div>

        <div class='row'>
            <ul>
                <li v-if='!posts_loaded'>Loading Posts...</li>
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
</template>

<script>
    export default {
        props: ['posts'],
        data() {

        },

        methods: {

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
            showPostDetails(post){
                // bubble up to App
                this.$emit('show-post-details',post);
            },
            showSource(source){
                // bubble up
                this.source_id = source.id;
                this.source_name = source.name;
                this.fetchPostList();
            },
            readButtonMessage(post)
            {
                if (post.read) {
                    return "Mark Unread";
                }
                return "Mark Read";
            },
        },
    }
</script>