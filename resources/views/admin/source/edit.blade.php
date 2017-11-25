@extends('layouts.administration')

@section('content')
<div class="container">
    <form method="POST" action="/admin/source/{{$source->id}}" >
        <input name="_method" type="hidden" value="PUT">
        @include('admin.source._form')
    <input type="submit" class="btn btn-primary"></input>
    </form>
    <button type="button" class="btn btn-danger pull-right" data-toggle="modal" data-target="#areYouSure">
      Delete source
    </button>

{{-- Modal --}}
    <div id="areYouSure" class="modal fade" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Are you sure?</h4>

          </div>
          <div class="modal-body">
            <p>Deleting this source will remove all their data</p>
          </div>
          <div class="modal-footer">
            <form method="POST" action="{{route('admin.source.destroy', ['id'=>$source->id])}}">
                {{csrf_field()}}
                <input name="_method" type="hidden" value="DELETE">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger">Yes, permanently delete source</button>
            </form>
          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

</div>
@stop