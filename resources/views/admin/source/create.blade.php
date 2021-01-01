@extends('admin.layout') @section('content')

<div class="flex w-full">
  <h2 class="text-4xl font-bold text-gray-600">Create New Source</h2>
</div>
<hr class="my-12">
  <div id="quickfill" v-cloak>
  <div v-if="error_message" class="flex justify-between items-center text-red-700 mb-4 bg-red-100 shadow-md border border-red-200 px-2 rounded-lg" role="alert">
    @{{error_message}}
    <div class="p-2 cursor-pointer" @click="error_message=false">&times;</div>
  </div>
  <div v-if="success_message" class="flex justify-between items-center text-green-700 mb-4 bg-green-100 shadow-md border border-green-200 px-2 rounded-lg" role="alert">
    @{{success_message}}
    <div class="p-2 cursor-pointer" @click="success_message=false">&times;</div>
  </div>
  <div v-if = "quickstatus === 'closed'">
  	<button class="ir_button" @click="quickstatus='open'">Quick Add +</button>
  </div>
  <div v-else>
    <form class="flex">
        <input v-model="url" type="text" class="ir_input attached" id="url" placeholder="http://sourcehomepage.com">
        <button @click.prevent="submit()" class="ir_button attached" v-text="button_status" :disabled="button_status=='Getting Info'"></button>
    </form>
  	  
  		
  </div>

    <form method="POST" action="{{route('admin.source.store')}}">
      @include('admin.source._form')
      <input type="submit" class="ir_button"></input>
    </form>
  </div>
  
  @stop 

  @section('extra_scripts')
  <script>
    let quick_fill_app = new Vue({
      el: '#quickfill',
      data: {
        error_message: false,
        success_message: false,
      	quickstatus: 'closed',           // Whether or not the [quick add +] button is shown
      	button_status: 'Get Info',       // what's written on the button
        url: ''                          // URL being fetched
      },
      methods: {
        submit: function() {
        	console.log('submitting');
        	this.button_status = "Getting Info";
	        axios.get('/api/urlanalyze?url=' + this.url)
	        .then((res)=> {
            if (res.data.status == "ok") {
  	        	document.querySelector('input[name="name"]').value = res.data.result['title'];
  	        	document.querySelector('input[name="description"]').value = res.data.result['description'];
  	        	document.querySelector('input[name="url"]').value = res.data.result['canonical_url'];
  	        	document.querySelector('input[name="author"]').value = res.data.result['author'];
  	        	document.querySelector('input[name="fetcher_source"]').value = res.data.result['rss'];       
              this.button_status = 'Get Info';
              this.quickstatus = 'closed';
              this.error_message = false; // A success will wipe out hanging error messages.
              this.success_message = 'Success. Items automatically filled. Edit form then submit';
            } else {
              this.error_message = res.data.error_messages[0];
  	        	this.button_status = 'Try Again';
            }
	        }).catch((res)=>{
            this.error_message = 'there was a problem with the url. Please double check then try again';
            this.url = '';
            this.button_status = 'Get Info';
            this.quickstatus= 'closed';
          });
        },
      }
    });

  </script>
  @stop
