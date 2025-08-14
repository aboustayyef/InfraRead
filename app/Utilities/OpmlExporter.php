<?php

namespace App\Utilities;

use App\Models\Category;
use App\Models\Source;

class OpmlExporter
{
    /**
     * Generate OPML XML content for all sources
     */
    public function generate(): string
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        // Root OPML element
        $opml = $xml->createElement('opml');
        $opml->setAttribute('version', '2.0');
        $xml->appendChild($opml);

        // Head section
        $head = $xml->createElement('head');
        $opml->appendChild($head);

        $title = $xml->createElement('title', 'Infraread RSS Feeds');
        $head->appendChild($title);

        $created = $xml->createElement('dateCreated', now()->toRfc2822String());
        $head->appendChild($created);

        // Body section
        $body = $xml->createElement('body');
        $opml->appendChild($body);

        // Add categories and their sources
        $categories = Category::with('sources')->get();

        foreach ($categories as $category) {
            $categoryOutline = $xml->createElement('outline');
            $categoryOutline->setAttribute('text', $category->description);
            $categoryOutline->setAttribute('title', $category->description);

            foreach ($category->sources as $source) {
                $sourceOutline = $xml->createElement('outline');
                $sourceOutline->setAttribute('type', 'rss');
                $sourceOutline->setAttribute('text', $source->name);
                $sourceOutline->setAttribute('title', $source->name);
                $sourceOutline->setAttribute('xmlUrl', $this->getRssUrl($source));

                if ($source->url) {
                    $sourceOutline->setAttribute('htmlUrl', $source->url);
                }

                $categoryOutline->appendChild($sourceOutline);
            }

            $body->appendChild($categoryOutline);
        }

        // Add uncategorized sources
        $uncategorizedSources = Source::whereNull('category_id')->get();
        foreach ($uncategorizedSources as $source) {
            $sourceOutline = $xml->createElement('outline');
            $sourceOutline->setAttribute('type', 'rss');
            $sourceOutline->setAttribute('text', $source->name);
            $sourceOutline->setAttribute('title', $source->name);
            $sourceOutline->setAttribute('xmlUrl', $this->getRssUrl($source));

            if ($source->url) {
                $sourceOutline->setAttribute('htmlUrl', $source->url);
            }

            $body->appendChild($sourceOutline);
        }

        return $xml->saveXML();
    }

    /**
     * Get RSS URL based on environment configuration
     */
    private function getRssUrl(Source $source): string
    {
        if (env('OPML_EXPORT_LOCAL_RSS_URLS', false)) {
            return route('api.v1.sources.rss', $source->id);
        }

        return $source->fetcher_source;
    }
}
