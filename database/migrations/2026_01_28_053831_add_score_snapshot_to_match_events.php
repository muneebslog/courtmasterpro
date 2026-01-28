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
        Schema::table('match_events', function (Blueprint $table) {
            $table->unsignedTinyInteger('team_a_points')->nullable()->after('team_id');
            $table->unsignedTinyInteger('team_b_points')->nullable()->after('team_a_points');
        });
    }

    public function down(): void
    {
        Schema::table('match_events', function (Blueprint $table) {
            $table->dropColumn(['team_a_points', 'team_b_points']);
        });
    }
};
