<?php

namespace Tests\Feature\Tools\Quiz;

use App\Tools\Quiz\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_five_mockup_categories(): void
    {
        $this->seed(CategorySeeder::class);

        $this->assertSame(5, Category::count());

        $this->assertDatabaseHas('categories', ['slug' => 'laravel', 'name' => 'Laravel']);
        $this->assertDatabaseHas('categories', ['slug' => 'vue-blade', 'name' => 'Vue / Blade']);
        $this->assertDatabaseHas('categories', ['slug' => 'git', 'name' => 'Git']);
        $this->assertDatabaseHas('categories', ['slug' => 'sql', 'name' => 'SQL']);
        $this->assertDatabaseHas('categories', ['slug' => 'js-fundamentals', 'name' => 'JS Fundamentals']);
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(CategorySeeder::class);
        $this->seed(CategorySeeder::class);

        $this->assertSame(5, Category::count());
    }

    public function test_category_slug_is_unique(): void
    {
        Category::factory()->create(['slug' => 'duplicate']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Category::factory()->create(['slug' => 'duplicate']);
    }
}
