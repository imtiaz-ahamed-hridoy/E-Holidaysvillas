<?php

namespace App\Http\Controllers\API\Blog;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Traits\ApiResponse;

class BlogController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $blogs = Blog::with(['categories', 'tags'])->where('status', 'active')->latest()->paginate(10);
        return $this->success('Blogs retrieved successfully.', $blogs);
    }

    public function show($id)
    {
        $blog = Blog::with(['categories', 'tags'])->where('blog_id', $id)->first();
        if (!$blog) {
            return $this->error('Blog not found.', 404);
        }
        return $this->success('Blog retrieved successfully.', $blog);
    }
}
