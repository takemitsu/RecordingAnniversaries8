<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = DB::table('users')->orderBy('id')->first();

        foreach (range(1,4) as $index) {
            DB::table('entities')->insert([
                'user_id' => $user->id,
                'name' => fake()->sentence(2),
                'desc' => fake()->sentence(),
            ]);
        }
    }
}
