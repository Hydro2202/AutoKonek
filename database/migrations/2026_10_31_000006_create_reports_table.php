<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('establishment_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('review_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('reason', 50);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        // A report must point at exactly one target: an establishment OR a review.
        DB::statement('ALTER TABLE reports ADD CONSTRAINT reports_single_target_check CHECK ((establishment_id IS NOT NULL AND review_id IS NULL) OR (establishment_id IS NULL AND review_id IS NOT NULL))');
        DB::statement("ALTER TABLE reports ADD CONSTRAINT reports_status_check CHECK (status IN ('pending', 'resolved', 'dismissed'))");

        // One open report per user per target.
        DB::statement("CREATE UNIQUE INDEX reports_open_establishment_unique ON reports (user_id, establishment_id) WHERE status = 'pending' AND establishment_id IS NOT NULL");
        DB::statement("CREATE UNIQUE INDEX reports_open_review_unique ON reports (user_id, review_id) WHERE status = 'pending' AND review_id IS NOT NULL");
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
