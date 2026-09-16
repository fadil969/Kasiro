<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed default accounts: one Admin and one User (login via username).
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => 'admin123',
                'role' => 'admin',
                'status' => 'Aktif',
            ],
            [
                'name' => 'Kasir Satu',
                'username' => 'kasir',
                'password' => 'kasir123',
                'role' => 'user',
                'status' => 'Aktif',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['username' => $data['username']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'role' => $data['role'],
                    'status' => $data['status'],
                ]
            );
        }
    }
}
