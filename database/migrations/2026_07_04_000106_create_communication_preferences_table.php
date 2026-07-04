<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->boolean('prefers_text')->default(false);
            $table->boolean('direct_communication')->default(false);
            $table->boolean('slow_responder')->default(false);
            $table->boolean('prefers_quiet_plans')->default(false);
            $table->boolean('chat_before_meeting')->default(false);
            $table->boolean('no_surprise_calls')->default(false);
            $table->timestamps();

            $table->unique('profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_preferences');
    }
};
