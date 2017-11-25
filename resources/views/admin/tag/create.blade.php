@extends('layouts.administration');
@section('content')
    <div class="container">
        <h1>Create new Tag</h1>
        <form method="POST" action="/admin/tag/" >
            @include('admin.tag._form')
        <input type="submit" class="btn btn-primary"></input>
        </form>
    </div>
@stop