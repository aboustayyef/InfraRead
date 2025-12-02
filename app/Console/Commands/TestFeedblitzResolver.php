<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestFeedblitzResolver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feedblitz:test-resolver {--timeout=5 : HTTP timeout in seconds} {--redirects=5 : Max redirect hops}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resolve FeedBlitz redirect links in a sample HTML payload and report results.';

    /**
     * Sample HTML content containing FeedBlitz links to resolve.
     *
     * @var string
     */
    private string $sampleHtml = <<<'HTML'
<p>1. <a href="https://feeds.feedblitz.com/~/t/0/0/marginalrevolution/~https://x.com/RMLLowe/status/1994624153731682503" target="_blank" rel="noopener">The origins of Thomas Nagel</a>?</p>
<p>2. <a href="https://feeds.feedblitz.com/~/t/0/0/marginalrevolution/~https://www.washingtonpost.com/climate-environment/interactive/2025/vulture-extinction-rabies-dogs/?itid=hp-top-table-main_p001_f012" target="_blank" rel="noopener">The cost of too few vultures?</a></p>
<p>3. <a href="https://feeds.feedblitz.com/~/t/0/0/marginalrevolution/~https://x.com/SebastienBubeck/status/1994946303546331508" target="_blank" rel="noopener">AI solving previously unsolved math problems</a>.</p>
<p>4. <a href="https://feeds.feedblitz.com/~/t/0/0/marginalrevolution/~https://x.com/_sholtodouglas/status/1995027114740125989?t=ZzblIZsD3h9AVNzGfCUPlw&amp;s=08" target="_blank" rel="noopener">An LLM writes about what it is like to be an LLM</a>.</p>
<p>5. <a href="https://feeds.feedblitz.com/~/t/0/0/marginalrevolution/~https://www.commonreader.co.uk/p/tom-stoppards-ordinary-magic?utm_source=post-email-title&amp;publication_id=120973&amp;post_id=180318245&amp;utm_campaign=email-post-title&amp;isFreemail=false&amp;r=3o9&amp;triedRedirect=true&amp;utm_medium=email" target="_blank" rel="noopener">Henry Oliver on Stoppard</a>.</p>
<p>6. <a href="https://feeds.feedblitz.com/~/t/0/0/marginalrevolution/~https://www.bbc.com/news/articles/cx2p2xp19r3o" target="_blank" rel="noopener">Art deco in Mumbai</a>.</p>
<p>The post <a href="https://feeds.feedblitz.com/~/t/0/0/marginalrevolution/~https://marginalrevolution.com/marginalrevolution/2025/12/monday-assorted-links-537.html">Monday assorted links</a> appeared first on <a href="https://feeds.feedblitz.com/~/t/0/0/marginalrevolution/~https://marginalrevolution.com">Marginal REVOLUTION</a>.</p>
<img align="left" border="0" height="1" width="1" alt="" style="border:0;float:left;margin:0;padding:0;width:1px!important;height:1px!important;" hspace="0" src="https://feeds.feedblitz.com/~/i/930760325/0/marginalrevolution">
<div style="clear:both;padding-top:0.2em;"><a title="Like on Facebook" href="https://feeds.feedblitz.com/_/28/930760325/marginalrevolution"><img height="20" src="https://assets.feedblitz.com/i/fblike20.png" style="border:0;margin:0;padding:0;"></a> <a title="Pin it!" href="https://feeds.feedblitz.com/_/29/930760325/marginalrevolution,"><img height="20" src="https://assets.feedblitz.com/i/pinterest20.png" style="border:0;margin:0;padding:0;"></a> <a title="Post to X.com" href="https://feeds.feedblitz.com/_/24/930760325/marginalrevolution"><img height="20" src="https://assets.feedblitz.com/i/x.png" style="border:0;margin:0;padding:0;"></a> <a title="Subscribe by email" href="https://feeds.feedblitz.com/_/19/930760325/marginalrevolution"><img height="20" src="https://assets.feedblitz.com/i/email20.png" style="border:0;margin:0;padding:0;"></a> <a title="Subscribe by RSS" href="https://feeds.feedblitz.com/_/20/930760325/marginalrevolution"><img height="20" src="https://assets.feedblitz.com/i/rss20.png" style="border:0;margin:0;padding:0;"></a> <a rel="NOFOLLOW" title="View Comments" href="https://marginalrevolution.com/marginalrevolution/2025/12/monday-assorted-links-537.html#comments"><img height="20" style="border:0;margin:0;padding:0;" src="https://assets.feedblitz.com/i/comments20.png"></a> <a title="Follow Comments via RSS" href="https://marginalrevolution.com/marginalrevolution/2025/12/monday-assorted-links-537.html/feed"><img height="20" style="border:0;margin:0;padding:0;" src="https://assets.feedblitz.com/i/commentsrss20.png"></a> 
<div style="clear:left;"><a rel="NOFOLLOW" href="https://marginalrevolution.com/marginalrevolution/2025/12/monday-assorted-links-537.html#comments"><h3>Comments</h3></a><ul><li><a rel="NOFOLLOW" href="https://marginalrevolution.com/marginalrevolution/2025/12/monday-assorted-links-537.html#comment-160990096">In reply to Jimmy.   This is just the beginning! It's great! ...</a> <i>by Neurotic</i></li><li><a rel="NOFOLLOW" href="https://marginalrevolution.com/marginalrevolution/2025/12/monday-assorted-links-537.html#comment-160990095">In reply to Stop the hype.   Good luck in trying to get TC to ...</a> <i>by Neurotic</i></li><li><a rel="NOFOLLOW" href="https://marginalrevolution.com/marginalrevolution/2025/12/monday-assorted-links-537.html#comment-160990093">#1… Someone told me that when Nagel announced he was reading ...</a> <i>by Donald Pretari</i></li><li><a rel="NOFOLLOW" href="https://marginalrevolution.com/marginalrevolution/2025/12/monday-assorted-links-537.html#comment-160990090">In reply to CA.   CA Overview   An MR commenter that that ...</a> <i>by RAD</i></li><li><a rel="NOFOLLOW" href="https://marginalrevolution.com/marginalrevolution/2025/12/monday-assorted-links-537.html#comment-160990086">In reply to Cancelled for wongthink.   That's how I feel about ...</a> <i>by RAD</i></li><li><a rel="NOFOLLOW" href="https://marginalrevolution.com/marginalrevolution/2025/12/monday-assorted-links-537.html#comments">Plus 10 more...</a></li></ul></div> </div>
HTML;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $timeout = (int) $this->option('timeout');
        $maxRedirects = (int) $this->option('redirects');

        $pattern = '/https?:\/\/feeds\.feedblitz\.com\/[^\s"\']+/i';
        preg_match_all($pattern, $this->sampleHtml, $matches);
        $urls = array_values(array_unique($matches[0] ?? []));

        if (empty($urls)) {
            $this->error('No FeedBlitz links found in the sample HTML.');
            return 1;
        }

        $this->info("Found " . count($urls) . " FeedBlitz links. Resolving...");
        $allOk = true;

        foreach ($urls as $url) {
            $this->line("");
            $this->comment("→ Resolving: {$url}");
            $start = microtime(true);

            try {
                $response = Http::withHeaders([
                        'User-Agent' => 'InfraRead-Resolver/1.0 (+https://infraread.test)',
                    ])
                    ->timeout($timeout)
                    ->retry(1, 200)
                    ->withOptions([
                        'allow_redirects' => [
                            'track_redirects' => true,
                            'max' => $maxRedirects,
                        ],
                    ])
                    ->get($url);

                $stats = $response->handlerStats();
                $finalUrl = $stats['url'] ?? $url;
                $duration = number_format(microtime(true) - $start, 2);
                $redirects = $stats['redirect_count'] ?? 0;

                $this->info("   Status: {$response->status()} | Redirects: {$redirects} | Time: {$duration}s");
                $this->info("   Final:  {$finalUrl}");

                if (!$response->successful()) {
                    $allOk = false;
                }
            } catch (\Exception $e) {
                $allOk = false;
                $this->error("   Failed: {$e->getMessage()}");
            }
        }

        $this->line('');
        if ($allOk) {
            $this->info('✅ All links resolved successfully.');
            return 0;
        }

        $this->warn('⚠️  Some links failed to resolve. See output above.');
        return 1;
    }
}
