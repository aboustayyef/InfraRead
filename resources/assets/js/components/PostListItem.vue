<template>
    <!-- When a user clicks on an area of the post, change the current post and mark it as read -->
    <div class="columns" :class=" {read: post.read}">
        <!-- Left Pane -->
        <div class="column is-half">
            <div class="content">
                <a href="#"><h1 v-html="post.title" 
                    @click="showPostDetails()"
                    class="has-text-grey-dark is-title is-size-4 has-text-weight-bold">
                </h1></a>
                <a href="#"><h2 
                    @click.prevent="showSource()" 
                    class="has-text-primary is-subtitle is-size-5 is-uppercase has-text-weight-semibold inline-at-mobile"
                    >{{ post.source.name }}
                </h2></a>
                <h3 
                    @click="showPostDetails()" 
                    class="is-size-6 has-text-grey-light inline-at-mobile" >
                    {{post.time_ago}}
                </h3>
            </div>
        </div>

        <!-- Right Pane -->
        <div class="column is-half">
            <p
                @click="showPostDetails()" 
                class="has-text-grey">
                {{ post.excerpt }}
            </p>
            <br>
            <a class="button" @click="togglePostRead()">{{readButtonMessage()}}</a>
        </div>

    </div>
</template>

<script>
    export default {
        props: ['post'],       
        methods: {
            readButtonMessage()
            {
                if (this.post.read) {
                    return "Mark Unread";
                }
                return "Mark Read";
            },
            showSource(){
                this.$emit('show-source');
            },
            togglePostRead(){
                this.$emit('toggleReadStatus')
            },
            showPostDetails()
            {
                // mark post as read
                this.$emit('toggleReadStatus');

                // Tells the the parent components that the current post has changed
                this.$emit('show-post-details') 
            }
        },
    }
</script>