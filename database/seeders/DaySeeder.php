<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $entities = DB::table('entities')->orderBy('id')->take(3)->get();
        foreach ($entities as $entity) {
            foreach (range(1, $entity->id) as $index) {
                DB::table('days')->insert([
                    'entity_id' => $entity->id,
                    'name' => fake()->sentence(3),
                    'desc' => fake()->sentence(),
                    'anniv_at' => fake()->date(),
                ]);
            }
        }
    }
}
