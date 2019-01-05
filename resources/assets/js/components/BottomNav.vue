<template>
  <nav :class="{'open': page == 'post details' }">
    <div class="container">
      <a aria-label="close" @click.prevent="closeWindow" class="button is-medium">Done</a>
    </div>
  </nav>
</template>
<script>
    export default {
        props: ['page'],
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
    padding-bottom:1em;
    position:fixed;
    bottom:0;
    transform: translateX(100%);
    transition: transform 0.3s ease;
  }
  .button{
    padding-left:2em;
    padding-right:2em;
  }
  nav.open{
    transform: translateX(0%);
  }
  .container{
    padding: 1em 2em;
  }
</style>