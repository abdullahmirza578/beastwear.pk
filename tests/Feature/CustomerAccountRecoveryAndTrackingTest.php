<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CustomerAccountRecoveryAndTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_request_and_use_a_password_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'customer@example.test']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });
        $this->assertNotNull($token);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'a-new-secure-password',
            'password_confirmation' => 'a-new-secure-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('a-new-secure-password', $user->fresh()->password));
    }

    public function test_local_log_mailer_displays_a_usable_reset_link_without_sending_email(): void
    {
        $this->app['env'] = 'local';
        $this->withoutMiddleware(VerifyCsrfToken::class);
        config(['mail.default' => 'log']);

        $user = User::factory()->create(['email' => 'customer@example.test']);

        $response = $this->from(route('forgot-password'))
            ->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('forgot-password'))
            ->assertSessionHas('status')
            ->assertSessionHas('reset_link');

        $resetLink = session('reset_link');
        $this->assertNotEmpty($resetLink);

        $response->assertSessionHas('reset_link', $resetLink);
        $this->get(route('forgot-password'))
            ->assertOk()
            ->assertSee('Local development only')
            ->assertSee($resetLink);
        $this->get($resetLink)->assertOk()->assertSee('Reset Password');

        $token = parse_url($resetLink, PHP_URL_PATH);
        $token = basename($token);
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'a-new-secure-password',
            'password_confirmation' => 'a-new-secure-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('a-new-secure-password', $user->fresh()->password));
    }

    public function test_order_tracking_requires_both_order_number_and_checkout_phone(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'BW-260101-TRACK123',
            'customer_name' => 'Beast Customer',
            'customer_phone' => '03001234567',
            'customer_email' => $user->email,
            'shipping_address' => 'House 1, Main Road',
            'city' => 'Lahore',
            'subtotal' => 2000,
            'discount' => 0,
            'shipping_fee' => 250,
            'total_amount' => 2250,
            'payment_method' => 'COD',
        ]);

        $this->post(route('track-order.lookup'), [
            'order_number' => $order->order_number,
            'customer_phone' => '03000000000',
        ])->assertRedirect()
            ->assertSessionHasErrors('order_number');

        $order->update([
            'public_tracking_id' => 'BW-TRK-TEST123456',
            'shipping_courier' => 'TCS',
            'tracking_number' => 'TCS-400 123 4567',
            'order_status' => 'shipped',
            'shipping_status' => 'shipped',
        ]);

        $this->post(route('track-order.lookup'), [
            'order_number' => 'bwtrk-test123456',
            'customer_phone' => '+92 300 123 4567',
        ])->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('BW-TRK-TEST123456')
            ->assertSee('TCS-400 123 4567')
            ->assertSee('TCS');
    }

    public function test_order_tracking_accepts_order_number_and_formatted_checkout_phone(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'BW-260101-TRACK123',
            'customer_name' => 'Beast Customer',
            'customer_phone' => '0300-123-4567',
            'shipping_address' => 'House 1, Main Road',
            'city' => 'Lahore',
            'subtotal' => 2000,
            'discount' => 0,
            'shipping_fee' => 250,
            'total_amount' => 2250,
            'payment_method' => 'COD',
        ]);

        $this->post(route('track-order.lookup'), [
            'order_number' => ' bw-260101-track123 ',
            'customer_phone' => '0092 300 123 4567',
        ])->assertOk()
            ->assertSee($order->order_number);
    }
}
