<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Loads every Philippine region, province, city and municipality from
 * database/data/ph-locations.json (official PSGC data). Safe to run again.
 */
class PhilippineLocationSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('data/ph-locations.json')), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($data) {
            DB::table('regions')->upsert(
                collect($data['regions'])->map(fn ($r) => [
                    'psgc_code' => $r[0], 'name' => $r[1], 'official_name' => $r[2], 'island_group' => $r[3],
                ])->all(),
                ['psgc_code'], ['name', 'official_name', 'island_group'],
            );
            $regionIds = DB::table('regions')->pluck('id', 'psgc_code');

            DB::table('provinces')->upsert(
                collect($data['provinces'])->map(fn ($p) => [
                    'psgc_code' => $p[0], 'region_id' => $regionIds[$p[1]], 'name' => $p[2],
                ])->all(),
                ['psgc_code'], ['region_id', 'name'],
            );
            $provinceIds = DB::table('provinces')->pluck('id', 'psgc_code');

            foreach (array_chunk($data['cities'], 500) as $chunk) {
                DB::table('cities')->upsert(
                    collect($chunk)->map(fn ($c) => [
                        'psgc_code' => $c[0],
                        'region_id' => $regionIds[$c[1]],
                        'province_id' => $c[2] ? $provinceIds[$c[2]] : null,
                        'name' => $c[3],
                        'is_city' => (bool) $c[4],
                    ])->all(),
                    ['psgc_code'], ['region_id', 'province_id', 'name', 'is_city'],
                );
            }
        });
    }
}
