<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-05-27
 * Time: 10:00
 */

namespace HaiGe;
use Wormhole\Protocols\HaiGe\Protocol\Frame;
//心跳
use Wormhole\Protocols\HaiGe\Evse\Frame\Heartbeat as HeartbeatFrame;
use Wormhole\Protocols\HaiGe\Evse\DataArea\Heartbeat as HeartbeatDataArea;

//账单
use Wormhole\Protocols\HaiGe\Evse\Frame\SetBill as SetBillFrame;
use Wormhole\Protocols\HaiGe\Evse\DataArea\SetBill as SetBillDataArea;

//开启充电
use Wormhole\Protocols\HaiGe\Evse\Frame\StartCharge as StartChargeFrame;
use Wormhole\Protocols\HaiGe\Evse\DataArea\StartCharge as StartChargeDataArea;

//停止充电
use Wormhole\Protocols\HaiGe\Evse\Frame\StopCharge as StopChargeFrame;
use Wormhole\Protocols\HaiGe\Evse\DataArea\StopCharge as StopChargeDataArea;

//预约
use Wormhole\Protocols\HaiGe\Evse\Frame\Appointment as AppointmentFrame;
use Wormhole\Protocols\HaiGe\Evse\DataArea\Appointment as AppointmentDataArea;

//取消预约
use Wormhole\Protocols\HaiGe\Evse\Frame\CancelAppointment as CancelAppointmentFrame;
use Wormhole\Protocols\HaiGe\Evse\DataArea\CancelAppointment as CancelAppointmentDataArea;

//重启
use Wormhole\Protocols\HaiGe\Evse\Frame\Restart as RestartFrame;


//对时时间
use Wormhole\Protocols\HaiGe\Evse\Frame\SetTime as SetTimeFrame;
use Wormhole\Protocols\HaiGe\Evse\DataArea\SetTime as SetTimeDataArea;


//获取费率
use Wormhole\Protocols\HaiGe\Evse\Frame\Get24HsCommodityStrategy as Get24HsCommodityStrategyFrame;
use Wormhole\Protocols\HaiGe\Evse\DataArea\Get24HsCommodityStrategy as Get24HsCommodityStrategyDataArea;

//获取费率
use Wormhole\Protocols\HaiGe\Evse\Frame\Set24HsCommodityStrategy as Set24HsCommodityStrategyFrame;
use Wormhole\Protocols\HaiGe\Evse\DataArea\Set24HsCommodityStrategy as Set24HsCommodityStrategyDataArea;

//刷卡
use Wormhole\Protocols\HaiGe\Evse\Frame\PayByCard as PayByCardFrame;
use Wormhole\Protocols\HaiGe\Evse\DataArea\PayByCard as PayByCardDataArea;




class EvseFrame
{
    public function loadCommonFrame($frame){
        $cmd = new Frame();
        $cmd->load($frame);

        //log_message('DEBUG', __CLASS__ . "/" . __FUNCTION__ . "@" . __LINE__ . " " . " operator :".$cmd->getOperator() );

        return $cmd;
    }



    /**
     * @param int $register
     * @param int $responseCode
     * @param int $carriers
     * @param int $deviceAddress
     * @param int $number
     * @return array
     */
    public function Hearbeat($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new HeartbeatFrame();
        $dataArea = new HeartbeatDataArea();

        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        $dataArea->setLockChannel(0);
        $dataArea->setEvseStatus(12);
        $dataArea->setChargePower(4110);
        $dataArea->setChargeMoney(4110);
        $dataArea->setParkStatus(1);
        $dataArea->setIsLock(1);
        $dataArea->setFault(0);
        $dataArea->setChargeVoltage(7511);
        $dataArea->setChargeCurrent(1232);
        $dataArea->setChargeTime(10);
        $dataArea->setPower(3121);
        $dataArea->setInterfaceStatus(0);
        $dataArea->setSocStatus(255);
        $dataArea->setLeftTime(255);
        $dataArea->setDetailFault(0);
        $dataArea->setReserve(0);

        $frame->setDataArea($dataArea);
        return $frame->build();

    }


    /**
     * 账单
     * @param int $equipmentId
     * @return array
     */
    public function bill($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new SetBillFrame();
        $dataArea = new SetBillDataArea();

        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        $dataArea->setChargeStartTime('20150510132011');
        $dataArea->setChargeEndTime('20150510135011');
        $dataArea->setCardNum('86135535231478095531');
        $dataArea->setBeforePower(0);
        $dataArea->setAfterPower(0);
        $dataArea->setChargePower(0);
        $dataArea->setChargeMoney(0);
        $dataArea->setBeforeBalance(0);
        $dataArea->setAfterBalance(0);
        $dataArea->setServiceCharge(0);
        $dataArea->setOfflinePayment(1);
        $frame->setDataArea($dataArea);

        return $frame->build();
    }


    /**
     * 开启充电
     * @param int $equipmentId
     * @return array
     */
    public function startCharge($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new StartChargeFrame();
        $dataArea = new StartChargeDataArea();

        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        $dataArea->setGunNum(1);
        $dataArea->setControlType(4);
        $dataArea->setStartData(50);
        $dataArea->setTimerStart('01141250');
        $dataArea->setUserCard('86135535231478095531');

        $frame->setDataArea($dataArea);
        return $frame->build();

    }

    /**
     * 停止充电
     * @param int $equipmentId
     * @return array
     */
    public function stopCharge($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new StopChargeFrame();
        $dataArea = new StopChargeDataArea();

        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        $dataArea->setGunNum(1);
        $dataArea->setControlType(4);
        $dataArea->setChargeTime(50);
        $dataArea->setChargeStatus(25);
        $dataArea->setUserCard('86135535231478095531');

        $frame->setDataArea($dataArea);
        return $frame->build();

    }


    /**
     * 预约应答
     * @param int $equipmentId
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

        $dataArea->setGunNum(1);
        $dataArea->setUserCard('86135535231478095531');

        $frame->setDataArea($dataArea);
        return $frame->build();

    }




    /**
     * 取消预约应答
     * @param int $equipmentId
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

        $dataArea->setGunNum(1);
        $dataArea->setUserCard('86135535231478095531');

        $frame->setDataArea($dataArea);
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
     * 费率上报
     * @param string $equipmentId
     * @return array
     */
    public function Get24HsCommodityStrategy($register, $responseCode, $carriers, $deviceAddress, $number){

        $frame = new Get24HsCommodityStrategyFrame();
        $dataArea = new Get24HsCommodityStrategyDataArea();

        $frame->setRegister($register);
        $frame->setResponseCode($responseCode);
        $frame->setCarriers($carriers);
        $frame->setDeviceAddress($deviceAddress);
        $frame->setNumber($number);

        $dataArea->setCommodityOne(1000);
        $dataArea->setCommodityTwo(1000);
        $dataArea->setCommodityThree(1000);
        $dataArea->setCommodityFour(1000);
        $dataArea->setServiceCharge(1000);
        $dataArea->setImplement(10);

        $frame->setDataArea($dataArea);
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

        $dataArea->setCommodityOne(1000);
        $dataArea->setCommodityTwo(1000);
        $dataArea->setCommodityThree(1000);
        $dataArea->setCommodityFour(1000);
        $dataArea->setServiceCharge(1000);
        $dataArea->setImplement(10);

        $frame->setDataArea($dataArea);
        return $frame->build();

    }



    /**
     * 刷卡上报
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


        $frame->setDataArea($dataArea);
        return $frame->build();

    }








}