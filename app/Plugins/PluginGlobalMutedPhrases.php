<?php

namespace App\Plugins;

use App\Models\Post;
use Illuminate\Support\Str;

/**********************************************************************************
 * Global Muted Phrases Plugin
 **********************************************************************************
 *
 * About This Plugin
 * -------------------
 * Automatically marks posts as read if their title contains any globally muted phrases.
 * This replaces the legacy Livewire muted phrases functionality with a plugin-based approach.
 *
 * Modified Properties:
 * --------------------
 * Post->read - Sets to 1 if title contains any muted phrase
 */

class PluginGlobalMutedPhrases implements PluginInterface
{
    private $post;
    private $options;

    public function __construct(Post $post, array $options = [])
    {
        $this->post = $post;
        $this->options = $options;
    }

    public function handle(): bool
    {
        try {
            $mutedPhrases = $this->getMutedPhrases();

            if (empty($mutedPhrases)) {
                return true; // No muted phrases configured
            }

            $title = $this->post->title;
            $caseSensitive = $this->options['case_sensitive'] ?? false;

            foreach ($mutedPhrases as $phrase) {
                if (empty(trim($phrase))) {
                    continue; // Skip empty phrases
                }

                $matches = $caseSensitive
                    ? Str::contains($title, $phrase)
                    : Str::contains(Str::lower($title), Str::lower($phrase));

                if ($matches) {
                    $this->post->read = 1;

                    // Optionally log which phrase caused the muting
                    if ($this->options['log_matches'] ?? false) {
                        \Log::info("Post muted by phrase", [
                            'post_id' => $this->post->id,
                            'post_title' => $title,
                            'muted_phrase' => $phrase,
                            'source' => $this->post->source->name ?? 'Unknown'
                        ]);
                    }

                    break; // No need to check other phrases once we found a match
                }
            }

            $this->post->save();
            return true;

        } catch (\Exception $e) {
            \Log::error('Global Muted Phrases plugin failed', [
                'post_id' => $this->post->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Get muted phrases from configuration
     *
     * @return array List of muted phrases
     */
    private function getMutedPhrases(): array
    {
        // Check if custom phrases are provided via options
        if (isset($this->options['custom_phrases']) && is_array($this->options['custom_phrases'])) {
            return $this->options['custom_phrases'];
        }

        // Hard-coded muted phrases - edit this array to manage muted phrases
        return [
            'Club MacStories',
            '(Sponsor)',
            'AppStories, Episode',
            'MacStories Unwind',
            'Appearance:',
            'thread', // For slow boring threads
            '[Sponsor]',
        ];
    }

    public function getMetadata(): array
    {
        return [
            'name' => 'Global Muted Phrases',
            'description' => 'Automatically marks posts as read if their title contains hard-coded muted phrases',
            'version' => '2.0.0',
            'author' => 'InfraRead',
            'modifies' => ['read'],
            'options' => [
                'case_sensitive' => 'Boolean - Whether phrase matching should be case sensitive (default: false)',
                'log_matches' => 'Boolean - Whether to log when posts are muted (default: false)',
                'custom_phrases' => 'Array - Custom list of phrases to use instead of hard-coded phrases'
            ],
            'scope' => 'global', // This plugin should apply to all sources
            'priority' => 10, // High priority to mute posts early in processing
            'configuration' => 'hard_coded' // Indicates phrases are managed in code
        ];
    }
}
