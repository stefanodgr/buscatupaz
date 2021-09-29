<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LevelExtrainfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->text('description');
            $table->string('youtube_link');
        });

        Schema::create('levels_options', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('level_id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
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
            $table->dropColumn('description');
            $table->dropColumn('youtube_link');
        });

        Schema::drop('levels_options');
    }
}
