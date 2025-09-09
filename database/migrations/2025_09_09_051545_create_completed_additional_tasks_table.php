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
        Schema::create('completed_additional_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_template_id')->nullable()->constrained()->onDelete('set null');
            $table->string('task_name');
            $table->date('completed_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['station_id', 'completed_date']);
            $table->index(['user_id', 'completed_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('completed_additional_tasks');
    }
};
