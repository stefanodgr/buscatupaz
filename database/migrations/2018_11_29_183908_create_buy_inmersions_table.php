<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuyInmersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buy_inmersions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('teacher_id');
            $table->integer('total_price');
            $table->date('inmersion_start');
            $table->date('inmersion_end');
            $table->string('hour_format');
            $table->date('second_payment_date');
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('buy_inmersions');
    }
}
