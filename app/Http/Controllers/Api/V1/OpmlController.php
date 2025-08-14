<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ImportOpmlRequest;
use App\Models\Category;
use App\Models\Source;
use App\Utilities\OpmlImporter;
use App\Utilities\OpmlExporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OpmlController extends Controller
{
    /**
     * Export all sources as OPML file.
     *
     * This provides programmatic access to OPML export functionality
     * while maintaining the existing web route for direct downloads.
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $exporter = new OpmlExporter();
            $opmlContent = $exporter->generate();

            return response()->json([
                'message' => 'OPML exported successfully',
                'data' => [
                    'content' => $opmlContent,
                    'filename' => 'infraread-feeds-' . now()->format('Y-m-d') . '.opml',
                    'sources_count' => Source::count(),
                    'categories_count' => Category::count(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to export OPML',
                'error' => 'Could not generate OPML file'
            ], 500);
        }
    }

    /**
     * Preview OPML file before import.
     *
     * This allows users to see what will be imported without
     * actually performing the destructive import operation.
     */
    public function preview(ImportOpmlRequest $request): JsonResponse
    {
        try {
            $file = $request->file('opml');
            $tempPath = $file->store('temp');

            $importer = new OpmlImporter();
            $preview = $importer->preview(Storage::path($tempPath));

            // Clean up temp file
            Storage::delete($tempPath);

            return response()->json([
                'message' => 'OPML preview generated successfully',
                'data' => $preview
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to preview OPML',
                'error' => 'Could not parse OPML file: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Import sources from OPML file.
     *
     * Enhanced version with better error handling, validation,
     * and optional merge vs replace behavior.
     */
    public function import(ImportOpmlRequest $request): JsonResponse
    {
        try {
            $file = $request->file('opml');
            $mode = $request->get('mode', 'replace'); // 'replace' or 'merge'

            $tempPath = $file->store('temp');

            $importer = new OpmlImporter();
            $result = $importer->import(Storage::path($tempPath), $mode);

            // Clean up temp file
            Storage::delete($tempPath);

            return response()->json([
                'message' => 'OPML imported successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to import OPML',
                'error' => 'Import failed: ' . $e->getMessage()
            ], 422);
        }
    }
}
