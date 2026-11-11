<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * SQLite only accepts CHECK constraints inside CREATE TABLE, which Laravel's schema
 * builder cannot write. This adds the same rule as a pair of triggers instead: any
 * INSERT or UPDATE that breaks it is aborted, just like a CHECK constraint.
 */
class SqliteCheck
{
    /**
     * @param  string  $condition  An SQL condition that must hold for every row. Refer to columns as NEW.column.
     */
    public static function add(string $table, string $name, string $condition, string $message): void
    {
        $message = str_replace("'", "''", $message);

        foreach (['INSERT', 'UPDATE'] as $event) {
            $trigger = $name.'_'.strtolower($event);

            DB::unprepared("
                CREATE TRIGGER {$trigger} BEFORE {$event} ON {$table}
                FOR EACH ROW WHEN NOT ({$condition})
                BEGIN
                    SELECT RAISE(ABORT, '{$message}');
                END
            ");
        }
    }
}
