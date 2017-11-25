<template>
    <div class="container">
        <div class='row'>
            <ul>
                <li v-if='list.length === 0'>There are no posts yet!</li>
                <li v-for="(post, index) in list">
                    <div class="columns" @click="updateCurrentPost(post)" v-bind:class=" {read: post.read}">
                        <!-- Left Pane -->
                        <div class="column is-half">
                            <h1 class="has-text-grey-dark is-title is-size-4 has-text-weight-bold">{{ post.title }}</h1>
                            <h2 class="has-text-primary is-subtitle is-size-5 is-uppercase has-text-weight-semibold">{{ post.source.name }}</h2>
                            <h3 class="is-size-6 has-text-grey-light" >{{post.time_ago}}</h3>
                        </div>
        
                        <!-- Right Pane -->
                        <div class="column is-half">
                            <p class="has-text-grey">{{ post.excerpt }}</p>
                            <a class="button" @click="markPostRead(post)">Mark Read</a>
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
            };
        },
        
        created() {
            this.fetchPostList();
        },
        
        methods: {
            fetchPostList() 
            {
                axios.get('api/posts').then((res) => {
                    this.list = res.data;
                });
            },
            markPostRead(post)
            {
                post.read = 1;
                axios.patch('api/posts/'+post.id, {read: 1})
                .then((res) => {
                    this.fetchPostList();
                });
            },
            updateCurrentPost(post)
            {
                this.$emit('update', post) 
            }
        }
    }
</script>