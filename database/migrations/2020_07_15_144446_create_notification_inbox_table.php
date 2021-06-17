<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationInboxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_inbox', function (Blueprint $table) {
            $table
                ->uuid('id')
                ->primary()
                ->unique();
            $table->uuid('notification_id')->nullable();
            $table->longText('title');
            $table->longText('subtitle')->nullable();
            $table->uuid('user_id');
            $table->boolean('status')->default(0);
            $table->boolean('visible')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('notification_inbox', function ($table) {
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users');
            $table
                ->foreign('notification_id')
                ->references('id')
                ->on('notification');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_inbox');
    }
}
