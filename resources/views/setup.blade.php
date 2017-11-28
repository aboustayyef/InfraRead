<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Setup</title>
    <!-- CSRF Stuff -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>window.Laravel = { csrfToken: '{{ csrf_token() }}' }</script> 
    <link rel="stylesheet" type="text/css" href="/css/app.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">

</head>
<body>
    <section class="hero is-medium is-primary is-bold">
        <div class="hero-body">
            <div class="container">
                <h1 class="title">
                    Hello :)
                </h1>
                <h2 class="subtitle">
                    We noticed that you don't have any feeds yet
                </h2>
                <form action="/uploadOpml" method="post" enctype="multipart/form-data">
                    {{csrf_field()}}
                    <div class="file">
                        <label class="file-label">
                            <input class="file-input" type="file" name="opml">
                            <span class="file-cta">
                                <span class="file-label">
                                    Choose an OPML File 
                                </span>
                            </span>
                        </label>
                        &nbsp;
                    <button type="submit" class="button is-warning">Upload</button>
                    </div>
                </form>                

            </div>
        </div>
    </section>
</body>
</html>