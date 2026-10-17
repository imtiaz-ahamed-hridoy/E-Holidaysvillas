<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $primaryKey = 'blog_id';
    protected $fillable = [
        'title',
        'slug',
        'content',
        'images',
        'approx_read_time',
        'status',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'blog_category', 'blog_id', 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'blog_tag', 'blog_id', 'tag_id');
    }

    // Accessor for absolute image URLs
    public function getImagesAttribute($value)
    {
        $images = is_string($value) ? json_decode($value, true) : $value;
        if (is_array($images)) {
            return array_map(function ($image) {
                return \App\Helpers\Helper::imageUrl($image);
            }, $images);
        }
        return $images ?? [];
    }
}
