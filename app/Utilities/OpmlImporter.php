<?php

namespace App\Utilities;

use App\Models\Category;
use App\Models\Source;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OpmlImporter
{
    /**
     * Legacy method to maintain backward compatibility
     */
    public static function process()
    {
        $importer = new self();
        return $importer->import(storage_path().'/app/uploaded/feeds.opml', 'replace');
    }

    /**
     * Preview OPML file contents without importing
     */
    public function preview(string $filePath): array
    {
        $this->validateOpmlFile($filePath);

        $xml = simplexml_load_file($filePath);
        $collection = collect(json_decode(json_encode($xml), true));

        if (!isset($collection['body']['outline'])) {
            throw new \Exception('Invalid OPML format: no feeds found');
        }

        $feeds = $collection['body']['outline'];
        $preview = [
            'categories' => [],
            'uncategorized_sources' => [],
            'total_categories' => 0,
            'total_sources' => 0,
        ];

        foreach ($feeds as $group) {
            if (isset($group['outline'])) {
                // This is a category with sources
                $categoryName = $group['@attributes']['title'] ?? 'Unnamed Category';
                $sources = [];

                foreach ($group['outline'] as $source) {
                    $sourceDetails = $source['@attributes'] ?? $source;
                    $sources[] = [
                        'name' => $sourceDetails['text'] ?? 'Unknown',
                        'url' => $sourceDetails['xmlUrl'] ?? '',
                        'site_url' => $sourceDetails['htmlUrl'] ?? '',
                    ];
                }

                $preview['categories'][] = [
                    'name' => $categoryName,
                    'sources' => $sources,
                    'source_count' => count($sources),
                ];

                $preview['total_sources'] += count($sources);
                $preview['total_categories']++;
            } else {
                // This is an uncategorized source
                $sourceDetails = $group['@attributes'] ?? $group;
                $preview['uncategorized_sources'][] = [
                    'name' => $sourceDetails['text'] ?? 'Unknown',
                    'url' => $sourceDetails['xmlUrl'] ?? '',
                    'site_url' => $sourceDetails['htmlUrl'] ?? '',
                ];
                $preview['total_sources']++;
            }
        }

        return $preview;
    }

    /**
     * Import OPML file with improved error handling and merge capability
     */
    public function import(string $filePath, string $mode = 'replace'): array
    {
        $this->validateOpmlFile($filePath);

        DB::beginTransaction();

        try {
            $xml = simplexml_load_file($filePath);
            $collection = collect(json_decode(json_encode($xml), true));

            if (!isset($collection['body']['outline'])) {
                throw new \Exception('Invalid OPML format: no feeds found');
            }

            $feeds = $collection['body']['outline'];
            $result = [
                'mode' => $mode,
                'categories_created' => 0,
                'sources_created' => 0,
                'sources_skipped' => 0,
                'errors' => [],
            ];

            // Handle replace mode
            if ($mode === 'replace') {
                DB::table('sources')->truncate();
                DB::table('categories')->truncate();
                DB::table('posts')->truncate();
                Log::info('OPML Import: Cleared existing data for replace mode');
            }

            foreach ($feeds as $group) {
                try {
                    if (isset($group['outline'])) {
                        // Process category with sources
                        $this->processCategoryGroup($group, $result, $mode);
                    } else {
                        // Process uncategorized source
                        $this->processUncategorizedSource($group, $result, $mode);
                    }
                } catch (\Exception $e) {
                    $result['errors'][] = 'Error processing group: ' . $e->getMessage();
                    Log::error('OPML Import error', ['error' => $e->getMessage(), 'group' => $group]);
                }
            }

            DB::commit();
            Log::info('OPML Import completed successfully', $result);

            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('OPML Import failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Validate OPML file
     */
    private function validateOpmlFile(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new \Exception('OPML file not found');
        }

        if (!is_readable($filePath)) {
            throw new \Exception('OPML file is not readable');
        }

        // Basic XML validation
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($filePath);

        if ($xml === false) {
            $errors = libxml_get_errors();
            $errorMessage = 'Invalid XML: ';
            foreach ($errors as $error) {
                $errorMessage .= $error->message . ' ';
            }
            throw new \Exception(trim($errorMessage));
        }

        // Check if it's actually OPML
        if ($xml->getName() !== 'opml') {
            throw new \Exception('File is not a valid OPML document');
        }
    }

    /**
     * Process a category group with sources
     */
    private function processCategoryGroup(array $group, array &$result, string $mode): void
    {
        $categoryTitle = $group['@attributes']['title'] ?? 'Unnamed Category';

        // Find or create category
        if ($mode === 'merge') {
            $category = Category::firstOrCreate(
                ['description' => $categoryTitle],
                ['description' => $categoryTitle]
            );
            if ($category->wasRecentlyCreated) {
                $result['categories_created']++;
            }
        } else {
            $category = Category::create(['description' => $categoryTitle]);
            $result['categories_created']++;
        }

        // Process sources in this category
        foreach ($group['outline'] as $source) {
            $this->processSource($source, $category->id, $result, $mode);
        }
    }

    /**
     * Process an uncategorized source
     */
    private function processUncategorizedSource(array $source, array &$result, string $mode): void
    {
        // Create or find "Uncategorized" category
        if ($mode === 'merge') {
            $category = Category::firstOrCreate(
                ['description' => 'Uncategorized'],
                ['description' => 'Uncategorized']
            );
            if ($category->wasRecentlyCreated) {
                $result['categories_created']++;
            }
        } else {
            $category = Category::firstOrCreate(['description' => 'Uncategorized']);
            if ($category->wasRecentlyCreated) {
                $result['categories_created']++;
            }
        }

        $this->processSource($source, $category->id, $result, $mode);
    }

    /**
     * Process a single source
     */
    private function processSource(array $source, int $categoryId, array &$result, string $mode): void
    {
        $sourceDetails = $source['@attributes'] ?? $source;

        $sourceData = [
            'name' => $sourceDetails['text'] ?? 'Unknown Feed',
            'description' => $sourceDetails['title'] ?? $sourceDetails['text'] ?? 'Unknown Feed',
            'url' => $sourceDetails['htmlUrl'] ?? '',
            'author' => '',
            'fetcher_kind' => 'rss',
            'fetcher_source' => $sourceDetails['xmlUrl'] ?? '',
            'active' => 1,
            'why_deactivated' => null,
            'category_id' => $categoryId,
        ];

        // Validate required fields
        if (empty($sourceData['fetcher_source'])) {
            $result['errors'][] = "Skipping source '{$sourceData['name']}': no RSS URL provided";
            $result['sources_skipped']++;
            return;
        }

        try {
            if ($mode === 'merge') {
                // Check if source already exists
                $existingSource = Source::where('fetcher_source', $sourceData['fetcher_source'])->first();
                if ($existingSource) {
                    $result['sources_skipped']++;
                    return;
                }
            }

            Source::create($sourceData);
            $result['sources_created']++;

        } catch (\Exception $e) {
            $result['errors'][] = "Failed to create source '{$sourceData['name']}': " . $e->getMessage();
            $result['sources_skipped']++;
        }
    }
}
