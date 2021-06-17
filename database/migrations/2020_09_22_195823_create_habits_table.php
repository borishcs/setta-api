<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHabitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('habits', function (Blueprint $table) {
            $table
                ->uuid('id')
                ->primary()
                ->unique();

            $table->uuid('habit_setta_id')->nullable();
            $table
                ->foreign('habit_setta_id')
                ->references('id')
                ->on('habits_setta');

            $table->uuid('user_id');
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->uuid('tag_id')->nullable();
            $table
                ->foreign('tag_id')
                ->references('id')
                ->on('tags');

            $table->enum('period', [
                'sunrise',
                'morning',
                'afternoon',
                'night',
                'undefined',
            ]);
            $table->string('title');
            $table->longText('note')->nullable();
            $table->json('repeat');
            $table->timestamp('final_date')->nullable();
            $table->timestamp('last_completed')->nullable();
            $table->integer('streak')->nullable();
            $table->integer('max_streak')->nullable();

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
        //Schema::dropIfExists('habit');
        DB::statement('drop table habits cascade');
    }
}
