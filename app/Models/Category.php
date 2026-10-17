<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $primaryKey = 'category_id';
    protected $fillable = ['name', 'slug', 'status'];

    public function blogs()
    {
        return $this->belongsToMany(Blog::class, 'blog_category', 'category_id', 'blog_id');
    }
}
