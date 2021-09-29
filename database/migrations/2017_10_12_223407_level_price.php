<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LevelPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->integer('price')->default(0);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->integer('is_free')->default(1);
        });

        Schema::create('users_levels', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('level_id')->unsigned();
            $table->integer('paid');
            $table->string('transaction_id')->nullable();

            $table->primary(['user_id', 'level_id']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->dropColumn('price');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('is_free');
        });

        Schema::drop('users_levels');
    }
}
