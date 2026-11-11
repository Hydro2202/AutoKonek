<?php

use App\Support\SqliteCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('establishment_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('rating');
            $table->text('comment');
            $table->timestamps();

            $table->unique(['user_id', 'establishment_id']);
            $table->index('establishment_id');
        });

        SqliteCheck::add('reviews', 'reviews_rating_check', 'NEW.rating BETWEEN 1 AND 5', 'A rating must be between 1 and 5.');
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
