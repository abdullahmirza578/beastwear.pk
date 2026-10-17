<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_pages_render_the_shared_layout(): void
    {
        foreach ([
            'home', 'about', 'category', 'contact', 'faq', 'forgot-password',
            'login', 'privacy', 'register', 'returns', 'search', 'shipping',
            'shop', 'size-guide', 'terms', 'track-order',
        ] as $routeName) {
            $response = $this->get(route($routeName))->assertOk();
            $html = $response->getContent();

            $this->assertSame(1, substr_count($html, 'id="site-header"'), $routeName);
            $this->assertSame(1, substr_count($html, 'id="site-footer"'), $routeName);
        }
    }

    public function test_guests_are_redirected_to_the_admin_login(): void
    {
        $this->get('/admin')
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_login_form_is_available_to_guests(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertSee('name="email"', false)
            ->assertSee('/admin-assets/css/admin.css', false);
    }

    public function test_regular_users_cannot_access_the_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admins_can_access_the_panel_and_log_out(): void
    {
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->status = 'active';
        $admin->is_admin = true;
        $admin->save();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_legacy_is_admin_flag_grants_admin_access_when_role_is_out_of_sync(): void
    {
        $admin = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_pages_render_the_admin_layout(): void
    {
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->status = 'active';
        $admin->is_admin = true;
        $admin->save();

        $this->actingAs($admin);

        foreach ([
            'admin.dashboard', 'admin.products.index', 'admin.products.create',
            'admin.products.edit', 'admin.categories.index', 'admin.categories.form',
            'admin.orders.index', 'admin.orders.show', 'admin.customers.index',
            'admin.customers.show', 'admin.inventory.index', 'admin.coupons.index',
            'admin.reviews.index', 'admin.banners.index', 'admin.admins.index', 'admin.reports',
            'admin.settings', 'admin.shipping-settings', 'admin.profile',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_admin_login_rejects_non_admin_accounts(): void
    {
        $user = User::factory()->create();

        $this->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_create_activates_an_existing_user_when_granting_admin_access(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);

        $this->artisan('admin:create', ['email' => $user->email])
            ->expectsOutput("Admin access granted to {$user->email}.")
            ->assertExitCode(0);

        $this->assertSame('admin', $user->fresh()->role);
        $this->assertSame('active', $user->fresh()->status);
        $this->assertTrue($user->fresh()->is_admin);
    }
}
