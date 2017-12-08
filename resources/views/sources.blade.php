<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Source</title>
    <!-- CSRF Stuff -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>window.Laravel = { csrfToken: '{{ csrf_token() }}' }</script> 
    <link rel="stylesheet" type="text/css" href="{{mix('/css/app.css')}}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<style>
  ul{
    margin:1em 0 2em 0;
  }
  li{
    text-transform: uppercase;
    border-top: 1px solid  whitesmoke; 
    padding: 3px 0;
    cursor: pointer;
  }
</style>
<body> 
      <div class="section">
        <div class="container">
         <div class="columns">
            <div class="column">
              <h2 class="has-text-grey-dark is-title is-size-4 has-text-weight-bold">Articles</h2>
              <ul>
                <a href="/app"><li class="is-primary" >Show All Articles</li></a>
              </ul>
              
              <h2 class="has-text-grey-dark is-title is-size-4 has-text-weight-bold">Categories</h2>
              <ul>
                @foreach($categories as $category)
                    <a href="{{'/app/category/' . $category->id}}"><li class="is-primary" >{{$category->description}}</li></a>
                @endforeach
              </ul>
            </div>

            <div class="column">
              <h2 class="has-text-grey-dark is-title is-size-4 has-text-weight-bold">Sources</h2>
              <ul>
                @foreach($sources as $source)
                    <a href="{{'/app/source/' . $source->id}}"><li class="is-primary" >{{$source->name}}</li></a>
                @endforeach
              </ul>
            </div>

          </div> 
        </div>
      </div>
</body>
</html>