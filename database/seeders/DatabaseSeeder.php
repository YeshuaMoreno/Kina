<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Catálogos base
        $this->call([
            InterestSeeder::class,
            IdentityTagSeeder::class,
        ]);

        // Usuario admin de prueba
        User::firstOrCreate(
            ['email' => 'admin@kina.local'],
            [
                'name' => 'Kina Admin',
                'password' => 'password',
                'birthdate' => '1990-01-01',
                'is_adult_confirmed' => true,
                'is_admin' => true,
            ],
        );

        // Usuario normal de prueba
        User::firstOrCreate(
            ['email' => 'test@kina.local'],
            [
                'name' => 'Usuario Prueba',
                'password' => 'password',
                'birthdate' => '1995-06-15',
                'is_adult_confirmed' => true,
            ],
        );
    }
}
