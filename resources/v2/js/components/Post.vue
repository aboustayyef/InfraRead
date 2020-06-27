<template>
<div>
    <div class="container">
      <div class="content is-marginless">
          <a :href="post.url"><h1 class="has-text-grey-dark title is-4 has-text-weight-bold">{{ post.title }}</h1></a>
          <h2 class="has-text-primary subtitle is-5 is-uppercase has-text-weight-semibold">{{ post.source.name }}</h2>
          <p class="is-6 has-text-grey-light" >
            {{post.time_ago}}
            <span v-if="post.author">
              by {{post.author}}
            </span>
          </p>          
      </div>
      <hr>
      <div class="content">
          <div v-html="sanitized_content"></div>
       </div> 
    </div>
</div>
</template>

<script>
export default {
    props:['post'],
    data() {
        return {
        }
    },
    computed:{
        sanitized_content(){
            var san = this.post.content.replace(/http\:/gi, 'https\:') || this.post.content;
              return san;
        }
    },

    methods: {
    }
}
</script>

<style></style>