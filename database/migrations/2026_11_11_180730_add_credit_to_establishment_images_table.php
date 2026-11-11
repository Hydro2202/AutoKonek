<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Photos taken from free sources (for example Wikimedia Commons) must name their
     * author and licence. Photos uploaded by owners leave these empty.
     */
    public function up(): void
    {
        Schema::table('establishment_images', function (Blueprint $table) {
            $table->string('credit', 255)->nullable()->after('is_primary');
            $table->string('credit_url', 500)->nullable()->after('credit');
        });
    }

    public function down(): void
    {
        Schema::table('establishment_images', function (Blueprint $table) {
            $table->dropColumn(['credit', 'credit_url']);
        });
    }
};
