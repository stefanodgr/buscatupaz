<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ActiveDeleTrialExtra extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('active_dele_trial', function (Blueprint $table) {
            $table->integer('charge_dollar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('active_dele_trial', function (Blueprint $table) {
            $table->dropColumn('charge_dollar');
        });
    }
}
