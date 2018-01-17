<?php
/**
 * Created by PhpStorm.
 * User: lingfeng.chen
 * Date: 2017/2/10
 * Time: 下午6:15
 */

namespace Wormhole\Protocols;


use Wormhole\Validators\RealtimeChargeInfoValidator;
use Wormhole\Validators\StartChargeValidator;
use Wormhole\Validators\StopChargeValidator;

interface ApiInterface
{
    /**
     * 启动充电，启动业务：
     * 0、判断参数是否ok，交给validator 就好了；
     * 1、判断当前monitor_code是否存在，不存在返回提示信息；
     * 2、判断当前monitor_code对应的桩是否在充电中，充电中返回提示信息；
     * 3、根据order_id 生成 evse_order_id，携带启动相关数据，存储进charge_order_map表；
     * 4、更新数据进充电口（充电状态，充电参数等），准备进行启动充电；
     * 5、调用ProtocolController启动充电；
     * 6、发送成功，返回命令已经下发（status = 200）；否则返回发送失败 （status = 500 ）
     * @param StartChargeValidator $chargeValidator
     * @param string $hash 暂时没用
     * @return mixed
     */
    public function startCharge(StartChargeValidator $chargeValidator, $hash);

    /**
     * 实时充电数据获取，业务逻辑：
     * 0、判断参数是否ok，交给validator 就好了；
     * 1、判断当前order_id，是否有效；无效返回；
     * 2、获取当前充电口的数据，将数据返回；
     * @param RealtimeChargeInfoValidator $validator
     * @param string $hash 暂时没用
     * @return mixed
     * [
     *  'order_id' => $port->order_id,
     *   'evse_code' => $port->monitor_code,
     *   'start_time=>' => $port->start_time,
     *   'duration' => $port->duration,
     *   'power' => $port->power/1000,
     *   'charged_power' => $port->charged_power/1000,
     *
     *   'charge_volt_a' => $port->ac_voltage_a/1000,            //交流A相充电电压，单位：V
     *   'charge_curt_a' => $port->ac_current_a/1000,             //交流A相充电电流，单位：A
     *   'charge_volt_b' => $port->ac_voltage_b/1000,            //交流B相充电电压，单位：V
     *   'charge_curt_b' => $port->ac_current_b/1000,            //交流B相充电电流，单位：A
     *   'charge_volt_c' => $port->ac_voltage_c/1000,             //交流C相充电电压，单位：V
     *   'charge_curt_c' => $port->ac_current_c/1000,          //交流C相充电电流，单位：A
     *
     *   'charge_mode' => $port->bms_mode,                 //设置BMS充电模式
     *   'require_volt' => $port->voltage_bms/1000,               //BMS需求电压，单位：V
     *   'require_curt' => $port->current_bms/1000,               //BMS需求电流，单位：A
     *
     *   'drt_charge_volt' => $port->dc_voltage/1000,             //直流充电电压 ，单位：V
     *   'drt_charge_curt' => $port->dc_current/1000,             //直流充电电流，单位：A
     *  ];
     */
    public function realtimeChargeInfo(RealtimeChargeInfoValidator $validator, $hash);

    /**
     * 停止充电，停止业务：
     * 0、判断参数是否ok，交给validator 就好了；
     * 1、判断order_id，是否有效；无效返回；
     * 2、更新数据进充电口（状态数据），准备停止充电
     * 3、调用ProtocolController停止充电；
     * 4、发送成功，返回命令已下发（status = 200）；否则返回发送失败；（status = 500 ）
     * @param StopChargeValidator $validator
     * @param $hash
     * @return mixed
     */
    public function stopCharge(StopChargeValidator $validator, $hash);


}