<template>
<div class="container">
  <!-- Level with breadcrumbs and settings -->
      <div class="level">
          <nav class="breadcrumb has-arrow-separator level-left" style="margin-bottom:0" aria-label="breadcrumbs">
            <ul>
              <li><a href="/app/sources">Home</a></li>
              <li class="is-active">&nbsp;
                  <span v-if="posts_description == 'All Posts'">
                  {{posts_description}}
                  </span>
                  <span v-else class="tag" >
                      {{posts_description}} &nbsp; 
                      <button class="delete is-small" @click.prevent="showallposts"></button>
                  </span>

              </li>
            </ul>
          </nav>
          <form>
              <div class="level-right">
                  <label class="ios7-switch">
                      <input
                        type="checkbox"
                        v-model="unread_only"
                      >
                      <span></span>
                      Unread Only <span v-if="unread_count>0">({{unread_count}})</span>
                      &nbsp;&nbsp;
                  </label>
                   <label class="ios7-switch">
                      <input
                        type="checkbox"
                        v-model="oldest_on_top"
                      >
                      <span></span>
                      Oldest On Top
                  </label>
              </div>
          </form>
      </div>
      <div class="level" v-if="unread_count > 0" >
          <div class="level-left">
          <small class="has-text-grey-light" v-if="last_successful_crawl !== 'problem'">Last Update: {{last_successful_crawl}}</small>
          <span v-else class="tag is-small is-warning">There was a problem updating</span>
          </div>
          <div class="level-right buttons">
              <button class="button" v-show="!areyousure" @click="toggleAreYouSure()">Mark All Posts as Read</button>
              <p v-show="areyousure" class="level-item">Are you sure?</p>
              <a v-show="areyousure" class="level-item button is-danger" href="/markallread">Yes</a>
              <a v-show="areyousure" class="level-item button" @click="toggleAreYouSure">No</a>
          </div>
      </div>
  </div>
</template>
<script>
    export default {
      props: [
      'posts_description',
      'unread_count', 
      'last_successful_crawl',
      ],
      data(){
        return {
          areyousure:false,
          unread_only: true,
          oldest_on_top: true
        }
      },
      watch: {
        unread_only(){
          this.$emit('UnreadOnlyToggle');
        },
        oldest_on_top(){
          this.$emit('OldestOnTopToggle',this.oldest_on_top);
        },
      },
      methods: {
        toggleAreYouSure()
          {
              this.areyousure = !this.areyousure;
          },
        showallposts()
          {
            window.location ='/';
          }
      },
    }
</script>
<style scoped></style>