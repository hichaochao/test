<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNjintEvsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('njint_evses', function (Blueprint $table) {
            $table->uuid('id')->comment('充电桩ID');
            $table->string('code',40)->comment('充电桩编号');

            $table->string('protocol_name',64)->default('')->comment('充电枪所用协议名称');
            $table->tinyInteger('version')->unsigned()->default(0x10)->comment('版本号');

            //状态信息
            $table->tinyInteger('online',FALSE,TRUE)->default(0)->comment('是否联网');
            $table->timestamp('last_update_status_time')->nullable()->comment('最后更新充电状态时间');




            $table->integer('sign_period')->default(1800)->comment('签到时间间隔');
            $table->integer('project_type')->default(0)->comment('充电桩项目类型');
            $table->tinyInteger('backstage_validate',FALSE,TRUE)->default(0)->comment('后台验证');
            $table->integer('timer_report_period')->default(15)->comment('定时上报间隔');
            $table->tinyInteger('heartbeat_period')->unsigned()->default(3)->comment('心跳上报周期');
            $table->integer('heartbeat_overtime_times')->default(3)->comment('心跳包检测超时次数');
            $table->integer('pile_status_report_period')->default(3)->comment('充电桩状态信息报上报周期');
            $table->integer('commu_type')->default(0)->comment('通信模式');
            $table->integer('evse_type')->default(0)->comment('充电桩类型');
            $table->integer('sign_intervals')->default(0)->comment('签到间隔时间');
            $table->integer('operate_inter_para')->default(0)->comment('运行内部变量');
            $table->integer('charge_pwd_validate')->default(0)->comment('充电密码验证(预留)');
            $table->integer('commodity_type')->default(0)->comment('计价策略');
            $table->tinyInteger('port_quantity',FALSE,TRUE)->default(0)->comment('充电枪个数');
            $table->string('chareg_record_times')->default('')->comment('充电记录数量');


            $table->string('start_times')->default('')->comment('启动次数');
            $table->string('evse_software_version')->default('')->comment('充电桩软件版本');
            $table->string('pay_qr_code')->default('')->comment('用户支付二维码');
            $table->string('svc_hot_line2')->default('')->comment('客户服务热线2');
            $table->string('svc_hot_line1')->default('')->comment('客户服务热线1');
            $table->string('qr_code')->default('')->comment('二维码');
            $table->string('mac_address')->default('')->comment('MAC地址');
            $table->string('operator_pwd')->default('')->comment('操作员密码');
            $table->string('admin_pwd')->default('')->comment('管理员密码');
            $table->string('standard_time')->default('')->comment('标准时钟时间');

            $table->string('ad_light_close_minutes')->default('')->comment('广告灯关闭起始分钟');
            $table->string('ad_light_close_hours')->default('')->comment('广告灯关闭起始小时');
            $table->string('ad_light_open_minutes')->default('')->comment('广告灯开启起始分钟');
            $table->string('ad_light_open_hours')->default('')->comment('广告灯开启起始小时');
            $table->string('alltime_charge_rate')->default('')->comment('全时段电费费率');
            $table->string('svc_fee')->default('')->comment('服务费价格');

            $table->string('center_svc_address')->default('')->comment('中心服务器地址');

            $table->integer('max_charge_volt')->default(0)->comment('最高充电电压');

            $table->integer('max_charge_curt')->default(0)->comment('最大充电电流');

            //$table->integer('net_status')->default(0)->comment('联网状态');

            //$table->integer('net_status_update_time')->default(0)->comment('联网状态更新时间');






            ////内部数据
            //$table->smallInteger('sign_period',FALSE,TRUE)->default(1800)->comment('签到时间间隔');
            //$table->smallInteger('project_type',FALSE,TRUE)->default(0)->comment('充电桩项目类型');
            //$table->tinyInteger('backstage_validate',FALSE,TRUE)->default(0)->comment('后台验证');
            //$table->tinyInteger('plate_validate',FALSE,TRUE)->default(0)->comment('车牌验证');
            //$table->tinyInteger('car_vin_bind',FALSE,TRUE)->default(0)->comment('车卡VIN绑定');
            //$table->integer('timer_report_period',FALSE,TRUE)->default(15)->comment('定时上报间隔，单位：s');

            //    'N_HEARTBEAT_REPORT_PERIOD'       => ['type' => 'int', 'unsigned' => TRUE, 'null' => TRUE, 'default' => 3, 'comment' => '心跳上报周期'],
            //    'N_HEARTBEAT_OVERTIME_TIMES'       => ['type' => 'int', 'unsigned' => TRUE, 'null' => TRUE, 'default' => 3, 'comment' => '心跳包检测超时次数'],
            //
            //    'N_PILE_STATUS_REPORT_PERIOD'       => ['type' => 'int', 'unsigned' => TRUE, 'null' => TRUE, 'default' => 3, 'comment' => '充电桩状态信息报上报周期'],
            //    'N_COMMU_TYPE'       => ['type' => 'int', 'unsigned' => TRUE, 'null' => TRUE, 'default' => 0, 'comment' => '通信模式'],
            //    'N_EVSE_TYPE'       => ['type' => 'int', 'unsigned' => TRUE, 'null' => TRUE, 'default' => 0, 'comment' => '充电桩类型'],
            //    'N_DATA_UPLOAD_TYPE'       => ['type' => 'int', 'unsigned' => TRUE, 'null' => TRUE, 'default' => 0, 'comment' => '数据上传模式'],
            //    'N_SIGN_INTERVALS'       => ['type' => 'int', 'unsigned' => TRUE, 'null' => TRUE, 'default' => 0, 'comment' => '签到间隔时间'],
            //    'N_OPERATE_INTER_PARA'       => ['type' => 'int', 'unsigned' => TRUE, 'null' => TRUE, 'default' => 0, 'comment' => '运行内部变量'],
            //    'N_CHARGE_PWD_VALIDATE'       => ['type' => 'int', 'unsigned' => TRUE, 'null' => TRUE, 'default' => 0, 'comment' => '充电密码验证(预留)'],
            //    'N_COMMODITY_TYPE'       => ['type' => 'int', 'unsigned' => TRUE, 'null' => TRUE, 'default' => 0, 'comment' => '计价策略'],
            //
            //

            //
            //
            //
            //
            //    'C_ABRASE_PERCENT'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '擦除完成百分比'],
            //    'C_CHAREG_RECORD_TIMES'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '充电记录数量'],
            //    'C_START_TIMES'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '启动次数'],
            //    'C_EVSE_SOFTWARE_VERSION'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '充电桩软件版本'],
            //    'C_PAY_QR_CODE'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '用户支付二维码'],
            //    'C_SVC_HOT_LINE2'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '客户服务热线2'],
            //    'C_SVC_HOT_LINE1'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '客户服务热线1'],
            //    'C_QR_CODE'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '二维码'],
            //    'C_MAC_ADDRESS'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => 'MAC地址'],
            //    'C_OPERATOR_PWD'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '操作员密码'],
            //    'C_ADMIN_PWD'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '管理员密码'],
            //    'C_STANDARD_TIME'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '标准时钟时间'],
            //    'C_DEBUG_CTRL_DATA'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '调试控制数据'],
            //    'C_AD_LIGHT_CLOSE_MINUTES'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '广告灯关闭起始分钟'],
            //    'C_AD_LIGHT_CLOSE_HOURS'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '广告灯关闭起始小时'],
            //    'C_AD_LIGHT_OPEN_MINUTES'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '广告灯开启起始分钟'],
            //    'C_AD_LIGHT_OPEN_HOURS'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '广告灯开启起始小时'],
            //    'C_ALLTIME_CHARGE_RATE'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '全时段电费费率'],
            //    'C_SVC_FEE'          => ['type' => 'int', 'constraint' => 10, 'null' => TRUE, 'comment' => '服务费价格'],
            //    'C_CENTER_SVC_ADDRESS'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '中心服务器地址'],
            //    'C_CENTER_SVC_PORT'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '中心服务器端口'],
            //    'C_BMS_PROTECT_TEMP'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => 'BMS充电保护温度'],
            //    'C_BMS_PROTECT_VOLT'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => 'BMS单体保护电压'],
            //    'C_CARD_PROTOCOL_NUM'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '充电卡片协议编号'],
            //    'C_PASS_NUM'          => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '通道号'],
            //    'N_MAX_CHAREG_VOLT'          => ['type' => 'int', 'constraint' => 10, 'null' => TRUE, 'default' => 0,  'comment' => '最高充电电压'],
            //    'N_MAX_CHAREG_CURT'         => ['type' => 'int', 'constraint' => 10, 'null' => TRUE, 'default' => 0,  'comment' => '最大充电电流'],


            //
            //    //增加
            //
            //    'C_CARD_READER_TYPE'   => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '读卡器类型'],
            //    'C_CARD_READER_BAUD_RATE'   => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '读卡器波特率'],
                //'C_CARD_READER_BAUD_RATE'   => ['type' => 'varchar', 'constraint' => 255, 'null' => TRUE, 'comment' => '读卡器波特率'],


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
        Schema::dropIfExists('njint_evses');
    }
}
