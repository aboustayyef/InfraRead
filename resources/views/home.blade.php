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
        <link rel="manifest" href="/manifest.webmanifest">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#B90C11">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="InfraRead">
        <link rel="apple-touch-icon" href="/img/apple-touch-icon.png">
        <link rel="apple-touch-startup-image" href="/img/ios-splash/ios-splash-640x1136.png" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
        <link rel="apple-touch-startup-image" href="/img/ios-splash/ios-splash-750x1334.png" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
        <link rel="apple-touch-startup-image" href="/img/ios-splash/ios-splash-828x1792.png" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
        <link rel="apple-touch-startup-image" href="/img/ios-splash/ios-splash-1125x2436.png" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
        <link rel="apple-touch-startup-image" href="/img/ios-splash/ios-splash-1170x2532.png" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
        <link rel="apple-touch-startup-image" href="/img/ios-splash/ios-splash-1179x2556.png" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
        <link rel="apple-touch-startup-image" href="/img/ios-splash/ios-splash-1242x2208.png" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
        <link rel="apple-touch-startup-image" href="/img/ios-splash/ios-splash-1242x2688.png" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
        <link rel="apple-touch-startup-image" href="/img/ios-splash/ios-splash-1284x2778.png" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
        <link rel="apple-touch-startup-image" href="/img/ios-splash/ios-splash-1290x2796.png" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
        <link rel="apple-touch-startup-image" href="/img/ios-splash/ios-splash-1536x2048.png" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
        <link rel="apple-touch-startup-image" href="/img/ios-splash/ios-splash-1668x2224.png" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
        <link rel="apple-touch-startup-image" href="/img/ios-splash/ios-splash-1668x2388.png" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
        <link rel="apple-touch-startup-image" href="/img/ios-splash/ios-splash-2048x2732.png" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
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
