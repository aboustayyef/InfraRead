<?php

namespace App\Utilities;

use App\Source;
use App\Tag;
use Illuminate\Support\Facades\DB;

class OpmlImporter 
{
    public static function process(){
        
        // Get content from OPML in storage
        // reference: http://us2.php.net/simplexml
        $collection = collect(json_decode(json_encode(simplexml_load_file(storage_path().'/app/uploaded/feeds.opml')),TRUE));
        $feeds = $collection['body']['outline'];

        DB::table('sources')->truncate();
        DB::table('tags')->truncate();

        foreach ($feeds as $key => $group) {
            
            // create group as tag
            $tag = Tag::Create([
                'description'       =>  $group['@attributes']['title'],
            ]);

            // populate group feeds 
            foreach ($group['outline'] as $source) {
                $source_details = $source['@attributes'];
                $result = Source::create([
                    'name'              =>  $source_details['text'],
                    'description'       =>  $source_details['title'],
                    'url'               =>  $source_details['htmlUrl'],
                    'author'            =>  '',
                    'fetcher_kind'      =>  'rss',
                    'fetcher_source'    =>  $source_details['xmlUrl'],
                    'active'            =>  1,
                    'why_deactivated'   => null,
                    'tag_id'            => $tag->id
                ]);
            }
        }
    }
}
