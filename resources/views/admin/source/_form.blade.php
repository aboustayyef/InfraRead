{{csrf_field()}}

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
