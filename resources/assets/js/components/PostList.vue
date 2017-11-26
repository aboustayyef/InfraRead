<template>
    <div class="container">
        <div class="tabs is-right">
            <ul>
                <li :class="{'is-active':only_unread}" @click="only_unread = ! only_unread"><a>Unread Posts</a></li>
                <li :class="{'is-active':!only_unread}" @click="only_unread = ! only_unread"><a>All Posts</a></li>
            </ul>
        </div>
        <!-- If a source is selected, display a breadcrumb navigation to enable going back-->
        <nav v-if="source_id" class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="#" @click.prevent="showSource({id:null,name:null})">Home</a></li>
                <li>{{source_name}}</li>
            </ul>
        </nav>
        <div class='row'>
            <ul>
                <li v-if='list.length === 0'>There are no posts yet!</li>
                <li v-for="post in list">
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
        data() {
            return {
                raw_list: [],           // all posts
                only_unread: true,       // show only unread posts
                source_id: null,        // if null, show all posts, otherwise, show only posts by source
                source_name: null,      // name of the source
            };
        },
        
        created() {
            this.fetchPostList();
        },
        computed: {
            list(){
                if (this.only_unread) {
                    return this.raw_list.filter(item => item.read == 0);
                }
                // otherwise
                return this.raw_list;
            }
        },
        methods: {
            fetchPostList() 
            {
                let request_string = 'api/posts';
                if (this.source_id) {
                    request_string = 'api/sourcePosts/' + this.source_id;
                }
                axios.get(request_string).then((res) => {
                    this.raw_list = res.data;

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