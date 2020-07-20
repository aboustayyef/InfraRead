<template>
    <div class="container" v-if="page == 'post details'" >
      <a  aria-label="close" @click.prevent="closeWindow" class="fab is-medium"><div class="close">&times;</div></a>
      <a href="" @click.prevent="$emit('clickSaveLater')" class="fab right">{{save_for_later_message}}</a>
    </div>

</template>
<script>
    export default {
        props: ['page','saving_later_status'],
        methods:
        {
            closeWindow()
            {
                this.$emit('closeWindow'); 
            }
        },
        computed: {
          save_for_later_message: function(){
            if (this.saving_later_status == 'nothing') {
              return 'Save'; 
            }
            if (this.saving_later_status == 'saving') {
              return '...';
            }

            if (this.saving_later_status == 'success') {
              return 'Saved!'
            }


          }
        },
    }
</script>
<style scoped>
.close{
  height:60px;
}
  .fab {
    display: grid;
    place-items: center;
    z-index: 300;
    width: 70px;
    height: 70px;
    background-color: black;
    border-radius: 50%;
    -webkit-box-shadow: 0 6px 10px 0 #666;
    box-shadow: 0 6px 10px 0 #666;
    -webkit-transition: all 0.1s ease-in-out;
    transition: all 0.1s ease-in-out;
    color: white;
    line-height: 50px;
    position: fixed;
    left: 50px;
    bottom: 50px;
    font-weight: 700;
    font-size: 60px;
}

.fab.right{
  left:150px;
  font-size:1em;
  background-color: beige;
  color:black;
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