<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InfraRead</title>
    <!-- CSRF Stuff -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>window.Laravel = { csrfToken: '{{ csrf_token() }}' }</script> 
    <script>window.posts_source = '{{$posts_source}}'</script> 
    <script>window.posts_description = '{{$posts_description}}'</script> 
    <script>window.page = '{{$page}}'</script> 
    <link rel="stylesheet" type="text/css" href="{{mix('/css/app.css')}}">
    <link rel="manifest" href="/manifest.json">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#da2525">

</head>
<noscript>
    <h3>
        This App Requires Javascript to Work
    </h3>
    <p>Sorry For Inconvenience</p>
</noscript>
<body>
    <div id="app" v-cloak>
        <app refreshInterval="{!! config('infraread.refresh_interval') !!}" last_successful_crawl="{{$last_successful_crawl}}" ></app>
    </div>
    <script src="{{ mix('/js/app.js') }}"></script>
</body>
</html>