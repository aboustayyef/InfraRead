{{csrf_field()}}
<div class="row">
        <div class="flex flex-col mt-4">
            <label for="name" class="control-label">Name</label>
            <input class="ir_input {{ $errors->has('name') ? 'warning' : '' }}" name="name" type="text" id="name" value="{{old('name', $source->name)}}">
            <small class="text-primary">{{ $errors->first('name') }}</small>
        </div>

        <div class="flex flex-col mt-4">
            <label for="description" class="control-label">Description</label>
            <input class="ir_input {{ $errors->has('description') ? 'warning' : '' }}" name="description" type="text" id="description" value="{{old('description', $source->description)}}">
            <small class="text-primary">{{ $errors->first('description') }}</small>
        </div>

        <div class="flex flex-col mt-4">
            <label for="url" class="control-label">source's Homepage</label>
            <input class="ir_input {{ $errors->has('url') ? 'warning' : '' }}" name="url" type="text" id="url" value="{{old('url', $source->url)}}">
            <small class="text-primary">{{ $errors->first('url') }}</small>
        </div>

        <div class="flex flex-col mt-4">
            <label for="author" class="control-label">Author</label>
            <input class="ir_input {{ $errors->has('author') ? 'warning' : '' }}" name="author" type="text" id="author" value="{{old('author', $source->author)}}">
            <small class="text-primary">{{ $errors->first('author') }}</small>
        </div>

        <input type="hidden" name="fetcher_kind" value="rss">

        <div class="flex flex-col mt-4">
            <label for="fetcher_source" class="control-label">RSS Feed:</label>
            <input class="ir_input {{ $errors->has('fetcher_source') ? 'warning' : '' }} " name="fetcher_source" type="text" id="fetcher_source" value="{{old('fetcher_source', $source->fetcher_source)}}">
            <small class="text-primary">{{ $errors->first('fetcher_source') }}</small>
        </div> 
       

    <div class="my-4">    
        {{-- Categories --}}
        @if($categories->count() > 0)
            <h4>Category:</h4>
            @foreach($categories as $category)
            <div class="radio">
              <div class="flex items-center mt-1">
                <input class="p-2 border-primary border-2 cursor-pointer mr-4" type="radio" name="category_id" id="optionsRadios1" value="{{$category->id}}" @if($category->sources->contains('id', $source->id)) checked @endif>
                    {{$category->description}}
              </div>
            </div>
            @endforeach
            <small class="text-primary">@if ($errors->first('category_id')) You need to pick a category (you can always change it later) @endif</small>

        @else
            <h4>Hint</h4>
            <p>You can create categories to organize your sources</p>
            <a href="/admin/category/create" class="btn btn-lg btn-primary">Create a Category</a>
        @endif
    </div>
</div>

