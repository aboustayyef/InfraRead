<?php

namespace App;

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


