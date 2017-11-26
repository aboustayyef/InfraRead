<template>
    <div>
        <post-details 
            :activepost="activepost" 
            :visible="visible"
            v-on:toggle="visible = !visible">
        </post-details>

        <post-list 
            v-on:show-post-details="updateActivePost(...arguments)"
            >
        </post-list>
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
                visible: false
            };
        },
        
        methods: {
            updateActivePost(post) {
                this.activepost = post;
                this.visible = ! this.visible;
            }
        }
    }
</script>