<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'dispatcher@example.com'],
            [
                'name' => 'Dispatcher',
                'role' => UserRole::Dispatcher->value,
                'password' => Hash::make('password'),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'master1@example.com'],
            [
                'name' => 'Master #1',
                'role' => UserRole::Master->value,
                'password' => Hash::make('password'),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'master2@example.com'],
            [
                'name' => 'Master #2',
                'role' => UserRole::Master->value,
                'password' => Hash::make('password'),
            ]
        );
    }
}