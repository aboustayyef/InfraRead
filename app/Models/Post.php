<?php

namespace App\Models;

use App\Plugins\Kernel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Post extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['time_ago'];
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'posted_at',
    ];

    public static function getLastSuccesfulCrawl(){
        try {
            $last_crawl = new Carbon(File::get(storage_path(). 'LastSuccessfulCrawl.txt'));
            // If more than 30 minutes ago, there's a problem that needs to be looked into
            if ($last_crawl->diffInMinutes() > 30) {
                return 'problem';
            }

            return $last_crawl->diffForHumans();
        } catch (\Exception $e) {
            return 'problem';
        }
    }

    public static function getLatest($howmany = 60)
    {
        return static::with(['Source', 'Category'])->OrderBy('posted_at', 'desc')->take($howmany)->get();
    }

    public static function getOldestUnreadPost()
    {
        $post = static::where('read', 0)->orderBy('posted_at', 'asc')->take(1)->get()->first();

        return $post;
    }

    public function getTimeAgoAttribute()
    {
        return $this->posted_at->diffForHumans();
    }

    /*
        Relationships
     */

    public function source()
    {
        return $this->belongsTo('App\Models\Source');
    }

    public function media()
    {
        return $this->hasMany('App\Models\Media');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    /*
        Utility Functions
     */
    public static function uid_exists($uid)
    {
        return static::where('uid', $uid)->count() > 0;
    }

    public static function uidExists($uid)
    {
        return static::where('uid', $uid)->count() > 0;
    }

    /*
        Images and Media
     */

    public function hasCache()
    {
        return $this->media->count() > 0;
    }

    /**
     * Get appropriate Image (choose between cache and original).
     *
     * @return (string) Image Location
     */
    public function image()
    {
        // if cache exists, return cache,
        if ($this->hasCache()) {
            return '/img/media/'.$this->media->first()->pointer;
        }
        // other wise if an original image exists, return it
        if ($this->original_image && $this->original_image !== 'NULL') {
            return $this->original_image;
        }
        // otherwise, no image exists;
        return null;
    }

    public function rgb()
    {
        if ($this->hasCache()) {
            $values = json_decode($this->media()->latest()->take(1)->first()->dominant_color);
            $string = 'rgb('.$values[0].','.$values[1].','.$values[2].')';

            return $string;
        }

        return null;
    }

    /**
     * Cache original image if cache doesn't exist.
     *
     * @return null
     */
    public function cacheImage($days = 21)
    {
        // ignore caching posts that are older than $days;
        if ($this->posted_at->diffInDays() > $days) {
            return;
        }
        if ($this->media->count() == 0) {
            $image = Media::createFromImage($this->original_image, 'post');
            $image->post_id = $this->id;
            $image->save();
        }
    }

    public function applyPlugins()
    {
        // Get list of Plugins for this Post from Plugins kernel
        $all_plugins = (new Kernel())->get();

        // If this post's source has plugins, apply them
        if (isset($all_plugins[$this->source->shortname()])) {
            $applicable_plugins = $all_plugins[$this->source->shortname()];

            foreach ($applicable_plugins as $plugin) {
                $className = 'App\Plugins\Plugin'.$plugin;
                $post = (new $className($this))->handle();
            }
        }
    }
}
