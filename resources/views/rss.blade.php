<?xml version="1.0"?>
<rss version="2.0">
    <channel>
        <title>{{$channel->name}}</title>
        <link>{{$channel->url}}</link>
        <description>{{$channel->description}}</description>
        <language>en-us</language>
        @foreach($items as $item)
            <item>
                <title>{{$item->title}}</title>
                <link>{{$item->url}}</link>
                <description>{{$item->content}}</description>
                <pubDate>{{$item->posted_at->toRfc2822String()}}</pubDate>
                <guid>{{$item->url}}-{{$item->id}}</guid>
            </item>
        @endforeach
    </channel>
</rss>
