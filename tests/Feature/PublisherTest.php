<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Override;
use Tests\TestCase;

class PublisherTest extends TestCase
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

    public function test_loaner_can_get_all_publishers()
    {
        Publisher::factory(3)->create();
        $this->actingAs($this->loaner)->getJson('/api/publishers')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_get_all_publishers()
    {
        Publisher::factory(3)->create();
        $this->actingAs($this->admin)->getJson('/api/publishers')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_staff_can_get_all_publishers()
    {
        Publisher::factory(3)->create();
        $this->actingAs($this->staff)->getJson('/api/publishers')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_loaner_fail_create_publisher_with_valid_data()
    {
        $payload = [
            'name' => 'Penerbit 1',
            'address' => 'Alamat Penerbit 1',
            'phone' => '08123456789',
            'email' => 'penerbit1@example.com'
        ];
        $this->actingAs($this->loaner)->postJson('/api/publishers', $payload)
            ->assertStatus(403);
    }

    public function test_staff_can_create_publisher_with_valid_data()
    {
        $payload = [
            'name' => 'Penerbit 1',
            'address' => 'Alamat Penerbit 1',
            'phone' => '08123456789',
            'email' => 'penerbit1@example.com'
        ];
        $this->actingAs($this->staff)->postJson('/api/publishers', $payload)
            ->assertStatus(201);
    }

    public function test_admin_can_create_publisher_with_valid_data()
    {
        $payload = [
            'name' => 'Penerbit 1',
            'address' => 'Alamat Penerbit 1',
            'phone' => '08123456789',
            'email' => 'penerbit1@example.com'
        ];
        $this->actingAs($this->admin)->postJson('/api/publishers', $payload)
            ->assertStatus(201);
    }

    public function test_previleged_user_fail_create_if_email_format_is_invalid()
    {
        $payload = [
            'name' => 'Penerbit',
            'address' => 'Alamat Penerbit',
            'phone' => '08123456789',
            'email' => 'penerbit-example-com'
        ];
        $this->actingAs($this->staff)->postJson('/api/publishers', $payload)->assertStatus(422);
    }

    public function test_previleged_user_can_create_publisher_without_optional_fields()
    {
        $response = $this->actingAs($this->staff)->postJson('/api/publishers', ['name' => 'Penerbit']);
        $response->assertstatus(201)->assertJsonPath('data.attributes.name', 'Penerbit');
    }

    public function test_previleged_user_fail_create_publisher_if_required_fields_are_missing()
    {
        $this->actingAs($this->admin)->postJson('/api/publishers', [])->assertStatus(422);
    }

    public function test_previleged_user_fail_create_publisher_with_duplicate_email()
    {
        Publisher::factory()->create(['email' => 'penerbit@example.com']);
        $payload = [
            'name' => 'Penerbit Baru',
            'email' => 'penerbit@example.com'
        ];
        $this->actingAs($this->admin)->postJson('/api/publishers', $payload)->assertStatus(422);
    }

    public function test_loaner_can_get_single_publisher()
    {
        $publisher = Publisher::factory()->create();
        $this->actingAs($this->loaner)->getJson("/api/publishers/{$publisher->id}")->assertStatus(200);
    }

    public function test_staff_can_get_single_publisher()
    {
        $publisher = Publisher::factory()->create();
        $this->actingAs($this->staff)->getJson("/api/publishers/{$publisher->id}")->assertStatus(200)
            ->assertJsonPath('data.attributes.name', $publisher->name);
    }

    public function test_admin_can_get_single_publisher()
    {
        $publisher = Publisher::factory()->create();
        $this->actingAs($this->admin)->getJson("/api/publishers/{$publisher->id}")->assertStatus(200)
            ->assertJsonPath('data.attributes.name', $publisher->name);
    }

    public function test_publisher_returns_404_if_not_found()
    {
        $this->actingAs($this->admin)->getJson('/api/publishers/34225')->assertStatus(404);
    }

    public function test_loaner_fail_update_publisher()
    {
        $publisherPayload = [
            'name' => 'John Doe',
            'address' => 'Jalan Kayu Bakar No. 43',
            'phone' => '08123456789',
            'email' => 'johndoe@example.com'
        ];
        $publisher1 = Publisher::factory()->create($publisherPayload);

        $updatePayload = [
            'name' => 'John Sins',
            'address' => 'Jalan Kayu Mahoni No. 113',
            'phone' => '081987654321',
            'email' => 'johnsins113@example.com'
        ];
        $this->actingAs($this->loaner)->putJson("/api/publishers/{$publisher1->id}", $updatePayload)
            ->assertstatus(403);
    }

    public function test_staff_can_update_publisher()
    {
        $publisherPayload = [
            'name' => 'John Doe',
            'address' => 'Jalan Kayu Bakar No. 43',
            'phone' => '08123456789',
            'email' => 'johndoe@example.com'
        ];
        $publisher1 = Publisher::factory()->create($publisherPayload);

        $updatePayload = [
            'name' => 'John Sins',
            'address' => 'Jalan Kayu Mahoni No. 113',
            'phone' => '081987654321',
            'email' => 'johnsins113@example.com'
        ];

        $this->actingAs($this->staff)->putJson("/api/publishers/{$publisher1->id}", $updatePayload)
            ->assertstatus(200);
    }

    public function test_admin_can_update_publisher()
    {
        $publisherPayload = [
            'name' => 'John Doe',
            'address' => 'Jalan Kayu Bakar No. 43',
            'phone' => '08123456789',
            'email' => 'johndoe@example.com'
        ];
        $publisher1 = Publisher::factory()->create($publisherPayload);

        $updatePayload = [
            'name' => 'John Sins',
            'address' => 'Jalan Kayu Mahoni No. 113',
            'phone' => '081987654321',
            'email' => 'johndoe@example.com'
        ];

        $this->actingAs($this->admin)->putJson("/api/publishers/{$publisher1->id}", $updatePayload)
            ->assertstatus(200);
    }

    public function test_previleged_user_can_update_publisher_with_its_data()
    {
        $publisher = Publisher::factory()->create([
            'name' => 'John Doe',
            'address' => 'Jalan Kayu Bakar No. 43',
            'phone' => '08123456789',
            'email' => 'johndoe@example.com'
        ]);

        $this->actingAs($this->staff)
            ->putJson("/api/publishers/{$publisher->id}", [
                'name' => 'John Doe',
                'address' => 'Jalan Kayu Bakar No. 43',
                'phone' => '08123456789',
                'email' => 'johndoe@example.com'
            ])
            ->assertStatus(200);
    }

    public function test_previleged_user_fail_update_publisher_if_not_found()
    {
        $this->actingAs($this->admin)
            ->putJson('/api/categories/3481641', [
                'name' => 'John Doe',
                'address' => 'Jalan Kayu Bakar No. 43',
                'phone' => '08123456789',
                'email' => 'johndoe@example.com'
            ])
            ->assertStatus(404);
    }

    public function test_previleged_user_fail_update_publisher_if_email_is_existed()
    {
        $publisher1 = Publisher::factory()->create();
        $publisher2 = Publisher::factory()->create();

        $updatePayload = [
            'name' => $publisher2->name,
            'address' => $publisher2->address,
            'phone' => $publisher2->phone,
            'email' => $publisher1->email
        ];

        $response = $this->actingAs($this->admin)->putJson("/api/publishers/{$publisher2->id}", $updatePayload);
        $response->assertstatus(422);
        $response->assertJsonPath('message', 'The email has already been taken.');
    }
}
