<template>
    <div class="container">
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
                list: [],
                source_id: null,
                source_name: null,
            };
        },
        
        created() {
            this.fetchPostList();
        },
        
        methods: {
            fetchPostList() 
            {
                let request_string = 'api/posts';
                if (this.source_id) {
                    request_string = 'api/posts/' + this.source_id;
                }
                axios.get(request_string).then((res) => {
                    this.list = res.data;
                });
            },
            togglePostRead(post)
            {
                post.read = 1 - post.read; // toggle between 0 and 1
                this.$emit('testing','the payload');

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