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
        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->date('date');
            $table->decimal('weight', 5, 2);
            $table->integer('sleep_hours');
            $table->integer('sleep_minutes');
            $table->json('meals')->nullable();
            $table->text('meal_detail')->nullable();
            $table->json('meal_photos')->nullable();
            $table->json('exercises')->nullable();
            $table->text('exercise_detail')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};
