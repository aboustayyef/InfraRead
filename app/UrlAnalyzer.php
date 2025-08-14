<?php

namespace App;

use Embed\Embed;

class UrlAnalyzer
{
    public $status;
    public $error_messages;
    public $result;
    private $url;

    public function __construct($url)
    {
        $this->url = $url;
        $this->status = 'ok';
        $this->error_messages = [];
        $this->result = [
            'title' => null,
            'description' => null,
            'author' => null,
            'rss' => null,
            'canonical_url' => null,
        ];

        $this->analyze();
    }

    /**
     * Analyze the URL and extract metadata.
     * This method does the heavy lifting and can be easily tested.
     */
    private function analyze()
    {
        // Step 1: Basic URL validation
        if (!$this->isValidUrl($this->url)) {
            return $this->abort('URL is not valid. Make sure it starts with http:// or https://');
        }

        // Step 2: Check if URL is reachable and analyze with Embed
        try {
            $embed = new Embed();
            $info = $embed->get($this->url);
            
            $this->extractMetadata($info);
            
        } catch (\Embed\Exceptions\InvalidUrlException $e) {
            return $this->abort('Cannot access URL: ' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->abort('Failed to analyze URL: ' . $e->getMessage());
        }

        // Step 3: Validate that we found usable content
        $this->validateResults();
    }

    /**
     * Validate URL format
     */
    private function isValidUrl($url)
    {
        if (empty($url)) {
            return false;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        // Additional check for http/https
        if (!preg_match('/^https?:\/\//', $url)) {
            return false;
        }

        return true;
    }

    /**
     * Extract metadata from Embed result
     */
    private function extractMetadata($embed)
    {
        // Extract basic metadata
        $this->result['title'] = $this->cleanString($embed->title) ?: 'Untitled';
        $this->result['description'] = $this->cleanString($embed->description) ?: '';
        $this->result['author'] = $this->cleanString($embed->authorName) ?: '';
        $this->result['canonical_url'] = (string) $embed->url ?: $this->url;

        // Extract RSS/Atom feeds
        $feeds = $embed->feeds;
        if (!empty($feeds)) {
            // Take the first available feed
            $this->result['rss'] = (string) $feeds[0];
        }
    }

    /**
     * Clean and sanitize extracted strings
     */
    private function cleanString($string)
    {
        if (empty($string)) {
            return null;
        }

        // Remove extra whitespace and decode HTML entities
        $cleaned = trim(html_entity_decode($string, ENT_QUOTES, 'UTF-8'));
        
        // Remove control characters
        $cleaned = preg_replace('/[\x00-\x1F\x7F]/', '', $cleaned);
        
        return $cleaned ?: null;
    }

    /**
     * Validate the extracted results
     */
    private function validateResults()
    {
        $warnings = [];

        // Check if we found a title
        if (empty($this->result['title']) || $this->result['title'] === 'Untitled') {
            $warnings[] = 'No title found on the page';
        }

        // Check if we found any feeds
        if (empty($this->result['rss'])) {
            $warnings[] = 'No RSS or Atom feed discovered';
        }

        // For now, we don't fail on warnings, but we could log them
        if (!empty($warnings) && config('app.debug')) {
            \Log::info('UrlAnalyzer warnings for ' . $this->url, $warnings);
        }
    }

    /**
     * Mark analysis as failed with error message
     */
    private function abort($message)
    {
        $this->status = 'error';
        $this->error_messages[] = $message;
        
        // Reset result to safe defaults
        $this->result = [
            'title' => null,
            'description' => null,
            'author' => null,
            'rss' => null,
            'canonical_url' => $this->url,
        ];

        return;
    }

    /**
     * Check if analysis was successful
     */
    public function isSuccessful()
    {
        return $this->status === 'ok';
    }

    /**
     * Check if RSS feed was found
     */
    public function hasRssFeed()
    {
        return !empty($this->result['rss']);
    }

    /**
     * Get all extracted metadata
     */
    public function getMetadata()
    {
        return $this->result;
    }

    /**
     * Get RSS feed URL if found
     */
    public function getRssFeed()
    {
        return $this->result['rss'] ?? null;
    }

    /**
     * Get first error message
     */
    public function getFirstError()
    {
        return $this->error_messages[0] ?? 'Unknown error';
    }

    /**
     * Static factory method for easier testing
     */
    public static function create($url)
    {
        return new static($url);
    }
}
