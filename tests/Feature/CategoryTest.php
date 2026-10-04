<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\Shelf;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Override;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $loaner;
    private User $admin;
    private User $staff;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->loaner = User::factory()->create(['role' => RoleEnum::LOANER]);
        $this->staff = User::factory()->create(['role' => RoleEnum::STAFF]);
        $this->admin = User::factory()->create(['role' => RoleEnum::ADMIN]);
    }

    public function test_loaner_can_get_all_categories()
    {
        Category::factory(3)->create();
        $this->actingAs($this->loaner)->getJson('/api/categories')->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_get_all_categories()
    {
        Category::factory(3)->create();
        $this->actingAs($this->admin)->getJson('/api/categories')->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_staff_can_get_all_categories()
    {
        Category::factory(3)->create();
        $this->actingAs($this->staff)->getJson('/api/categories')->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_loaner_fail_create_category_with_valid_data()
    {
        $this->actingAs($this->loaner)
            ->postJson('/api/categories', ['name' => 'Kategori'])
            ->assertStatus(403);
    }

    public function test_admin_can_create_category_with_valid_data()
    {
        $this->actingAs($this->admin)
            ->postJson('/api/categories', ['name' => 'Kategori'])
            ->assertStatus(201)
            ->assertJsonPath('data.attributes.name', "Kategori");
    }

    public function test_previleged_user_fail_create_category_if_required_field_is_missing()
    {
        $this->actingAs($this->admin)
            ->postJson('/api/categories')
            ->assertStatus(422);
    }

    public function test_previleged_user_fail_create_category_if_name_is_existed()
    {
        $kategori1 = Category::factory()->create();
        $this->actingAs($this->admin)
            ->postJson('/api/categories', ['name' => $kategori1->name])
            ->assertStatus(422);
    }

    public function test_loaner_can_get_single_category()
    {
        $kategori = Category::factory()->create();
        $this->actingAs($this->loaner)->getJson("/api/categories/{$kategori->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', $kategori->name);
    }

    public function test_admin_can_get_single_category()
    {
        $kategori = Category::factory()->create();
        $this->actingAs($this->admin)->getJson("/api/categories/{$kategori->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', $kategori->name);
    }

    public function test_staff_can_get_single_category()
    {
        $kategori = Category::factory()->create();
        $this->actingAs($this->staff)->getJson("/api/categories/{$kategori->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', $kategori->name);
    }

    public function test_category_returns_404_if_not_found()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/categories/3148341')
            ->assertStatus(404);
    }

    public function test_loaner_fail_update_category()
    {
        $kategori = Category::factory()->create(['name' => 'Kategori']);
        $this->actingAs($this->loaner)
            ->putJson("/api/categories/{$kategori->id}", ['name' => 'Kategori Baru'])
            ->assertStatus(403);
    }

    public function test_admin_can_update_category()
    {
        $kategori = Category::factory()->create(['name' => 'Kategori']);
        $this->actingAs($this->admin)
            ->putJson("/api/categories/{$kategori->id}", ['name' => 'Kategori Baru'])
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'Kategori Baru');
    }

    public function test_staff_can_update_category()
    {
        $kategori = Category::factory()->create(['name' => 'Kategori']);
        $this->actingAs($this->staff)
            ->putJson("/api/categories/{$kategori->id}", ['name' => 'Kategori Baru'])
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'Kategori Baru');
    }

    public function test_previleged_user_fail_update_category_if_name_is_existed()
    {
        $kategori1 = Category::factory()->create(['name' => 'Kategori 1']);
        $kategori2 = Category::factory()->create(['name' => 'Kategori 2']);

        $this->actingAs($this->admin)
            ->putJson("/api/categories/{$kategori2->id}", ['name' => $kategori1->name])
            ->assertStatus(422);
    }

    public function test_previleged_user_fail_update_category_if_not_found()
    {
        $this->actingAs($this->admin)
            ->putJson('/api/categories/91264', ['name' => 'Kategori gagal'])
            ->assertStatus(404);
    }

    public function test_previleged_user_can_update_category_with_its_data()
    {
        $kategori = Category::factory()->create(['name' => 'Kategori 1']);
        $this->actingAs($this->admin)
            ->putJson("/api/categories/{$kategori->id}", ['name' => $kategori->name])
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', $kategori->name);
    }

    public function test_authenticated_user_can_get_books_by_category()
    {
        $categories = Category::factory(3)->create();
        $publishers = Publisher::factory(8)->create();
        $authors = Author::factory(8)->create();
        $shelves = Shelf::factory()->create();

        $targetBooks = Book::factory(3)
            ->recycle($publishers)
            ->recycle($authors)
            ->recycle($shelves)
            ->create();

        foreach ($targetBooks as $book) {
            $book->categories()->attach($categories[1]->id);
        }

        $otherBooks = Book::factory(2)
            ->recycle($publishers)
            ->recycle($authors)
            ->recycle($shelves)
            ->create();

        $this->actingAs($this->admin)
            ->getJson("/api/categories/{$categories[1]->id}?include=books")
            ->assertStatus(200)
            ->assertJsonCount(3, 'data.relationships.books.data')
            ->assertJsonPath('included.0.attributes.title', $targetBooks[0]->title)
            ->assertJsonPath('included.0.relationships.author.name', $targetBooks[0]->author->name)
            ->assertJsonPath('included.1.attributes.title', $targetBooks[1]->title)
            ->assertJsonPath('included.2.attributes.title', $targetBooks[2]->title);
    }
}
