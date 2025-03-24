<?php

namespace App\Models;

use App\Plugins\Kernel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


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

    public function summary($numSentences = 2)
    {
        $apiKey = env('OPENAI_KEY');

        // Keep HTML but remove potentially dangerous scripts or styles
        $text = $this->content; // Already contains HTML

        // OpenAI API endpoint
        $endpoint = 'https://api.openai.com/v1/chat/completions';

        // Clarify in the system prompt how blockquotes should be interpreted
        $systemPrompt = <<<EOT
You are a helpful assistant that summarizes HTML content.
The content may include <blockquote> tags, which indicate quoted text from someone else.
Distinguish between the main author's commentary and the quoted material when generating the summary.
EOT;

        $userPrompt = <<<EOT
Summarize the following content in {$numSentences} sentences.
Preserve any <blockquote> tags from the input, as they indicate quoted material.
Don't include <blockquote> tags in the output, but wrap each output sentence in a <p> tag to make it HTML-ready.

{$text}
EOT;

        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => $userPrompt,
                ],
            ],
            'max_tokens' => 200,
            'temperature' => 0.5,
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post($endpoint, $data);

        if ($response->failed()) {
            \Log::error('OpenAI API Error: ' . $response->body());
            return 'Error summarizing the text.';
        }

        return optional($response->json())['choices'][0]['message']['content'] ?? 'Error generating summary';
    }



    public function old_summary($numSentences = 4)
    {
        $apiKey = env('OPENAI_KEY');
        $text = strip_tags($this->content);
        $text = str_replace(PHP_EOL, '', $text);

        // The API endpoint for text summarization using GPT-4o mini
        $endpoint = 'https://api.openai.com/v1/chat/completions';

        // Create the request payload
        $data = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant that summarizes text.'
                ],
                [
                    'role' => 'user',
                    'content' => 'Summarize this in ' . $numSentences . ' sentences: ' . $text
                ]
            ],
            'max_tokens' => 250, // Increased max_tokens for better summaries
            'temperature' => 0.5,
        ];

        // Send the request using Laravel's Http class
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post($endpoint, $data);

        if ($response->failed()) {
            return 'Error summarizing';
        } else {
            $summary = $response->json()['choices'][0]['message']['content'];
            return $summary;
        }
    }
    public static function getLastSuccesfulCrawl()
    {
        try {
            $crawl_status = [];
            $last_crawl = new Carbon(File::get(storage_path() . '/app/LastSuccessfulCrawl.txt'));
            // If more than 80 minutes ago, there's a problem that needs to be looked into
            if ($last_crawl->diffInMinutes() > 80) {
                $crawl_status['status'] = 'warning';
                $crawl_status['message'] = 'Warning. Last Crawl was ' . $last_crawl->diffForHumans();
                return collect($crawl_status);
            }

            $crawl_status['status'] = 'ok';
            $crawl_status['message'] = $last_crawl->diffForHumans();
            return collect($crawl_status);
        } catch (\Exception $e) {
            dd($e);
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
            return '/img/media/' . $this->media->first()->pointer;
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
            $string = 'rgb(' . $values[0] . ',' . $values[1] . ',' . $values[2] . ')';

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

    public function markMutedPhrasesAsRead()
    {
        // Get List of Muted Phrases
        $jsonString = Storage::disk('local')->get("muted_phrases.json");
        $list_of_phrases = json_decode($jsonString, true); // Converts to an array
        if (Str::contains($this->title, $list_of_phrases)) {
            $this->read = 1;
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
                $className = 'App\Plugins\Plugin' . $plugin;
                (new $className($this))->handle();
            }
        }
    }
}
