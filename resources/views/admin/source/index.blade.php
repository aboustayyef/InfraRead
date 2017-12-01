@extends('layouts.administration')

@section('content')
<div class="container">
    <div>
        <h1>Manage Sources</h1>
        <div class="row">
            <div class="col-md-2">
                <a href="{{route('admin.source.create')}}" class="btn btn-primary">Create New source</a>
            </div>
            <div class="col-md-4 pull-right">
                <input type="text" placeholder="Filter Results" v-model="filter_key" class="form-control">
            </div>
        </div>
        
    </div>
    <div v-if="filter_key.length > 0" v-text="`Results: ${filtered_sources.length}`" class="pull-right" style="padding:1em 0">
    </div>
    <table class="table table-striped" style="margin-top:2em">
        <thead>
            <tr>
                <th><a @click="sortNumeric" data-sort="id" href="#">ID</a></th>
                <th><a @click="sort" data-sort="name" href="#">Name</a></th>
                <th><a @click="sort" data-sort="description" href="#">Description</a></th>
                <th><a @click="sort" data-sort="author" href="#">author</a></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="source in this.filtered_sources" v-if="appReady"> 
                <th v-text="source.id"></th>
                <td>@{{source.name}}</td>
                <td>@{{source.description}}</td>
                <td>@{{source.author}}</td>
                <td><a :href=`/admin/source/${source.id}/edit`>edit</a></td>
            </tr>
        </tbody>
    </table> 
</div>
@stop

@section('extra_scripts')
    <script>
        var app = new Vue({
            el: '#app',
            mounted(){
                axios.get('/api/source').then((response) => {
                    this.all_sources= response.data;
                    this.appReady = 1;
                });
            },
            data: {
                appReady : 0,
                all_sources: [],
                filter_key: "",
            },
            computed: {
                // a computed getter
                filtered_sources: function () {
                    return this.all_sources.filter((source) =>
                        (source.name.toLowerCase().includes(this.filter_key.toLowerCase()) || source.name.toLowerCase().includes(this.filter_key.toLowerCase()) || source.description.toLowerCase().includes(this.filter_key.toLowerCase()) || source.author.toLowerCase().includes(this.filter_key.toLowerCase()))
                    );
                }
              },
            methods:
            {
                sortNumeric: function(e){
                    var sorting_key = e.target.dataset.sort;
                    this.all_sources.sort(function(a,b){
                        var valueA = a[sorting_key];
                        var valueB = b[sorting_key];
                        if (valueA < valueB) {
                            return -1;
                        }
                        if (valueA > valueB) {
                            return 1;
                        }
                        return 0;
                    });
                },
                sort: function(e){
                    // sort by name
                    var sorting_key = e.target.dataset.sort;
                    this.all_sources.sort(function(a, b) {
                      var valueA = a[sorting_key].toUpperCase(); // ignore upper and lowercase
                      var valueB = b[sorting_key].toUpperCase(); // ignore upper and lowercase
                      if (valueA < valueB) {
                        return -1;
                      }
                      if (valueA > valueB) {
                        return 1;
                      }
                      // names must be equal
                      return 0;
                    });
                }
            },
        });
    </script>
@stop