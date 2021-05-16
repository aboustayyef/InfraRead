<template>
  <div  class="relative w-full h-screen p-4 pt-12 overflow-y-auto text-left md:p-12">
    <div v-cloak v-if="posts_loaded == false">Loading...</div>
    <div v-if="posts_loaded == true" class="flex items-center justify-between mx-auto mb-6 max-w-7xl">
        
        <!-- Logo -->
        <a href="/"><img class="h-8 md:h-12" src="/img/infraread144.png"></a>
        
        <!-- Unread Count -->
        <div id="ReadCount" class="text-gray-500 uppercase">
                <span v-if="number_of_unread_posts > 0"> unread: {{number_of_unread_posts}} </span>
                <span v-else>Done! Enjoy your day</span>
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

        <div v-if="last_successful_crawl_data.status == 'warning'" class="text-yellow-800 max-w-7xl bg-yellow-50 w-full mx-auto px-4 py-2 border border-yellow-200 ">
            {{last_successful_crawl_data.message}}
        </div> 

        <!-- Well Done! You've read everything -->
        <div v-if="number_of_unread_posts < 1" class="container mx-auto">
            <svg id="b799fbe1-7cb7-4635-8884-b814a9e7e215" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" class="mt-12 w-1/2 mx-auto" viewBox="0 0 640.65724 577.17886"><path d="M438.404,307.82132c-11.01605-32.92966-45.0348-51.23148-67.41427-43.7448s-38.5377,42.57432-27.52165,75.504c10.19672,30.48049,41.09425,48.07684,62.34943,44.93672a2.4111,2.4111,0,0,0-.29727.86443l-.64994,5.42078a2.44167,2.44167,0,0,0,3.24841,2.589l10.94888-3.92591a2.44172,2.44172,0,0,0,.8633-4.06338l-3.94671-3.77257a2.41653,2.41653,0,0,0-.46175-.33188C434.48011,371.08126,448.62292,338.36807,438.404,307.82132Z" transform="translate(-279.67138 -161.41057)" fill="#f2f2f2"/><path d="M398.52421,283.59439a40.17042,40.17042,0,0,1,18.17735,52.25234c-.9474,2.15673,2.53662,3.29094,3.4778,1.1483A43.92349,43.92349,0,0,0,400.556,280.54609C398.48974,279.45115,396.44264,282.49133,398.52421,283.59439Z" transform="translate(-279.67138 -161.41057)" fill="#fff"/><path d="M884.404,259.82132c-11.016-32.92966-45.0348-51.23148-67.41427-43.7448s-38.5377,42.57432-27.52165,75.504c10.19672,30.48049,41.09425,48.07684,62.34943,44.93672a2.4111,2.4111,0,0,0-.29727.86443l-.64994,5.42078a2.44167,2.44167,0,0,0,3.24841,2.589l10.94888-3.92591a2.44172,2.44172,0,0,0,.8633-4.06338l-3.94671-3.77257a2.41653,2.41653,0,0,0-.46175-.33188C880.48011,323.08126,894.62292,290.36807,884.404,259.82132Z" transform="translate(-279.67138 -161.41057)" fill="#f2f2f2"/><path d="M844.52421,235.59439a40.17042,40.17042,0,0,1,18.17735,52.25234c-.9474,2.15673,2.53662,3.29094,3.4778,1.1483A43.92349,43.92349,0,0,0,846.556,232.54609C844.48974,231.45115,842.44264,234.49133,844.52421,235.59439Z" transform="translate(-279.67138 -161.41057)" fill="#fff"/><path d="M625.404,206.82132c-11.016-32.92966-45.0348-51.23148-67.41427-43.7448s-38.5377,42.57432-27.52165,75.504c10.19672,30.48049,41.09425,48.07684,62.34943,44.93672a2.4111,2.4111,0,0,0-.29727.86443l-.64994,5.42078a2.44167,2.44167,0,0,0,3.24841,2.589l10.94888-3.92591a2.44172,2.44172,0,0,0,.8633-4.06338l-3.94671-3.77257a2.41653,2.41653,0,0,0-.46175-.33188C621.48011,270.08126,635.62292,237.36807,625.404,206.82132Z" transform="translate(-279.67138 -161.41057)" fill="#f2f2f2"/><path d="M585.52421,182.59439a40.17042,40.17042,0,0,1,18.17735,52.25234c-.9474,2.15673,2.53662,3.29094,3.4778,1.1483A43.92349,43.92349,0,0,0,587.556,179.54609C585.48974,178.45115,583.44264,181.49133,585.52421,182.59439Z" transform="translate(-279.67138 -161.41057)" fill="#fff"/><circle cx="290.48241" cy="201.23783" r="2.62444" fill="#ccc"/><circle cx="284.8888" cy="303.95675" r="5.18452" fill="#ccc"/><circle cx="198.09305" cy="286.18213" r="2.99584" fill="#ccc"/><circle cx="243.60567" cy="236.87991" r="3.24432" fill="#ccc"/><circle cx="403.25036" cy="427.44691" r="2.62444" fill="#ccc"/><circle cx="317.30063" cy="370.92163" r="5.18452" fill="#ccc"/><circle cx="376.33906" cy="304.8618" r="2.99584" fill="#ccc"/><circle cx="396.04016" cy="369.00206" r="3.24432" fill="#ccc"/><ellipse cx="836.6349" cy="684.24158" rx="6.76007" ry="21.53369" transform="translate(-523.77203 535.2876) rotate(-39.93837)" fill="#2f2e41"/><circle cx="511.15638" cy="515.35311" r="43.06732" fill="#2f2e41"/><rect x="516.30144" y="549.15264" width="13.08374" height="23.44171" fill="#2f2e41"/><rect x="490.13396" y="549.15264" width="13.08374" height="23.44171" fill="#2f2e41"/><ellipse cx="518.48204" cy="572.86693" rx="10.90314" ry="4.08868" fill="#2f2e41"/><ellipse cx="492.31456" cy="572.3218" rx="10.90314" ry="4.08868" fill="#2f2e41"/><path d="M777.30491,622.62674c3.84558-15.487,20.82056-24.60077,37.91474-20.35617s27.83429,20.2403,23.9887,35.7273-16.60394,15.537-33.69812,11.29235S773.45933,638.11377,777.30491,622.62674Z" transform="translate(-279.67138 -161.41057)" fill="#e6e6e6"/><ellipse cx="742.31957" cy="656.78005" rx="6.76007" ry="21.53369" transform="translate(-448.87657 884.62646) rotate(-64.62574)" fill="#2f2e41"/><circle cx="504.71776" cy="506.26552" r="14.35864" fill="#fff"/><circle cx="498.81639" cy="501.11873" r="4.78621" fill="#3f3d56"/><path d="M793.39653,697.59149a9.57244,9.57244,0,0,1-18.83533,3.42884h0l-.00336-.0185c-.94177-5.20215,3.08039-7.043,8.28253-7.98474S792.45481,692.38941,793.39653,697.59149Z" transform="translate(-279.67138 -161.41057)" fill="#fff"/><ellipse cx="430.43769" cy="644.24148" rx="21.53369" ry="6.76007" transform="translate(-604.69403 654.88722) rotate(-69.08217)" fill="#2f2e41"/><circle cx="110.95912" cy="515.35305" r="43.06735" fill="#2f2e41"/><rect x="91.33351" y="549.15265" width="13.08374" height="23.44171" fill="#2f2e41"/><rect x="117.50099" y="549.15265" width="13.08374" height="23.44171" fill="#2f2e41"/><ellipse cx="102.23666" cy="572.86693" rx="10.90314" ry="4.08868" fill="#2f2e41"/><ellipse cx="128.40414" cy="572.32178" rx="10.90314" ry="4.08868" fill="#2f2e41"/><circle cx="112.04946" cy="504.44983" r="14.71921" fill="#fff"/><circle cx="112.04945" cy="504.44983" r="4.90643" fill="#3f3d56"/><path d="M348.85377,636.7121c-3.47748-15.57379,7.63867-31.31043,24.82861-35.1488s33.94422,5.67511,37.4217,21.24884-7.91492,21.31762-25.10486,25.156S352.33125,652.286,348.85377,636.7121Z" transform="translate(-279.67138 -161.41057)" fill="#e6e6e6"/><ellipse cx="342.12235" cy="656.7801" rx="6.76007" ry="21.53369" transform="translate(-677.57762 523.03713) rotate(-64.62574)" fill="#2f2e41"/><path d="M369.476,692.30048c0,4.21515,10.85328,12.53857,22.89658,12.53857s23.33514-11.867,23.33514-16.08209-11.29193.81775-23.33514.81775S369.476,688.08533,369.476,692.30048Z" transform="translate(-279.67138 -161.41057)" fill="#fff"/><circle cx="316.67837" cy="474.35295" r="43.06733" fill="#2f2e41"/><rect x="297.05274" y="508.15264" width="13.08374" height="23.44171" fill="#2f2e41"/><rect x="323.22024" y="508.15264" width="13.08373" height="23.44171" fill="#2f2e41"/><ellipse cx="307.95583" cy="531.86693" rx="10.90314" ry="4.08868" fill="#2f2e41"/><ellipse cx="334.12337" cy="531.32176" rx="10.90314" ry="4.08868" fill="#2f2e41"/><path d="M554.573,595.71222c-3.47747-15.57379,7.63866-31.31042,24.82866-35.1488s33.94422,5.67511,37.4217,21.2489-7.91492,21.31769-25.10486,25.156S558.05049,611.286,554.573,595.71222Z" transform="translate(-279.67138 -161.41057)" fill="#6c63ff"/><ellipse cx="637.4534" cy="611.64234" rx="23.89244" ry="7.50055" transform="translate(-525.45495 468.82357) rotate(-45.0221)" fill="#2f2e41"/><ellipse cx="544.74805" cy="614.64234" rx="7.50055" ry="23.89244" transform="translate(-554.71702 403.49289) rotate(-44.9779)" fill="#2f2e41"/><path d="M858.1781,362.43165c-1.4297-44.15967-31.675-79.01642-67.55466-77.8548-79.853,2.36609-81.80308,148.80118-4.70743,159.32l-1.63226,12.97539,23.81077-.77089-2.46814-12.84263C836.64005,436.4039,859.47429,402.46761,858.1781,362.43165Z" transform="translate(-279.67138 -161.41057)" fill="#ff6584"/><path d="M750.91418,386.91539l-8.99528.29122a146.49353,146.49353,0,0,1-1.65029-50.97329l8.99528-.29123Q744.28032,362.56747,750.91418,386.91539Z" transform="translate(-279.67138 -161.41057)" fill="#fff"/><rect x="782.36555" y="443.49202" width="27" height="6" transform="translate(-293.7025 -135.42359) rotate(-1.85434)" fill="#3f3d56"/><polygon points="451.208 511.273 448.491 510.001 515.303 283.429 518.02 284.702 451.208 511.273" fill="#3f3d56"/><path d="M913.47588,399.77386c16.3163-41.05969,2.49878-85.09205-30.86214-98.349-74.15988-29.70541-134.40019,103.77921-67.9117,144.19784l-6.676,11.2453,22.13927,8.7977,2.8634-12.76032C864.20072,459,898.68322,436.99934,913.47588,399.77386Z" transform="translate(-279.67138 -161.41057)" fill="#6c63ff"/><path d="M805.35487,379.40605l-8.36382-3.32362a146.49359,146.49359,0,0,1,18.83383-47.395l8.36382,3.32362Q808.99136,354.434,805.35487,379.40605Z" transform="translate(-279.67138 -161.41057)" fill="#fff"/><rect x="819.78863" y="438.4737" width="6" height="27" transform="translate(-180.75644 888.28233) rotate(-68.32812)" fill="#3f3d56"/><polygon points="450.68 505.309 448.073 503.825 541.351 290.957 543.958 292.44 450.68 505.309" fill="#3f3d56"/><path d="M406.46357,365.28486c-17.653-40.503-58.64132-61.70977-91.54993-47.36676-73.32174,31.71782-21.00088,168.50073,54.522,149.77438l3.28,12.65965,21.8391-9.51844-7.04085-11.02049C413.79663,441.97911,422.46809,402.00562,406.46357,365.28486Z" transform="translate(-279.67138 -161.41057)" fill="#6c63ff"/><path d="M315.84888,427.6866l-8.25042,3.5959a146.49372,146.49372,0,0,1-20.37675-46.75242l8.25043-3.59589Q300.68422,407.51577,315.84888,427.6866Z" transform="translate(-279.67138 -161.41057)" fill="#fff"/><rect x="366.13977" y="463.42577" width="27" height="6" transform="translate(-434.41032 29.11894) rotate(-23.54967)" fill="#3f3d56"/><path d="M448.08285,673.26319h0a1.49991,1.49991,0,0,1-1.81676-1.09466L381.05867,465.5523l2.91162-.722,65.20734,206.61591A1.49991,1.49991,0,0,1,448.08285,673.26319Z" transform="translate(-279.67138 -161.41057)" fill="#3f3d56"/><path d="M465.51017,738.58943h-157a1,1,0,0,1,0-2h157a1,1,0,0,1,0,2Z" transform="translate(-279.67138 -161.41057)" fill="#ccc"/><path d="M864.51017,738.58943h-157a1,1,0,0,1,0-2h157a1,1,0,0,1,0,2Z" transform="translate(-279.67138 -161.41057)" fill="#ccc"/><path d="M677.51017,697.58943h-157a1,1,0,0,1,0-2h157a1,1,0,0,1,0,2Z" transform="translate(-279.67138 -161.41057)" fill="#ccc"/><circle cx="314.86635" cy="468.24973" r="14.71921" fill="#fff"/><circle cx="314.86635" cy="468.24974" r="4.90643" fill="#3f3d56"/><path d="M606.22226,656.88283a9.57244,9.57244,0,0,1-18.83533,3.42884h0l-.00335-.0185c-.94178-5.20215,3.08038-7.043,8.28253-7.98474S605.28055,651.68075,606.22226,656.88283Z" transform="translate(-279.67138 -161.41057)" fill="#fff"/></svg>
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
  props: ['refreshInterval','last_successful_crawl'],
  data() {
    return {
      posts_loaded: false,
      last_successful_crawl_data:{},
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
      external_links: []
    };
  },
  created() {
      this.fetch_posts_from_server();
      this.last_successful_crawl_data = JSON.parse(this.last_successful_crawl); 
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
        axios.get('/api/' + this.which_posts).then((res) => {
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
        this.external_links = [];
    },
    show_highlighted_post(){
        document.querySelector('#post-'+this.highlighter_position).scrollIntoView({behavior: "smooth", block: "center", inline: "nearest"});
    },

    turn_on_external_links_shortcuts: function(){
        let shortcut_style = "mr-1 text-gray-700 px-2 bg-yellow-200 text-grey-800";
        document.querySelectorAll('#post-content a').forEach((link,i) => {
            var html = `<span class="externallink ${shortcut_style}">${i}</span>`;
            link.insertAdjacentHTML('beforeend', html);
            this.external_links.push(link.getAttribute('href'));
        })
        this.external_links_shortcuts = true;
    },

    turn_off_external_links_shortcuts: function(){
        document.querySelector('#post-content').innerHTML = this.displayed_post.content;
        this.external_links_shortcuts = false;
        this.external_links = [];
    },

    handle_keyboard_shortcut(key){
        console.log(key);

        // external links shortcuts
        if (key.match(/\d/)) { 
            if (eval(key) < this.external_links.length) {
                window.open(this.external_links[key],'_blank');
            }
        }
        switch (key) {
            case ('f' || 'F'):
                if (this.view == 'post') {
                    if (! this.external_links_shortcuts) {
                        console.log('turn on');
                        this.turn_on_external_links_shortcuts();
                    } else {
                        this.turn_off_external_links_shortcuts();
                    }
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