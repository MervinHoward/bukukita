<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use App\Models\Publisher;
use App\Models\Shelf;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'                => fake()->sentence(3),
            'author_id'            => Author::factory(),
            'publisher_id'         => Publisher::factory(),
            'publication_year'     => fake()->year(),
            'shelf_id'             => Shelf::factory(),
            'description'          => fake()->paragraph(),
            'isbn'                 => fake()->unique()->isbn13(),
            'physical_description' => fake()->numberBetween(100, 800) . ' hlm; ' . fake()->numberBetween(15, 25) . ' cm'
        ];
    }
}
