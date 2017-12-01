{{csrf_field()}}

<div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
    <label for="description" class="control-label">Description</label>
    <input class="form-control" name="description" type="text" id="description" value="{{old('description', $category->description)}}">
    <small class="text-danger">{{ $errors->first('description') }}</small>
</div>