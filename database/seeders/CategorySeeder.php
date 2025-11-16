<?php

namespace Database\Seeders;

use App\Models\category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Canonical set of categories available inside the app.
     *
     * @var array<int, string>
     */
    protected array $defaults = [
        'Personal Development',
        'Technology',
        'Trends',
        'Travel',
        'Entertainment',
        'Movies',
        'Education',
        'Learning',
        'Psychology',
        'Life',
        'Startups',
        'Business',
        'Sports',
        'Health',
        'Society',
        'Culture',
        'Book Reviews',
        'Critical Thinking',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->defaults as $content) {
            category::firstOrCreate(['content' => $content]);
        }
    }
}
