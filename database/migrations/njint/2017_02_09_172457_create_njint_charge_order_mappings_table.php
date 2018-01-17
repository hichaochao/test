<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNjintChargeOrderMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('njint_charge_order_mappings', function (Blueprint $table) {
            $table->uuid('id')->comment("映射id");
            $table->uuid('evse_id')->comment("充电桩id");
            $table->uuid('port_id')->comment("充电抢id");
            $table->string('code',8)->comment("充电桩code");
            $table->string('monitor_code',36)->comment("monitorcode");
            $table->uuid('order_id')->comment("充电订单id");
            $table->string('evse_order_id',32)->default("")->comment("发给充电桩的订单ID");

            $table->tinyInteger('start_type',FALSE,TRUE)->default(0)->comment('启动模式：0、立即；1、定时');
            $table->integer('start_args',FALSE,TRUE)->default(0)->comment('启动模式参数：立即：无效；定时：时间，单位：s');
            $table->tinyInteger('charge_tactics')->default(0)->comment('充电策略：0：充满、1：时长充电、2电量、3金额');
            $table->tinyInteger('charge_args')->default(0)->comment('充电参数：对应于策略的，充满：无效；时长：时间长度；');

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
        Schema::dropIfExists('njint_charge_order_mappings');
    }
}
