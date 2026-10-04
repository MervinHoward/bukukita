<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\Shelf;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Override;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $loaner;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => RoleEnum::ADMIN]);
        $this->staff = User::factory()->create(['role' => RoleEnum::STAFF]);
        $this->loaner = User::factory()->create(['role' => RoleEnum::LOANER]);
    }

    private function admin()
    {
        return $this->actingAs($this->admin);
    }

    private function staff()
    {
        return $this->actingAs($this->staff);
    }

    private function loaner()
    {
        return $this->actingAs($this->loaner);
    }

    public function test_loaner_can_get_all_books()
    {
        Book::factory(3)->create();

        $this->loaner()
            ->getJson('/api/books')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_get_all_books()
    {
        Book::factory(3)->create();

        $this->admin()
            ->getJson('/api/books')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_staff_can_get_all_books()
    {
        Book::factory(3)->create();

        $this->staff()
            ->getJson('/api/books')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_authenticated_user_can_get_single_book_with_all_relations()
    {
        $author    = Author::factory()->create(['first_name' => 'Pramoedya']);
        $publisher = Publisher::factory()->create(['name' => 'Lentera Dipantara']);
        $shelf     = Shelf::factory()->create(['code' => 'RAK-A1']);

        $categories = Category::factory(4)->create();

        $book = Book::factory()->create([
            'title'        => 'Bumi Manusia',
            'author_id'    => $author->id,
            'publisher_id' => $publisher->id,
            'shelf_id'     => $shelf->id,
            'isbn'         => '978-979-97312-3-4',
        ]);

        $book->categories()->attach($categories->pluck('id'));
        $this->loaner()
            ->getJson("/api/books/{$book->id}?include=author,publisher,shelf,categories")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.title', 'Bumi Manusia')
            ->assertJsonPath('data.attributes.isbn', '978-979-97312-3-4')
            ->assertJsonPath('data.relationships.author.data.id', (string) $author->id)
            ->assertJsonPath('data.relationships.publisher.data.id', (string) $publisher->id)
            ->assertJsonPath('data.relationships.shelf.data.id', (string) $shelf->id)
            ->assertJsonCount(4, 'data.relationships.categories.data');
    }

    public function test_authenticated_user_can_search_book_by_title_or_isbn()
    {
        $targetBook = Book::factory()->create([
            'title' => 'Laskar Pelangi',
            'isbn' => '9786022916635'
        ]);

        Book::factory(3)->state(new Sequence(
            ['title' => 'Algoritma dan Pemrograman 1'],
            ['title' => 'Algoritma dan Pemrograman 2'],
            ['title' => 'Algoritma dan Pemrograman 3']
        ))->create();

        $this->loaner()->getJson("/api/books?search={$targetBook->title}")
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $this->admin()->getJson("/api/books?search=9786022916635")
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $this->admin()->getJson("/api/books?search=978-602-291-663-5")
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $this->staff()->getJson("/api/books?search=Algoritma%20dan%20Pemrograman")
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');

        $this->staff()->getJson("/api/books?search=Algoritma%20dan%20Pemrograman%203")
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_loaner_fail_create_book_with_valid_data()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        Category::factory()->create();

        $createPayload = [
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'description' => '	Dasar-Dasar Deep Learning dan Implementasinya',
            'isbn' => '9786237498773',
            'physical_description' => '16 x 23 cm ; xiv + 152 hlm',
            'categories' => [1]
        ];
        $this->loaner()->postJson('/api/books', $createPayload)->assertStatus(403);
    }

    public function test_admin_can_create_book_with_valid_data()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        $categories = Category::factory()->createMany([['name' => 'Machine Learning'], ['name' => 'Non-Fiksi']]);

        $createPayload = [
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'description' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'isbn' => '9786237498773',
            'physical_description' => '16 x 23 cm ; xiv + 152 hlm',
            'categories' => $categories->pluck('id')->toArray()
        ];
        $this->admin()->postJson('/api/books', $createPayload)
            ->assertStatus(201);
        $this->assertDatabaseHas('books', [
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'description' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'isbn' => '9786237498773',
            'physical_description' => '16 x 23 cm ; xiv + 152 hlm'
        ]);
    }
    public function test_staff_can_create_book_with_valid_data()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        Category::factory()->createMany([['name' => 'Machine Learning'], ['name' => 'Non-Fiksi']]);

        $createPayload = [
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'description' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'isbn' => '9786237498773',
            'physical_description' => '16 x 23 cm ; xiv + 152 hlm',
            'categories' => [1, 2]
        ];
        $this->staff()->postJson('/api/books', $createPayload)
            ->assertStatus(201);
        $this->assertDatabaseHas('books', [
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'description' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'isbn' => '9786237498773',
            'physical_description' => '16 x 23 cm ; xiv + 152 hlm'
        ]);
    }

    public function test_previleged_users_fail_create_book_if_required_fields_are_missing()
    {
        $this->admin()->postJson('/api/books', [])->assertStatus(422);
    }

    public function test_previleged_user_can_create_book_without_optional_fields()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        Category::factory()->createMany([['name' => 'Machine Learning'], ['name' => 'Non-Fiksi']]);

        $createPayload = [
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'isbn' => '9786237498773',
            'categories' => [1, 2]
        ];

        $this->admin()->postJson('/api/books', $createPayload)
            ->assertStatus(201);
        $this->assertDatabaseHas('books', [
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'isbn' => '9786237498773'
        ]);
    }

    public function test_previleged_user_can_create_book_if_title_is_existed()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        $categories = Category::factory()->createMany([['name' => 'Machine Learning'], ['name' => 'Non-Fiksi']]);

        $book = Book::factory()->create([
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'isbn' => '9786237498773'
        ]);

        $book->categories()->attach($categories->pluck('id')->toArray());

        $createPayload = [
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'isbn' => '9786237498773',
            'categories' => [1]
        ];

        $this->admin()->postJson('/api/books', $createPayload)
            ->assertStatus(201);
        $this->assertDatabaseCount('books', 2);
    }

    public function test_loaner_can_get_single_book()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        $category = Category::factory()->create(['name' => 'Machine Learning']);
        $book = Book::factory()
            ->recycle($author)
            ->recycle($publisher)
            ->recycle($shelf)
            ->recycle($category)
            ->create();
        $this->loaner()
            ->getJson("/api/books/{$book->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', (string) $book->id)
            ->assertJsonPath('data.attributes.title', $book->title);
    }

    public function test_admin_can_get_single_book()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        $category = Category::factory()->create(['name' => 'Machine Learning']);
        $book = Book::factory()
            ->recycle($author)
            ->recycle($publisher)
            ->recycle($shelf)
            ->recycle($category)
            ->create();
        $this->admin()
            ->getJson("/api/books/{$book->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', (string) $book->id)
            ->assertJsonPath('data.attributes.title', $book->title);
    }

    public function test_staff_can_get_single_book()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        $category = Category::factory()->create(['name' => 'Machine Learning']);
        $book = Book::factory()
            ->recycle($author)
            ->recycle($publisher)
            ->recycle($shelf)
            ->recycle($category)
            ->create();
        $this->staff()
            ->getJson("/api/books/{$book->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', (string) $book->id)
            ->assertJsonPath('data.attributes.title', $book->title);
    }

    public function test_book_returns_404_if_not_found()
    {
        $this->loaner()->getJson('/api/books/342312')
            ->assertStatus(404);
    }

    public function test_loaner_fail_update_book()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        Category::factory()->createMany([['name' => 'Machine Learning'], ['name' => 'Non-Fiksi'], ['name' => 'Pendidikan']]);

        $book = Book::factory()->create([
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'isbn' => '9786237498773'
        ]);

        $book->categories()->attach([1, 2]);

        $this->loaner()->putJson("/api/books/{$book->id}", [
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya serta Penerapan',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'isbn' => '9786237498773',
            'categories' => [1, 2, 3]
        ])->assertStatus(403);
    }

    public function test_admin_can_update_book()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        Category::factory()->createMany([['name' => 'Machine Learning'], ['name' => 'Non-Fiksi'], ['name' => 'Pendidikan']]);

        $book = Book::factory()->create([
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'isbn' => '9786237498773'
        ]);

        $book->categories()->attach([1, 2]);

        $this->admin()->putJson("/api/books/{$book->id}", [
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya serta Penerapan',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'isbn' => '9786237498773',
            'categories' => [1, 2, 3]
        ])->assertStatus(200)
            ->assertJsonPath('data.attributes.title', 'Dasar-Dasar Deep Learning dan Implementasinya serta Penerapan');
        $this->assertDatabaseHas('books', [
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya serta Penerapan',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'isbn' => '9786237498773'
        ]);
    }

    public function test_staff_can_update_book()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        Category::factory()->createMany([['name' => 'Machine Learning'], ['name' => 'Non-Fiksi'], ['name' => 'Pendidikan']]);

        $book = Book::factory()->create([
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'isbn' => '9786237498773'
        ]);

        $book->categories()->attach([1, 2]);

        $this->staff()->putJson("/api/books/{$book->id}", [
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya serta Penerapan',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'isbn' => '9786237498773',
            'categories' => [1, 2, 3]
        ])->assertStatus(200)
            ->assertJsonPath('data.attributes.title', 'Dasar-Dasar Deep Learning dan Implementasinya serta Penerapan');
        $this->assertDatabaseHas('books', [
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya serta Penerapan',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'isbn' => '9786237498773'
        ]);
    }

    public function test_previleged_can_update_book_with_its_data()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        Category::factory()->createMany([['name' => 'Machine Learning'], ['name' => 'Non-Fiksi'], ['name' => 'Pendidikan']]);

        $book = Book::factory()->create([
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'isbn' => '9786237498773'
        ]);

        $book->categories()->attach([1, 2]);

        $this->staff()->putJson("/api/books/{$book->id}", [
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'isbn' => '9786237498773',
            'categories' => [1, 2, 3]
        ])->assertStatus(200);
    }

    public function test_previleged_user_fail_update_book_if_not_found()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        Category::factory()->createMany([['name' => 'Machine Learning'], ['name' => 'Non-Fiksi'], ['name' => 'Pendidikan']]);

        $this->staff()->putJson('/api/books/31414', [
            'title' => 'Dasar-Dasar Deep Learning dan Implementasinya serta Penerapan',
            'author_id' => $author->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2021,
            'shelf_id' => $shelf->id,
            'isbn' => '9786237498773',
            'categories' => [1, 2, 3]
        ])->assertStatus(404);
    }

    public function test_admin_can_create_book_exemplar()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        $categories = Category::factory()->createMany([['name' => 'Machine Learning'], ['name' => 'Non-Fiksi']]);
        $book = Book::factory()
            ->recycle($author)
            ->recycle($publisher)
            ->recycle($shelf)
            ->create([
                'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
                'publication_year' => 2021,
                'description' => 'Dasar-Dasar Deep Learning dan Implementasinya',
                'isbn' => '9786237498773',
                'physical_description' => '16 x 23 cm ; xiv + 152 hlm'
            ]);
        $book->categories()->sync($categories->pluck('id')->toArray());

        $this->admin()->postJson("/api/exemplars/create/{$book->id}", [
            'book_id' => $book->id,
            'edition' => 'edisi pertama',
            'status' => 'available'
        ])->assertStatus(201);
    }

    public function test_staff_can_create_book_exemplar()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        $categories = Category::factory()->createMany([['name' => 'Machine Learning'], ['name' => 'Non-Fiksi']]);
        $book = Book::factory()
            ->recycle($author)
            ->recycle($publisher)
            ->recycle($shelf)
            ->create([
                'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
                'publication_year' => 2021,
                'description' => 'Dasar-Dasar Deep Learning dan Implementasinya',
                'isbn' => '9786237498773',
                'physical_description' => '16 x 23 cm ; xiv + 152 hlm'
            ]);
        $book->categories()->sync($categories->pluck('id')->toArray());

        $this->staff()->postJson("/api/exemplars/create/{$book->id}", [
            'book_id' => $book->id,
            'edition' => 'edisi pertama',
            'status' => 'available'
        ])->assertStatus(201);
    }

    public function test_loaner_can_create_book_exemplar()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        $categories = Category::factory()->createMany([['name' => 'Machine Learning'], ['name' => 'Non-Fiksi']]);
        $book = Book::factory()
            ->recycle($author)
            ->recycle($publisher)
            ->recycle($shelf)
            ->create([
                'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
                'publication_year' => 2021,
                'description' => 'Dasar-Dasar Deep Learning dan Implementasinya',
                'isbn' => '9786237498773',
                'physical_description' => '16 x 23 cm ; xiv + 152 hlm'
            ]);
        $book->categories()->sync($categories->pluck('id')->toArray());

        $this->loaner()->postJson("/api/exemplars/create/{$book->id}", [
            'book_id' => $book->id,
            'edition' => 'edisi pertama',
            'status' => 'available'
        ])->assertStatus(403);
    }

    public function test_previlege_user_can_update_book_exemplar_status()
    {
        $author = Author::factory()->create(['first_name' => 'Yaya', 'last_name' => 'Haryadi']);
        $publisher = Publisher::factory()->create(['name' => 'Gava Media']);
        $shelf = Shelf::factory()->create();
        $categories = Category::factory()->createMany([['name' => 'Machine Learning'], ['name' => 'Non-Fiksi']]);
        $book = Book::factory()
            ->recycle($author)
            ->recycle($publisher)
            ->recycle($shelf)
            ->create([
                'title' => 'Dasar-Dasar Deep Learning dan Implementasinya',
                'publication_year' => 2021,
                'description' => 'Dasar-Dasar Deep Learning dan Implementasinya',
                'isbn' => '9786237498773',
                'physical_description' => '16 x 23 cm ; xiv + 152 hlm'
            ]);
        $book->categories()->sync($categories->pluck('id')->toArray());
        $exemplar = $book->exemplars()->create([
            'edition' => 'edisi pertama',
            'status' => 'available'
        ]);

        $this->admin()->putJson("/api/exemplars/change/{$exemplar->id}", [
            'status' => 'on_loan'
        ])->assertStatus(200);
        $this->assertDatabaseHas('book_exemplars', [
            'id'     => $exemplar->id,
            'status' => 'on_loan',
        ]);
        $this->admin()->putJson("/api/exemplars/change/{$exemplar->id}", [
            'status' => 'disappear'
        ])->assertStatus(200);
        $this->assertDatabaseHas('book_exemplars', [
            'id'     => $exemplar->id,
            'status' => 'disappear',
        ]);
    }
}
