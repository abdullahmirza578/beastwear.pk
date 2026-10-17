<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BannerController extends Controller
{
    private const HERO_POSITIONS = ['home-hero', 'hero'];

    private const PROMO_POSITIONS = ['Below Hero', 'Mid Page', 'Above Footer', 'Footer'];

    public function data(): JsonResponse
    {
        $hero = Banner::query()
            ->whereIn('position', self::HERO_POSITIONS)
            ->orderByRaw("CASE WHEN position = 'home-hero' THEN 0 ELSE 1 END")
            ->latest('id')
            ->first();

        $promos = Banner::query()
            ->whereNotIn('position', self::HERO_POSITIONS)
            ->whereIn('position', self::PROMO_POSITIONS)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return response()->json([
            'hero' => $hero ? $this->heroData($hero) : null,
            'promos' => $promos->map(fn (Banner $banner): array => $this->promoData($banner)),
        ]);
    }

    public function saveHero(Request $request): JsonResponse
    {
        $data = $request->validate([
            'heading' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'buttonText' => ['required', 'string', 'max:100'],
            'buttonLink' => ['required', 'string', 'max:2048'],
            'active' => ['required', 'boolean'],
            'desktopImage' => ['nullable', 'image', 'max:8192'],
            'mobileImage' => ['nullable', 'image', 'max:8192'],
        ]);
        $this->validateBannerLink($data['buttonLink']);

        $banner = Banner::query()
            ->whereIn('position', self::HERO_POSITIONS)
            ->orderByRaw("CASE WHEN position = 'home-hero' THEN 0 ELSE 1 END")
            ->latest('id')
            ->first() ?? new Banner;

        $banner->title = $data['heading'];
        $banner->subtitle = $data['description'] ?? '';
        $banner->button_text = $data['buttonText'];
        $banner->button_url = $data['buttonLink'];
        $banner->position = 'home-hero';
        $banner->status = $request->boolean('active') ? 'active' : 'inactive';

        $oldDesktopImage = $banner->image;
        $oldMobileImage = $banner->mobile_image;
        if ($request->hasFile('desktopImage')) {
            $banner->image = $request->file('desktopImage')->storePublicly('banners', 'public');
        }

        if ($request->hasFile('mobileImage')) {
            $banner->mobile_image = $request->file('mobileImage')->storePublicly('banners', 'public');
        }

        $banner->save();
        if ($oldDesktopImage !== $banner->image) {
            $this->deleteStoredImage($oldDesktopImage);
        }
        if ($oldMobileImage !== $banner->mobile_image) {
            $this->deleteStoredImage($oldMobileImage);
        }

        return response()->json(['hero' => $this->heroData($banner->refresh())]);
    }

    public function storePromo(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:5000'],
            'link' => ['required', 'string', 'max:2048'],
            'position' => ['required', Rule::in(self::PROMO_POSITIONS)],
            'active' => ['required', 'boolean'],
            'image' => ['required', 'image', 'max:8192'],
        ]);
        $this->validateBannerLink($data['link']);

        $banner = new Banner([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? '',
            'button_url' => $data['link'],
            'position' => $data['position'],
            'status' => $request->boolean('active') ? 'active' : 'inactive',
            'image' => $request->file('image')->storePublicly('banners', 'public'),
        ]);
        $banner->save();

        return response()->json(['promo' => $this->promoData($banner)], 201);
    }

    public function updatePromo(Request $request, Banner $banner): JsonResponse
    {
        abort_if(in_array($banner->position, self::HERO_POSITIONS, true), 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:5000'],
            'link' => ['required', 'string', 'max:2048'],
            'position' => ['required', Rule::in(self::PROMO_POSITIONS)],
            'active' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'max:8192'],
        ]);
        $this->validateBannerLink($data['link']);

        $banner->title = $data['title'];
        $banner->subtitle = $data['subtitle'] ?? '';
        $banner->button_url = $data['link'];
        $banner->position = $data['position'];
        $banner->status = $request->boolean('active') ? 'active' : 'inactive';
        $oldImage = $banner->image;
        if ($request->hasFile('image')) {
            $banner->image = $request->file('image')->storePublicly('banners', 'public');
        }
        $banner->save();
        if ($oldImage !== $banner->image) {
            $this->deleteStoredImage($oldImage);
        }

        return response()->json(['promo' => $this->promoData($banner)]);
    }

    public function destroyPromo(Banner $banner): JsonResponse
    {
        abort_if(in_array($banner->position, self::HERO_POSITIONS, true), 404);
        $this->deleteStoredImage($banner->image);
        $this->deleteStoredImage($banner->mobile_image);
        $banner->delete();

        return response()->json(['deleted' => true]);
    }

    private function validateBannerLink(string $url): void
    {
        if (! str_starts_with($url, '/') && ! preg_match('/^https?:\/\//i', $url)) {
            throw ValidationException::withMessages([
                'link' => 'Banner links must be a site path or an HTTP(S) URL.',
            ]);
        }
    }

    private function deleteStoredImage(?string $path): void
    {
        if ($path && ! filter_var($path, FILTER_VALIDATE_URL)) {
            $path = preg_replace('#^/?storage/#', '', ltrim($path, '/'));
            if (! str_starts_with($path, 'banners/')) {
                return;
            }
            Storage::disk('public')->delete($path);
        }
    }

    private function heroData(Banner $banner): array
    {
        return [
            'id' => $banner->id,
            'heading' => $banner->title,
            'description' => $banner->subtitle,
            'buttonText' => $banner->button_text,
            'buttonLink' => $banner->button_url,
            'desktopImage' => $banner->image_url,
            'mobileImage' => $banner->mobile_image_url,
            'active' => $banner->status === 'active',
        ];
    }

    private function promoData(Banner $banner): array
    {
        return [
            'id' => $banner->id,
            'title' => $banner->title,
            'subtitle' => $banner->subtitle,
            'image' => $banner->image_url,
            'link' => $banner->button_url,
            'position' => $banner->position,
            'active' => $banner->status === 'active',
        ];
    }
}
