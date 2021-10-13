<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Setup</title>
    <!-- CSRF Stuff -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>window.Laravel = { csrfToken: '{{ csrf_token() }}' }</script>
    {{-- <link rel="stylesheet" type="text/css" href="{{mix('/css/app_bulma.css')}}"> --}}
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <section class="container mx-auto mt-4">
        <div class="">
            <div class="">
                <h1 class="text-3xl font-bold">
                    Hello :)
                </h1>
                <h2 class="mt-4 text-lg">
                    We noticed that you don't have any feeds yet
                </h2>
                <form action="/uploadOpml" method="post" enctype="multipart/form-data">
                    {{csrf_field()}}
                    <div class="mt-4">
                        <label class="file-label">
                            <input class="file-input" type="file" name="opml">
                            <span class="file-cta">
                                <span class="file-label">
                                    Choose an OPML File
                                </span>
                            </span>
                        </label>
                        &nbsp;
                    <button style="display:none" id="nameOfFile" type="submit" class="button is-warning">Upload</button>
                    </div>

                </form>
                <script>
                    // script to update name of selected file before uploading
                    let input_button = document.getElementsByClassName('file-input')[0];
                    let file_label_button = document.getElementById('nameOfFile');
                    updateButton=function(){
                        file_label_button.innerHTML = "Click to upload " + input_button.value.replace(/C:\\fakepath\\/i, '');
                        file_label_button.style.display = 'block';
                    };
                    input_button.addEventListener('change', updateButton);
                    // document.getElementsByClassName('file-input')[0].value.replace(/C:\\fakepath\\/i, '');
                </script>
            </div>
        </div>
    </section>
</body>
</html>
