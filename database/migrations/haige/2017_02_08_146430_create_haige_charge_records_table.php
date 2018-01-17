<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHaiGeChargeRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('haige_charge_records', function (Blueprint $table) {

            //$table->uuid('id')->comment('电桩充电记录ID');
            $table->string('charge_records_id',40)->default("")->commnet('电桩充电记录ID');

            //桩数据
            $table->string('evse_id',40)->default("")->commnet('充电桩ID');
            $table->string('evse_code',40)->default("")->commnet('充电桩编号');


            //充电口数据
            $table->tinyInteger('port_type')->unsigned()->default(0)->comment('充电枪位置类型');
            $table->tinyInteger('port_number')->unsigned()->default(0)->comment('充电枪口号');
            $table->string('port_id',40)->default("")->commnet('充电抢ID');
            $table->string('monitor_code',40)->default("")->commnet('monitor充电桩编号');

            //monitor启动信息
            $table->uuid('order_id')->nullable()->comment('订单id');
            $table->string('evse_order_id')->nullable()->comment('用卡号／用户标识，本次充电的唯一标识，由协议生成');
            $table->tinyInteger('charge_type')->unsigned()->default(0)->comment('充电策略');
            $table->integer('charge_args')->unsigned()->default(0)->comment('充电策略参数');
            $table->string('card_id',40)->default("")->commnet('充电卡号');

            //充电信息
            $table->tinyInteger('start_soc')->unsigned()->default(0)->comment('开始SOC');
            $table->tinyInteger('end_soc')->unsigned()->default(0)->comment('结束SOC');
            $table->integer('duration')->unsigned()->default(0)->comment('充电时长，单位：s');
            $table->timestamp('start_time')->nullable()->commnet('充电开始时间');
            $table->timestamp('end_time')->nullable()->commnet('充电结束时间');
            $table->integer('stop_reason')->unsigned()->default(0)->comment('结束原因');
            $table->float('charged_power')->default(0)->comment('本次充电电量（单位:kwh）');
            $table->float('meter_before')->default(0)->comment('充电前电表读数，单位：kwh');
            $table->float('meter_after')->default(0)->comment('充电后电表读数，单位：kwh');
            $table->integer('charged_fee')->unsigned()->default(0)->comment('本次充电金额（单位：分）');
            $table->integer('card_balance_before')->default(0)->comment('充电前卡余额（单位：1分）');
            $table->string('vin',17)->default('')->comment('车辆VIN');
            $table->string('plate_number',8)->default('')->comment('车牌号');
            $table->string('times_power',2048)->default("")->commnet('时段电量json字符串，时间单位：s，电量单位：kwh');
            $table->tinyInteger('start_type')->unsigned()->default(0)->comment('启动方式');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('haige_charge_records');
    }
}
