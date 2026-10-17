<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeAdmin extends Command
{
    protected $signature = 'admin:create {email : Email address of the admin user}';

    protected $description = 'Create an admin user or grant admin access to an existing user';

    public function handle(): int
    {
        $email = strtolower((string) $this->argument('email'));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid email address is required.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            if ($user->isAdmin() && $user->is_admin && $user->status === 'active') {
                $this->info("{$user->email} already has admin access.");

                return self::SUCCESS;
            }

            $user->role = 'admin';
            $user->is_admin = true;
            $user->status = 'active';
            $user->save();
            $this->info("Admin access granted to {$user->email}.");

            return self::SUCCESS;
        }

        $name = $this->ask('Admin name');
        $password = $this->secret('Admin password (minimum 12 characters)');
        $confirmation = $this->secret('Confirm admin password');

        if (! is_string($name) || trim($name) === '' || ! is_string($password) || strlen($password) < 12 || $password !== $confirmation) {
            $this->error('A name and matching password of at least 12 characters are required.');

            return self::FAILURE;
        }

        $user = new User([
            'name' => trim($name),
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
        ]);
        $user->is_admin = true;
        $user->save();

        $this->info("Admin account created for {$email}.");

        return self::SUCCESS;
    }
}
