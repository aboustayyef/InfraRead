<?php

namespace App\Utilities;

use App\Models\Category;
use App\Models\Source;
use Illuminate\Support\Facades\DB;

class OpmlImporter
{
    public static function process()
    {
        // Get content from OPML in storage
        // reference: http://us2.php.net/simplexml
        $collection = collect(json_decode(json_encode(simplexml_load_file(storage_path().'/app/uploaded/feeds.opml')), true));
        $feeds = $collection['body']['outline'];

        // If you're importing a new OPML, it is assumed that you're starting from zero
        // Hence all content-related tables are truncated
        DB::table('sources')->truncate();
        DB::table('categories')->truncate();
        DB::table('posts')->truncate();

        foreach ($feeds as $key => $group) {
            // OPML from inoreader allow to have folders or having a source without a folder so there is no outline

            if (isset($group['outline'])) {
                // create group as Category
                $category = Category::Create([
                    'description' => $group['@attributes']['title'],
                ]);

                // populate group feeds
                foreach ($group['outline'] as $source) {
                    $source_details = isset($source['@attributes']) ? $source['@attributes'] : $source;
                    $result = Source::create([
                        'name' => $source_details['text'],
                        'description' => $source_details['title'],
                        'url' => $source_details['htmlUrl'],
                        'author' => '',
                        'fetcher_kind' => 'rss',
                        'fetcher_source' => $source_details['xmlUrl'],
                        'active' => 1,
                        'why_deactivated' => null,
                        'category_id' => $category->id,
                    ]);
                }
            } else {
                $source = $group;

                $category = Category::firstOrCreate([
                    'description' => 'NULL',
                ]);

                $source_details = isset($source['@attributes']) ? $source['@attributes'] : $source;
                $result = Source::create([
                    'name' => $source_details['text'],
                    'description' => $source_details['title'],
                    'url' => $source_details['htmlUrl'],
                    'author' => '',
                    'fetcher_kind' => 'rss',
                    'fetcher_source' => $source_details['xmlUrl'],
                    'active' => 1,
                    'why_deactivated' => null,
                    'category_id' => $category->id,
                ]);
            }
        }
    }
}
