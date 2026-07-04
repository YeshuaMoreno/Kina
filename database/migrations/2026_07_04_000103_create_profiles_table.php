<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('display_name')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->text('bio')->nullable();

            // Intención de conexión: amistad, pareja_formal, algo_casual, comunidad
            $table->string('looking_for')->nullable();

            // Batería social: baja, media, alta
            $table->string('social_battery')->nullable();

            // Etiquetas sensibles ocultas por default
            $table->boolean('show_sensitive_tags')->default(false);
            // Visibilidad de etiquetas sensibles: nunca, solo_conexiones, publico
            $table->string('sensitive_tags_visibility')->default('nunca');

            // Visibilidad del perfil: publico, solo_conexiones, nunca
            $table->string('profile_visibility')->default('publico');

            $table->boolean('onboarding_completed')->default(false);
            $table->timestamp('paused_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
