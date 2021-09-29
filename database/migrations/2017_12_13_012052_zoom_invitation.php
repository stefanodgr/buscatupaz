<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ZoomInvitation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('classes', function (Blueprint $table) {
            $column=Schema::hasColumn('classes', 'zoom_invitation');
            if (!$column) {
                $table->integer('zoom_invitation')->default(0);
            }


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('classes', function (Blueprint $table) {
            $column=Schema::hasColumn('classes', 'zoom_invitation');
            if ($column) {
                $table->dropColumn('zoom_invitation');
            }

        });
    }
}
