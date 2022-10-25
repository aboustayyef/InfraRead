<?xml version="1.0" encoding="utf-8"?>
<?xml-stylesheet type="text/xsl" href="/assets/opml.xsl"?>
<opml version="2.0">

    <head>
        <title>Infraread subscriptions</title>
    </head>

    <body>
        @foreach ($categories as $category)
        <outline title="{{$category->description}}">
            @foreach ($category->sources as $source)
            @if(env('OPML') == 'original')
            <outline title="{{$source->name}}" text="{{$source->description}}" xmlUrl="{{$source->fetcher_source}}" type="rss" htmlUrl="{{$source->url}}" />
            @else
            <outline title="{{$source->name}}" text="{{$source->description}}" xmlUrl="{{env('APP_URL')}}/rss/{{$source->id}}" type="rss" htmlUrl="{{$source->url}}" />
            @endif
            @endforeach
        </outline>
        @endforeach
    </body>
</opml>
