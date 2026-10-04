<?php

namespace Tests\Unit;

use App\Enums\RoleEnum;
use App\Models\Author;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Override;
use Tests\TestCase;

class AuthorTest extends TestCase
{
    use RefreshDatabase;

    private $staff;
    private $admin;
    private $loaner;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->staff = User::factory()->create(['role' => RoleEnum::STAFF]);
        $this->admin = User::factory()->create(['role' => RoleEnum::ADMIN]);
        $this->loaner = User::factory()->create(['role' => RoleEnum::LOANER]);
    }

    public function test_loaner_can_get_all_authors()
    {
        Author::factory()->count(3)->create();
        $response = $this->actingAs($this->loaner)->getJson('/api/authors');
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_staff_can_get_all_authors()
    {
        Author::factory()->count(3)->create();
        $response = $this->actingAs($this->staff)->getJson('/api/authors');
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_get_all_authors()
    {
        Author::factory()->count(3)->create();
        $response = $this->actingAs($this->admin)->getJson('/api/authors');
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_loaner_fail_create_author_with_valid_data()
    {
        $payload = [
            'first_name' => 'pramoedya',
            'middle_name' => 'ananta',
            'last_name' => 'toer',
            'email' => 'pram@example.com',
            'bio' => 'penulis novel bumi manusia'
        ];
        $response = $this->actingAs($this->loaner)->postJson('/api/authors', $payload);
        $response->assertStatus(403);
    }
    public function test_staff_can_create_author_with_valid_data()
    {
        $payload = [
            'first_name' => 'pramoedya',
            'middle_name' => 'ananta',
            'last_name' => 'toer',
            'email' => 'pram@example.com',
            'bio' => 'penulis novel bumi manusia'
        ];
        $response = $this->actingAs($this->staff)->postJson('/api/authors', $payload);
        $response->assertStatus(201)->assertJsonFragment(['email' => 'pram@example.com']);
        $this->assertDatabaseHas('authors', [
            'first_name'  => 'Pramoedya',
            'middle_name' => 'Ananta',
            'last_name'   => 'Toer',
            'email'       => 'pram@example.com',
        ]);
    }
    public function test_admin_can_create_author_with_valid_data()
    {
        $payload = [
            'first_name' => 'pramoedya',
            'middle_name' => 'ananta',
            'last_name' => 'toer',
            'email' => 'pram@example.com',
            'bio' => 'penulis novel bumi manusia'
        ];
        $response = $this->actingAs($this->admin)->postJson('/api/authors', $payload);
        $response->assertStatus(201)->assertJsonFragment(['email' => 'pram@example.com']);
        $this->assertDatabaseHas('authors', [
            'first_name'  => 'Pramoedya',
            'middle_name' => 'Ananta',
            'last_name'   => 'Toer',
            'email'       => 'pram@example.com',
        ]);
    }

    public function test_previleged_user_fail_create_if_email_format_is_invalid()
    {
        $payload = [
            'first_name' => 'pramoedya',
            'middle_name' => 'ananta',
            'last_name' => 'toer',
            'email' => 'pram-example-com',
            'bio' => 'penulis novel bumi manusia'
        ];
        $response = $this->actingAs($this->staff)->postJson('/api/authors', $payload);
        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_previleged_user_can_create_author_without_optional_fields()
    {
        $payload = [
            'first_name' => 'pramoedya',
            'last_name' => 'toer',
            'email' => 'pram@example.com'
        ];
        $response = $this->actingAs($this->staff)->postJson('/api/authors', $payload);
        $response->assertStatus(201)->assertJsonFragment(['email' => 'pram@example.com']);
        $this->assertDatabaseHas('authors', [
            'first_name'  => 'Pramoedya',
            'middle_name' => null,
            'last_name'   => 'Toer',
            'email'       => 'pram@example.com',
            'bio'         => null
        ]);
    }

    public function test_previleged_user_fail_create_author_if_required_fields_are_missing()
    {
        $response = $this->actingAs($this->staff)->postJson('/api/authors', []);
        $response->assertStatus(422)->assertJsonValidationErrors(['first_name', 'last_name', 'email']);
    }

    public function test_previleged_user_fail_create_author_with_duplicate_email()
    {
        Author::factory()->create(['email' => 'existing@example.com']);
        $payload = [
            'first_name' => 'Meta',
            'last_name' => 'Klepin',
            'email' => 'existing@example.com'
        ];
        $this->actingAs($this->staff)->postJson('/api/authors', $payload)->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_loaner_can_get_single_author()
    {
        $author = Author::factory()->create([
            'first_name'    => 'Pramoedya',
            'last_name'     => 'Toer',
            'email'         => 'pram@example.com'
        ]);
        $response = $this->actingAs($this->loaner)->getJson("/api/authors/{$author->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.id', (string) $author->id)
            ->assertJsonPath('data.type', 'authors')
            ->assertJsonPath('data.attributes.first_name', 'Pramoedya')
            ->assertJsonPath('data.attributes.last_name', 'Toer')
            ->assertJsonPath('data.attributes.email', 'pram@example.com');
    }

    public function test_staff_can_get_single_author()
    {
        $author = Author::factory()->create([
            'first_name'    => 'Pramoedya',
            'last_name'     => 'Toer',
            'email'         => 'pram@example.com'
        ]);
        $response = $this->actingAs($this->staff)->getJson("/api/authors/{$author->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.id', (string) $author->id)
            ->assertJsonPath('data.type', 'authors')
            ->assertJsonPath('data.attributes.first_name', 'Pramoedya')
            ->assertJsonPath('data.attributes.last_name', 'Toer')
            ->assertJsonPath('data.attributes.email', 'pram@example.com');
    }

    public function test_admin_can_get_single_author()
    {
        $author = Author::factory()->create([
            'first_name'    => 'Pramoedya',
            'last_name'     => 'Toer',
            'email'         => 'pram@example.com'
        ]);
        $response = $this->actingAs($this->admin)->getJson("/api/authors/{$author->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.id', (string) $author->id)
            ->assertJsonPath('data.type', 'authors')
            ->assertJsonPath('data.attributes.first_name', 'Pramoedya')
            ->assertJsonPath('data.attributes.last_name', 'Toer')
            ->assertJsonPath('data.attributes.email', 'pram@example.com');
    }

    public function test_author_returns_404_if_not_found()
    {
        $this->actingAs($this->loaner)->getJson('/api/authors/81467')->assertStatus(404);
    }

    public function test_loaner_fail_update_author()
    {
        $author = Author::factory()->create([
            'first_name' => 'pramoedya',
            'last_name'  => 'toer',
            'email'      => 'pram@example.com',
            'bio'        => 'Bio lama',
        ]);

        $updatePayload = [
            'first_name'    => 'pramoedya ananta',
            'middle_name'   => 'subagyo',
            'last_name'     => 'toer',
            'email'         => 'pram@example.com',
            'bio'           => 'bio baru setelah update.'
        ];

        $response = [
            'first_name'    => 'Pramoedya Ananta',
            'middle_name'   => 'Subagyo',
            'last_name'     => 'Toer',
            'email'         => 'pram@example.com',
            'bio'           => 'bio baru setelah update.'
        ];

        $response = $this->actingAs($this->loaner)->putJson("/api/authors/{$author->id}", $updatePayload);

        $response->assertStatus(403);
    }
    public function test_admin_can_update_author()
    {
        $author = Author::factory()->create([
            'first_name' => 'pramoedya',
            'last_name'  => 'toer',
            'email'      => 'pram@example.com',
            'bio'        => 'Bio lama',
        ]);

        $updatePayload = [
            'first_name'    => 'pramoedya ananta',
            'middle_name'   => 'subagyo',
            'last_name'     => 'toer',
            'email'         => 'pram@example.com',
            'bio'           => 'bio baru setelah update.'
        ];

        $response = [
            'first_name'    => 'Pramoedya Ananta',
            'middle_name'   => 'Subagyo',
            'last_name'     => 'Toer',
            'email'         => 'pram@example.com',
            'bio'           => 'bio baru setelah update.'
        ];

        $response = $this->actingAs($this->admin)->putJson("/api/authors/{$author->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.first_name', 'Pramoedya Ananta')
            ->assertJsonPath('data.attributes.bio', 'Bio baru setelah update.');
    }
    public function test_staff_can_update_author()
    {
        $author = Author::factory()->create([
            'first_name' => 'pramoedya',
            'last_name'  => 'toer',
            'email'      => 'pram@example.com',
            'bio'        => 'Bio lama',
        ]);

        $updatePayload = [
            'first_name'    => 'pramoedya ananta',
            'middle_name'   => 'subagyo',
            'last_name'     => 'toer',
            'email'         => 'pram@example.com',
            'bio'           => 'bio baru setelah update.'
        ];

        $response = [
            'first_name'    => 'Pramoedya Ananta',
            'middle_name'   => 'Subagyo',
            'last_name'     => 'Toer',
            'email'         => 'pram@example.com',
            'bio'           => 'bio baru setelah update.'
        ];

        $response = $this->actingAs($this->staff)->putJson("/api/authors/{$author->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.first_name', 'Pramoedya Ananta')
            ->assertJsonPath('data.attributes.bio', 'Bio baru setelah update.');
    }

    public function test_previleged_user_can_update_author_with_its_data()
    {
        $author = Author::factory()->create([
            'first_name' => 'pramoedya',
            'last_name'  => 'toer',
            'email'      => 'pram@example.com',
            'bio'        => 'Bio lama',
        ]);
        $this->actingAs($this->admin)
            ->putJson("/api/authors/{$author->id}", [
                'first_name' => 'pramoedya',
                'last_name'  => 'toer',
                'email'      => 'pram@example.com',
                'bio'        => 'Bio lama',
            ])
            ->assertStatus(200);
    }

    public function test_previleged_user_fail_update_author_if_not_found()
    {
        $this->actingAs($this->admin)
            ->putJson('/api/authors/134821', [
                'first_name' => 'pramoedya',
                'last_name'  => 'toer',
                'email'      => 'pram@example.com',
                'bio'        => 'Bio lama',
            ])
            ->assertStatus(404);
    }

    public function test_previleged_user_fail_update_author_if_email_is_existed()
    {
        $author1 = Author::factory()->create([
            'first_name' => 'pramoedya',
            'last_name'  => 'toer',
            'email'      => 'pram@example.com',
            'bio'        => 'Bio lama',
        ]);

        $author2 = Author::factory()->create([
            'first_name' => 'kelvin',
            'last_name'  => 'taring',
            'email'      => 'kling@example.com',
            'bio'        => 'Bio kelvin',
        ]);

        $updatePayload = [
            'first_name' => 'kelvin',
            'last_name'  => 'taring',
            'email'      => 'pram@example.com',
            'bio'        => 'Bio kelvin',
        ];

        $response = $this->actingAs($this->staff)->putJson("/api/authors/{$author2->id}", $updatePayload);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }
}
