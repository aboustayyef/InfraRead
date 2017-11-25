@extends('layouts.administration');
@section('content')
    <div class="container">
        <h1>Create new source</h1>
        <form method="POST" action="/admin/source/" >
            @include('admin.source._form')
        <input type="submit" class="btn btn-primary"></input>
        </form>
    </div>
@stop