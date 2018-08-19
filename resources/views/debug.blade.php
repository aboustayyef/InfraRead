<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InfraRead</title>
    <!-- CSRF Stuff -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" type="text/css" href="{{mix('/css/app.css')}}">
    <link rel="manifest" href="/manifest.json">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#da2525">

</head>

<body>
    @foreach($posts as $post)
    <section class="section">
        <div class="container">
            {{-- <div class="columns">
                <div class="column"><h1 class="title">Original: raw</h1>{{$post->original_content}}</div>
                <div class="column"><h1 class="title">Cleaned: raw</h1>{{$post->cleaned_content}}</div>
            </div>
             --}}

            <div class="columns">
                <div class="column"><h1 class="title">Original: rendered</h1>{!!$post->original_content!!}</div>
                <div class="column"><h1 class="title">Cleaned: rendered</h1>{!!$post->cleaned_content!!}</div>
            </div>
        </div>
    </section>
    <hr>
    @endforeach();
    <p>Posts: {{$posts->count()}}</p>
</body>
</html>