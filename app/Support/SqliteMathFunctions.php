<?php

namespace App\Support;

use Pdo\Sqlite;

/**
 * The SQLite library bundled with PHP is usually built without its math functions,
 * so the "near me" distance query (haversine formula) would fail. This registers
 * them on the connection, backed by PHP's own math functions.
 */
class SqliteMathFunctions
{
    /** @var array<string, callable(float): float> */
    private const SINGLE_ARGUMENT = [
        'sin' => 'sin',
        'cos' => 'cos',
        'asin' => 'asin',
        'sqrt' => 'sqrt',
        'radians' => 'deg2rad',
    ];

    public static function register(Sqlite $pdo): void
    {
        foreach (self::SINGLE_ARGUMENT as $name => $function) {
            $pdo->createFunction(
                $name,
                fn (mixed $value): ?float => $value === null ? null : $function((float) $value),
                1,
                Sqlite::DETERMINISTIC,
            );
        }

        $pdo->createFunction(
            'power',
            fn (mixed $base, mixed $exponent): ?float => $base === null || $exponent === null ? null : ((float) $base) ** ((float) $exponent),
            2,
            Sqlite::DETERMINISTIC,
        );
    }
}
