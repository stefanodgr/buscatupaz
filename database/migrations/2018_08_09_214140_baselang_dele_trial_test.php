<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BaselangDeleTrialTest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('baselang_dele_trial_test', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('completed');
            $table->date('ends_at_last_subscription');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('baselang_dele_trial_test');
    }
}
