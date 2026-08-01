<?php

namespace Database\Seeders;

use App\Tools\Quiz\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Laravel', 'slug' => 'laravel', 'color' => '#2e9cca'],
            ['name' => 'Vue / Blade', 'slug' => 'vue-blade', 'color' => '#9b5fcf'],
            ['name' => 'Git', 'slug' => 'git', 'color' => '#C3073F'],
            ['name' => 'SQL', 'slug' => 'sql', 'color' => '#666666'],
            ['name' => 'JS Fundamentals', 'slug' => 'js-fundamentals', 'color' => '#2e9cca'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
