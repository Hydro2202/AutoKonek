<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Official Philippine Standard Geographic Code (PSGC) hierarchy:
        // region > province > city or municipality. Metro Manila has no provinces.
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('psgc_code', 10)->unique();
            $table->string('name', 100);
            $table->string('official_name', 120);
            $table->string('island_group', 10);
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->restrictOnDelete();
            $table->string('psgc_code', 10)->unique();
            $table->string('name', 100);

            $table->index(['region_id', 'name']);
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->restrictOnDelete();
            $table->foreignId('province_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('psgc_code', 10)->unique();
            $table->string('name', 120);
            $table->boolean('is_city')->default(false);

            $table->index(['province_id', 'name']);
            $table->index(['region_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('regions');
    }
};
