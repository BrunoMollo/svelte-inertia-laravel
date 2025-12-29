<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create superadmin user
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('querty123'),
                'email_verified_at' => now(),
            ]
        );
        $superadmin->assignRole('superadmin');

        // Create teacher user
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Teacher',
                'password' => Hash::make('querty123'),
                'email_verified_at' => now(),
            ]
        );
        $teacher->assignRole('teacher');

        // Create student user
        $student = User::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Student',
                'password' => Hash::make('querty123'),
                'email_verified_at' => now(),
            ]
        );
        $student->assignRole('student');
    }
}
