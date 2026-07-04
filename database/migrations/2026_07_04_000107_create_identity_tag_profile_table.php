<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_tag_profile', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('identity_tag_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_visible')->default(false);
            // nunca, solo_conexiones, publico
            $table->string('visibility')->default('nunca');
            $table->timestamps();

            $table->unique(['profile_id', 'identity_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_tag_profile');
    }
};
