@extends('layouts.administration')

@section('content')
<div class="container">
   <div>
        <h1>Categories</h1>
        <a href="{{route('admin.category.create')}}" class="btn btn-primary pull-right">Create New category</a>
   </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Description</th>
                <th>&nbsp;</th>
            </tr>
        </thead>

        <tbody>
            @foreach($categories as $category)
                <tr>
                    <td>{{$category->description}}</td>
                    <td><a href="{{route('admin.category.edit', ['id' => $category->id])}}">edit</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@stop