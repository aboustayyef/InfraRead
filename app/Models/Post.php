<?php

namespace App\Models;

use App\Plugins\Kernel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
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

    /**
     * The attributes that should be cast to native types.
     *
     * This tells Laravel to automatically convert database values
     * to proper PHP types when retrieving and setting.
     */
    protected $casts = [
        'read' => 'boolean',
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

    /**
     * Apply plugins to this post with comprehensive error handling.
     *
     * This method processes all configured plugins for the post's source,
     * applying them in sequence while capturing detailed error information.
     * Plugin failures are logged but don't prevent other plugins from running.
     *
     * @throws \App\Exceptions\FeedProcessing\PluginException If critical plugin failure occurs
     */
    public function applyPlugins()
    {
        // Get list of plugins for this post from plugins kernel
        $kernel = new \App\Plugins\Kernel();
        $allPlugins = $kernel->getPluginsForSource($this->source);

        if (empty($allPlugins)) {
            return; // No plugins configured for this source
        }

        $successfulPlugins = [];
        $failedPlugins = [];

        foreach ($allPlugins as $pluginConfig) {
            $pluginName = $pluginConfig['name'];
            $pluginOptions = $pluginConfig['options'] ?? [];

            try {
                $this->executePlugin($pluginName, $pluginOptions);
                $successfulPlugins[] = $pluginName;

            } catch (\Exception $e) {
                $failedPlugins[] = [
                    'name' => $pluginName,
                    'error' => $e->getMessage(),
                    'options' => $pluginOptions
                ];

                // Create structured plugin exception with context
                $pluginException = \App\Exceptions\FeedProcessing\PluginException::executionError(
                    $this->source,
                    $pluginName,
                    $e->getMessage(),
                    $this->id
                );

                // Log the plugin failure but continue processing other plugins
                \Log::warning('Plugin execution failed', [
                    'exception' => $pluginException->getMessage(),
                    'context' => $pluginException->getContext(),
                    'plugin_name' => $pluginName,
                    'post_id' => $this->id
                ]);
            }
        }

        // Log summary of plugin processing
        if (!empty($successfulPlugins) || !empty($failedPlugins)) {
            \Log::info('Plugin processing completed for post', [
                'post_id' => $this->id,
                'source_id' => $this->source->id,
                'successful_plugins' => $successfulPlugins,
                'failed_plugins' => count($failedPlugins),
                'total_plugins' => count($allPlugins)
            ]);
        }
    }

    /**
     * Execute a single plugin with the given options.
     *
     * @param string $pluginName Name of the plugin class (without Plugin prefix)
     * @param array $options Plugin configuration options
     * @throws \Exception If plugin execution fails
     */
    private function executePlugin(string $pluginName, array $options = []): void
    {
        $className = 'App\\Plugins\\Plugin' . $pluginName;

        // Check if plugin class exists
        if (!class_exists($className)) {
            throw new \Exception("Plugin class {$className} not found");
        }

        // Check if plugin implements the required interface
        if (!is_subclass_of($className, \App\Plugins\PluginInterface::class)) {
            throw new \Exception("Plugin {$pluginName} must implement PluginInterface");
        }

        // Create plugin instance and execute
        $plugin = new $className($this, $options);
        $result = $plugin->handle();

        // Check if plugin execution was successful
        if ($result === false) {
            throw new \Exception("Plugin {$pluginName} returned false, indicating execution failure");
        }
    }
}
