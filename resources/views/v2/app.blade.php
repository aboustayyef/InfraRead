<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Infraread V2 - Powered by LiveWire</title>
    @livewireStyles
    <link rel="stylesheet" href="{{mix('/css/app.css')}}">
</head>
<body>
    @livewire('app')
</body>
@livewireScripts
<script src="{{mix('/js/v2.js')}}"></script>
</html>