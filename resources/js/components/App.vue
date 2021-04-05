<template>
  <div  class="relative w-full h-screen p-4 pt-12 overflow-y-auto text-left md:p-12">
    <div v-cloak v-if="posts_loaded == false">Loading...</div>
    <div v-if="posts_loaded == true" class="flex items-center justify-between mx-auto mb-6 max-w-7xl">
        
        <!-- Logo -->
        <a href="/"><img class="h-8 md:h-12" src="/img/infraread144.png"></a>
        
        <!-- Unread Count -->
        <div id="ReadCount" class="text-gray-500 uppercase">
                unread: {{number_of_unread_posts}} 
        </div>

        <!-- Settings Gear -->
        <a href="/admin" class="block">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 text-gray-300 cursor-pointer hover:text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </a>
        
    </div>
        
        <!-- Banner that will only be displayed if viewing one source -->
        <div v-if="which_source !== 'all'"> 
            <div class="container flex items-center justify-between p-2 py-4 mx-auto mb-4 rounded-md shadow-md bg-gray-50 max-w-7xl">
                <div class="text-sm font-semibold text-gray-600 uppercase">Posts by {{source_name}}</div>
                <button @click="reset_to_all()" class="w-8 h-8 text-lg text-gray-400 bg-gray-100 rounded-full hover:bg-primary hover:text-white">
                    &times;
                </button>
            </div>
        </div>
        
        <!-- List of Posts -->
        <div v-for="(post , index) in unread_posts" :key="post.id" class="p-2 mx-auto border-b border-gray-200 cursor-pointer max-w-7xl" :class="{'bg-yellow-50': highlighter_on && index==highlighter_position }">

                <!-- Individual Post -->
                <div :id="'post-' + index" class="md:flex">

                    <!-- Title, author and date -->
                    <div class="w-full md:mr-12 md:w-1/2">
                        <h2 v-on:click="display_post(post)" class="pt-6 text-2xl font-semibold text-gray-700 cursor-pointer">{{post.title}}</h2>
                        <h3 v-on:click="switch_source('source', post.source.id, post.source.name)" class="mt-2 text-xl font-semibold uppercase text-primary">{{post.source.name}}</h3>
                        <h4 class="mt-4 text-lg text-gray-500">{{post.time_ago}}</h4>
                    </div>

                    <!-- Body of Post -->
                    <div v-on:click="display_post(post)" class="w-full mt-6 text-xl font-light leading-relaxed text-gray-400 cursor-pointer overflow-clip md:mt-0 md:w-1/2">
                        <p>{{post.excerpt}}</p>
                    </div>

                </div>
                
                <!-- Mark as Read Button -->
                <div class="w-1/2 mb-6">
                    <button v-on:click="mark_post_as_read(post)" class="px-4 py-2 mt-4 border border-gray-300 rounded-md hover:bg-primary hover:text-white">Mark Read</button>
                </div>
                
        </div>
        
        <post 
            :post="displayed_post"
            v-on:exit-post="exit_post"
        >
        </post>  

    <!-- Messages -->
    <div class="fixed inline-block px-8 py-2 transition duration-75 ease-out transform border border-gray-600 shadow-md translate-x-72 top-8 right-8"
                  :class="{'-translate-x-72' : show_message == true , 'bg-yellow-100': message_kind == 'warning', 'bg-blue-100': message_kind == 'info', 'bg-green-100': message_kind == 'success'}" 
    >
    {{message_content}} 
    </div>

</div>

</template>
<script>
export default {
  props: ['refreshInterval'],
  data() {
    return {
      posts_loaded: false,
      posts: {},
      displayed_post:{},
      posts_marked_as_read:[],
      which_posts:'all',
      which_source: 'all',
      source_name:'',
      highlighter_on: false,
      highlighter_position: 0,
      message_kind:'warning',
      message_content: 'this is the message',
      show_message: false,
      external_links_shortcuts: false,
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
        this.posts_loaded = true;
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
        this.external_links_shortcuts = false;
        this.displayed_post = p;
        // Timeout the animation then set as read
            this.mark_post_as_read(p);
    },
    mark_post_as_read: function(p){
        // update locally
        p.read = 1;
        this.posts_marked_as_read.push(p);
        // update on the server
        axios.patch("/api/posts/" + p.id, { read: 1 })
        // If server update works, don't report anything
        .then((res) => { })
        // If there's a problem, undo mark as read
        .catch((res) => {
          p.read = 0;
          this.posts_marked_as_read.pop();
          this.display_message('warning','Cannot contact server',2000);
        })
    },
    display_message(kind,content,time){
        this.message_kind = kind;
        this.message_content = content;
        this.show_message = true;
        setTimeout(()=> {
            this.show_message = false;
        }, time);
    },
    exit_post: function(){
        this.displayed_post = {};
    },
    show_highlighted_post(){
        document.querySelector('#post-'+this.highlighter_position).scrollIntoView({behavior: "smooth", block: "center", inline: "nearest"});
    },

    turn_on_external_links_shortcuts: function(){
        let shortcut_style = "mx-1 px-2 bg-yellow-200 text-grey-800";
        document.querySelectorAll('#post-content a').forEach(function(link,i){
            var html = `<span data-destination="${link.getAttribute('href')}" class="externallink ${shortcut_style}">${i}</span>`;
            link.insertAdjacentHTML('afterend', html);
        })
        this.external_links_shortcuts = true;
    },

    turn_off_external_links_shortcuts: function(){
        document.querySelector('#post-content').innerHTML = this.displayed_post.content;
        this.external_links_shortcuts = false;
    },

    handle_keyboard_shortcut(key){
        console.log(key);
        switch (key) {
            case ('f' || 'F'):
                if (! this.external_links_shortcuts) {
                    console.log('turn on');
                    this.turn_on_external_links_shortcuts();
                } else {
                    this.turn_off_external_links_shortcuts();
                }

                break;
            case 'Escape':
                if (this.view == 'post') {
                   this.exit_post(); 
                }
                if (this.view == 'list' && this.highlighter_on){
                   this.highlighter_on = false;
                   this.highlighter_position = 0; 
                }
                break;
            case ' ':
                if (this.view == 'post') {
                   document.querySelector('#post-view').scrollBy({top: 500, behavior: 'smooth'});
                }
                break;
            case ('j' || 'J'): 
                if (this.view == 'post') {
                   document.querySelector('#post-view').scrollBy(0, 200) 
                } else {
                    if (this.highlighter_on == false) {
                        this.highlighter_on = true;
                        this.show_highlighted_post();
                    } else {
                        if (this.highlighter_position < this.number_of_unread_posts - 1) {
                            this.highlighter_position++;
                            this.show_highlighted_post();
                        }
                    }
                }
                break;
            case ('k' || 'K'): 
                if (this.view == 'post') {
                   document.querySelector('#post-view').scrollBy(0, -200) 
                } else {
                    if (this.highlighter_on == false) {
                        this.highlighter_on = true;
                        this.show_highlighted_post();
                    } else {
                        if (this.highlighter_position > 0) {
                            this.highlighter_position--;
                            this.show_highlighted_post();
                        }
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
                break;
            case ('e' || 'E'):
                if (this.view == 'list' && this.highlighter_on == true) {
                   this.mark_post_as_read(this.highlighted_post); 
                   return;
                }
                if (this.view == 'post') {
                    this.exit_post();
                }
                break;
            case ('u' || 'U'):
                if (this.view == 'list' && this.posts_marked_as_read.length > 0) {
                    
                    // mark last post in list as unread
                    let last_post_marked_as_read = this.posts_marked_as_read[this.posts_marked_as_read.length - 1];
                    last_post_marked_as_read.read = 0;

                    // update on server
                    axios.patch("/api/posts/" + last_post_marked_as_read.id, { read: 0 })
                    // If server update works, update list of posts marked as read 
                    .then((res) => { 
                        this.posts_marked_as_read.pop();
                    })
                    // If there's a problem, undo mark as read
                    .catch((res) => {
                    last_post_marked_as_read.read = 1;
                    this.display_message('warning','Cannot contact server',2000);
                    }) 
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