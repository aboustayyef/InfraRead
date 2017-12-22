@extends('layouts.administration') @section('content')
<div class="container">

  <hr>
  <div id="quickfill">
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
      	quickstatus: 'closed',
      	button_status: 'Get Info',
        url: ''
      },
      mounted: function() {
        console.log('mounted');
      },
      methods: {
        submit: function() {
        	console.log('submitting');
        	this.button_status = "Getting Info";
	        axios.get('/api/urlanalyze?url=' + this.url)
	        .then((res)=> {
	        	document.querySelector('input[name="name"]').value = res.data.result['title'];
	        	document.querySelector('input[name="description"]').value = res.data.result['description'];
	        	document.querySelector('input[name="url"]').value = res.data.result['canonical_url'];
	        	document.querySelector('input[name="author"]').value = res.data.result['author'];
	        	document.querySelector('input[name="fetcher_source"]').value = res.data.result['rss'];
	        	this.button_status = 'Get Info';
	        	this.quickstatus = 'closed';
	        });
        },
      }
    });

  </script>
  @stop
