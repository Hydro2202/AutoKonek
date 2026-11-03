<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('establishments', function (Blueprint $table) {
            $table->id();
            // Deleting an owner removes their listings; a category in use cannot be deleted.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->text('description');
            $table->string('address', 255);
            $table->string('city', 100);
            $table->string('municipality', 100)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('contact_email')->nullable();
            $table->text('opening_hours')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'category_id']);
            $table->index('city');
        });

        DB::statement("ALTER TABLE establishments ADD CONSTRAINT establishments_status_check CHECK (status IN ('pending', 'approved', 'rejected'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('establishments');
    }
};
