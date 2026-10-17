<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email && ! $password) {
            $this->command?->warn('Skipping admin seed: set ADMIN_EMAIL and ADMIN_PASSWORD in .env, or run php artisan admin:create.');

            return;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || ! is_string($password) || strlen($password) < 12) {
            throw new \InvalidArgumentException('Admin seeding requires a valid ADMIN_EMAIL and an ADMIN_PASSWORD of at least 12 characters.');
        }

        $user = User::firstOrNew(['email' => strtolower($email)]);
        if (! $user->exists) {
            $user->name = 'BeastWear Administrator';
            $user->password = Hash::make($password);
        }
        $user->role = 'admin';
        $user->is_admin = true;
        $user->status = 'active';
        $user->save();
    }
}
