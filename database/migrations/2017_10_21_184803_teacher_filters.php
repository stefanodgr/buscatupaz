<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TeacherFilters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_interests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('interest_id');
        });

        Schema::create('interests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('interests');
            $table->string('gender');
            $table->string('teaching_style');
            $table->string('strongest_with');
            $table->string('english_level');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users_interests');
        Schema::drop('interests');

        Schema::table('users', function (Blueprint $table) {
            $table->string('interests')->nullable();
            $table->dropColumn('gender');
            $table->dropColumn('teaching_style');
            $table->dropColumn('strongest_with');
            $table->dropColumn('english_level');
        });
    }
}
