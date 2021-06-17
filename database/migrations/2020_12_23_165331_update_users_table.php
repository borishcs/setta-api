<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function ($table) {
            $table
                ->enum('profession', [
                    'autônomo',
                    'empreendedor',
                    'profissional empregado',
                    'desempregado',
                    'outros',
                ])
                ->nullable();
            $table
                ->enum('interest', [
                    'produtividade',
                    'Desenvolvimento pessoal',
                    'Manutenção do foco',
                    'Criação/manutenção de hábitos',
                    'Gestão de tempo/tarefas do dia',
                    'Nenhuma das anteriores',
                ])
                ->nullable();
            $table
                ->enum('age', [
                    '13 - 17',
                    '18 - 24',
                    '25 - 34',
                    '35 - 44',
                    '45 - 54',
                    '55 - 64',
                    '65+',
                ])
                ->nullable();
            $table->timestamp('terms_of_use')->useCurrent();
            $table->dropColumn('born_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('profession');
            $table->dropColumn('interest');
            $table->dropColumn('age');
            $table->dropColumn('terms_of_use');
            $table->dropColumn('born_at');
        });
    }
}
