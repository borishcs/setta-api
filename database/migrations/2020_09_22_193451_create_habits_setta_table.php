<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHabitsSettaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('habits_setta', function (Blueprint $table) {
            $table
                ->uuid('id')
                ->primary()
                ->unique();

            $table->uuid('tag_id')->nullable();
            $table
                ->foreign('tag_id')
                ->references('id')
                ->on('tags');

            $table->string('title');
            $table->longText('note')->nullable();
            $table->longText('image')->nullable();

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
        Schema::dropIfExists('habits_setta');
    }
}
