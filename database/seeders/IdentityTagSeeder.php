<?php

namespace Database\Seeders;

use App\Models\IdentityTag;
use Illuminate\Database\Seeder;

class IdentityTagSeeder extends Seeder
{
    public function run(): void
    {
        // is_sensitive => ocultas por default y sujetas a consentimiento explícito.
        $tags = [
            'Autismo' => true,
            'TDAH' => true,
            'Ansiedad social' => true,
            'Asexual' => true,
            'Demisexual' => true,
            'Sapiosexual' => true,
            'Introvertido' => false,
            'Otro' => false,
        ];

        foreach ($tags as $name => $isSensitive) {
            IdentityTag::firstOrCreate(
                ['name' => $name],
                ['is_sensitive' => $isSensitive],
            );
        }
    }
}
