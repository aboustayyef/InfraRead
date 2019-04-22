<?php

namespace App;

use App\Media;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Plugins\Kernel;

class Post extends Model
{
    protected $guarded = [];
    protected $appends = ['time_ago'];
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'posted_at'
    ]; 

    public static function getLatest($howmany = 60)
    {
        return Static::with(['Source','Category'])->OrderBy('posted_at','desc')->take($howmany)->get();
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
        return $this->belongsTo('App\Source');
    }

    public function media()
    {
      return $this->hasMany('App\Media');
    }

    public function category()
    {
        return $this->belongsTo('App\Category');
    }

    /*
        Utility Functions
     */ 
    public static function uid_exists($uid)
    {
        return Static::where('uid',$uid)->count() > 0;
    }

    public static function uidExists($uid)
    {
        return Static::where('uid',$uid)->count() > 0;
    }


    /*
        Images and Media
     */

    public function hasCache(){
        return $this->media->count() > 0 ;
    }
    /**
     * Get appropriate Image (choose between cache and original)
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
     * Cache original image if cache doesn't exist
     * @return null 
     */
    public function cacheImage($days = 21)
    {

        // ignore caching posts that are older than $days;
        if ($this->posted_at->diffInDays() > $days ) {
            return ;
        }
        if ($this->media->count() == 0) {
            $image = Media::createFromImage($this->original_image, 'post');
            $image->post_id = $this->id;
            $image->save();
        }
    }
    public function applyPlugins(){
        // Get list of Plugins for this Post from Plugins kernel
        $all_plugins = (new Kernel)->get();

        // If this post's source has plugins, apply them
        if ( isset($all_plugins[$this->source->shortname()])) {
            $applicable_plugins = $all_plugins[$this->source->shortname()];
            
            foreach ($applicable_plugins as $plugin) {
                $className = 'App\Plugins\Plugin'.$plugin;
                $post = (new $className($this))->handle();
            }
        }

    }
}