<?php

namespace Database\Factories;

use App\Enums\ImportStatus;
use App\Models\Category;
use App\Models\ImportBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportBatch>
 */
class ImportBatchFactory extends Factory
{
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
