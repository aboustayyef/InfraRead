<?php

namespace App\Models;

use App\Plugins\Kernel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class Post extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['time_ago'];

    /**
     * The attributes that should be cast to native types.
     *
     * This tells Laravel to automatically convert database values
     * to proper PHP types when retrieving and setting.
     */
    protected $casts = [
        'read' => 'boolean',
        'posted_at' => 'datetime',
    ];

    public function summary(int $numSentences = 2): string
    {
        $apiKey = config('services.openai.key');

        $text = $this->content; // Already contains HTML
        $contentAnalysis = $this->analyzeSummaryContent($text);
        $summaryProfile = $this->summaryProfileFor($contentAnalysis);

        // OpenAI API endpoint
        $endpoint = 'https://api.openai.com/v1/chat/completions';

        $formatInstruction = $summaryProfile['format_instruction'];

        $systemPrompt = <<<EOT
You summarize HTML articles for an RSS reader.
The content may include <blockquote> tags or quoted excerpts from other people.
Do not attribute quoted claims to the blogger unless the blogger clearly endorses them.
Distinguish the blogger's own framing or commentary from the quoted source's point.
If the post is mostly quotes, summarize the theme of the curated quotes and any framing the blogger adds.
Return HTML with only <p>, <ul>, and <li> tags. Do not include headings, blockquotes, links, or markdown.
EOT;

        $userPrompt = <<<EOT
Summarize the following content.

Article analysis:
- Total words: {$contentAnalysis['total_words']}
- Quoted words: {$contentAnalysis['quoted_words']}
- Quote profile: {$contentAnalysis['quote_profile']}
- Article size: {$contentAnalysis['size_profile']}

Summary rules:
- {$formatInstruction}
- Prefer the blogger's framing, but include quoted ideas when they are central to the post.
- If a claim comes from quoted material, make that clear in plain language.
- Paraphrase quotes instead of reproducing them.
- Keep the summary substantially shorter than the article.
- Do not include <blockquote> tags.

{$text}
EOT;

        $data = [
            'model' => 'gpt-4.1-mini',
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
            'max_tokens' => $summaryProfile['max_tokens'],
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

    public function explainQuote(string $quote): string
    {
        $apiKey = config('services.openai.key');
        $quote = $this->normalizeQuoteText($quote);

        $endpoint = 'https://api.openai.com/v1/chat/completions';

        $systemPrompt = <<<EOT
You explain dense quoted passages from articles for a general RSS reader.
Use simple language without talking down to the reader.
Do not add claims beyond the quoted passage.
If the passage includes numbers or comparisons, preserve the direction and approximate magnitude.
Return HTML with only <p>, <ul>, and <li> tags. Do not include headings, links, blockquotes, or markdown.
EOT;

        $userPrompt = <<<EOT
Explain this quoted passage in simple language.

Rules:
- Prefer one short <p> paragraph.
- Use a <ul> with up to 3 <li> items only if the quote contains several distinct findings.
- Keep it under 120 words.
- Make the practical meaning clear for a non-specialist reader.

Quoted passage:
{$quote}
EOT;

        $data = [
            'model' => 'gpt-4.1-mini',
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
            'max_tokens' => 180,
            'temperature' => 0.4,
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post($endpoint, $data);

        if ($response->failed()) {
            \Log::error('OpenAI Quote Explanation API Error: ' . $response->body());
            return 'Error explaining the quote.';
        }

        return optional($response->json())['choices'][0]['message']['content'] ?? 'Error generating explanation';
    }

    public function cachedQuoteExplanation(string $quote): array
    {
        $normalizedQuote = $this->normalizeQuoteText($quote);
        $cacheKey = $this->quoteExplanationCacheKey($normalizedQuote);
        $cachedExplanation = Cache::get($cacheKey);

        if ($cachedExplanation) {
            return [
                'explanation' => $cachedExplanation,
                'cached' => true,
                'hash' => $this->quoteExplanationHash($normalizedQuote),
            ];
        }

        $explanation = $this->explainQuote($normalizedQuote);

        if (! str_starts_with($explanation, 'Error')) {
            Cache::forever($cacheKey, $explanation);
        }

        return [
            'explanation' => $explanation,
            'cached' => false,
            'hash' => $this->quoteExplanationHash($normalizedQuote),
        ];
    }

    public function quoteExplanationHash(string $quote): string
    {
        return sha1($this->normalizeQuoteText($quote));
    }

    public function normalizeQuoteText(string $quote): string
    {
        return (string) Str::of($quote)->stripTags()->squish();
    }

    private function quoteExplanationCacheKey(string $quote): string
    {
        return "post_quote_explanation:{$this->id}:".$this->quoteExplanationHash($quote);
    }

    /**
     * @return array{total_words: int, quoted_words: int, quote_ratio: float, quote_profile: string, size_profile: string}
     */
    private function analyzeSummaryContent(?string $html): array
    {
        $html = (string) $html;
        $plainText = (string) Str::of($html)->stripTags()->squish();
        $totalWords = Str::wordCount($plainText);
        $quotedWords = $this->countBlockquoteWords($html);
        $quoteRatio = $totalWords > 0 ? $quotedWords / $totalWords : 0.0;

        return [
            'total_words' => $totalWords,
            'quoted_words' => $quotedWords,
            'quote_ratio' => $quoteRatio,
            'quote_profile' => $this->quoteProfileFor($quoteRatio),
            'size_profile' => $this->sizeProfileFor($totalWords),
        ];
    }

    private function countBlockquoteWords(string $html): int
    {
        preg_match_all('/<blockquote\b[^>]*>(.*?)<\/blockquote>/is', $html, $matches);

        return collect($matches[1] ?? [])
            ->sum(fn (string $blockquote): int => Str::wordCount((string) Str::of($blockquote)->stripTags()->squish()));
    }

    private function quoteProfileFor(float $quoteRatio): string
    {
        if ($quoteRatio >= 0.65) {
            return 'mostly quoted material';
        }

        if ($quoteRatio >= 0.25) {
            return 'mixed commentary and quoted material';
        }

        return 'mostly original commentary';
    }

    private function sizeProfileFor(int $totalWords): string
    {
        if ($totalWords < 500) {
            return 'short';
        }

        if ($totalWords < 1600) {
            return 'medium';
        }

        return 'large';
    }

    /**
     * @param array{total_words: int, quoted_words: int, quote_ratio: float, quote_profile: string, size_profile: string} $contentAnalysis
     * @return array{format_instruction: string, max_tokens: int}
     */
    private function summaryProfileFor(array $contentAnalysis): array
    {
        if ($contentAnalysis['size_profile'] === 'short') {
            return [
                'format_instruction' => 'Return exactly one concise <p> paragraph, no more than 70 words.',
                'max_tokens' => 130,
            ];
        }

        if ($contentAnalysis['quote_profile'] === 'mostly quoted material' && $contentAnalysis['size_profile'] === 'large') {
            return [
                'format_instruction' => 'Return a short <p> paragraph explaining the blogger\'s framing, followed by a <ul> list of 3 to 5 concise <li> main points from the quoted material.',
                'max_tokens' => 260,
            ];
        }

        return [
            'format_instruction' => 'Return exactly two concise <p> paragraphs, no more than 160 words total.',
            'max_tokens' => 230,
        ];
    }



    public function old_summary($numSentences = 4)
    {
        $apiKey = config('services.openai.key');
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
        if (!$this->posted_at) {
            return null;
        }

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
