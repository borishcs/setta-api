<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table
                ->uuid('id')
                ->primary()
                ->unique();

            $table->uuid('user_id');
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->string('parent_id')->nullable();

            $table->uuid('tag_id')->nullable();
            $table
                ->foreign('tag_id')
                ->references('id')
                ->on('tags');

            $table
                ->enum('period', [
                    'sunrise',
                    'morning',
                    'afternoon',
                    'night',
                    'undefined',
                ])
                ->nullable();
            $table->uuid('habit_id')->nullable();
            $table->boolean('schedule')->nullable();
            $table->string('title');
            $table->longText('note')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('order')->nullable();
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
        Schema::dropIfExists('tasks');
    }
}
