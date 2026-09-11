<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SourceSeeder::class);

        User::updateOrCreate(
            ['email' => 'demo@wout-job.test'],
            ['name' => 'Compte de démo', 'password' => Hash::make('password')],
        );

        $this->command?->info('Connexion démo : demo@wout-job.test / password');
    }
}
