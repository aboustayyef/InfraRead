<template>
    <div>
        <post-details :activepost="activepost"></post-details>
        <post-list v-on:update="updateActivePost"></post-list>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                activepost: {
                    title: 'No Post selected yet',
                    source:{name: 'No Source yet'},
                    time_ago:'',
                    content: "Content will be available once a post is selected"
                },
            };
        },
        
        methods: {
            updateActivePost(post) {
                this.activepost = post;
            }
        }
    }
</script>