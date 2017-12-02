<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mustapha's Feed Reader</title>
    <!-- CSRF Stuff -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>window.Laravel = { csrfToken: '{{ csrf_token() }}' }</script> 
    <link rel="stylesheet" type="text/css" href="{{mix('/css/app.css')}}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

</head>
<body>
    <div id="app" v-cloak>
        <app></app>
    </div>
    <script src="{{ mix('/js/app.js') }}"></script>
</body>
</html>