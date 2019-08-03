<template>
    <!-- When a user clicks on an area of the post, change the current post and mark it as read -->
    <div class="columns" :class=" {read: post.read, highlighted: highlighted}">
        <!-- Left Pane -->
        <div class="column is-half">
            <div class="content">
                <a :href="post.url" target="_blank"><h1 v-html="post.title"
                    @click="showPostDetails($event)"
                    class="has-text-grey-dark is-title is-size-5 has-text-weight-bold">
                </h1></a>
                <a :href='"/app/source/" + post.source.id' ><h2 
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
            <a :href="post.url" target="_blank">
                <p
                    @click="showPostDetails($event)"
                    class="has-text-grey">
                    {{ post.excerpt }}
                </p>
            </a>
            <br>
            <a class="button" @click="togglePostRead(post)">{{readButtonMessage()}}</a>
        </div>

    </div>
</template>

<script>
    export default {
        props: ['post','index','keyboard_navigation_active','keyboard_navigation_index'],       
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
            togglePostRead(post)
            {
                this.$emit('toggle-post-read', post);
            },
            showPostDetails(event)
            {
                if((event && event.ctrlKey) || (event && event.metaKey)) {
                    return;
                }
                event.preventDefault();
                // Tells the the parent components that the current post has changed
                this.$emit('show-post-details')
            }
        },
        computed: {
            highlighted(){
                return (this.keyboard_navigation_active && this.keyboard_navigation_index == this.index);
            }
        },
    }
</script>
<style scoped>
    p {
         cursor: pointer;
         line-height: 1.5;
    }
    .column{
        padding: 0.5rem 0.75rem;
        overflow-x:hidden;
    }
    .highlighted{
        background-color: beige;
    }
</style>