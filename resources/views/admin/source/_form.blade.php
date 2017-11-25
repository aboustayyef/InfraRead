{{csrf_field()}}

<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
    <label for="name" class="control-label">Name</label>
    <input class="form-control" name="name" type="text" id="name" value="{{old('name', $source->name)}}">
    <small class="text-danger">{{ $errors->first('name') }}</small>
</div>

<div class="form-group{{ $errors->has('nickname') ? ' has-error' : '' }}">
    <label for="nickname" class="control-label">Nickname (One Word That is Unique to the source, used to identify and for url)</label>
    <input class="form-control" name="nickname" type="text" id="nickname" value="{{old('nickname', $source->nickname)}}">
    <small class="text-danger">{{ $errors->first('nickname') }}</small>
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

<div class="form-group{{ $errors->has('twitter') ? ' has-error' : '' }}">
    <label for="twitter" class="control-label">Twitter Username (without @)</label>
    <input class="form-control" name="twitter" type="text" id="twitter" value="{{old('twitter', $source->twitter)}}">
    <small class="text-danger">{{ $errors->first('twitter') }}</small>
</div>

<div class="form-group{{ $errors->has('fetcher_kind') ? ' has-error' : '' }}">
    <label for="fetcher_kind" class="control-label">Fetcher Kind</label>
    <input class="form-control" name="fetcher_kind" type="text" id="fetcher_kind" value="{{old('fetcher_kind', $source->fetcher_kind)}}">
    <small class="text-danger">{{ $errors->first('fetcher_kind') }}</small>
</div>

<div class="form-group{{ $errors->has('fetcher_source') ? ' has-error' : '' }}">
    <label for="fetcher_source" class="control-label">Fetcher Source</label>
    <input class="form-control" name="fetcher_source" type="text" id="fetcher_source" value="{{old('fetcher_source', $source->fetcher_source)}}">
    <small class="text-danger">{{ $errors->first('fetcher_source') }}</small>
</div>
