<?php

namespace App;

use App\Media;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $guarded=['id'];
    
    public function posts()
    {
      return $this->hasMany('App\Post');
    }
    public function media()
    {
      return $this->hasMany('App\Media');
    }
    
    public function tag()
    {
      return $this->belongsTo('App\Tag');
    }

    public function createAvatar($img)
    {
      $avatar = Media::createFromImage($img, 'source');
      $avatar->source_id = $this->id;
      $avatar->save();
    } 
    
    public function hasAvatar()
    {
      # code...
    }

    public function avatar(){
      if (! $this->hasAvatar()) {
        return null;
      }

    }
    public static function getByNickname($nickname)
    {
      try {
       return Static::where('nickname',$nickname)->first(); 
      } catch (\Exception $e) {
        return null;
      }
    }

    public function daysSinceLastPost()
    {
      return $this->posts->last()->posted_at->diffInDays(new Carbon);
    }

    public function updatePosts()
    {
      $className = 'App\Fetchers\\' . $this->fetcher_kind . 'Fetcher';

      // Fetch The Posts into a collection;
      $posts = (new $className($this))->fetch();

      if ($posts->count() == 0) {
        return 'No New Posts Available';
      }
      
      foreach ($posts as $post) {
        $post->source_id = $this->id;
        $post->save(); 
      }
      return $posts->count() . ' new posts saved';
    }


    /**
     * These are the rules for validating field form submissions
     * @return array 
     */
    public static function validationRules($create = true)
    {
        $available_fetcher_kinds = ['rss'];
        $rules =  [
            'name'  =>  'required|min:6',
            'url'   =>  'required|url',
            'nickname'=> 'required|alpha_num',
            'description'   =>  'max:140',
            'twitter'  =>   'required|alpha_dash',
            'fetcher_source'   =>  'required|url',      
            'fetcher_kind'   =>  'in:'. implode(',', $available_fetcher_kinds),      
       ];
       if ($create) {
           $rules['nickname'] .= '|unique:blogs';
       }
       return $rules;
    }
    public function tags()
    {
      return $this->BelongsToMany('App\Tag');
    }
}
