<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\category;
use App\Models\post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (User::count() === 0) {
            User::factory()->count(25)->create();
        }

        if (category::count() === 0) {
            $this->call(CategorySeeder::class);
        }

        post::factory()
            ->count(60)
            ->create();
    }
}
