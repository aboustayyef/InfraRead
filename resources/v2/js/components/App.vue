<template>
  <section class="section">
    <div class="container is-fluid">
        <div class="columns">

            <!-- Leftmost column: <sources>, Sources.vue -->
            <div class="column is-3" style="border:1px solid silver" >
                <sources :sources="sources" :categories="categories" :posts="posts"></sources>
            </div>

            <!-- Middle Column: <post-titles>, PostTitles.vue -->
            <div class="column is-4" style="background-color:beige">Posts</div>
            
            <!-- Rightmost Column: <post-content>, PostContent.vue -->
            <div class="column" style="background-color:teal">Post Details</div>

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
        }
    }
</script>
<style scoped></style>