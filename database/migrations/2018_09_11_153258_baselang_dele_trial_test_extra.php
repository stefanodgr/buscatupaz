<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BaselangDeleTrialTestExtra extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('baselang_dele_trial_test', function (Blueprint $table) {
            $table->string('from')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('baselang_dele_trial_test', function (Blueprint $table) {
            $table->dropColumn('from');
        });
    }
}
