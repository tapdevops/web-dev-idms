<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVHistoryStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        \DB::unprepared("DROP VIEW IF EXISTS v_list_history_status");
        DB::unprepared(file_get_contents(__DIR__. '/../sql/v_list_history_status.sql'));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        \DB::unprepared("DROP VIEW IF EXISTS v_list_history_status");
        DB::unprepared(file_get_contents(__DIR__. '/../sql/v_list_history_status.sql'));
    }
}
