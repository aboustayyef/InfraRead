@extends('admin.layout') @section('content')
<div class="container">

  <hr>
  <div id="quickfill" v-cloak>
  <div v-if="error_message" class="alert alert-warning alert-dismissible" role="alert">
    <button type="button" class="close" @click="error_message=false" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    @{{error_message}}
  </div>
  <div v-if="success_message" class="alert alert-success alert-dismissible" role="alert">
    <button type="button" class="close" @click="success_message=false" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    @{{success_message}}
  </div>
  <div v-if = "quickstatus === 'closed'">
  	<button class="btn btn-default btn-lg" @click="quickstatus='open'">Quick Add +</button>
  </div>
  <div v-else>
  	<div class="well" style="overflow:auto">
  	  <div class="col-md-8">
  	    <form class="form-horizontal">
  	      <div class="form-group">
  	        <label for="url" class="col-sm-2 control-label">URL:</label>
  	        <div class="col-sm-10">
  	          <input v-model="url" type="text" class="form-control" id="url" placeholder="http://sourcehomepage.com">
  	        </div>
  	      </div>
  	      <div class="form-group">
  	        <div class="col-sm-offset-2 col-sm-10">
  	          <button @click.prevent="submit()" class="btn btn-default" v-text="button_status" :disabled="button_status=='Getting Info'"></button>
  	        </div>
  	      </div>
  	    </form>
  	  </div>
  		</div>
  </div>


    <h2>Create new source</h2>
    <form method="POST" action="{{route('admin.source.store')}}">
      @include('admin.source._form')
      <input type="submit" class="btn btn-primary"></input>
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
	        });
        },
      }
    });

  </script>
  @stop
