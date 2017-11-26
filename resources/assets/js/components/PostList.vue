<template>
    <div class="container">
        <!-- If a source is selected, display a breadcrumb navigation to enable going back-->
        <nav v-if="source" class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="#" @click.prevent="updateSource({id:null,name:null})">Home</a></li>
                <li>{{source_name}}</li>
            </ul>
        </nav>
        <div class='row'>
            <ul>
                <li v-if='list.length === 0'>There are no posts yet!</li>
                <li v-for="post in list">
                    <!-- When a user clicks on an area of the post, change the current post and mark it as read -->
                    <div class="columns" :class=" {read: post.read}">
                        <!-- Left Pane -->
                        <div class="column is-half">
                            <div class="content">
                                <a href="#"><h1 v-html="post.title" 
                                    @click="updateCurrentPost(post)"
                                    class="has-text-grey-dark is-title is-size-4 has-text-weight-bold">
                                </h1></a>
                                <a href="#"><h2 
                                    @click.prevent="updateSource(post.source)" 
                                    class="has-text-primary is-subtitle is-size-5 is-uppercase has-text-weight-semibold"
                                    >{{ post.source.name }}
                                </h2></a>
                                <h3 
                                    @click="updateCurrentPost(post)" 
                                    class="is-size-6 has-text-grey-light" >
                                    {{post.time_ago}}
                                </h3>
                            </div>
                        </div>
        
                        <!-- Right Pane -->
                        <div class="column is-half">
                            <p
                                @click="updateCurrentPost(post)" 
                                class="has-text-grey">
                                {{ post.excerpt }}
                            </p>
                            <br>
                            <a class="button" @click="togglePostRead(post)">{{readButtonMessage(post)}}</a>
                        </div>
        
                    </div>
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
                source: null,
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
                if (this.source) {
                    request_string = 'api/posts/' + this.source;
                }
                axios.get(request_string).then((res) => {
                    this.list = res.data;
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
            readButtonMessage(post)
            {
                if (post.read) {
                    return "Mark Unread";
                }
                return "Mark Read";
            },
            updateSource(source){
                this.source = source.id;
                this.source_name = source.name;
                this.fetchPostList();
            },
            updateCurrentPost(post)
            {
                // mark post as read
                this.togglePostRead(post);

                // Tells the the parent component <posts> that the current post has changed
                this.$emit('update', post) 
            }
        },
    }
</script>