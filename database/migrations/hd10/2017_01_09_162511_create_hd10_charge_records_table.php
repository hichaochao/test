<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHd10ChargeRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hd10_charge_records', function (Blueprint $table) {
            $table->uuid('id',32)->comment('充电记录ID');

            $table->uuid('evse_id',32)->comment('充电桩ID');
            $table->string('code',8)->comment('充电桩编号');
            $table->string('monitor_code',32)->default('')->comment('monitor server的充电桩编号');

            $table->uuid('order_id',32)->default('')->comment('monitor充电单号');
            $table->integer('evse_order_id')->unsigned()->default(0)->commnet('桩一次充电的用户信息标识');

            $table->timestamp('start_time')->nullable()->comment('启动时间,timestamp');
            $table->timestamp('end_time')->nullable()->comment('停止时间,timestamp');
            $table->integer('charged_power')->default(0)->comment('电量,单位:wh');
            $table->integer('duration')->default(0)->comment('时长,单位:s');
            $table->integer('fee')->default(0)->comment('金额,单位:分');
            $table->text('formatted_power')->comment('格式化后的数据["time"=>"","duration"=>"","power"=>""]');

            $table->boolean('is_billing')->default(0)->comment('是否计费:0计费,1不计费');
            $table->tinyInteger('start_type',FALSE,TRUE)->default(0)->comment('启动模式：0、立即；1、定时');
            $table->integer('start_args',FALSE,TRUE)->default(0)->comment('启动模式参数：立即：无效；定时：时间，单位：s');
            $table->tinyInteger('charge_type')->default(0)->comment('充电策略：0：充满、1：时长充电、2电量、3金额');
            $table->integer('charge_args')->unsigned()->default(0)->comment('充电参数：对应于策略的，充满：无效；时长：时间长度；');

            $table->tinyInteger('stop_reason')->unsigned()->default(0)->comment('停止原因：
                                                                           0、未知
                                                                           1、正常结束
                                                                           2、过压结束
                                                                           3、过流结束
                                                                           4、漏电结束
                                                                           5、急停结束
                                                                           6、拔枪结束
                                                                           7、断线补偿 ');
            $table->tinyInteger('push_monitor_result')->default(0)->comment('推送monitor结果：0，失败；1：成功');

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
        Schema::dropIfExists('hd10_charge_records');
    }
}
