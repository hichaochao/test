<?php
/**
 * Created by PhpStorm.
 * User: sc
 * Date: 2016-05-27
 * Time: 16:07
 */

namespace Wormhole\Protocols\HaiGe\Protocol\Evse\DataArea;

use Wormhole\Protocols\Tools;

class SetBill
{
    /**
     * @var int 充电开始时间
     */
    private $chargeStartTime;

    /**
     * @var int 充电结束时间
     */
    private $chargeEndTime;


    /**
     * @var int 用户卡号
     */
    private $cardNum;

    /**
     * @var int 充电前电表读数
     */
    private $beforePower;
    /**
     * @var int 充电后电表读数
     */
    private $afterPower;
    /**
     * @var int 本次充电电量
     */
    private $chargePower;
    /**
     * @var int 本次充电金额
     */
    private $chargeMoney;
    /**
     * @var int 充电前卡余额
     */
    private $beforeBalance;
    /**
     * @var int 充电后卡余额
     */
    private $afterBalance;
    /**
     * @var int 服务费金额
     */
    private $serviceCharge;
    /**
     * @var int 是否线下支付
     */
    private $offlinePayment;

    public function __construct()
    {

    }


    /**
     * @return int
     */
    public function getChargeStartTime()
    {
        return $this->chargeStartTime;
    }

    /**
     * @param int $chargeStartTime
     */
    public function setChargeStartTime($chargeStartTime)
    {
        $this->chargeStartTime = $chargeStartTime;
    }


    /**
     * @return int
     */
    public function getChargeEndTime()
    {
        return $this->chargeEndTime;
    }

    /**
     * @param int $chargeEndTime
     */
    public function setChargeEndTime($chargeEndTime)
    {
        $this->chargeEndTime = $chargeEndTime;
    }


    /**
     * @return int
     */
    public function getCardNum()
    {
        return $this->cardNum;
    }

    /**
     * @param int $cardNum
     */
    public function setCardNum($cardNum)
    {
        $this->cardNum = $cardNum;
    }


    /**
     * @return int
     */
    public function getBeforePower()
    {
        return $this->beforePower;
    }

    /**
     * @param int $beforePower
     */
    public function setBeforePower($beforePower)
    {
        $this->beforePower = $beforePower;
    }


    /**
     * @return int
     */
    public function getAfterPower()
    {
        return $this->afterPower;
    }

    /**
     * @param int $afterPower
     */
    public function setAfterPower($afterPower)
    {
        $this->afterPower = $afterPower;
    }



    /**
     * @return int
     */
    public function getChargePower()
    {
        return $this->chargePower;
    }

    /**
     * @param int $chargePower
     */
    public function setChargePower($chargePower)
    {
        $this->chargePower = $chargePower;
    }



    /**
     * @return int
     */
    public function getChargeMoney()
    {
        return $this->chargeMoney;
    }

    /**
     * @param int $chargeMoney
     */
    public function setChargeMoney($chargeMoney)
    {
        $this->chargeMoney = $chargeMoney;
    }


    /**
     * @return int
     */
    public function getBeforeBalance()
    {
        return $this->beforeBalance;
    }

    /**
     * @param int $beforeBalance
     */
    public function setBeforeBalance($beforeBalance)
    {
        $this->beforeBalance = $beforeBalance;
    }



    /**
     * @return int
     */
    public function getAfterBalance()
    {
        return $this->afterBalance;
    }

    /**
     * @param int $afterBalance
     */
    public function setAfterBalance($afterBalance)
    {
        $this->afterBalance = $afterBalance;
    }



    /**
     * @return int
     */
    public function getServiceCharge()
    {
        return $this->serviceCharge;
    }

    /**
     * @param int $serviceCharge
     */
    public function setServiceCharge($serviceCharge)
    {
        $this->serviceCharge = $serviceCharge;
    }


    /**
     * @return int
     */
    public function getOfflinePayment()
    {
        return $this->offlinePayment;
    }

    /**
     * @param int $offlinePayment
     */
    public function setOfflinePayment($offlinePayment)
    {
        $this->offlinePayment = $offlinePayment;
    }




    public function build()
    {

        $frame = array();
        $frame = array_merge($frame, Tools::decToDbcArray($this->chargeStartTime, 7));//充电开始时间
        $frame = array_merge($frame, Tools::decToDbcArray($this->chargeEndTime, 7));//充电结束时间
        $frame = array_merge($frame, Tools::decToDbcArray($this->cardNum, 10));//用户卡号

        $frame = array_merge($frame,Tools::decToArray($this->beforePower,4)); //充电前电表读数
        $frame = array_merge($frame,Tools::decToArray($this->afterPower,4)); //充电后电表读数
        $frame = array_merge($frame,Tools::decToArray($this->chargePower,4)); //本次充电电量
        $frame = array_merge($frame,Tools::decToArray($this->chargeMoney,4)); //本次充电金额
        $frame = array_merge($frame,Tools::decToArray($this->beforeBalance,4)); //充电前卡余额
        $frame = array_merge($frame,Tools::decToArray($this->afterBalance,4)); //充电后卡余额
        $frame = array_merge($frame,Tools::decToArray($this->serviceCharge,2)); //服务费金额
        array_push($frame, $this->offlinePayment);//是否线下支付

        return $frame;

    }

    public function load($dataArea)
    {
        $offset = 0;
        $this->chargeStartTime = Tools::dbcArrayTodec(array_slice($dataArea,$offset,7));
        $offset = $offset+7;
        $this->chargeEndTime = Tools::dbcArrayTodec(array_slice($dataArea,$offset,7));
        $offset = $offset+7;
        $this->cardNum = Tools::dbcArrayTodec(array_slice($dataArea,$offset,10));
        $offset = $offset+10;

        $this->beforePower = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset = $offset+4;
        $this->afterPower = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset = $offset+4;
        $this->chargePower = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset = $offset+4;
        $this->chargeMoney = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset = $offset+4;
        $this->beforeBalance = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset = $offset+4;
        $this->afterBalance = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset = $offset+4;
        $this->serviceCharge = Tools::arrayToDec(array_slice($dataArea,$offset,2));
        $offset = $offset+2;
        $this->offlinePayment = $dataArea[$offset];
        $offset++;

    }

}