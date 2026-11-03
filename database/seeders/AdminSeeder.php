<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the first administrator from ADMIN_EMAIL / ADMIN_PASSWORD in .env.
 * Nothing is hardcoded: if the password is missing, no admin is created.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD not set in .env - no administrator created.');

            return;
        }

        $admin = User::firstOrNew(['email' => $email]);
        $admin->name = env('ADMIN_NAME', 'AutoKonek Admin');
        $admin->password = Hash::make($password);
        $admin->role = User::ROLE_ADMIN;
        $admin->is_active = true;
        $admin->email_verified_at = now();
        $admin->save();
    }
}
