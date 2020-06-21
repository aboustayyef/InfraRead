<template>
<div>
    <div v-if="filteredPosts.length == 0">No Posts To See</div>
        <article v-else v-for="post in filteredPosts">
                <div class="centered">
                    <span 
                        :class="{'dot':true,'read':post.read == 1}"
                    >
                    </span>
                </div>
                <div>
                    <h1 class="title is-size-6"> {{post.title}}</h1>
                    <h2 class="subtitle is-size-7" v-if="highlightedSource=='allUnread' || highlightedSource == 'today'">
                        {{post.source.name}}
                    </h2>
                </div>
        </article>
</div>
</template>

<script>
export default {
    props:['posts', 'highlightedSource'],
    data() {
        return {
            test: "This is the value of test"
        }
    },
    computed: {
        filteredPosts() {
            if (this.highlightedSource == "allUnread") {
                return this.posts.filter((post) =>
                post.read == 0
            );
            }
            
            if (this.highlightedSource == "today") {
                let todayseconds = (new Date).setHours(0,0,0,0);
                return this.posts.filter((post) =>
                post.seconds > todayseconds && post.read == 0
            );
            }

            // Else return source specific post
            return this.posts.filter((post) => 
              post.source_id == this.highlightedSource
            );

        }
    },
    methods: {
    }
}
</script>

<style>
    article{
        background:white;
        padding:0.5em 0.2em;
        margin:0.2em 0;
        display:flex;
    }
    .dot {
        height: 12px;
        width: 12px;
        background-color: #da2525;
        border-radius: 50%;
        display: inline-block;
        margin:0 0.7em;
    }
    .dot.read{
        background-color:transparent;
        border:2px solid #e6c9c9;
    }
    .centered{
        display:flex;
        align-items: center;
        justify-content: center;
    }
</style>