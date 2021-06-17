<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HabitsSettaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('habits_setta')->insert([
            'id' => '1000d6ec-e5c2-47ea-9b2c-0bd361e321f9',
            'tag_id' => 'cbeee2be-57f0-427c-aeb6-cca7459fe2d8',
            'title' => 'Meditação',
            'note' => 'Rotina 15min meditação diária',
            'image' => 'meditacao.jpg',
            'created_at' => now(0),
            'updated_at' => now(0),
        ]);

        DB::table('habits_setta')->insert([
            'id' => 'a3dcb0ef-4e7d-442c-81d7-110654fb8167',
            'tag_id' => 'cbeee2be-57f0-427c-aeb6-cca7459fe2d8',
            'title' => 'Podcast',
            'note' => 'Ouvir podcast sobre design Thinking',
            'image' => 'podcast.jpg',
            'created_at' => now(0),
            'updated_at' => now(0),
        ]);
    }
}
