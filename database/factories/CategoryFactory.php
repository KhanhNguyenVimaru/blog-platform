<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\category>
 */
class CategoryFactory extends Factory
{
    /**
     * Canonical list of default categories to seed.
     *
     * @var array<int, string>
     */
    protected static array $defaults = [
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
     * Track which default value should be used next so factory() calls
     * can iterate through the predefined list deterministically.
     */
    protected static int $defaultIndex = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = self::$defaults[self::$defaultIndex % count(self::$defaults)];
        self::$defaultIndex++;

        return [
            'content' => $name,
        ];
    }
}
