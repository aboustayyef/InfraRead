<template>
  <div  class="pt-12 relative text-left w-full h-screen overflow-y-auto p-12">
    <div class="max-w-7xl mx-auto flex mb-6 justify-between">
        <div id="ReadCount" class="bg-primary px-4 py-1 rounded-md text-white">
            <div class="max-w-7xl mx-auto">
                Unread: {{number_of_unread_posts}} 
            </div>
        </div>
        <a href="/admin" class="block">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 text-gray-300 hover:text-primary cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </a>
    </div>
        
        <div v-if="which_source !== 'all'"> 
            <div class="bg-gray-50 shadow-md rounded-md mb-4 flex justify-between max-w-7xl p-2 container mx-auto py-4 items-center">
                <div class="text-gray-600 uppercase text-sm font-semibold">Posts by {{source_name}}</div>
                <button @click="reset_to_all()" class="text-lg text-gray-400  w-8 h-8 rounded-full bg-gray-100 hover:bg-primary hover:text-white">
                    &times;
                </button>
            </div>
        </div>
        
        <div v-for="(post , index) in unread_posts" :key="post.id" class="border-b border-gray-200 max-w-7xl mx-auto cursor-pointer p-2" :class="{'bg-yellow-50': highlighter_on && index==highlighter_position }">
                <div :id="'post-' + index" class="flex">
                <div class="mr-12 w-1/2">
                        <h2 v-on:click="display_post(post)" class="cursor-pointer text-2xl font-semibold text-gray-700 pt-6">{{post.title}}</h2>
                        <h3 v-on:click="switch_source('source', post.source.id, post.source.name)" class="mt-2 font-semibold text-xl uppercase text-primary">{{post.source.name}}</h3>
                        <h4 class="mt-4 text-gray-500 text-lg">{{post.time_ago}}</h4>
                    </div>
                    <div v-on:click="display_post(post)" class="cursor-pointer w-1/2 font-light leading-relaxed text-gray-400 text-xl">
                        <p>{{post.excerpt}}</p>
                    </div>
                </div>
                <div class="w-1/2 mb-6">
                    <button v-on:click="mark_post_as_read(post)" class="border border-gray-300 rounded-md px-4 py-2 mt-4 hover:bg-primary hover:text-white">Mark Read</button>
                </div>
        </div>
        


        <post 
            :post="displayed_post"
            v-on:exit-post="exit_post"
        >
        </post>  
</div>

</template>
<script>
export default {
  props: ['refreshInterval'],
  data() {
    return {
      posts: {},
      displayed_post:{},
      which_posts:'all',
      which_source: 'all',
      source_name:'',
      highlighter_on: false,
      highlighter_position: 0
    };
  },
  created() {
      this.fetch_posts_from_server();
      window.addEventListener('keydown', (e) => {
          this.handle_keyboard_shortcut(e.key);
      })
  },
  computed: {
      unread_posts: function(){
          if (Object.keys(this.posts).length > 0) {
              return this.posts.filter((post) => !post.read == 1 );
          }
      },
      number_of_unread_posts: function(){
          if (Object.keys(this.posts).length > 0) {
              return Object.keys(this.unread_posts).length
      }},
    //   view mode: post or list
      view: function(){
          if (Object.keys(this.displayed_post).length > 0) {
              return 'post'
          }
          return 'list'
      },
      highlighted_post: function(){
          return this.unread_posts[this.highlighter_position];
      }
  },
  methods: {
    fetch_posts_from_server: function(){
        axios.get('/simpleapi/' + this.which_posts).then((res) => {
        this.posts = res.data;
      })
    },
    reset_to_all: function(){
            this.which_posts = 'all';
            this.which_source = 'all';
            this.source_name = '';
            this.fetch_posts_from_server()
    },
    switch_source: function(which, details = null, name=null){
        if (which == 'all') {
            this.reset_to_all()
        } else {
            this.which_posts = which + '/' + details;
            this.which_source = details;
            this.source_name = name
        }
        this.fetch_posts_from_server()
    },
    display_post: function(p) {
        this.displayed_post = p;
        // Timeout the animation then set as read
        setTimeout(() => {
            this.mark_post_as_read(p);
        }, 200)
    },
    mark_post_as_read: function(p){
        // update locally
        p.read = 1;
        // update on the server
        axios.patch("/api/posts/" + p.id, { read: 1 })
        // If server update works, don't report anything
        .then((res) => { })
        // If there's a problem, undo mark as read
        .catch((res) => {
          p.read = 0;
        })
    },
    exit_post: function(){
        this.displayed_post = {};
    },
    show_highlighted_post(){
        document.querySelector('#post-'+this.highlighter_position).scrollIntoView({behavior: "smooth", block: "center", inline: "nearest"});
    },
    handle_keyboard_shortcut(key){
        console.log(key);
        switch (key) {
            case 'Escape':
                if (this.view == 'post') {
                   this.exit_post(); 
                }
                if (this.view == 'list' && this.highlighter_on){
                   this.highlighter_on = false;
                   this.highlighter_position = 0; 
                }
                break;
            case ('j' || 'J'): 
                if (this.highlighter_on == false) {
                    this.highlighter_on = true;
                    this.show_highlighted_post();
                } else {
                    if (this.highlighter_position < this.number_of_unread_posts - 1) {
                        this.highlighter_position++;
                        this.show_highlighted_post();
                    }
                }
                break;
            case ('k' || 'K'): 
                if (this.highlighter_on == false) {
                    this.highlighter_on = true;
                    this.show_highlighted_post();
                } else {
                    if (this.highlighter_position > 0) {
                        this.highlighter_position--;
                        this.show_highlighted_post();
                    }
                }
                break;
            case 'Enter': 
                if (this.view == 'list' && this.highlighter_on == true) {
                    this.display_post(this.highlighted_post);
                } 
                break;
            
            case ('o' || 'O'):
                if (this.view == 'list' && this.highlighter_on == true) {
                   this.display_post(this.highlighted_post); 
                   return;
                }
                if (this.view == 'post') {
                   window.open(this.displayed_post.url,'_blank');
                }

            default:
                break;
        }
    }
  },
};
</script>

<style scoped>
</style>