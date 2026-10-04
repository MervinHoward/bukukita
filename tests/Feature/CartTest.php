<?php

namespace Tests\Feature;

use App\Enums\BookStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookExemplar;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\Shelf;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Override;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;
    private User $loaner1;
    private User $loaner2;
    private $books = [];

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->loaner1 = User::factory()->create(['role' => RoleEnum::LOANER->value]);
        $this->loaner2 = User::factory()->create(['role' => RoleEnum::LOANER->value]);
        $categories = Category::factory(5)->create();
        $publishers = Publisher::factory(10)->create();
        $authors = Author::factory(10)->create();
        $shelves = Shelf::factory(3)->create();
        $this->books = Book::factory(10)->recycle($authors)->recycle($publishers)->recycle($shelves)->create();
        foreach ($this->books as $book) {
            $book->categories()->attach(
                $categories->random(rand(1, $categories->count()))->pluck('id')
            );
            $book->exemplars()->create([
                'edition' => fake()->randomElement(['edisi pertama', 'edisi kedua']),
                'status'  => fake()->randomElement([
                    BookStatusEnum::Available->value,
                    BookStatusEnum::OnLoan->value
                ])
            ]);
        }
    }

    public function test_loaner_can_add_books_to_cart()
    {
        $exemplar = BookExemplar::whereStatus(BookStatusEnum::Available->value)->first();
        $this->actingAs($this->loaner1)->postJson('/api/carts', [
            'book_exemplar_id' => $exemplar->id
        ])->assertStatus(201);
        $this->assertDatabaseHas('carts', [
            'loaner_id' => $this->loaner1->id
        ]);
        $this->assertDatabaseHas('cart_details', [
            'book_exemplar_id' => $exemplar->id
        ]);
    }

    public function test_loaner_can_view_their_own_cart()
    {
        $exemplar = BookExemplar::whereStatus(BookStatusEnum::Available)->first();
        $this->actingAs($this->loaner1)->postJson('/api/carts', [
            'book_exemplar_id' => $exemplar->id
        ])->assertStatus(201);
        $this->actingAs($this->loaner1)->getJson('/api/carts')->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->where('data.type', 'carts')->where('data.attributes.loaner_id', $this->loaner1->id)->etc());
    }
}
