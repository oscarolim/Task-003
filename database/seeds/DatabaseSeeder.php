<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('apitokens')->insert([
            'token' => Str::random(64),
            'valid' => true,
        ]);

        DB::table('armaments')->insert([
            'title' => 'Ion Canons'
        ]);

        DB::table('armaments')->insert([
            'title' => 'Turbo Laser'
        ]);

        DB::table('armaments')->insert([
            'title' => 'Tractor Beam'
        ]);
    }
}
