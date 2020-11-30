<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Eloquent Relationships.
     */
    public function sources()
    {
        return $this->hasMany('App\Models\Source');
    }

    public function posts()
    {
        return $this->hasMany('App\Models\Post');
    }

    public function getLatestPosts($howmany = 20)
    {
        return Post::with(['Source', 'Category'])->where('category_id', $this->id)->OrderBy('posted_at', 'desc')->take($howmany)->get();
    }

    /**
     * Form Validation.
     */
    public static function validationRules($create = true)
    {
        $rules = [
            'description' => 'required|min:6',
       ];

        return $rules;
    }
}
