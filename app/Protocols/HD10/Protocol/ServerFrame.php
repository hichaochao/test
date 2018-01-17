<?php
namespace Wormhole\Protocols\HD10\Protocol;
use Wormhole\Protocols\HD10\Protocol\Frame;
use Wormhole\Protocols\HD10\Protocol\Server\DataArea\CardSign as CardSignDataArea;
use Wormhole\Protocols\HD10\Protocol\Server\DataArea\CommoditySet as CommoditySetDataArea;
use Wormhole\Protocols\HD10\Protocol\Server\DataArea\HeartBeat as HeartBeatDataArea;
use Wormhole\Protocols\HD10\Protocol\Server\DataArea\ReservationCharge as ReservationChargeDaraArea;
use Wormhole\Protocols\HD10\Protocol\Server\DataArea\SignIn as SignInDataArea;
use Wormhole\Protocols\HD10\Protocol\Server\DataArea\StartChargeCheck as StartChargeCheckDataArea;
use Wormhole\Protocols\HD10\Protocol\Server\DataArea\StopCharge as StopChargeDataArea;
use Wormhole\Protocols\HD10\Protocol\Server\DataArea\UnReservationCharge as UnReservationChargeDataArea;
use Wormhole\Protocols\HD10\Protocol\Server\DataArea\ChargeLog as UploadChargeInfoDataArea;
use Wormhole\Protocols\HD10\Protocol\Server\Frame\CardSign as CardSignFrame;
use Wormhole\Protocols\HD10\Protocol\Server\Frame\CommoditySet as CommoditySetFrame;
use Wormhole\Protocols\HD10\Protocol\Server\Frame\HeartBeat as HeartBeatFrame;
use Wormhole\Protocols\HD10\Protocol\Server\Frame\ReservationCharge as ReservationChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Server\Frame\SignIn as SignInFrame;
use Wormhole\Protocols\HD10\Protocol\Server\DataArea\StartCharge as StartChargeDataArea;
use Wormhole\Protocols\HD10\Protocol\Server\Frame\StartCharge as StartChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Server\Frame\StartChargeCheck as StartChargeCheckFrame;
use Wormhole\Protocols\HD10\Protocol\Server\Frame\StopCharge as StopChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Server\Frame\UnReservationCharge as UnReservationChargeFrame;
use Wormhole\Protocols\HD10\Protocol\Server\Frame\ChargeLog as UploadChargeInfoFrame;

/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/20
 * Time: 15:20
 */

class ServerFrame extends Frame
{
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
     * @param $userId
     * @param $isBilling
     * @param $chargeTactics
     * @param $chargeArgs
     * @param $userBalance
     * 服务器启动充电
     */
    public function startCharge($evseCode,$userId,$isBilling,$chargeTactics,$chargeArgs,$userBalance){
        $startCharge = new StartChargeFrame();

        $dataArea = new StartChargeDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setUserId($userId);
        $dataArea->setChargePayType($isBilling);
        $dataArea->setChargeType($chargeTactics);
        $dataArea->setChargeParameter($chargeArgs);
        $dataArea->setUserBalance($userBalance);

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
     * @param $resultCode
     * 停止充电
     */
    public function stopCharge($evseCode,$usaerId){
        $stopCharge = new StopChargeFrame();

        $dataArea = new StopChargeDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setUserId($usaerId);

        $stopCharge->setDataArea($dataArea);

        return $stopCharge->build();
    }

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
     * 服务器响应登录
     */
    public function signIn($evseCode,$resultCode){
        $signIn = new SignInFrame();

        $dataArea = new SignInDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setResultCode($resultCode);

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
     * @param $evseCode
     * @return array
     * 心跳
     */
    public function heartBeat($evseCode){
        $HeartBeat = new HeartBeatFrame();

        $dataArea = new HeartBeatDataArea();
        $dataArea->setEvseCode($evseCode);

        $HeartBeat->setDataArea($dataArea);

        return $HeartBeat->build();
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
     * @param $resultCode
     * @return array
     * 服务器响应卡片签权
     */
    public function cardSign($evseCode,$resultCode){
        $cardSign = new CardSignFrame();

        $dataArea = new CardSignDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setResultCode($resultCode);

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
     * @param $userId
     * @param $time
     * 预约
     */
    public function reservationCharge($evseCode,$userId,$time){
        $reservationCharge = new ReservationChargeFrame();

        $dataArea = new ReservationChargeDaraArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setUserId($userId);
        $dataArea->setTime($time);

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
     * @param $userId
     * @return array
     * 解约
     */
    public function unReservationCharge($evseCode,$userId){
        $unReservationCharge = new UnReservationChargeFrame();

        $dataArea = new UnReservationChargeDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setUserId($userId);

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
     * 开机自检
     */
    public function startChargeCheck($evseCode){
        $startChargeCheck = new StartChargeCheckFrame();

        $dataArea = new StartChargeCheckDataArea();
        $dataArea->setEvseCode($evseCode);

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
     * @param $type
     * @param $rates array
     * 服务器费率设置
     */
    public function commoditySet($evseCode,$type,$rates){
        $commodity = new CommoditySetFrame();

        $dataArea = new CommoditySetDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setType($type);
        $dataArea->setRates($rates);

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
     * @param $evseCode
     * @param $resultCode
     * 服务器响应充电记录上报
     */
    public function uploadChargeInfo($evseCode,$resultCode){
        $uploadChargeInfo = new UploadChargeInfoFrame();

        $dataArea = new UploadChargeInfoDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setResultCode($resultCode);

        $uploadChargeInfo->setDataArea($dataArea);

        return $uploadChargeInfo->build();
    }
}