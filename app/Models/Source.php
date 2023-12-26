<?php

namespace App\Models;

use App\Fetchers\rssFetcher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function posts($howmany = null)
    {
        $q =  $this->hasMany('App\Models\Post');
        if ($howmany) {
            return $q->latest()->take($howmany)->get();
        }
        return $q;
    }

    public function media()
    {
        return $this->hasMany('App\Models\Media');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function createAvatar($img)
    {
        $avatar = Media::createFromImage($img, 'source');
        $avatar->source_id = $this->id;
        $avatar->save();
    }

    public function hasAvatar()
    {
        // code...
    }

    public function avatar()
    {
        if (!$this->hasAvatar()) {
            return null;
        }
    }

    public static function getByNickname($nickname)
    {
        try {
            return static::where('nickname', $nickname)->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function daysSinceLastPost()
    {
        return $this->posts->last()->posted_at->diffInDays(new Carbon());
    }

    public function updatePosts(): string
    {
        // $className = 'App\Fetchers\\'.$this->fetcher_kind.'Fetcher';

        // Fetch The Posts into a collection;
        // $posts = (new $className($this))->fetch();

        $posts = (new rssFetcher($this))->fetch();
        if ($posts->count() == 0) {
            return 'No New Posts Available';
        }

        foreach ($posts as $post) {
            $post->source_id = $this->id;
            $post->save();
            $post->applyPlugins();
            $post->markMutedPhrasesAsRead();
            $post->save();
        }

        return $posts->count().' new posts saved';
    }

    public function getLatestPosts($howmany = 60)
    {
        return Post::with(['Source', 'Category'])->where('source_id', $this->id)->OrderBy('posted_at', 'desc')->take($howmany)->get();
    }

    /**
     * These are the rules for validating field form submissions.
     *
     * @return array
     */
    public static function validationRules($create = true)
    {
        $available_fetcher_kinds = ['rss'];
        $rules = [
            'name' => 'required',
            'url' => 'required|url',
            'description' => 'max:140',
            'category_id' => 'required',
            'fetcher_source' => 'required|url',
            'fetcher_kind' => 'in:'.implode(',', $available_fetcher_kinds),
       ];

        return $rules;
    }

    public function categories()
    {
        return $this->BelongsToMany('App\Category');
    }

    public function shortname()
    {
        // www.slashdot.com --> wwwslashdotcom
        return \Illuminate\Support\Str::slug($this->url);
    }

    // FOR VERSION 2

    public function latestPostsSinceEarliestUnread()
    {
        // Get all the posts since the earliest unread one or $minimum_posts, whichever is bigger
        $minimum_posts = 10;
        $maximum_posts = 40;

        // 1- Get the date of the earliest unread post
        $earliest_unread_post = $this->posts()->where('read', 0)->orderBy('posted_at', 'asc')->take(1)->get();

        // If there are no unread posts return the latest posts
        if ($earliest_unread_post->count() < 1) {
            return $this->posts()->with(['source'])->orderBy('posted_at', 'desc')->take($minimum_posts)->get();
        }

        // Otherwise get all posts since the earliest unread post
        $date_of_earliest_uread_post = (string) $earliest_unread_post->first()->posted_at;
        $all_posts_since_earliest_unread =
        $this->posts()->with(['source'])
        ->where('posted_at', '>=', $date_of_earliest_uread_post)
        ->orderBy('posted_at', 'desc')->take($maximum_posts)->get();

        // if the posts are less than $minimum_posts get latest posts instead
        if ($all_posts_since_earliest_unread->count() < $minimum_posts) {
            return $this->posts()->with(['source'])->orderBy('posted_at', 'desc')->take($minimum_posts)->get();
        }
        // otherwise return all posts since earliest unread post
        return $all_posts_since_earliest_unread;
    }
}
