@extends('layouts.administration')

@section('content')
<div class="container">
   <div>
        <h1>Tags in LB</h1>
        <a href="{{route('admin.tag.create')}}" class="btn btn-primary pull-right">Create New tag</a>
   </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nickname</th>
                <th>Description</th>
                <th>Color</th>
                <th>&nbsp;</th>
            </tr>
        </thead>

        <tbody>
            @foreach($tags as $tag)
                <tr>

                    <td>{{$tag->nickname}}</td>
                    <td>{{$tag->description}}</td>
                    <td>{{$tag->color}}</td>
                    <td><a href="{{route('admin.tag.edit', ['id' => $tag->id])}}">edit</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@stop