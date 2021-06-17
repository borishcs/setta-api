<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    use \App\Http\Traits\UsesUuid;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tags')->insert([
            'id' => 'cbeee2be-57f0-427c-aeb6-cca7459fe2d8',
            'title' => 'Bem-estar',
            'created_at' => now(0),
            'updated_at' => now(0),
        ]);

        DB::table('tags')->insert([
            'id' => 'cf1d7996-7e54-4807-b358-af0f3f6fd43c',
            'title' => 'Trabalho',
            'created_at' => now(0),
            'updated_at' => now(0),
        ]);

        DB::table('tags')->insert([
            'id' => 'd71adfb4-90da-444a-b2a6-cb3bd8863f50',
            'title' => 'Criatividade',
            'created_at' => now(0),
            'updated_at' => now(0),
        ]);

        DB::table('tags')->insert([
            'id' => '792d8170-ebac-444c-8be1-554cd2620271',
            'title' => 'Estudos',
            'created_at' => now(0),
            'updated_at' => now(0),
        ]);
    }
}
