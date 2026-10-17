<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key checks to prevent issues with deletions
        Schema::disableForeignKeyConstraints();

        // Clear existing data
        DB::table('users')->truncate();
        DB::table('system_settings')->truncate();
        DB::table('mail_settings')->truncate();
        DB::table('dynamic_pages')->truncate();
        DB::table('blogs')->truncate();
        DB::table('categories')->truncate();
        DB::table('tags')->truncate();
        DB::table('blog_category')->truncate();
        DB::table('blog_tag')->truncate();
        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();

        // Call seeders
        $this->call([
            UserSeeder::class,
            SystemSettingSeeder::class,
            MailSettingSeeder::class,
            DynamicPageSeeder::class,
            BlogSeeder::class,
        ]);
    }
}
