{{csrf_field()}}

<div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
    <label for="description" class="control-label">Description</label>
    <input class="ir_input {{ $errors->has('description') ? ' warning' : '' }}" name="description" type="text" id="description" value="{{old('description', $category->description)}}">
    <small class="text-primary">{{ $errors->first('description') }}</small>
</div>