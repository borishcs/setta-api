<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('notification')->insert([
            'name' => 'bom_dia',
            'title' => 'Bom Dia',
            'subtitle' => 'Bom Dia',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notification')->insert([
            'name' => 'boa_noite',
            'title' => 'Boa Noite',
            'subtitle' => 'Boa Noite',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notification')->insert([
            'name' => 'nova_semana',
            'title' => 'Nova Semana',
            'subtitle' => 'Nova Semana',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notification')->insert([
            'name' => 'novo_mes',
            'title' => 'Novo Mês',
            'subtitle' => 'Novo Mês',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notification')->insert([
            'name' => 'premium_limit_6_horas',
            'title' => 'Plano Premium',
            'subtitle' => 'Faltam 6 horas para expirar seu periodo de testes.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notification')->insert([
            'name' => 'premium_limit_24_horas',
            'title' => 'Plano Premium',
            'subtitle' =>
                'Seu teste premium expirou, assine agora para ter todas funcionalidades.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        /*
        DB::table('notification')->insert([
            'type' => '0', //all
            'token' => '999999',
            'title' => 'Nova Semana',
            'subtitle' => '',
            'destiny' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notification')->insert([
            'type' => '0', //all
            'token' => '777777',
            'title' => 'Novo Mês',
            'subtitle' => '',
            'destiny' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        */
    }
}
