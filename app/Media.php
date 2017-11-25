<?php

namespace App;

use ColorThief\ColorThief;
use Illuminate\Database\Eloquent\Model;
use Intervention\Image\Facades\Image;

class Media extends Model
{
    // Here, "source" refers to news source, like blogs
    public function source()
    {
        return $this->belongsTo('App\Source');
    }

    public function post()
    {
        return $this->belongsTo('App\Post');
    }

    public static function createFromImage($img, $kind = 'post')
    {

        // only two kinds are supported
        if (!in_array($kind, ['post','source'])) {
            throw new \Exception("kind has to be either 'post' or 'source'", 1);
        }

        $img = Image::make($img);
        
        // guard against non-image formats
        if ($img->mime !== 'image/jpeg' && $img->mime !== 'image/png') {
            throw new \Exception("Can only support jpeg and png images", 1);
        }

        // use proper dimensions according to 
        if ($kind == 'source') 
        {
            $img->fit(100,100);
        } 
        else if ($kind == 'post')
        {
            $img->fit(600,300);
        }

        // create unique name
        $pointer = $kind . '_' . time() . '_' . rand(999999999,1);

        // full path to save
        $path = public_path().'/img/media/';
        $extension = $img->mime == 'image/jpeg' ? '.jpg' : '.png';
        $full_path = $path.$pointer.$extension;

        // store image
        // todo: handle storage error
        $img->save($full_path);

        // get dominant color
        $dominant_color = json_encode(ColorThief::getColor($full_path));

        // save to database
        $media = new Static;
        $media->pointer = $pointer.$extension;
        $media->dominant_color = $dominant_color;
        $media->save();
        return $media;
    }
}
