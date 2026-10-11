<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name'             => 'Super Admin',
            'email'            => 'superadmin@hima-ti.com',
            'nim'              => null,
            'password'         => Hash::make('Admin@1234'),
            'role'             => 'super_admin',
            'password_changed' => true,
        ]);

        $user->assignRole('super_admin');
    }
}
