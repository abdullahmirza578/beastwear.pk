<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_customer_api_reads_registered_customers_from_the_database(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $customer = User::factory()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.test',
            'phone' => '03001234567',
            'role' => 'customer',
            'status' => 'active',
        ]);
        Address::create([
            'user_id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'city' => 'Lahore',
            'area' => 'Gulberg',
            'address' => 'House 1, Main Road',
            'address_type' => 'home',
            'is_default' => true,
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.data.customers.index'))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $customer->id)
            ->assertJsonPath('0.name', 'Test Customer')
            ->assertJsonPath('0.city', 'Lahore')
            ->assertJsonPath('0.status', 'Active');

        $this->actingAs($admin)
            ->getJson(route('admin.data.customers.show', $customer->id))
            ->assertOk()
            ->assertJsonPath('email', 'customer@example.test')
            ->assertJsonPath('area', 'Gulberg');
    }

    public function test_accounts_marked_as_admin_are_not_returned_as_customers(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.data.customers.index'))
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_admin_customer_edits_are_persisted_to_the_user_and_address_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);
        $address = Address::create([
            'user_id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone ?? '',
            'city' => 'Lahore',
            'address' => 'Old address',
            'address_type' => 'home',
            'is_default' => true,
        ]);

        $this->actingAs($admin)
            ->putJson(route('admin.data.customers.update', $customer->id), [
                'name' => 'Updated Customer',
                'email' => 'updated@example.test',
                'phone' => '03007654321',
                'city' => 'Karachi',
                'area' => 'Clifton',
                'address' => 'New address',
                'status' => 'Blocked',
            ])
            ->assertOk()
            ->assertJsonPath('name', 'Updated Customer')
            ->assertJsonPath('city', 'Karachi')
            ->assertJsonPath('status', 'Blocked');

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Updated Customer',
            'email' => 'updated@example.test',
            'phone' => '03007654321',
            'status' => 'blocked',
        ]);
        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'city' => 'Karachi',
            'area' => 'Clifton',
            'address' => 'New address',
        ]);
    }
}
