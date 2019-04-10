<template>
    <transition name="fade">
    <div v-if="page == 'post list' && unread_count > 0"  class = "unread_count">
        <button class="button is-primary is-small">Unread: {{unread_count}}</button>
    </div>
    </transition>
</template>
<script>
    export default {
        props: ['page', 'unread_count'],
    }
</script>
<style scoped>
    .unread_count{
        z-index:300;
        position: fixed;
        left:1em;
    }
    .button{
       border-top-right-radius:0;
       border-top-left-radius:0;
       border-top:0;
 
    }
    .fade-enter-active, .fade-leave-active {
      transition: opacity .5s;
    }
    .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
      opacity: 0;
}
</style>