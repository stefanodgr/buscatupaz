<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DescriptionNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->text('description')->unsigned()->nullable()->change();
            $table->text('youtube_link')->unsigned()->nullable()->change();
            $table->text('course_id')->unsigned()->nullable()->change();
        });


        Schema::table('lessons', function (Blueprint $table) {
            $table->text('options')->unsigned()->nullable()->change();
            $table->text('level_id')->unsigned()->nullable()->change();
        });



    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
