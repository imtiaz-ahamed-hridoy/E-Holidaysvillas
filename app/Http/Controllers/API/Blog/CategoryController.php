<?php

namespace App\Http\Controllers\API\Blog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * Get all active categories
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories = Category::where('status', 'active')->latest()->paginate(20);
        return $this->success('Categories retrieved successfully.', $categories);
    }

    /**
     * Get blogs by category ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $category = Category::where('category_id', $id)->where('status', 'active')->first();

        if (!$category) {
            return $this->error('Category not found.', 404);
        }

        return $this->success('Category retrieved successfully.', $category);
    }
}
