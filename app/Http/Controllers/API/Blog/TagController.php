<?php

namespace App\Http\Controllers\API\Blog;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    use ApiResponse;

    /**
     * Get all tags
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $tags = Tag::latest()->paginate(30);
        return $this->success('Tags retrieved successfully.', $tags);
    }

    /**
     * Get blogs by tag ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $tag = Tag::where('tag_id', $id)->first();

        if (!$tag) {
            return $this->error('Tag not found.', 404);
        }

        return $this->success('Tag retrieved successfully.', $tag);
    }
}
