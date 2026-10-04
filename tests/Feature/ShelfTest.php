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

class ShelfTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $loaner;
    private $shelves = [];
    private $createPayload = ['code' => 'Kode Baru'];
    private $updatePayload = ['code' => 'Kode Updated'];

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => RoleEnum::ADMIN]);
        $this->staff = User::factory()->create(['role' => RoleEnum::STAFF]);
        $this->loaner = User::factory()->create(['role' => RoleEnum::LOANER]);
        $this->shelves = Shelf::factory(5)->create();
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

    public function test_loaner_can_get_all_shelves()
    {
        $this->loaner()
            ->getJson('/api/shelves')
            ->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_admin_can_get_all_shelves()
    {
        $this->admin()
            ->getJson('/api/shelves')
            ->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_staff_can_get_all_shelves()
    {
        $this->staff()
            ->getJson('/api/shelves')
            ->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_loaner_fail_create_shelf_with_valid_data()
    {
        $this->loaner()
            ->postJson('/api/shelves', ['code' => "Kode Baru"])
            ->assertStatus(403);
    }

    public function test_admin_can_create_shelf_with_valid_data()
    {
        $this->admin()
            ->postJson('/api/shelves', $this->createPayload)
            ->assertStatus(201)
            ->assertJsonFragment($this->createPayload);
        $this->assertDatabaseHas('shelves', $this->createPayload);
    }

    public function test_staff_can_create_shelf_with_valid_data()
    {
        $this->staff()
            ->postJson('/api/shelves', $this->createPayload)
            ->assertStatus(201)
            ->assertJsonFragment($this->createPayload);
        $this->assertDatabaseHas('shelves', $this->createPayload);
    }

    public function test_previleged_user_fail_create_shelf_if_required_field_is_missing()
    {
        $this->staff()
            ->postJson('/api/shelves', ['code' => null])
            ->assertStatus(422);
    }

    public function test_previleged_user_fail_create_shelf_if_name_is_existed()
    {
        $this->staff()
            ->postJson('/api/shelves', ['code' => $this->shelves[3]->code])
            ->assertStatus(422);
    }

    public function test_loaner_can_get_single_shelf()
    {
        $this->loaner()
            ->getJson("/api/shelves/{$this->shelves[3]->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.code', $this->shelves[3]->code);
    }

    public function test_admin_can_get_single_shelf()
    {
        $this->admin()
            ->getJson("/api/shelves/{$this->shelves[3]->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.code', $this->shelves[3]->code);
    }

    public function test_staff_can_get_single_shelf()
    {
        $this->staff()
            ->getJson("/api/shelves/{$this->shelves[3]->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.code', $this->shelves[3]->code);
    }

    public function test_shelf_returns_404_if_not_found()
    {
        $this->staff()
            ->getJson('/api/shelves/1327612')
            ->assertStatus(404);
    }

    public function test_loaner_fail_update_shelf()
    {
        $this->loaner()
            ->putJson("/api/shelves/{$this->shelves[3]->id}", $this->updatePayload)
            ->assertStatus(403);
    }

    public function test_admin_can_update_shelf()
    {
        $this->admin()
            ->putJson("/api/shelves/{$this->shelves[3]->id}", $this->updatePayload)
            ->assertStatus(200);
    }

    public function test_staff_can_update_shelf()
    {
        $this->staff()
            ->putJson("/api/shelves/{$this->shelves[3]->id}", $this->updatePayload)
            ->assertStatus(200);
    }

    public function test_previleged_user_fail_update_shelf_if_name_is_existed()
    {
        $this->admin()
            ->putJson("/api/shelves/{$this->shelves[3]->id}", ['code' => $this->shelves[4]->code])
            ->assertStatus(422);
    }

    public function test_previleged_user_fail_update_shelf_if_not_found()
    {
        $this->admin()
            ->putJson("/api/shelves/312414", $this->updatePayload)
            ->assertStatus(404);
    }

    public function test_previleged_user_can_update_shelf_with_its_data()
    {
        $this->admin()
            ->putJson("/api/shelves/{$this->shelves[3]->id}", ['code' => $this->shelves[3]->code])
            ->assertStatus(200);
    }

    public function test_authenticated_user_can_get_books_by_shelf()
    {
        $targetShelf = $this->shelves[3];
        $categories = Category::factory(5)->create();
        $authors = Author::factory(5)->create();
        $publishers = Publisher::factory(5)->create();
        $books = Book::factory(4)
            ->recycle($authors)
            ->recycle($publishers)
            ->create(['shelf_id' => $targetShelf->id]);
        foreach ($books as $book) {
            $randCat = $categories->random(rand(1, 4));
            $book->categories()->attach($randCat);
        }
        Book::factory(3)->create(['shelf_id' => $this->shelves[1]->id]);
        Book::factory(1)->create(['shelf_id' => $this->shelves[4]->id]);
        $this->loaner()
            ->getJson("/api/shelves/{$targetShelf->id}?include=books")
            ->assertStatus(200)
            ->assertJsonCount(4, 'data')
            ->assertJsonCount(4, 'data.relationships.books.data');
    }
}
