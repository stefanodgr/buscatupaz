<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActiveLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('active_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->date('activation_day');
            $table->date('date_to_schedule');
            $table->date('trial_payday');
            $table->string('plan');
            $table->integer('price');
            $table->integer('new_student')->default(0);
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
        Schema::dropIfExists('active_locations');
    }
}
