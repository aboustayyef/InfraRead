<template>
  <section class="section">
    <div class="container">
        <div class="columns">
            <div class="column is-one-quarter" style="background-color:silver">
                <sources></sources>
            </div>
            <div class="column is-one-quarter" style="background-color:beige">Posts</div>
            <div class="column" style="background-color:teal">Post Details</div>
        </div> <!-- /columns -->
    </div> <!-- /container -->
  </section>
</template>
<script>
    export default {
        props: [
            'refreshinterval','last_successful_crawl'
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