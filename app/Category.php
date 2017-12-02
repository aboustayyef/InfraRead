<?php

namespace App;

use App\Post;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded=['id'];
    
    /**
     * 
     * Eloquent Relationships
     * 
     */

    public function sources()
    {
      return $this->hasMany('App\Source');
    }

    public function posts()
    {
      return $this->hasMany('App\Post');
    }

    public function getLatestPosts($howmany = 20)
    {
      return Post::with(['Source','Category'])->where('category_id', $this->id)->OrderBy('posted_at','desc')->take($howmany)->get();
    }

    /**
     * 
     * Form Validation 
     * 
     */
    
    public static function validationRules($create=true)
    {
       $rules = [
            'description'  =>  'required|min:6',
       ];
      
       return $rules;
    }

}


