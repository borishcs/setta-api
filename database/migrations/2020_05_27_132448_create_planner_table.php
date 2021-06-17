<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlannerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('planner', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->uuid('user_id')->nullable();
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->uuid('task_id')->nullable();
            $table
                ->foreign('task_id')
                ->references('id')
                ->on('tasks');

            $table->integer('order');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('planner');
    }
}
