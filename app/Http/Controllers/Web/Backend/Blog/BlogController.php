<?php

namespace App\Http\Controllers\Web\Backend\Blog;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Tag;
use App\Helpers\Helper;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BlogController extends Controller
{
    /**
     * Display a property of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Blog::latest()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($row) {
                    $images = $row->getRawOriginal('images');
                    $images = is_string($images) ? json_decode($images, true) : $images;
                    $url = (!empty($images) && isset($images[0])) ? url($images[0]) : asset('frontend/no-image.jpg');
                    return '<img src="' . $url . '" alt="Image" class="img-fluid" style="height: 50px; width: 50px;">';
                })
                ->addColumn('status', function ($row) {
                    $badge = $row->status == 'active' ? 'bg-success' : 'bg-danger';
                    return '<span class="badge ' . $badge . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="btn-list">
                        <a href="' . route('blog.edit', $row->blog_id) . '" class="btn btn-sm btn-primary">
                            <i class="fe fe-edit"></i>
                        </a>
                        <button type="button" onclick="deletePost(' . $row->blog_id . ')" class="btn btn-sm btn-danger">
                            <i class="fe fe-trash"></i>
                        </button>
                    </div>';
                })
                ->rawColumns(['image', 'status', 'action'])
                ->make(true);
        }
        return view('backend.layouts.blog.post.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('status', 'active')->get();
        $tags = Tag::all();
        return view('backend.layouts.blog.post.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:blogs,title',
            'content' => 'required|string',
            'approx_read_time' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'images.*' => 'nullable|file',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,category_id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,tag_id',
        ]);

        $blog = new Blog();
        $blog->setAttribute('title', $request->title);
        $blog->setAttribute('slug', Str::slug($request->title));
        $blog->setAttribute('content', $request->input('content'));
        $blog->setAttribute('approx_read_time', $request->approx_read_time);
        $blog->setAttribute('status', $request->status);

        $images = [];
        if ($request->hasFile('images')) {
            $baseURL = url('/');
            foreach ($request->file('images') as $index => $file) {
                $imageURL = Helper::fileUpload($file, 'blog', 'blog_' . time() . '_' . $index);
                $images[] = ltrim(str_replace($baseURL, '', $imageURL), '/');
            }
        }
        $blog->setAttribute('images', $images);
        $blog->save();

        $blog->categories()->sync($request->categories);
        $blog->tags()->sync($request->tags ?? []);

        return redirect()->route('blog.index')->with('success', 'Blog post created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $post = Blog::findOrFail($id);
        $categories = Category::where('status', 'active')->get();
        $tags = Tag::all();
        $selectedCategories = $post->categories->pluck('category_id')->toArray();
        $selectedTags = $post->tags->pluck('tag_id')->toArray();

        return view('backend.layouts.blog.post.edit', compact('post', 'categories', 'tags', 'selectedCategories', 'selectedTags'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            // specify primary key column (blog_id) to avoid default `id` usage
            'title' => 'required|string|max:255|unique:blogs,title,' . $id . ',blog_id',
            'content' => 'required|string',
            'approx_read_time' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'images.*' => 'nullable|file',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,category_id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,tag_id',
        ]);

        $blog = Blog::findOrFail($id);
        $blog->setAttribute('title', $request->title);
        $blog->setAttribute('slug', Str::slug($request->title));
        $blog->setAttribute('content', $request->input('content'));
        $blog->setAttribute('approx_read_time', $request->approx_read_time);
        $blog->setAttribute('status', $request->status);

        $images = $blog->getRawOriginal('images');
        $images = is_string($images) ? json_decode($images, true) : ($images ?? []);

        if ($request->hasFile('images')) {
            $baseURL = url('/');
            // Delete old images
            foreach ($images as $img) {
                Helper::fileDelete($img);
            }

            $newImages = [];
            foreach ($request->file('images') as $index => $file) {
                $imageURL = Helper::fileUpload($file, 'blog', 'blog_' . time() . '_' . $index);
                $newImages[] = ltrim(str_replace($baseURL, '', $imageURL), '/');
            }
            $blog->setAttribute('images', $newImages);
        }

        $blog->save();

        $blog->categories()->sync($request->categories);
        $blog->tags()->sync($request->tags ?? []);

        return redirect()->route('blog.index')->with('success', 'Blog post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $blog = Blog::findOrFail($id);
        $images = $blog->getRawOriginal('images');
        $images = is_string($images) ? json_decode($images, true) : ($images ?? []);

        foreach ($images as $img) {
            Helper::fileDelete($img);
        }

        $blog->categories()->detach();
        $blog->tags()->detach();
        $blog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blog post deleted successfully.'
        ]);
    }
}
