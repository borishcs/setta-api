<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_changes', function (Blueprint $table) {
            $table
                ->uuid('id')
                ->primary()
                ->unique();

            $table->uuid('user_id');
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users');
            $table->string('subscription_id')->nullable();
            $table->string('subscription_platform')->nullable();
            $table->string('notification_type')->nullable();
            $table->string('purchase_token')->nullable();
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
        Schema::dropIfExists('payment_history');
    }
}
