<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(): JsonResponse
    {
        $customers = User::query()
            ->where('role', 'customer')
            ->where('is_admin', false)
            ->with(['addresses' => fn ($query) => $query
                ->orderByDesc('is_default')
                ->orderByDesc('id')])
            ->latest()
            ->get()
            ->map(fn (User $customer) => $this->serialize($customer));

        return response()->json($customers);
    }

    public function show(string $customerId): JsonResponse
    {
        $customer = User::query()
            ->where('role', 'customer')
            ->where('is_admin', false)
            ->with(['addresses' => fn ($query) => $query
                ->orderByDesc('is_default')
                ->orderByDesc('id')])
            ->findOrFail($customerId);

        return response()->json($this->serialize($customer));
    }

    public function update(Request $request, string $customerId): JsonResponse
    {
        $customer = User::query()
            ->where('role', 'customer')
            ->where('is_admin', false)
            ->with(['addresses' => fn ($query) => $query
                ->orderByDesc('is_default')
                ->orderByDesc('id')])
            ->findOrFail($customerId);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($customer->id),
            ],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'status' => ['sometimes', 'required', Rule::in(['Active', 'Blocked'])],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'area' => ['sometimes', 'nullable', 'string', 'max:150'],
            'address' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $addressData = array_intersect_key($data, array_flip(['city', 'area', 'address']));
        unset($data['city'], $data['area'], $data['address']);

        DB::transaction(function () use ($customer, $data, $addressData): void {
            if (array_key_exists('email', $data)) {
                $data['email'] = strtolower($data['email']);
            }
            if (array_key_exists('status', $data)) {
                $data['status'] = strtolower($data['status']);
            }
            $customer->update($data);

            if ($addressData === []) {
                return;
            }

            $address = $customer->addresses->first();
            $hasAddressInput = collect($addressData)->contains(fn ($value) => filled($value));

            if ($address) {
                $address->update(array_filter($addressData, fn ($value) => $value !== null));

                return;
            }

            if (! $hasAddressInput) {
                return;
            }

            $validatedAddress = validator($addressData, [
                'city' => ['required', 'string', 'max:100'],
                'address' => ['required', 'string', 'max:2000'],
                'area' => ['nullable', 'string', 'max:150'],
            ])->validate();

            $customer->addresses()->create([
                ...$validatedAddress,
                'name' => $customer->name,
                'phone' => $customer->phone ?? '',
                'address_type' => 'home',
                'is_default' => true,
            ]);
        });

        return response()->json($this->serialize($customer->fresh()->load([
            'addresses' => fn ($query) => $query->orderByDesc('is_default')->orderByDesc('id'),
        ])));
    }

    private function serialize(User $customer): array
    {
        /** @var Address|null $address */
        $address = $customer->addresses->first();

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone ?? '',
            'status' => ucfirst($customer->status),
            'city' => $address?->city ?? '',
            'area' => $address?->area ?? '',
            'address' => $address?->address ?? '',
            'province' => '',
            'registeredAt' => $customer->created_at?->toIso8601String(),
            'notes' => '',
        ];
    }
}
