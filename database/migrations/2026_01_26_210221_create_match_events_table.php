<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('match_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->onDelete('cascade');
            $table->foreignId('set_id')->nullable()->constrained('sets')->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade');
            $table->enum('type', ['timeout', 'injury', 'walkover']);
            $table->text('description')->nullable();
            $table->dateTime('occurred_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_events');
    }
};
