<?php

use App\Support\SqliteCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

            // 1 while a report is open (pending), NULL once it is resolved or dismissed.
            // SQLite treats NULLs in a unique index as distinct values, so the two unique
            // indexes below only apply to open reports: one open report per user per target.
            $table->unsignedTinyInteger('open_flag')->nullable()->storedAs("CASE WHEN status = 'pending' THEN 1 ELSE NULL END");

            $table->index('status');
            $table->unique(['user_id', 'establishment_id', 'open_flag'], 'reports_open_establishment_unique');
            $table->unique(['user_id', 'review_id', 'open_flag'], 'reports_open_review_unique');
        });

        SqliteCheck::add('reports', 'reports_status_check', "NEW.status IN ('pending', 'resolved', 'dismissed')", 'A report status must be pending, resolved or dismissed.');

        // A report must point at exactly one target: an establishment OR a review.
        SqliteCheck::add('reports', 'reports_single_target_check', '(NEW.establishment_id IS NULL) <> (NEW.review_id IS NULL)', 'A report needs exactly one target: an establishment or a review.');
    }

    public function down(): void
    {
        // Dropping the table also drops its triggers.
        Schema::dropIfExists('reports');
    }
};
