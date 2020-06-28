<template>
<div>
    <div v-if="filteredPosts.length == 0">No Posts To See</div>
        <article v-else v-for="(post,n) in filteredPosts" v-bind:key="post.id" 
                @click="$emit('clickedOnPostTitle',n, post)"
                :class="{'selected': selectedPost && selectedPost.id == post.id }" 
                >
                <div class="centered">
                    <span 
                        :class="{'dot':true,'read':post.read == 1}"
                        @click.stop="$emit('clickedOnCircle',post)"
                    >
                    </span>
                </div>
                <div class="content">
                    <h1 class="title is-size-6"> {{post.title}}</h1>
                    <h2 class="subtitle is-size-7" v-if="highlightedSource=='allUnread' || highlightedSource == 'today'">
                        {{post.source.name}}
                    </h2>
                    <p :title="post.excerpt">{{post.excerpt | trimText}}</p>
                </div>
        </article>
</div>
</template>

<script>
export default {
    props:['selectedPost','posts', 'highlightedSource'],
    data() {
        return {
            unreadPosts: [],
            test: "This is the value of test"
        }
    },
    filters: {
        trimText: function(s){
            return s.substring(0, 100) + " ...";
        }
    },
    computed: {
        filteredPosts() {
            
            //  For the "allUnread" smart filter, we use a singleton 
            //  to prevent posts disappearing when marked read
            
            if (this.highlightedSource == "allUnread") {
                if (this.unreadPosts.length == 0) {
                    this.unreadPosts = this.posts.filter((post) => post.read == 0);
                }
                return this.unreadPosts;
            }
            
            if (this.highlightedSource == "today") {
                let todayseconds = (new Date).setHours(0,0,0,0);
                return this.posts.filter((post) =>
                post.seconds > todayseconds
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

    article {
        background:white;
        padding: 1.2em 1em 1.2em 0.5em;
        display:flex;
        cursor: pointer;
        border-bottom:1px solid silver;
    }
    article:hover{
        background-color:rgb(248, 248, 248);
    }
    article.selected{
        background-color: #f9e4e4;
    }
    .title + .subtitle{
        /* margin-bottom:0.5rem; */
    }
    article p{
        margin-top:-1rem;
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
    .dot:active{
     background-color:#5a1212; 
    }
    .dot:hover{
        background-color:#f0b0b0;
    }
    .centered{
        display:flex;
        align-items: center;
        justify-content: center;
    }
</style>