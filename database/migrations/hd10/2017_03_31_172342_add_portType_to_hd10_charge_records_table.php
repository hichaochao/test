<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPortTypeToHd10ChargeRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hd10_charge_records',function (Blueprint $table) {
            $table->tinyInteger('port_type')->unsigned()->default(0)->after('monitor_code')->comment('充电枪类型：1：直流 ；2、交流 ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hd10_charge_records', function($table) {
            $table->dropColumn('port_type');
        });
    }
}
