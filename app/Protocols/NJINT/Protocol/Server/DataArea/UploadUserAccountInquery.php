<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/5
 * Time: 15:41
 */

namespace Wormhole\Protocols\NJINT\Protocol\Server\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;

//TODO 协议处理
class UploadUserAccountInquery extends DataArea
{

    /**
     * @var array 协议预留1
     */
    private $reserved1;
    /**
     * @var array 协议预留2
     */
    private $reserved2;
    /**
     * 响应码
     * @var int
     */
    private $responseCode;
    /**
     * 帐户余额
     * @var
     */
    private $remainedSum;
    /**
     * 全时段电费费率
     * @var int
     */
    private $allTimeChargeFeeRate;
    /**
     * 服务费率
     * @var
     */
    private $svcFeeRate;
    /**
     * 充电密码验证
     * @var int
     */
    private $chargePwdVerify;
    /**
     * 验证 VIN 标志
     * @var
     */
    private $VINSignVerify;
    /**
     * 车牌验证
     * @var int
     */
    private $carNumVerify;
    /**
     * 余额指示
     * @var
     */
    private $reaminedAmountIndicate;


    public function __construct()
    {
        $this->reserved1=[0x00,0x00];
        $this->reserved2=[0x00,0x00];
    }

    /**
     * @return array
     */
    public function getReserved1()
    {
        return $this->reserved1;
    }

    /**
     * @param array $reserved1
     */
    public function setReserved1($reserved1)
    {
        $this->reserved1 = $reserved1;
    }

    /**
     * @return array
     */
    public function getReserved2()
    {
        return $this->reserved2;
    }

    /**
     * @param array $reserved2
     */
    public function setReserved2($reserved2)
    {
        $this->reserved2 = $reserved2;
    }

    /**
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @param int $responseCode
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
    }

    /**
     * @return mixed
     */
    public function getRemainedSum()
    {
        return $this->remainedSum;
    }

    /**
     * @param mixed $remainedSum
     */
    public function setRemainedSum($remainedSum)
    {
        $this->remainedSum = $remainedSum;
    }

    /**
     * @return int
     */
    public function getAllTimeChargeFeeRate()
    {
        return $this->allTimeChargeFeeRate;
    }

    /**
     * @param int $allTimeChargeFeeRate
     */
    public function setAllTimeChargeFeeRate($allTimeChargeFeeRate)
    {
        $this->allTimeChargeFeeRate = $allTimeChargeFeeRate;
    }

    /**
     * @return mixed
     */
    public function getSvcFeeRate()
    {
        return $this->svcFeeRate;
    }

    /**
     * @param mixed $svcFeeRate
     */
    public function setSvcFeeRate($svcFeeRate)
    {
        $this->svcFeeRate = $svcFeeRate;
    }

    /**
     * @return int
     */
    public function getChargePwdVerify()
    {
        return $this->chargePwdVerify;
    }

    /**
     * @param int $chargePwdVerify
     */
    public function setChargePwdVerify($chargePwdVerify)
    {
        $this->chargePwdVerify = $chargePwdVerify;
    }

    /**
     * @return mixed
     */
    public function getVINSignVerify()
    {
        return $this->VINSignVerify;
    }

    /**
     * @param mixed $VINSignVerify
     */
    public function setVINSignVerify($VINSignVerify)
    {
        $this->VINSignVerify = $VINSignVerify;
    }

    /**
     * @return int
     */
    public function getCarNumVerify()
    {
        return $this->carNumVerify;
    }

    /**
     * @param int $carNumVerify
     */
    public function setCarNumVerify($carNumVerify)
    {
        $this->carNumVerify = $carNumVerify;
    }

    /**
     * @return mixed
     */
    public function getReaminedAmountIndicate()
    {
        return $this->reaminedAmountIndicate;
    }

    /**
     * @param mixed $reaminedAmountIndicate
     */
    public function setReaminedAmountIndicate($reaminedAmountIndicate)
    {
        $this->reaminedAmountIndicate = $reaminedAmountIndicate;
    }

    public function build(){
        $frame = array_merge($this->reserved1,$this->reserved2); //预留1 预留2

        $frame=array_merge($frame,Tools::decToArray($this->responseCode,4));
        $frame=array_merge($frame,Tools::decToArray($this->remainedSum,4));
        $frame=array_merge($frame,Tools::decToArray($this->allTimeChargeFeeRate,2));
        $frame=array_merge($frame,Tools::decToArray($this->svcFeeRate,2));
        array_push($frame,$this->chargePwdVerify);
        array_push($frame,$this->VINSignVerify);
        array_push($frame,$this->carNumVerify);
        array_push($frame,$this->reaminedAmountIndicate);

        return $frame;

    }
    public function load($dataArea){
        $offset = 0;
        $this->reserved1 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->reserved2 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->responseCode = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;
        $this->remainedSum = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;
        $this->allTimeChargeFeeRate = Tools::arrayToDec(array_slice($dataArea,$offset,2));
        $offset+=2;
        $this->svcFeeRate = Tools::arrayToDec(array_slice($dataArea,$offset,2));
        $offset+=2;
        $this->chargePwdVerify = $dataArea[$offset];
        $offset++;
        $this->VINSignVerify = $dataArea[$offset];
        $offset++;
        $this->carNumVerify = $dataArea[$offset];
        $offset++;
        $this->reaminedAmountIndicate = $dataArea[$offset];
        $offset++;

    }

}