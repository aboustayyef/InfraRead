<template>
  <nav>
    <div class="container">
      <a aria-label="close" @click.prevent="closeWindow" class="button">Done</a>
    </div>
  </nav>
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
  nav{  
    background-color:#efefef;
    z-index:300;
    border-top:1px solid #da2525;
    width:100%;
    position:fixed;
    bottom:0;
  }
</style>