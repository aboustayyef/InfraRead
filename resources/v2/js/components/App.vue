<template>
  <section class="section">
    <div class="container is-fluid">
        <div class="columns fixedHeight">


            <!-- Leftmost column: <sources>, Sources.vue -->
            <div class="column fixedHeight is-3" style="border:1px solid silver;background-color:whitesmoke" >
                <sources 
                    :sources="sources" :categories="categories" :posts="posts"
                    :highlightedSource ="highlightedSource"
                    v-on:selectSource = "selectSource"
                >
                </sources>
            </div>


            <!-- Middle Column: <post-titles>, PostTitles.vue -->
            <div class="column is-paddingless fixedHeight is-4" style="border:1px solid silver">
                <post-titles
                    :posts = "posts" 
                    :selectedPost = "post"
                    :highlightedSource="highlightedSource"
                    v-on:clickedOnCircle = "togglePostReadStatus"
                    v-on:clickedOnPostTitle = "selectPost" 
                ></post-titles>
            </div>


            <!-- Rightmost Column: <post-content>, PostContent.vue -->
            <div class="column fixedHeight" style="padding:2.5rem; border:1px solid silver" >
                <post :post="post"></post>
            </div>


        </div> <!-- /columns -->
    </div> <!-- /container -->
  </section>
</template>
<script>
    export default {
        props: [
            'posts_raw','categories_raw','sources_raw','refreshinterval','last_successful_crawl'
        ],
        data() {
            return {
                sources: JSON.parse(this.sources_raw),
                categories: JSON.parse(this.categories_raw),
                highlightedSource: 'allUnread', // Which source is highlighted on the first column
                selectedPostIndex:0, // index of post highlighted in middle column
                post: null,
                posts:JSON.parse(this.posts_raw)
                .sort((a,b) => (a.posted_at > b.posted_at) ? -1 : ((b.posted_at > a.posted_at) ? 1 : 0))
                // ^ sorting array of objects by property (Newest first). Source: https://stackoverflow.com/questions/1129216/sort-array-of-objects-by-string-property-value
                .map(obj=> ({ ...obj, seconds: Date.parse(obj.posted_at) }))
                // ^ adding the "Seconds" property, for seconds since 1970, source: https://stackoverflow.com/questions/38922998/add-property-to-an-array-of-objects
            };
        },
        created() {
            
        },
        computed: {
        },
        methods: {
            selectSource({kind,value}){
                this.highlightedSource = value;
                this.selectedPostIndex = 0;
            },
            togglePostReadStatus(post){

                var targetPost = this.posts[this.getPostIndexByID(post.id)]; // We're using this because we're mutating original, unfiltered list of posts 
                var oldReadStatus = targetPost.read;
                var newReadStatus = Math.abs(targetPost.read - 1 ); 
                // First Toggle in the UI
                targetPost.read = newReadStatus;
                // Perform ajax request
                axios.patch('/api/posts/'+post.id, {read: newReadStatus})
                .then((res) => {
                    //nothing
                }).catch((res) => {
                    console.log('there was a problem with updating post status');
                    //undo from UI
                    targetPost.read = oldReadStatus;
                });
            },
            markPostRead(post){
                // bounce back if post already read
                if (post.read == 1) {
                    console.log('post already read');
                    return;
                }
                // otherwise
                var targetPost = this.posts[this.getPostIndexByID(post.id)]; // We're using this because we're mutating original, unfiltered list of posts 
                // First Toggle in the UI
                targetPost.read = 1;
                // Perform ajax request
                axios.patch('/api/posts/'+post.id, {read: 1})
                .then((res) => {
                    //nothing
                }).catch((res) => {
                    console.log('there was a problem with updating post status');
                    targetPost.read = 0;
                });

            },
            selectPost(n,p){

                this.selectedPostIndex = n;
                this.post = p;
                this.markPostRead(p);
                console.log(n);
            },

            // Utility functions
            // ===================
            getPostIndexByID(id){ // Get the array index of post from post id
                for (let index = 0; index < this.posts.length; index++) {
                    if (this.posts[index].id == id ) {
                        return index;
                    }
                }
                return false;
            }
        }
    }
</script>
<style scoped>
    .fixedHeight{
        height:90vh;
        overflow:scroll;
    }
</style>