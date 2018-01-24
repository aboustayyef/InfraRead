{{csrf_field()}}
<div class="row">
    <div class="col-md-8">
        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
            <label for="name" class="control-label">Name</label>
            <input class="form-control" name="name" type="text" id="name" value="{{old('name', $source->name)}}">
            <small class="text-danger">{{ $errors->first('name') }}</small>
        </div>

        <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
            <label for="description" class="control-label">Description</label>
            <input class="form-control" name="description" type="text" id="description" value="{{old('description', $source->description)}}">
            <small class="text-danger">{{ $errors->first('description') }}</small>
        </div>

        <div class="form-group{{ $errors->has('url') ? ' has-error' : '' }}">
            <label for="url" class="control-label">source's Homepage</label>
            <input class="form-control" name="url" type="text" id="url" value="{{old('url', $source->url)}}">
            <small class="text-danger">{{ $errors->first('url') }}</small>
        </div>

        <div class="form-group{{ $errors->has('author') ? ' has-error' : '' }}">
            <label for="author" class="control-label">Author</label>
            <input class="form-control" name="author" type="text" id="author" value="{{old('author', $source->author)}}">
            <small class="text-danger">{{ $errors->first('author') }}</small>
        </div>

        <input type="hidden" name="fetcher_kind" value="rss">

        <div class="form-group{{ $errors->has('fetcher_source') ? ' has-error' : '' }}">
            <label for="fetcher_source" class="control-label">RSS Feed:</label>
            <input class="form-control" name="fetcher_source" type="text" id="fetcher_source" value="{{old('fetcher_source', $source->fetcher_source)}}">
            <small class="text-danger">{{ $errors->first('fetcher_source') }}</small>
        </div> 
       
        <input type="hidden" name="full_content" value=0> {{-- In case unchecked below, value to pass is 0 --}} 
        <div class="form-group">
            <div class="checkbox">
                <label>
                  <input type="checkbox" name="full_content" value=1 @if($source->full_content == 1) checked @endif >Force Full Content (Readability mode) 
                </label>
            </div>
            <small class="text-danger">{{ $errors->first('full_content') }}</small>
        </div>

    </div> <!-- /col-md-8 -->

    <div class="col-md-4">    
        {{-- Categories --}}
        @if($categories->count() > 0)
            <h4>Category: (optional)</h4>
            @foreach($categories as $category)
            <div class="radio">
              <label>
                <input type="radio" name="category_id" id="optionsRadios1" value="{{$category->id}}" @if($category->sources->contains('id', $source->id)) checked @endif>
                    {{$category->description}}
              </label>
            </div>
            @endforeach
        @else
            <h4>Hint</h4>
            <p>You can create categories to organize your sources</p>
            <a href="/admin/category/create" class="btn btn-lg btn-primary">Create a Category</a>
        @endif
    </div>
</div>

