<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SourceResource;
use App\Models\Source;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    public function index(Request $request)
    {
        $query = Source::query();
        $includes = collect(explode(',', (string) $request->query('include')))
            ->map(fn($s) => trim($s))
            ->filter();
        $allowedIncludes = ['category'];
        $relations = $includes->intersect($allowedIncludes)->all();
        if ($relations) {
            $query->with($relations);
        }
        return SourceResource::collection($query->orderBy('name')->get());
    }

    public function show(Request $request, Source $source)
    {
        $includes = collect(explode(',', (string) $request->query('include')))
            ->map(fn($s) => trim($s))
            ->filter();
        $allowedIncludes = ['category'];
        $relations = $includes->intersect($allowedIncludes)->all();
        if ($relations) {
            $source->load($relations);
        }
        return new SourceResource($source);
    }
}
