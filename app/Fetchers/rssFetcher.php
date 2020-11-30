<?php

namespace App\Fetchers;

use App\Models\Post;
use App\Models\Source;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

class rssFetcher implements Fetchable
{
    private $list_of_post_links; // raw collection of link and uid keys
    private $list_of_new_posts; // collection of App\Post obejcts of new posts only

    public function __construct(Source $source)
    {
        $this->source = $source;
    }

    /**
     * The General Process of getting posts
     * Step 1: Get a list of post links
     * Step 2: Filter the list to only new posts and get details.
     *
     * @return Laravel Collection of post objects
     */
    public function fetch()
    {
        $this->list_of_post_links = $this->get_list_of_post_links();
        $this->list_of_new_posts = $this->get_new_posts();

        return $this->list_of_new_posts;
    }

    /**
     * This is step One. Use Simplepie to get list of new posts
     * Then remove everything but the link and uid and convert to collection.
     */
    public function get_list_of_post_links()
    {
        $rss_feed = $this->source->fetcher_source;
        $feed = \Feeds::make($rss_feed);

        // get all Items in the RSS feed
        $items = collect($feed->get_items());

        $items = $items->map(function ($item) {
            if (is_object($item->get_author())) {
                $author = $item->get_author()->get_name();
            } else {
                $author = '';
            }

            return
            [
                'url' => $item->get_link(),
                'uid' => $item->get_id(),
                'date' => $item->get_date(),
                'title' => html_entity_decode($item->get_title()),
                'url' => $item->get_link(),
                'author' => $author,
                'content' => $item->get_content(),
            ];
        });

        return $items;
    }

    /**
     * Step 2: Convert the collection of link/uid to full-fleshed post objects.
     *
     * @return a Laravel Collection of Post objects
     */
    public function get_new_posts()
    {
        //filter out posts that already exist in the database
        $new_links = $this->list_of_post_links->filter(function ($item) {
            return !Post::uidExists(substr($item['uid'], 0, 190));
        });

        $posts = $new_links->map(function ($item) {
            // Content Depends on whether source wants full Feed
            $post = new Post();
            $post->uid = substr($item['uid'], 0, 190);
            $post->content = $item['content'];
            $post->title = $item['title'];
            $post->url = $item['url'];
            if ($item['author']) {
                $post->author = $item['author'];
            }
            $post->excerpt = Str::limit(strip_tags($item['content']), 280);
            // $post->original_image = $e->image;
            $post->posted_at = new Carbon($item['date']);
            $post->category_id = $this->source->category->id;

            return $post;
        });

        return $posts;
    }

    // private function get_full_content_from_link($url)
    public static function get_full_content_from_link($url)
    {
        $client = new Client();
        try {
            $response = $client->request('GET', 'https://mercury.postlight.com/parser', [
                    'query' => [
                        'url' => $url,
                    ],
                    'headers' => [
                        'Content-Type:' => 'application/json',
                        'x-api-key' => env('MERCURY_API_KEY'),
                    ],
                ]);
            $content = json_decode($response->getBody());

            return $content->content;
        } catch (\Exception $e) {
            return false;
        }
    }
}
