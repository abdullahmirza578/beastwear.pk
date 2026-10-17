<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBannerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_hero_banner_and_customer_homepage_displays_it(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)->postJson(route('admin.banners.hero.save'), [
            'heading' => 'New BeastWear Drop',
            'description' => 'Premium new season essentials.',
            'buttonText' => 'Shop the drop',
            'buttonLink' => '/shop?sort=newest',
            'active' => '1',
            'desktopImage' => UploadedFile::fake()->create('hero.jpg', 100, 'image/jpeg'),
            'mobileImage' => UploadedFile::fake()->create('hero-mobile.jpg', 100, 'image/jpeg'),
        ])->assertOk()
            ->assertJsonPath('hero.heading', 'New BeastWear Drop')
            ->assertJsonPath('hero.active', true);

        $hero = Banner::where('position', 'home-hero')->firstOrFail();
        $this->assertStringStartsWith('banners/', $hero->image);
        Storage::disk('public')->assertExists($hero->image);
        Storage::disk('public')->assertExists($hero->mobile_image);

        $this->withServerVariables(['HTTP_HOST' => '127.0.0.1:8013'])
            ->actingAs($admin)
            ->getJson('/admin/banners/data')
            ->assertOk()
            ->assertJsonPath('hero.desktopImage', '/storage/'.$hero->image)
            ->assertJsonPath('hero.mobileImage', '/storage/'.$hero->mobile_image);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('New BeastWear Drop')
            ->assertSee('Shop the drop')
            ->assertSee($hero->image_url, false)
            ->assertSee($hero->mobile_image_url, false);
    }

    public function test_active_promotions_are_rendered_and_inactive_promotions_are_hidden(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)->postJson(route('admin.banners.promos.store'), [
            'title' => 'Summer Essentials',
            'subtitle' => 'Light layers for warmer days.',
            'link' => '/shop?sale=1',
            'position' => 'Below Hero',
            'active' => '1',
            'image' => UploadedFile::fake()->create('summer.jpg', 100, 'image/jpeg'),
        ])->assertCreated();

        $promo = Banner::where('title', 'Summer Essentials')->firstOrFail();
        $this->assertStringStartsWith('banners/', $promo->image);
        Storage::disk('public')->assertExists($promo->image);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Summer Essentials')
            ->assertSee('Light layers for warmer days.');

        $this->actingAs($admin)->putJson(route('admin.banners.promos.update', $promo), [
            'title' => $promo->title,
            'subtitle' => $promo->subtitle,
            'link' => $promo->button_url,
            'position' => $promo->position,
            'active' => '0',
        ])->assertOk();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Summer Essentials');
    }

    public function test_inactive_hero_uses_the_homepage_default(): void
    {
        Banner::create([
            'title' => 'Hidden hero text',
            'subtitle' => 'Should not display',
            'image' => 'https://example.com/hero.jpg',
            'position' => 'home-hero',
            'status' => 'inactive',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Hidden hero text')
            ->assertSee('Wear Your Beast');
    }
}
