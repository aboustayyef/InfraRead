<template>
    <div class="container">
          <div class="content">
            <a class="button" @click="closeWindow" aria-label="close">Done</a>
          </div>
          <div class="content">
          <a :href="active_post.url"><h1 class="has-text-grey-dark is-title is-size-4 has-text-weight-bold">{{ active_post.title }}</h1></a>
          <h2 class="has-text-primary is-subtitle is-size-5 is-uppercase has-text-weight-semibold">{{ active_post.source.name }}</h2>
          <h3 class="is-size-6 has-text-grey-light" >{{active_post.time_ago}}</h3>
          </div>
          <div class="content" v-html="active_post.content"></div>     
    </div>
</template>
<script>
    export default {
        props: ['active_post'],
        methods:
        {
            closeWindow()
            {
                this.$emit('closeWindow'); 
            }
        }
    }
</script>