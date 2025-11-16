<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $blocks = [
            [
                'id' => fake()->uuid(),
                'type' => 'header',
                'data' => [
                    'text' => fake()->sentence(6),
                    'level' => fake()->numberBetween(2, 4),
                ],
            ],
            [
                'id' => fake()->uuid(),
                'type' => 'paragraph',
                'data' => [
                    'text' => implode(' ', fake()->paragraphs(rand(2, 4))),
                ],
            ],
            [
                'id' => fake()->uuid(),
                'type' => 'list',
                'data' => [
                    'style' => 'unordered',
                    'items' => fake()->sentences(rand(2, 4)),
                ],
            ],
        ];

        return [
            'title' => fake()->unique()->sentence(8, true),
            'authorId' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'content' => json_encode([
                'time' => now()->getTimestampMs(),
                'blocks' => $blocks,
                'version' => '2.29.0',
            ]),
            'additionFile' => null,
            'groupId' => null,
            'status' => fake()->randomElement(['public', 'private']),
            'categoryId' => category::query()->inRandomOrder()->value('id') ?? category::factory(),
        ];
    }
}
