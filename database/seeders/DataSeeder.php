<?php

namespace Database\Seeders;

use App\Enums\BookStatusEnum;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\Shelf;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = Author::factory(5)->create();
        $publishers = Publisher::factory(5)->create();
        $shelves = Shelf::factory(3)->create();
        $categories = Category::factory(10)->create();

        $books = Book::factory(10)
            ->recycle($authors)
            ->recycle($publishers)
            ->recycle($shelves)
            ->afterCreating(function (Book $book) use ($categories) {
                $book->categories()->attach(
                    $categories->random(rand(1, $categories->count()))->pluck('id')
                );
            })
            ->create();

        foreach ($books as $b) {
            $i = 0;
            $n_exemplars = rand(1, 6);
            do {
                $b->exemplars()->create([
                    'edition' => fake()->randomElement(['edisi pertama', 'edisi kedua', 'edisi ketiga']),
                    'status' => fake()->randomElement([BookStatusEnum::Available, BookStatusEnum::Requesting, BookStatusEnum::Disappear, BookStatusEnum::OnLoan])
                ]);
                $i++;
            } while ($i <= $n_exemplars);
        }
    }
}
