<!DOCTYPE html>

<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>InfraRead</title>
        <!-- CSRF Stuff -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Transfer Variables from PHP to Js --}}
        <script>
            window.Laravel = {
                csrfToken: '{{ csrf_token() }}',
                apiToken: '{{ $api_token }}'
            }
        </script>
        <link rel="stylesheet" type="text/css" href="{{mix('/css/app.css')}}">
        <link rel="apple-touch-icon" href="/img/apple-touch-icon.png">
        <link rel="manifest" href="/manifest.webmanifest">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#B90C11">
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@300;700&display=swap" rel="stylesheet">
    </head>

    <noscript>
        <h3>
            This App Requires Javascript to Work
        </h3>
        <p>Sorry For Inconvenience</p>
    </noscript>

    <body>
        <div id="app" v-cloak>
            <app
                refreshInterval="{!! config('infraread.refresh_interval') !!}" >
            </app>
        </div>
        <script src="{{ mix('/js/app.js') }}"></script>
    </body>

</html>
