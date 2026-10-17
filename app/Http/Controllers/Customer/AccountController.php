<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('account.index', [
            'user' => $user,
            'orders' => $user->orders()->with('items')->latest()->take(5)->get(),
            'addresses' => $user->addresses()->latest()->get(),
            'wishlistCount' => $user->wishlist()->withCount('items')->first()?->items_count ?? 0,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->fill([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
        ]);
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return back()->with('status', 'Your account has been updated.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:2000'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'address_type' => ['required', Rule::in(['home', 'work', 'other'])],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();
        if ($request->boolean('is_default')) {
            $user->addresses()->update(['is_default' => false]);
        }
        $user->addresses()->create($data);

        return back()->with('status', 'Address saved.');
    }

    public function updateAddress(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:2000'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'address_type' => ['required', Rule::in(['home', 'work', 'other'])],
            'is_default' => ['sometimes', 'boolean'],
        ]);
        if ($request->boolean('is_default')) {
            $request->user()->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
        }
        $address->update($data);

        return back()->with('status', 'Address updated.');
    }

    public function destroyAddress(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 404);
        $address->delete();

        return back()->with('status', 'Address removed.');
    }
}
