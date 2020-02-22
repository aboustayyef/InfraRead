<template>
  <section id="details-area" :class="{'open': page == 'post details'}" >
    <div class="container">
      <div class="content is-marginless">
          <a :href="active_post.url"><h1 class="has-text-grey-dark title is-4 has-text-weight-bold">{{ active_post.title }}</h1></a>
          <h2 class="has-text-primary subtitle is-5 is-uppercase has-text-weight-semibold">{{ active_post.source.name }}</h2>
          <p class="is-6 has-text-grey-light" >
            {{active_post.time_ago}}
            <span v-if="active_post.author">
              by {{active_post.author}}
            </span>
          </p>          
      </div>
      <hr>
      <div class="content has-columns">
          <div v-html="sanitized_content"></div>
        </div> 
    </div>
  </section>
</template>
<script>
    export default {
        props: ['page','active_post','active_post_content'],
        data() {
          return {
            content: 'loading..'
          }
        },
        computed: {
            sanitized_content(){
              var san = this.active_post_content.replace(/http\:/gi, 'https\:') || this.active_post_content;
              return san;
            }
        },
        mounted () {
          axios.get('/api/postContentById/' + this.active_post.id).then((res) => {
                    this.content = res.data.content;
                });
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
  #details-area{
      background-color: white;
      z-index:200;
      padding-top:3em;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      bottom:0;
      overflow-y:scroll;
      -webkit-overflow-scrolling: touch;
      transform: translateX(100%);
      transition: transform 0.3s ease;
  }
  #details-area.open{
    transform: translateX(0%);
  }
  .container{
    padding:1em;
  }
  .content{
    margin-bottom:5em;
    overflow-x: hidden;
  }
  .content.has-columns{
    column-width: 22em;
    column-count:2;
    column-rule: 1px dotted #ddd;
  }

  .content img{
      max-width:100%;
    }
</style>