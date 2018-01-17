<?php
namespace Wormhole\Protocols\HD10\Protocol;
use Wormhole\Protocols\HD10\Protocol\Frame;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\CardSign as CardSignDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ChargeRealtime as ChargeDefaultDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\CommoditySet as CommoditySetDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\EventUpload as EventUploadDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\HeartBeat as HeartBeatDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ReservationCharge as ReservationChargeDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\SignIn as SignInDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\StartCharge as StartChargeDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\StartChargeCheck as StartChargeCheckDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\StopCharge as StopChargeDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\UnReservationCharge as UnReservationChargeDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\DataArea\ChargeLog as UploadChargeInfoDataArea;
use Wormhole\Protocols\HD10\Protocol\Evse\Frame\CardSign as CardSignFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\Frame\ChargeRealtime as ChargeDefaultFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\Frame\CommoditySet as CommoditySetFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\Frame\EventUpload as EventUploadFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\Frame\HeartBeat as HeartBeatFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\Frame\ReservationCharge as ReservationChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\Frame\SignIn as SignInFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\Frame\StartCharge as StartChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\Frame\StartChargeCheck as StartChargeCheckFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\Frame\StopCharge as StopChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\Frame\UnReservationCharge as UnReservationChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Evse\Frame\ChargeLog as UploadChargeInfoFrame;

/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/20
 * Time: 19:26
 */

class EvseFrame
{
    /**
     * @param $frame
     * @return SignInFrame
     * 解析帧/登录
     */
    public function loadSignIn($frame){
        $tmp = new SignInFrame();
        $tmp->loadFrame($frame);

        return $tmp;
    }

    /**
     * @param $evseCode
     * @return array
     * 桩请求登录
     */
    public function signIn($evseCode){
        $signIn = new SignInFrame();

        $dataArea = new SignInDataArea();
        $dataArea->setEvseCode($evseCode);

        $signIn->setDataArea($dataArea);

        return $signIn->build();
    }

    /**
     * @param $frame
     * @return HeartBeatFrame
     * 解析帧/心跳
     */
    public function loadHeartBeat($frame){
        $tmp = new HeartBeatFrame();
        $tmp->loadFrame($frame);

        return $tmp;
    }

    /**
     * @param $evseCode  string 桩编号
     * @param $chargeType  int 桩状态
     * @param $warningType int 警告状态
     * @param $gunType  int 枪状态
     * @param $emergencyType  int  急停状态
     * 桩发送心跳
     */
    public function heartBeat($evseCode,$chargeType,$warningType,$gunType,$emergencyType){
        $HeartBeat = new HeartBeatFrame();

        $dataArea = new HeartBeatDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setChargeType($chargeType);
        $dataArea->setWarningType($warningType);
        $dataArea->setGunType($gunType);
        $dataArea->setEmergencyType($emergencyType);

        $HeartBeat->setDataArea($dataArea);

        return $HeartBeat->build();
    }

    /**
     * @param $frame
     * @return StartChargeFrame
     * 解析帧/启动充电
     */
    public function loadStartCharge($frame){
        $tmp = new StartChargeFrame();

        $tmp->loadFrame($frame);

        return $tmp;
    }

    /**
     * @param $evseCode
     * @param $userId
     * @param $chargeType
     * @param $chargePayType
     * @param $chargeParameter
     * @param $userBalance
     * 桩响应请求充电
     */
    public function startCharge($evseCode,$resultCode){
        $startCharge = new StartChargeFrame();

        $dataArea = new StartChargeDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setResultCode($resultCode);

        $startCharge->setDataArea($dataArea);

        return $startCharge->build();
    }

    /**
     * @param $frame
     * @return StopChargeFrame
     * 解析帧/停止充电
     */
    public function loadStopCharge($frame){
        $tmp = new StopChargeFrame();
        $tmp->loadFrame($frame);

        return $tmp;
    }

    /**
     * @param $evseCode
     * @param $userId
     * 桩响应停止充电
     */
    public function stopCharge($evseCode,$resultCode){
        $stopCharge = new StopChargeFrame();

        $dataArea = new StopChargeDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setResultCode($resultCode);

        $stopCharge->setDataArea($dataArea);

        return $stopCharge->build();
    }

    /**
     * @param $frame
     * @return CardSignFrame
     * 解析帧/卡片签权
     */
    public function loadCardSign($frame){
        $tmp = new CardSignFrame();
        $tmp->loadFrame($frame);

        return $tmp;
    }

    /**
     * @param $evseCode
     * @param $cardNumber
     * 卡片签权
     */
    public function cardSign($evseCode,$cardNumber){
        $cardSign = new CardSignFrame();

        $dataArea = new CardSignDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setCardNumber($cardNumber);

        $cardSign->setDataArea($dataArea);

        return $cardSign->build();
    }

    /**
     * @param $frame
     * @return ReservationChargeFrame
     * 解析帧/预约
     */
    public function loadReservationCharge($frame){
        $tmp = new ReservationChargeFrame();
        $tmp->loadFrame($frame);

        return $tmp;
    }

    /**
     * @param $evseCode
     * @param $resultCode
     * 预约
     */
    public function reservationCharge($evseCode,$resultCode){
        $reservationCharge = new ReservationChargeFrame();

        $dataArea = new ReservationChargeDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setResultCode($resultCode);

        $reservationCharge->setDataArea($dataArea);

        return $reservationCharge->build();
    }

    /**
     * @param $frame
     * @return UnReservationChargeFrame
     * 解析帧/解约
     */
    public function loadUnReservationCharge($frame){
        $tmp = new UnReservationChargeFrame();
        $tmp->loadFrame($frame);

        return $tmp;
    }

    /**
     * @param $evseCode
     * @param $resultCode
     * @return array
     * 解约
     */
    public function unReservationCharge($evseCode,$resultCode){
        $unReservationCharge = new UnReservationChargeFrame();

        $dataArea = new UnReservationChargeDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setResultCode($resultCode);

        $unReservationCharge->setDataArea($dataArea);

        return $unReservationCharge->build();
    }

    /**
     * @param $frame
     * @return StartChargeCheckFrame
     * 解析帧/开机自检
     */
    public function loadStartChargeCheck($frame){
        $tmp = new StartChargeCheckFrame();
        $tmp->loadFrame($frame);

        return $tmp;
    }

    /**
     * @param $evseCode
     * @param $resultCode
     * 开机自检
     */
    public function startChargeCheck($evseCode,$resultCode){
        $startChargeCheck = new StartChargeCheckFrame();

        $dataArea = new StartChargeCheckDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setResultCode($resultCode);

        $startChargeCheck->setDataArea($dataArea);

        return $startChargeCheck->build();
    }

    /**
     * @param $frame
     * @return CommoditySetFrame
     * 解析帧/费率
     */
    public function loadCommoditySet($frame){
        $tmp = new CommoditySetFrame();
        $tmp->loadFrame($frame);

        return $tmp;
    }

    /**
     * @param $evseCode
     * 充电桩费率设置响应
     */
    public function commoditySet($evseCode){
        $commodity = new CommoditySetFrame();

        $dataArea = new CommoditySetDataArea();
        $dataArea->setEvseCode($evseCode);

        $commodity->setDataArea($dataArea);

        return $commodity->build();
    }

    /**
     * @param $frame
     * @return UploadChargeInfoFrame
     * 解析帧/充电记录上报
     */
    public function loadUploadChargeInfo($frame){
        $tmp = new UploadChargeInfoFrame();
        $tmp->loadFrame($frame);

        return $tmp;
    }

    /**
     * @param $evseCode string 桩编号
     * @param $userId  int 用户id
     * @param $startChargeTime int  开始时间戳
     * @param $stopChargeTime  int 结束时间戳
     * @param $power   int 电量
     * @param $money int 金额
     * 充电记录上报
     */
    public function uploadChargeInfo($evseCode,$userId,$startChargeTime,$stopChargeTime,$power,$money){
        $uploadChargeInfo = new UploadChargeInfoFrame();

        $dataArea = new UploadChargeInfoDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setUserId($userId);
        $dataArea->setStartChargeTime($startChargeTime);
        $dataArea->setStopChargeTime($stopChargeTime);
        $dataArea->setPower($power);
        $dataArea->setMoney($money);

        $uploadChargeInfo->setDataArea($dataArea);

        return $uploadChargeInfo->build();
    }

    /**
     * @param $frame
     * @return ChargeDefaultFrame
     * 解析帧/实时数据
     */
    public function loadChargeDefault($frame){
        $tmp = new ChargeDefaultFrame();
        $tmp->loadFrame($frame);

        return $tmp;
    }

    /**
     * @param $evseCode string 桩编号
     * @param $voltage int 电压
     * @param $electricCurrent int 电流
     * @param $chargeTime int 时间戳
     * @param $power   int  电量
     * @param $money  int  金额
     * 充电实时数据
     */
    public function chargeDefault($evseCode,$voltage,$electricCurrent,$chargeTime,$power,$money){
        $chargeDefault = new ChargeDefaultFrame();

        $dataArea = new ChargeDefaultDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setVoltage($voltage);
        $dataArea->setElectricCurrent($electricCurrent);
        $dataArea->setChargeTime($chargeTime);
        $dataArea->setPower($power);
        $dataArea->setMoney($money);

        $chargeDefault->setDataArea($dataArea);

        return $chargeDefault->build();
    }

    /**
     * @param $frame
     * @return ChargeDefaultFrame
     * 解析帧/事件上报
     */
    public function loadEventUpload($frame){
        $tmp = new EventUploadFrame();
        $tmp->loadFrame($frame);

        return $tmp;
    }

    /**
     * @param $evseCode
     * @param $status
     * 事件上报
     */
    public function eventUpload($evseCode,$status){
        $eventUpload = new EventUploadFrame();

        $dataArea = new EventUploadDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setStatus($status);

        $eventUpload->setDataArea($dataArea);

        return $eventUpload->build();
    }
}