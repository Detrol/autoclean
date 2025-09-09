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
        Schema::table('tasks', function (Blueprint $table) {
            // Startdatum för intervallet
            $table->date('start_date')->nullable()->after('interval_value');
            
            // Flexibelt fält för avancerade återkommande mönster
            $table->json('recurrence_pattern')->nullable()->after('start_date');
            
            // Slutdatum för återkommande uppgifter (optional)
            $table->date('end_date')->nullable()->after('recurrence_pattern');
            
            // Antal gånger uppgiften ska upprepas (optional)
            $table->integer('occurrences')->nullable()->after('end_date');
            
            // Uppdatera interval_type enum för att inkludera "yearly"
            $table->enum('interval_type', ['daily', 'weekly', 'monthly', 'yearly', 'custom'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'recurrence_pattern', 'end_date', 'occurrences']);
            
            // Återställ original enum
            $table->enum('interval_type', ['daily', 'weekly', 'monthly', 'custom'])->change();
        });
    }
};