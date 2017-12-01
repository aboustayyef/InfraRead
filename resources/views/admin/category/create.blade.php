@extends('layouts.administration');
@section('content')
    <div class="container">
        <h1>Create new Category</h1>
        <form method="POST" action="/admin/category/" >
            @include('admin.category._form')
        <input type="submit" class="btn btn-primary"></input>
        </form>
    </div>
@stop