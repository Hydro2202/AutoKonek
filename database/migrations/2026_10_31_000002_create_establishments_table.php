<?php

use App\Support\SqliteCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('establishments', function (Blueprint $table) {
            $table->id();
            // Deleting an owner removes their listings; a category or place in use cannot be deleted.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('city_id')->constrained('cities')->restrictOnDelete();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->text('description');
            $table->string('address', 255);
            $table->string('barangay', 100)->nullable();
            // GPS position, used for map directions and "near me" sorting. Optional.
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            // Set only for listings created from OpenStreetMap data, for example "node/123". Used for attribution.
            $table->string('osm_ref', 40)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('contact_email')->nullable();
            $table->text('opening_hours')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'category_id']);
            $table->index('city_id');
        });

        SqliteCheck::add('establishments', 'establishments_status_check', "NEW.status IN ('pending', 'approved', 'rejected')", 'A listing status must be pending, approved or rejected.');
        // Latitude and longitude are saved together, and must be real coordinates.
        SqliteCheck::add(
            'establishments',
            'establishments_coordinates_check',
            '(NEW.latitude IS NULL AND NEW.longitude IS NULL) OR (NEW.latitude IS NOT NULL AND NEW.longitude IS NOT NULL AND NEW.latitude BETWEEN -90 AND 90 AND NEW.longitude BETWEEN -180 AND 180)',
            'Latitude and longitude must be saved together and be real coordinates.',
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('establishments');
    }
};
