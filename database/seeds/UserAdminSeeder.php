<?php

use App\User;
use Illuminate\Database\Seeder;

class UserAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(User::class)->create([
            'name' => 'Setta',
            'email' => 'ti@setta.co',
            'password' => '123456',
        ]);
    }
}
