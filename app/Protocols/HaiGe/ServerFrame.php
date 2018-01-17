<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-10-24
 * Time: 17:59
 */

namespace Wormhole\Protocols\HaiGe;

use Wormhole\Protocols\HaiGe\Protocol\Frame;

//心跳
use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\Heartbeat as HeartbeatFrame;

//账单
use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\SetBill as SetBillFrame;

//启动充电
use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\StartCharge as StartChargeFrame;
use Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\StartCharge as StartChargeDataArea;


//启动充电回复
use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\StartChargeResponse as StartChargeResponseFrame;

//停止充电
use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\StopCharge as StopChargeFrame;
use Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\StopCharge as StopChargeDataArea;

//停止充电回复
use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\StopChargeResponse as StopChargeResponseFrame;

//预约指令
use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\Appointment as AppointmentFrame;
use Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\Appointment as AppointmentDataArea;

//取消预约指令
use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\CancelAppointment as CancelAppointmentFrame;
use Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\CancelAppointment as CancelAppointmentDataArea;

//取消预约指令回复
use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\CancelAppointmentResponse as CancelAppointmentResponseFrame;

//重启指令
use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\Restart as RestartFrame;

//对时时间
use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\SetTime as SetTimeFrame;
use Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\SetTime as SetTimeDataArea;

//费率查询
use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\Get24HsCommodityStrategy as Get24HsCommodityStrategyFrame;

//费率设置
use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\Set24HsCommodityStrategy as Set24HsCommodityStrategyFrame;
use Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\Set24HsCommodityStrategy as Set24HsCommodityStrategyDataArea;

//刷卡回应
use Wormhole\Protocols\HaiGe\Protocol\Server\Frame\PayByCard as PayByCardFrame;
use Wormhole\Protocols\HaiGe\Protocol\Server\DataArea\PayByCard as PayByCardDataArea;

class ServerFrame
{
    public function loadCommonFrame($frame){
        $cmd = new Frame();
        $cmd->load($frame);
        return $cmd;
    }








    /**
     * 心跳
     * @param int $equipmentId
     * @return array
     */
    public function heartbeat($evse){

        $frame = new HeartbeatFrame();
        $frame->setRegister($evse['registe']);
        $frame->setResponseCode($evse['responseCode']);
        $frame->setCarriers($evse['carriers']);
        $frame->setDeviceAddress($evse['deviceAddress']);
        $frame->setNumber($evse['number']);
        return $frame->build();
    }

    /**
     * 账单
     * @param string $equipmentId
     * @return array
     */
    public function SetBill($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new SetBillFrame();
        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);
        return $frame->build();
    }


    /**
     * 开启充电
     * @param string $equipmentId
     * @param int $controlType
     * @param int $chargeDuration
     * @return array
     */
    public function StartCharge($register, $responseCode, $carriers, $deviceAddress, $number, $controlType, $chargeDuration){

        $dataArea = new StartChargeDataArea();
        $frame = new StartChargeFrame();


        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        $dataArea->setGunNum(0);
        $dataArea->setControlType($controlType);
        $dataArea->setStartData($chargeDuration);
        $dataArea->setTimerStart('01141250');
        $dataArea->setUserCard('86135535231478095531');

        $frame->setDataArea($dataArea);
        return $frame->build();
    }


    /**
     * 开启充电回复
     * @param string $equipmentId
     * @return array
     */
    public function StartChargeResponse($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new StartChargeResponseFrame();

        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        return $frame->build();

    }



    /**
     * 停止充电
     * @param string $equipmentId
     * @return array
     */
    public function StopCharge($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new StopChargeFrame();
        $dataArea = new StopChargeDataArea();

        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        $dataArea->setGunNum(0);
        $dataArea->setControlType(4);
        //$dataArea->setControlType('8613 5535 2314 7809 5531');

        $frame->setDataArea($dataArea);
        return $frame->build();

    }


    /**
     * 停止充电回复
     * @param string $equipmentId
     * @return array
     */
    public function StopChargeResponse($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new StopChargeResponseFrame();

        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        return $frame->build();

    }


    /**
     * 预约指令
     * @param string $equipmentId
     * @return array
     */
    public function Appointment($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new AppointmentFrame();
        $dataArea = new AppointmentDataArea();

        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        $dataArea->setGunNum(0);
        $dataArea->setUserCard('86135535231478095531');

        $frame->setDataArea($dataArea);
        return $frame->build();

    }


    /**
     * 取消预约指令
     * @param string $equipmentId
     * @return array
     */
    public function CancelAppointment($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new CancelAppointmentFrame();
        $dataArea = new CancelAppointmentDataArea();

        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        $dataArea->setGunNum(0);
        $dataArea->setUserCard('86135535231478095531');

        $frame->setDataArea($dataArea);
        return $frame->build();

    }


    /**
     * 取消预约指令回复
     * @param string $equipmentId
     * @return array
     */
    public function CancelAppointmentResponse($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new CancelAppointmentResponseFrame();

        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        return $frame->build();

    }


    /**
     * 重启指令
     * @param string $equipmentId
     * @return array
     */
    public function Restart($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new RestartFrame();

        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        return $frame->build();

    }


    /**
     * 对时时间
     * @param string $equipmentId
     * @return array
     */
    public function SetTime($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new SetTimeFrame();
        $dataArea = new SetTimeDataArea();

        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        $dataArea->setTime('20150510132011');

        $frame->setDataArea($dataArea);
        return $frame->build();

    }




    /**
     * 查询费率
     * @param string $equipmentId
     * @return array
     */
    public function Get24HsCommodityStrategy($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new Get24HsCommodityStrategyFrame();

        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        return $frame->build();

    }


    /**
     * 费率设置
     * @param string $equipmentId
     * @return array
     */
    public function Set24HsCommodityStrategy($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new Set24HsCommodityStrategyFrame();
        $dataArea = new Set24HsCommodityStrategyDataArea();

        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        $dataArea->setCommodityOne(10);
        $dataArea->setCommodityTwo(10);
        $dataArea->setCommodityThree(10);
        $dataArea->setCommodityFour(10);
        $dataArea->setServiceCharge(10);
        $dataArea->setImplement(10);

        $frame->setDataArea($dataArea);
        return $frame->build();

    }


    /**
     * 刷卡回应
     * @param string $equipmentId
     * @return array
     */
    public function PayByCard($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new PayByCardFrame();
        $dataArea = new PayByCardDataArea();

        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        $dataArea->setGunNum(0);
        $dataArea->setUserCard('86135535231478095531');
        $dataArea->setUserCarDeffective(0);


        $frame->setDataArea($dataArea);
        return $frame->build();

    }




}
