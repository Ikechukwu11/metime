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
        Schema::create('daily_calendar', function (Blueprint $table) {
            $table->id();
            $table->string('first_reading')->nullable();
            $table->string('psalm')->nullable();
            $table->string('second_reading')->nullable();
            $table->string('gospel')->nullable();
            $table->string('season')->nullable();
            $table->string('sub_season')->nullable();
            $table->text('liturgical_note')->nullable();
            $table->text('historical_note')->nullable();
            $table->string('celebration_name')->nullable();
            $table->string('celebration_type')->nullable();
            $table->text('celebration_quote')->nullable();
            $table->text('celebration_description')->nullable();
            $table->string('celebration_image')->nullable();
            $table->date('date')->unique();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_calendar');
    }
};
