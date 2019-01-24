<template>
    <div class="container">
      <a v-if="page == 'post details'" aria-label="close" @click.prevent="closeWindow" class="fab is-medium">&times;</a>
    </div>
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
  .fab {
    z-index: 300;
    width: 70px;
    height: 70px;
    background-color: black;
    border-radius: 50%;
    -webkit-box-shadow: 0 6px 10px 0 #666;
    box-shadow: 0 6px 10px 0 #666;
    -webkit-transition: all 0.1s ease-in-out;
    transition: all 0.1s ease-in-out;
    font-size: 50px;
    color: white;
    text-align: center;
    line-height: 60px;
    position: fixed;
    left: 50px;
    bottom: 50px;
    font-weight: 700;
    font-size: 65px;
}
 
.fab:hover {
   box-shadow: 0 6px 14px 0 #666;
   transform: scale(1.05);
}
  nav.open{
    transform: translateX(0%);
  }
  .container{
    padding: 1em;
  }
</style>