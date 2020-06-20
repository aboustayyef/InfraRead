<template>
  <section class="section">
    <div class="container">
        <div class="columns">

            <!-- Leftmost column: <sources>, Sources.vue -->
            <div class="column is-one-quarter" style="border:1px solid silver" >
                <sources :sources="JSON.parse(sources)" :categories="JSON.parse(categories)" :posts="JSON.parse(posts)"></sources>
            </div>

            <!-- Middle Column: <post-titles>, PostTitles.vue -->
            <div class="column is-one-quarter" style="background-color:beige">Posts</div>
            
            <!-- Rightmost Column: <post-content>, PostContent.vue -->
            <div class="column" style="background-color:teal">Post Details</div>

        </div> <!-- /columns -->
    </div> <!-- /container -->
  </section>
</template>
<script>
    export default {
        props: [
            'posts','categories','sources','refreshinterval','last_successful_crawl'
        ],
        data() {
            return {
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