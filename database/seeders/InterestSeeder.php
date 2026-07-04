<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;

class InterestSeeder extends Seeder
{
    public function run(): void
    {
        $interests = [
            'Arte y cultura' => [
                'Lectura', 'Escritura', 'Poesía', 'Cine', 'Series', 'Museos',
                'Fotografía', 'Dibujo', 'Pintura',
            ],
            'Música' => [
                'Escuchar música', 'Tocar un instrumento', 'Conciertos', 'Vinilos',
            ],
            'Tecnología' => [
                'Programación', 'Videojuegos', 'Gadgets', 'Inteligencia artificial',
            ],
            'Naturaleza y aire libre' => [
                'Senderismo', 'Jardinería', 'Camping', 'Observar aves', 'Playa',
            ],
            'Bienestar' => [
                'Meditación', 'Yoga', 'Correr', 'Cocina', 'Repostería', 'Té y café',
            ],
            'Juegos y hobbies' => [
                'Juegos de mesa', 'Rompecabezas', 'Coleccionismo', 'Anime', 'Cómics',
            ],
            'Aprendizaje' => [
                'Idiomas', 'Historia', 'Ciencia', 'Filosofía', 'Astronomía',
            ],
            'Animales' => [
                'Perros', 'Gatos', 'Voluntariado con animales',
            ],
        ];

        foreach ($interests as $category => $names) {
            foreach ($names as $name) {
                Interest::firstOrCreate(
                    ['name' => $name],
                    ['category' => $category],
                );
            }
        }
    }
}
