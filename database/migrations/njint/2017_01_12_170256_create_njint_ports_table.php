<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNjintPortsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('njint_ports', function (Blueprint $table) {
            $table->uuid('id')->comment('充电枪ID');
            $table->uuid('evse_id')->comment('充电桩ID');
            $table->string('worker_id',36)->default('')->comment("工作id");
            $table->string('code',36)->comment('充电桩编号');
            $table->integer('port_number')->unsigned()->comment('枪口号');
            $table->string('monitor_code',36)->commnet('monitor充电桩编号');
            $table->tinyInteger('port_type')->unsigned()->default(0)->comment('充电枪类型：1：直流 ；2、交流 ');
            $table->tinyInteger('heartbeat_period')->unsigned()->default(3)->comment('心跳上报周期');

            //临时数据
            $table->tinyInteger('sequence')->unsigned()->default(0)->comment('序列号');



            //启动数据
            $table->uuid('order_id')->default("")->commnet('订单id');
            $table->string('evse_order_id',32)->default("")->commnet('用卡号／用户标识，本次充电的唯一标识，由协议生成');
            
            $table->tinyInteger('start_type',FALSE,TRUE)->default(0)->comment('启动模式：0、立即；1、定时');
            $table->integer('start_args',FALSE,TRUE)->default(0)->comment('启动模式参数：立即：无效；定时：时间，单位：s');
            $table->tinyInteger('charge_type',FALSE,TRUE)->default(0)->comment('充电策略：0：充满、1：时长充电、2电量、3金额');
            $table->tinyInteger('charge_args',FALSE,TRUE)->default(0)->comment('充电参数：
                                                                                //充满模式：无效
                                                                                //时长模式：时间，单位秒；
                                                                                //电量模式：电量，单位：度，精确到：0.01度；
                                                                                //金额模式：钱数，单位：元，精确到：0.01元；');
            $table->integer('user_balance',FALSE,TRUE)->default(0)->comment('用户余额：单位：分');



            //状态数据
            $table->tinyInteger('online',FALSE,TRUE)->default(0)->comment('充电枪联网状态：0：断网；1：联网');
            $table->tinyInteger('is_car_connected')->default(0)->comment('车辆是否连接:0,未连接；1，已连接');
            $table->tinyInteger('is_charging')->default(0)->comment('是否充电中:0,空闲；1，充电中');
            $table->integer('warning_status')->default(0)->comment('告警状态:0：正常；1，过压；2，过流；3，漏电；4，急停；等等见协议附录');
            $table->timestamp('last_update_status_time')->nullable()->comment('最后更新充电状态时间');


            $table->tinyInteger('task_status')->unsigned()->default(0)->comment('任务状态：0，无；1、启动充电中；2、停止充电中');

            //实时充电数据
            $table->timestamp('start_time')->nullable()->comment('启动充电时间');

            $table->integer('charged_power')->default(0)->commet('本次充电累计充电电量,单位：wh');
            $table->integer('fee')->unsigned()->default(0)->commet('本次充电累计充电费用,单位：分');
            $table->integer('duration')->unsigned()->default(0)->comment('充电时长,单位：s');

            $table->integer('power')->default(0)->comment('充电功率：w');
            $table->tinyInteger('current_soc',FALSE,TRUE)->default(0)->comment('当前SOC');

            $table->integer('left_time')->unsigned()->default(0)->comment('剩余充电时长,单位：m');
            $table->integer('ac_a_voltage')->unsigned()->default(0)->comment( '交流A相充电电压');
            $table->integer('ac_a_current')->unsigned()->default(0)->comment('交流A相充电电流');
            $table->integer('ac_b_voltage')->unsigned()->default(0)->comment( '交流B相充电电压');
            $table->integer('ac_b_current')->unsigned()->default(0)->comment('交流B相充电电流');
            $table->integer('ac_c_voltage')->unsigned()->default(0)->comment( '交流C相充电电压');
            $table->integer('ac_c_current')->unsigned()->default(0)->comment('交流C相充电电流');

            $table->integer('dc_voltage')->unsigned()->default(0)->comment('直流充电电压');
            $table->integer('dc_current')->unsigned()->default(0)->comment('直流充电电流');

            $table->timestamp('last_update_charge_info_time')->nullable()->comment('最后更新充电信息的时间');

            //bms信息
            $table->tinyInteger('bms_mode')->unsigned()->default(0)->comment('bms充电模式：1：恒压，2：恒流');
            $table->integer('bms_voltage')->unsigned()->default(0)->comment('bms需求电压');
            $table->integer('bms_current')->unsigned()->default(0)->comment('bms需求电流');



            //    'N_CURRENT_CHARGE_FEE'          => ['type' => 'int', 'constraint' => 10, 'null' => TRUE, 'default' => 0,  'comment' => '本次充电累计充电费用 单位0.01元'],
            //    'N_INITIAL_SOC'          => ['type' => 'int', 'constraint' => 5, 'null' => TRUE, 'default' => 0,  'comment' => '初始SOC %'],
            //    'N_CURRENT_SOC'          => ['type' => 'int', 'constraint' => 5, 'null' => TRUE, 'default' => 0,  'comment' => '当前SOC %'],
            //    'C_OFFLINE_CHARGE_AMOUNT'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'default' => 0,  'comment' => '离线可充电电量'],
            //    'N_RESE_OVERTIME'          => ['type' => 'int', 'constraint' => 5, 'null' => TRUE, 'comment' => '预约超时时间'],
            //    'D_RESE_TIME'          => ['type' => 'datetime', 'null' => TRUE, 'comment' => '预约/定时启动时间'],
            //    'N_CHARGE_STRATEGY_PARA'       => ['type' => 'int', 'constraint' => 10, 'null' => TRUE, 'comment' => '充电策略参数'],
            //
            //    'D_LAST_CHARGE_TIME'     => ['type' => 'datetime', 'null' => TRUE, 'comment' => '最近一次充电时间'],
            //    'D_LAST_START_TIME'     => ['type' => 'datetime', 'null' => TRUE, 'comment' => '最近一次启动时间'],
            //
            //
            //    'N_NOW_CHARGED_POWER'      => ['type' => 'int',   'null' => TRUE, 'default' => 0,  'comment' => '当前充电电量0.01度'],
            //
            //
            //    'N_POWER'      => ['type' => 'int', 'constraint' => 10, 'null' => TRUE, 'default' => 0,  'default' => 0, 'comment' => '充电功率 0.1kW'],


            //    'C_PROTOCOL_NAME' => ['type' => 'varchar', 'constraint' => 64, 'null' => TRUE, 'comment' => '充电枪所用协议名称'],



            //    'N_OFFNET_MARK'       => ['type' => 'int', 'unsigned' => TRUE, 'null' => TRUE, 'default' => 0, 'comment' => '断网充电标志'],

            //    'N_BCS_CHARGE_STATUS'       => ['type' => 'int', 'unsigned' => TRUE, 'null' => TRUE, 'default' => 0, 'comment' => 'BCS电池充电总状态'],
            //
            //
            //
            //
            //    'C_BEM_INFO'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => 'BEM报文'],
            //    'C_BSD_BMS_DATA'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => 'BSD BMS统计数据'],
            //    'C_BST_STOP_CHARGE'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => 'BST中止充电'],
            //    'C_BSM_CHARGE_STATUS_INFO'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => 'BSM动力蓄电池状态信息'],
            //    'C_VBI_INFO'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => 'VBI报文'],
            //    'C_BRM_CAR_IDEN_INFO'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => 'BRM车辆辨识报文'],

            //    'N_CURRENT_AMMETER_READING'          => ['type' => 'int', 'constraint' => 10, 'null' => TRUE,'default' => 0, 'comment' => '当前电表读数'],

            //    'N_BMS_CHARGING_MODE'         => ['type' => 'tinyint', 'constraint' => 5, 'null' => TRUE, 'default' => 0, 'comment' => 'BMS充电模式'],
            //    'N_BEFORE_CURRENT_AMMETER_READING' => ['type' => 'int', 'constraint' => 10, 'null' => TRUE, 'default' => 0, 'comment' => '充电前电表读数'],
            //    'N_RESERVATION_FLAG' => ['type' => 'tinyint', 'constraint' => 5, 'null' => TRUE, 'default' => 0, 'comment' => '预约标志'],
            //    'C_APPOINTMENT_CARD_NUMBER' => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '充电/预约卡号'],
            //    'N_CHARGING_CARD_BALANCE' => ['type' => 'int', 'constraint' => 10, 'null' => TRUE, 'default' => 0,  'comment' => '充电前卡余额 单位0.01元'],
            //    'N_UPGRADE_MODE' => ['type' => 'tinyint','constraint' => 10, 'null' => TRUE, 'comment' => '升级模式'],
            //
            //
            //
            //
            //

            //
            //

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
        Schema::dropIfExists('njint_ports');
    }
}
