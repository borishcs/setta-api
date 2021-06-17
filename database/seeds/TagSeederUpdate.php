<?php

use Illuminate\Database\Seeder;

class TagSeederUpdate extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tags')->insert([
            'id' => 'f22797c5-55e3-48a1-84ca-deba420603ab',
            'title' => 'Outro',
            'created_at' => now(0),
            'updated_at' => now(0),
        ]);
    }
}
