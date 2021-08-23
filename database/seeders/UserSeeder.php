<?php

namespace Database\Seeders;

use DB;
use Hash;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Andres Pinto',
            'email' => 'andrespintocamara@gmail.com',
            'password' => Hash::make('password'),
        ]);

    }
}
