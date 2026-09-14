<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin_it@bfcgroup.org'],
            [
                'name' => 'Iverson Craig',
                'password' => env('SEED_USER_PASSWORD', 'password'),
            ],
        );

        $this->call(CategorySeeder::class);
        $this->call(QuestionSeeder::class);
        $this->call(TipSeeder::class);
        $this->call(LabIdpTrackerSeeder::class);
    }
}
