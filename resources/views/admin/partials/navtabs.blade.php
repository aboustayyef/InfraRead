@if(request()->path() !== 'login' && request()->path() !== 'admin')
	<div class="container">
	    <ul class="nav nav-tabs">
	      <li role="presentation" @if(strstr(request()->path(), 'admin/source')) class="active" @endif><a href="/admin/source">Manage Sources</a></li>
	      <li role="presentation" @if(strstr(request()->path(), 'admin/category')) class="active" @endif><a href="/admin/category">Manage Categories</a></li>
	    </ul>
	</div>
@endif