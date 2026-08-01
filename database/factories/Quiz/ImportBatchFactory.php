<?php

namespace Database\Factories\Quiz;

use App\Tools\Quiz\Enums\ImportStatus;
use App\Tools\Quiz\Models\Category;
use App\Tools\Quiz\Models\ImportBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportBatch>
 */
class ImportBatchFactory extends Factory
{
    /** @var class-string<ImportBatch> */
    protected $model = ImportBatch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_type' => 'log',
            'category_id' => Category::factory(),
            'raw_content' => $this->faker->paragraph(),
            'status' => ImportStatus::Uploaded,
            'error_message' => null,
        ];
    }
}
