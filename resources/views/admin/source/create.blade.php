@extends('layouts.administration');
@section('content')
    <div class="container">
        <h2>Create new source</h2>
        <form method="POST" action="/admin/source/" >
            @include('admin.source._form')
        <input type="submit" class="btn btn-primary"></input>
        </form>
    </div>
@stop