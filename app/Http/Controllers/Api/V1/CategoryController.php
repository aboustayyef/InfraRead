<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreateCategoryRequest;
use App\Http\Requests\Api\V1\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('sources')
            ->orderBy('description')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function show(Category $category)
    {
        $category->load('sources');
        return new CategoryResource($category);
    }

    public function store(CreateCategoryRequest $request): JsonResponse
    {
        $category = Category::create([
            'description' => $request->validated()['description']
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category)
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update([
            'description' => $request->validated()['description']
        ]);

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category)
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Check if category has sources
            $sourceCount = $category->sources()->count();

            if ($sourceCount > 0) {
                // Find or create "Uncategorized" category
                $uncategorizedCategory = Category::firstOrCreate([
                    'description' => 'Uncategorized'
                ]);

                // Move all sources to uncategorized
                $category->sources()->update([
                    'category_id' => $uncategorizedCategory->id
                ]);
            }

            $category->delete();

            DB::commit();

            return response()->json([
                'message' => 'Category deleted successfully',
                'data' => [
                    'sources_moved' => $sourceCount,
                    'moved_to_category' => $sourceCount > 0 ? 'Uncategorized' : null
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete category',
                'error' => 'An error occurred while deleting the category'
            ], 500);
        }
    }
}
