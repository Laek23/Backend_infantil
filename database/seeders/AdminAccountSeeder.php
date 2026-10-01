<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminAccountSeeder extends Seeder
{
    public function run(): void
    {
        $name = env('ADMIN_NAME');
        $email = strtolower(trim((string) env('ADMIN_EMAIL')));
        $password = env('ADMIN_PASSWORD');

        if (blank($name) || blank($email) || blank($password)) {
            return;
        }

        if (strlen($password) < 12) {
            throw new RuntimeException('ADMIN_PASSWORD debe tener al menos 12 caracteres.');
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password, 'role' => 'admin'],
        );

        if ($user->role !== 'admin') {
            throw new RuntimeException('ADMIN_EMAIL ya pertenece a una cuenta de jugador. Usa otro correo para el administrador.');
        }
    }
}