<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHd10ChargeOrderMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hd10_charge_order_mappings', function (Blueprint $table) {
            $table->uuid('id')->comment("映射id");
            $table->uuid('evse_id')->comment("充电桩id");
            $table->string('code',8)->comment("充电桩code");
            $table->string('monitor_code',36)->comment("monitorcode");
            $table->uuid('order_id')->comment("充电订单id");
            $table->integer('evse_order_id')->unsigned()->default(0)->comment("发给充电桩的订单ID");

            $table->boolean('is_billing')->default(0)->comment('是否计费:0计费,1不计费');
            $table->tinyInteger('start_type',FALSE,TRUE)->default(0)->comment('启动模式：0、立即；1、定时');
            $table->integer('start_args',FALSE,TRUE)->default(0)->comment('启动模式参数：立即：无效；定时：时间，单位：s');
            $table->tinyInteger('charge_type',FALSE,TRUE)->default(0)->comment('充电策略：0：充满、1：时长充电、2电量、3金额');
            $table->integer('charge_args',FALSE,TRUE)->default(0)->comment('充电参数：
                                                                                //充满模式：无效
                                                                                //时长模式：时间，单位秒；
                                                                                //电量模式：电量，单位：度，精确到：0.01度；
                                                                                //金额模式：钱数，单位：元，精确到：0.01元；');
            $table->integer('user_balance',FALSE,TRUE)->default(0)->comment('用户余额：单位：分');
            $table->tinyInteger('is_start_success')->unsigned()->default(0)->comment("是否启动成功，0：失败（未成功），1：成功");
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hd10_charge_order_mappings');
    }
}
