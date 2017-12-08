<template>
  <div :class="{'modal': true , 'is-active': page == 'post details' }"> 
    <div class="modal-background"></div> 
    <div class="modal-card">
      <header class="modal-card-head">
        <a aria-label="close" @click.prevent="closeWindow" class="button">Done</a>
      </header> 
      <section class="modal-card-body">
        <div class="content">
          <a :href="active_post.url"><h1 class="has-text-grey-dark is-title is-size-4 has-text-weight-bold">{{ active_post.title }}</h1></a>
          <h2 class="has-text-primary is-subtitle is-size-5 is-uppercase has-text-weight-semibold">{{ active_post.source.name }}</h2>
          <h3 class="is-size-6 has-text-grey-light" >{{active_post.time_ago}}</h3>          
        </div> 
        <div class="content" v-html="active_post.content"></div>
      </section>
    </div>
  </div>
</template>
<script>
    export default {
        props: ['page','active_post'],
        methods:
        {
            closeWindow()
            {
                this.$emit('closeWindow'); 
            }
        }
    }
</script>