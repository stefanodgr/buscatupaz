<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TeacherEvaluation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_evaluation', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('teacher_id');
            $table->integer('evaluation');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('evaluation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users_evaluation');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('evaluation');
        });
    }
}
