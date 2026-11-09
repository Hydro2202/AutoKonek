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

            // 1 while a report is open (pending), NULL once it is resolved or dismissed.
            // PostgreSQL treats NULLs in a unique index as distinct values, so the two unique
            // indexes below only apply to open reports: one open report per user per target.
            $table->unsignedTinyInteger('open_flag')->nullable()->storedAs("CASE WHEN status = 'pending' THEN 1 ELSE NULL END");

            $table->index('status');
            $table->unique(['user_id', 'establishment_id', 'open_flag'], 'reports_open_establishment_unique');
            $table->unique(['user_id', 'review_id', 'open_flag'], 'reports_open_review_unique');
        });

        DB::statement("ALTER TABLE reports ADD CONSTRAINT reports_status_check CHECK (status IN ('pending', 'resolved', 'dismissed'))");

        // A report must point at exactly one target: an establishment OR a review.
        DB::unprepared("
            CREATE OR REPLACE FUNCTION reports_single_target_check()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF (NEW.establishment_id IS NULL) = (NEW.review_id IS NULL) THEN
                    RAISE EXCEPTION 'A report needs exactly one target: an establishment or a review.';
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared('
            CREATE TRIGGER reports_single_target_before_insert
            BEFORE INSERT ON reports FOR EACH ROW
            EXECUTE FUNCTION reports_single_target_check();
        ');

        DB::unprepared('
            CREATE TRIGGER reports_single_target_before_update
            BEFORE UPDATE ON reports FOR EACH ROW
            EXECUTE FUNCTION reports_single_target_check();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS reports_single_target_before_update ON reports');
        DB::unprepared('DROP TRIGGER IF EXISTS reports_single_target_before_insert ON reports');
        DB::unprepared('DROP FUNCTION IF EXISTS reports_single_target_check()');
        Schema::dropIfExists('reports');
    }
};
