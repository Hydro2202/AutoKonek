<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        DB::statement('ALTER TABLE reviews ADD CONSTRAINT reviews_rating_check CHECK (rating BETWEEN 1 AND 5)');
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
