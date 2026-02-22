<?php

namespace Modules\Inventory\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Inventory\Models\Category;
use Modules\Inventory\Http\Resources\CategoryResource;

/**
 * CategoryController - API for Product Categories
 */
class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * List categories (flat or tree)
     */
    public function index(Request $request): JsonResponse
    {
        $format = $request->get('format', 'flat'); // flat or tree

        if ($format === 'tree') {
            $categories = Category::with('children.children')
                ->whereNull('parent_id')
                ->active()
                ->orderBy('sort_order')
                ->get();
        } else {
            $categories = Category::with('parent')
                ->when($request->boolean('active_only', true), fn($q) => $q->active())
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Create a new category
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'sort_order' => 'integer',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category),
        ], 201);
    }

    /**
     * Get category details
     */
    public function show(Category $category): JsonResponse
    {
        $category->load(['parent', 'children']);

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Update a category
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => "nullable|string|max:255|unique:categories,slug,{$category->id}",
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        // Prevent setting self as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $category->id) {
            return response()->json([
                'success' => false,
                'message' => 'Category cannot be its own parent',
            ], 422);
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Delete a category
     */
    public function destroy(Category $category): JsonResponse
    {
        // Check for products
        if ($category->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with products. Move products first.',
            ], 422);
        }

        // Check for children
        if ($category->children()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with subcategories.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
