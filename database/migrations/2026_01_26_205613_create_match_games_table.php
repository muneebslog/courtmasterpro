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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained('rounds')->onDelete('cascade');
            $table->foreignId('team_a_id')->nullable()->constrained('teams')->onDelete('cascade');
            $table->foreignId('team_b_id')->nullable()->constrained('teams')->onDelete('cascade');
            $table->string('court_no')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            // umpire and referee can be string names for simplicity
            $table->string('umpire')->nullable();
            $table->string('referee')->nullable();
            // need to track shuttlecock used count
            $table->integer('shuttlecock_used_count')->default(1);
            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->enum('status', ['scheduled', 'live', 'completed', 'bye'])->default('scheduled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
