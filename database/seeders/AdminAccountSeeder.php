<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminAccountSeeder extends Seeder
{
    public function run(): void
    {
        $name = config('services.admin_bootstrap.name');
        $email = strtolower(trim((string) config('services.admin_bootstrap.email')));
        $password = config('services.admin_bootstrap.password');

        if (blank($name) || blank($email) || blank($password)) {
            return;
        }

        if (strlen($password) < 12) {
            throw new RuntimeException('ADMIN_PASSWORD debe tener al menos 12 caracteres.');
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'password' => $password,
            'role' => 'admin',
        ])->save();
    }
}