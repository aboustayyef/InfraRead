<template>
    <div>

        <post-list 
            v-on:update="updateActivePost">
        </post-list>

        <post-details 
            :activepost="activepost" 
            :visible="visible"
            v-on:toggle="visible = !visible">
        </post-details>
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