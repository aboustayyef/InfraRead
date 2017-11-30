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