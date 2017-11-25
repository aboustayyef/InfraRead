@extends('layouts.administration');
@section('content')
    <div class="container">
        <h1>Edit Tag</h1>
        <form method="POST" action="{{route('admin.tag.update',['id'=>$tag->id])}}" >
            <input name="_method" type="hidden" value="PUT">
            @include('admin.tag._form')
        <input type="submit" class="btn btn-primary"></input>
        </form>
    </div>
@stop