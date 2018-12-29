<template>
  <section class="section" v-if="page == 'post details'">
    <div class="container is-paddingless">
      <div class="content" style="max-width:800px; margin:auto">
          <p><a aria-label="close" @click.prevent="closeWindow" class="button">Done</a></p>
          <hr>
          <a :href="active_post.url"><h1 class="has-text-grey-dark title is-4 has-text-weight-bold">{{ active_post.title }}</h1></a>
          <h2 class="has-text-primary subtitle is-5 is-uppercase has-text-weight-semibold">{{ active_post.source.name }}</h2>
          <p class="is-6 has-text-grey-light" >
            {{active_post.time_ago}}
            <span v-if="active_post.author">
              by {{active_post.author}}
            </span>
          </p>          
          <div v-html="sanitized_content"></div>
          <hr>
          <p><a aria-label="close" @click.prevent="closeWindow" class="button">Done</a></p>
        </div> 
    </div>
  </section>
  <!-- <div :class="{'modal': true , 'is-active': page == 'post details' }"> 
    <div class="modal-background"></div> 
    <div class="modal-card">
      <header class="modal-card-head">
        <a aria-label="close" @click.prevent="closeWindow" class="button">Done</a>
      </header> 
      <section class="modal-card-body">
        <div class="content">
          <a :href="active_post.url"><h1 class="has-text-grey-dark title is-4 has-text-weight-bold">{{ active_post.title }}</h1></a>
          <h2 class="has-text-primary subtitle is-5 is-uppercase has-text-weight-semibold">{{ active_post.source.name }}</h2>
          <p class="is-6 has-text-grey-light" >
            {{active_post.time_ago}}
            <span v-if="active_post.author">
              by {{active_post.author}}
            </span>
          </p>          
        </div> 
        <div class="content" v-html="sanitized_content"></div>
      </section>
    </div>
  </div> -->
</template>
<script>
    export default {
        props: ['page','active_post'],
        computed: {
            sanitized_content(){
              var san = this.active_post.content.replace(/http\:/gi, 'https\:') || this.active_post.content;
              return san;
            }
        },
        methods:
        {
            closeWindow()
            {
                this.$emit('closeWindow'); 
            }
        }
    }
</script>
<style scoped>
</style>