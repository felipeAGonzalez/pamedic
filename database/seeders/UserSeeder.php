<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Soporte',
            'last_name_one' => 'pamedic',
            'last_name_two' => 'medicpa',
            'profesional_id'=> '138348484',
            'position' => 'ROOT',
            'email' => 'pamedic@gmail.com',
            'password' => Hash::make('apocalipsis'),
        ]);
    }
}
