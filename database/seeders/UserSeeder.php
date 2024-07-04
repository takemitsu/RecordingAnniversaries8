<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = "user@example.com";
        DB::table('users')->where('email', $email)->delete();
        DB::table('users')->insert([
            'name' => 'user',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);
    }
}
