<?php

namespace App\Fetchers;

use App\Post;
use App\Source;
use Carbon\Carbon;
use Embed\Embed;

class rssFetcher implements Fetchable
{
	
	private $list_of_post_links; // raw collection of link and uid keys
	private $list_of_new_posts; // collection of App\Post obejcts of new posts only

	function __construct(Source $source)
	{
		$this->source = $source;
	}

	/**
	 * The General Process of getting posts
	 * Step 1: Get a list of post links
	 * Step 2: Filter the list to only new posts and get details
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
	 * Then remove everything but the link and uid and convert to collection
	 */
	public function get_list_of_post_links()
	{
		$rss_feed = $this->source->fetcher_source; 
		$feed = \Feeds::make($rss_feed);

		// get all Items in the RSS feed
		$items = collect($feed->get_items());

		// keep only url and id
		$items = $items->map(function($item) {
			return 
			[
				'url' => $item->get_link(), 
				'uid' => $item->get_id(),
				'date'=> $item->get_date(),
				'title'=> $item->get_title(),
				'url'	=> $item->get_link(),	
				'content' => $item->get_content(),
			];
		});

		return $items;
	}

	/**
	 * Step 2: Convert the collection of link/uid to full-fleshed post objects
	 * @return a Laravel Collection of Post objects	
	 */

	public function get_new_posts()
	{
		//filter out posts that already exist in the database
		$new_links = $this->list_of_post_links->filter(function($item) {
			return ! Post::uidExists($item['uid']);
		});
		
		$posts = $new_links->map(function($item){
			// $e = Embed::create($item['url']);  
			$post = new Post;
			$post->uid = $item['uid'];
			$post->content = $item['content'];
			$post->title = $item['title'];
			$post->url = $item['url'];
			$post->excerpt = str_limit(strip_tags($item['content']), 280);
			// $post->original_image = $e->image;
			$post->posted_at = new Carbon($item['date']);
			$post->tag_id = $this->source->tag->id;
			return $post;
		});

		return $posts;
	}
}