<?php

namespace Tests\Feature;

use App\Livewire\Import\ImportPage;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ImportPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_submitting_a_session_log_creates_a_batch_and_imports_questions(): void
    {
        $category = Category::factory()->create();

        Livewire::test(ImportPage::class)
            ->set('tab', 'session')
            ->set('categoryId', $category->id)
            ->set('sessionLogText', "Middleware runs before the controller\nRoutes are registered in order")
            ->call('generate');

        $this->assertDatabaseHas('import_batches', ['source_type' => 'log', 'status' => 'imported']);
        $this->assertDatabaseHas('questions', ['category_id' => $category->id, 'answer' => 'controller']);
        $this->assertDatabaseHas('questions', ['category_id' => $category->id, 'answer' => 'order']);
    }

    public function test_submitting_an_uploaded_note_file_creates_a_batch_and_imports_questions(): void
    {
        Storage::fake('local');
        $category = Category::factory()->create();

        Livewire::test(ImportPage::class)
            ->set('tab', 'notes')
            ->set('categoryId', $category->id)
            ->set('noteFile', UploadedFile::fake()->createWithContent('note.md', "A note about facades\n"))
            ->call('generate');

        $this->assertDatabaseHas('import_batches', ['source_type' => 'note', 'status' => 'imported']);
        $this->assertDatabaseHas('questions', ['category_id' => $category->id, 'answer' => 'facades']);
    }

    public function test_generating_without_a_category_fails_validation(): void
    {
        Category::query()->delete();

        Livewire::test(ImportPage::class)
            ->set('tab', 'session')
            ->set('sessionLogText', 'something happened')
            ->call('generate')
            ->assertHasErrors(['categoryId']);
    }

    public function test_generating_a_note_without_a_file_fails_validation(): void
    {
        $category = Category::factory()->create();

        Livewire::test(ImportPage::class)
            ->set('tab', 'notes')
            ->set('categoryId', $category->id)
            ->call('generate')
            ->assertHasErrors(['noteFile']);
    }
}
