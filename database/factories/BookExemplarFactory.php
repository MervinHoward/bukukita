<?php

namespace Database\Factories;

use App\Enums\BookStatusEnum;
use App\Models\BookExemplar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookExemplar>
 */
class BookExemplarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_id' => fake()->numberBetween(1, 10),
            'edition' => "Edisi Pertama",
            'status' => BookStatusEnum::Available->value
        ];
    }
}
