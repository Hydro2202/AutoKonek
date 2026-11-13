<?php

use App\Support\SqliteCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Weekly pump prices per fuel. The Department of Energy (DOE) announces price
     * adjustments that take effect every Tuesday; each row is one fuel's price for
     * the week starting on "effective_on".
     */
    public function up(): void
    {
        Schema::create('fuel_prices', function (Blueprint $table) {
            $table->id();
            $table->string('fuel', 20);
            $table->date('effective_on');
            $table->decimal('price', 8, 2);
            // The DOE adjustment that produced this price. NULL for the first week on record.
            $table->decimal('change', 8, 2)->nullable();
            $table->string('source', 255)->nullable();
            $table->timestamps();

            $table->unique(['fuel', 'effective_on']);
        });

        SqliteCheck::add('fuel_prices', 'fuel_prices_fuel_check', "NEW.fuel IN ('gasoline', 'diesel', 'kerosene')", 'Fuel must be gasoline, diesel or kerosene.');
        SqliteCheck::add('fuel_prices', 'fuel_prices_price_check', 'NEW.price > 0', 'A fuel price must be more than zero.');
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_prices');
    }
};
