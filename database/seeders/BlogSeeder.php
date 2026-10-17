<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Categories
        $categories = [
            ['name' => 'Luxury Living', 'status' => 'active'],
            ['name' => 'Travel Tips', 'status' => 'active'],
            ['name' => 'Real Estate Trends', 'status' => 'active'],
            ['name' => 'Interior Design', 'status' => 'active'],
            ['name' => 'Destination Spotlights', 'status' => 'active'],
        ];

        $categoryIds = [];
        foreach ($categories as $cat) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'status' => $cat['status'],
                ]
            );
            $categoryIds[] = $category->category_id;
        }

        // 2. Create Tags
        $tags = [
            ['name' => 'Villas', 'status' => 'active'],
            ['name' => 'Modern', 'status' => 'active'],
            ['name' => 'Vacation', 'status' => 'active'],
            ['name' => 'Architecture', 'status' => 'active'],
            ['name' => 'Lifestyle', 'status' => 'active'],
            ['name' => 'Investment', 'status' => 'active'],
        ];

        $tagIds = [];
        foreach ($tags as $t) {
            $tag = Tag::updateOrCreate(
                ['slug' => Str::slug($t['name'])],
                [
                    'name' => $t['name'],
                    'status' => $t['status'],
                ]
            );
            $tagIds[] = $tag->tag_id;
        }

        // 3. Create Blogs
        $blogs = [
            [
                'title' => 'Top 10 Luxury Villas for Your Summer Vacation',
                'content' => '<p>Explore our curated list of the most stunning luxury villas across the globe. From infinity pools to private chefs, discover what makes these properties unique.</p><h2>1. The Ocean View Retreat</h2><p>Located in the heart of the Mediterranean, this villa offers unparalleled views of the turquoise sea.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1200'
                ],
                'approx_read_time' => '5 min',
                'status' => 'active',
            ],
            [
                'title' => 'The Future of Sustainable Architecture in 2026',
                'content' => '<p>Sustainable design is no longer just a trend; it is a necessity. Learn about the latest innovations in green building materials and energy-efficient homes.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1518005020251-58296d8f8d4c?auto=format&fit=crop&q=80&w=1200'
                ],
                'approx_read_time' => '7 min',
                'status' => 'active',
            ],
            [
                'title' => 'Investing in Vacation Rentals: What You Need to Know',
                'content' => '<p>Is a vacation home a good investment? We break down the ROI, management costs, and the best locations to buy in today\'s market.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&q=80&w=1200'
                ],
                'approx_read_time' => '10 min',
                'status' => 'active',
            ],
            [
                'title' => 'How to Design a Minimalist Living Space',
                'content' => '<p>Minimalism is about more than just aesthetics; it\'s a lifestyle. Discover practical tips for decluttering and creating a serene home environment.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1484154218962-a197022b5858?auto=format&fit=crop&q=80&w=1200'
                ],
                'approx_read_time' => '4 min',
                'status' => 'active',
            ],
            [
                'title' => 'Hidden Gems: Exotic Destinations You Must Visit',
                'content' => '<p>Beyond the typical tourist spots lie hidden treasures waiting to be explored. Join us as we travel to off-the-beaten-path locations.</p>',
                'images' => [
                    'https://images.unsplash.com/photo-1506929113675-b55f9d99569d?auto=format&fit=crop&q=80&w=1200'
                ],
                'approx_read_time' => '8 min',
                'status' => 'active',
            ],
        ];

        foreach ($blogs as $blogData) {
            $blog = Blog::updateOrCreate(
                ['slug' => Str::slug($blogData['title'])],
                [
                    'title' => $blogData['title'],
                    'content' => $blogData['content'],
                    'images' => $blogData['images'],
                    'approx_read_time' => $blogData['approx_read_time'],
                    'status' => $blogData['status'],
                ]
            );

            // Sync random categories and tags
            $randomCategories = collect($categoryIds)->random(rand(1, 2))->toArray();
            $randomTags = collect($tagIds)->random(rand(2, 4))->toArray();

            $blog->categories()->sync($randomCategories);
            $blog->tags()->sync($randomTags);
        }
    }
}
