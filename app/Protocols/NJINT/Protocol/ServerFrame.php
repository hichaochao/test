<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-25
 * Time: 17:59
 */

namespace Wormhole\Protocols\NJINT\Protocol;


use Wormhole\Protocols\NJINT\Protocol\Server\Frame\ChargingHistory as ChargingHistoryFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\ChargingHistory as ChargingHistoryDataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\EvseControl;
use Wormhole\Protocols\NJINT\Protocol\Server\Frame\SignIn as SignInFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\SignIn as SignInDataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\StartCharge as StartChargeFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\StartCharge as StartChargeDataArea;


use Wormhole\Protocols\NJINT\Protocol\Server\Frame\Heartbeat as HeartbeatFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\Heartbeat as HeartbeatDataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\EvseStatus as EvseStatusFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseStatus as EvseStatusDataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\UploadChargingLog as UploadChargingLogFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\UploadChargingLog as UploadChargingLogDataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\EvseControl as EvseControlFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseControl as EvseControlDataArea;
use Wormhole\Protocols\NJINT\Protocol\Server\Command\EvseControl as EvseControlCommand;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\EvseSetIntPara as EvseSetIntParaFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseSetIntPara as EvseSetIntParaDataArea;
use Wormhole\Protocols\NJINT\Protocol\Server\Command\EvseSetIntPara as EvseSetIntParaCommand;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\EvseSetCharPara as EvseSetCharParaFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseSetCharPara as EvseSetCharParaDataArea;
use Wormhole\Protocols\NJINT\Protocol\Server\Command\EvseSetCharPara as EvseSetCharParaCommand;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\EvseUploadCommand as EvseUploadCommandFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseUploadCommand as EvseUploadCommandDataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\UploadUserAccountInquery as UploadUserAccountInqueryFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\UploadUserAccountInquery as UploadUserAccountInqueryDataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\UploadUserPwdVerify as UploadUserPwdVerifyFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\UploadUserPwdVerify as UploadUserPwdVerifyDataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\UploadBMSInfo as UploadBMSInfoFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\UploadBMSInfo as UploadBMSInfoDataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\EraseInstruction as EraseInstructionFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EraseInstruction as EraseInstructionDataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\UpgrateFileSize as UpgrateFileSizeFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\UpgrateFileSize as UpgrateFileSizeDataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\UpgrateFileName as UpgrateFileNameFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\UpgrateFileName as UpgrateFileNameDataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\UpgrateFileData as UpgrateFileDataFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\UpgrateFileData as UpgrateFileDataDataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\UpgrateFileDataFinish as UpgrateFileDataFinishFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\UpgrateFileDataFinish as UpgrateFileDataFinishDataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\Restart as RestartFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\Restart as RestartDataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\Get24HsCommodityStrategy as Get24HsCommodityStrategyFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\Get24HsCommodityStrategy as Get24HsCommodityStrategyDataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Frame\Set24HsCommodityStrategy as Set24HsCommodityStrategyFrame;
use Wormhole\Protocols\NJINT\Protocol\Server\DataArea\Set24HsCommodityStrategy as Set24HsCommodityStrategyDataArea;
use Wormhole\Protocols\NJINT\Protocol\Server\Command\Commodity;



class ServerFrame
{
    public function loadCommonFrame($frame){
        $cmd = new Frame();
        $cmd->load($frame);
        return $cmd;
    }


    /**
     * 下发充电桩控制命令
     * @param int $sequence 序号
     * @param \Wormhole\Protocols\NJINT\Protocol\Server\DataArea\EvseControl $evseControlDataArea
     * @return string
     */
    public function evseControlCMD($sequence,$evseControlDataArea){
        $evseContorl = new EvseControl();
        $evseContorl->setSequence($sequence);
        $evseContorl->setDataArea($evseControlDataArea);

        return $evseContorl->build();
    }

    /**
     * @param $frame
     * @return EvseControl
     */
    public function loadEvseControlCMD($frame){
        $cmd = new EvseControl();
        $cmd->loadFrame($frame);
        return $cmd;
    }



    /**
     * @param $frame
     * @return StartChargeFrame
     */
    public function loadStartCharge($frame){
        $tmp = new StartChargeFrame();
        $tmp->load($frame);
        return $tmp;

    }


    public function heartbeat($sequence,$heartbeatReply){
        //log_message('DEBUG', __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " );
        $frame = new HeartbeatFrame();
        $frame->setSequence($sequence);
        $dataArea = new HeartbeatDataArea();
        $dataArea->setHeartbeatReply($heartbeatReply);
        $frame->setDataArea($dataArea);
        return $frame->build();
    }

    public function evseUploadCommand($sequence,$gunNum, $excuteResult){
        //log_message('DEBUG', __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " );
        $frame = new EvseUploadCommandFrame();
        $frame->setSequence($sequence);
        $dataArea = new EvseUploadCommandDataArea();
        $dataArea->setGunNum($gunNum);
        $dataArea->setExcuteResult($excuteResult);
        $frame->setDataArea($dataArea);
        return $frame->build();
    }

    public function UploadUserAccountInquery($sequence,$responseCode, $remainedSum,$allTimeChargeFeeRate, $svcFeeRate,$chargePwdVerify, $VINSignVerify,$carNumVerify, $reaminedAmountIndicate){
        //log_message('DEBUG', __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " );
        $frame = new UploadUserAccountInqueryFrame();
        $frame->setSequence($sequence);
        $dataArea = new UploadUserAccountInqueryDataArea();
        $dataArea->setResponseCode($responseCode);
        $dataArea->setRemainedSum($remainedSum);
        $dataArea->setAllTimeChargeFeeRate($allTimeChargeFeeRate);
        $dataArea->setSvcFeeRate($svcFeeRate);
        $dataArea->setChargePwdVerify($chargePwdVerify);
        $dataArea->setVINSignVerify($VINSignVerify);
        $dataArea->setCarNumVerify($carNumVerify);
        $dataArea->setReaminedAmountIndicate($reaminedAmountIndicate);
        $frame->setDataArea($dataArea);
        return $frame->build();
    }

    public function UploadUserPwdVerify($sequence,$responseCode, $remainedSum){
        //log_message('DEBUG', __NAMESPACE__ .  "/" . __CLASS__."/" . __FUNCTION__ . "@" . __LINE__ . " " );
        $frame = new UploadUserPwdVerifyFrame();
        $frame->setSequence($sequence);
        $dataArea = new UploadUserPwdVerifyDataArea();
        $dataArea->setResponseCode($responseCode);
        $dataArea->setRemainedSum($remainedSum);
        $frame->setDataArea($dataArea);
        return $frame->build();
    }

    /**
     * @param $sequence
     * @param $evseCode HeartbeatDataArea
     */
    public function heartbeatWithDataArea($sequence,$dataArea){
        $frame = new HeartbeatFrame();
        $frame->setSequence($sequence);
        $frame->setDataArea($dataArea);
        return $frame->build();
    }

    public function loadHeartbeat($frame){
        $cmd = new HeartbeatFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }

    public function loadEvseUploadCommand($frame){
        $cmd = new EvseUploadCommandFrame();
        $cmd->loadFrame($frame);
        return $cmd;

    }

    public function loadUploadUserAccountInquery($frame){
        $cmd = new UploadUserAccountInqueryFrame();
        $cmd->loadFrame($frame);
        return $cmd;

    }

    public function loadUploadUserPwdVerify($frame){
        $cmd = new UploadUserPwdVerifyFrame();
        $cmd->loadFrame($frame);
        return $cmd;

    }

    public function signInFrame($sequence){
        $frame = new SignInFrame();
        $frame->setSequence($sequence);
        $dataArea = new SignInDataArea();
        $frame->setDataArea($dataArea);
        return $frame->build();
    }
    public function uploadChargingLog($sequence,$gunNum,$cardId){
        $frame = new UploadChargingLogFrame();
        $frame->setSequence($sequence);

        $dataArea = new UploadChargingLogDataArea();
        $dataArea->setCardId($cardId);
        $dataArea->setGunNum($gunNum);

        $frame->setDataArea($dataArea);
        return  $frame->build();
    }

    public function evseStatusFrame($sequence,$gunNum){
        $frame = new EvseStatusFrame();
        $frame->setSequence($sequence);

        $dataArea = new EvseStatusDataArea();
        $dataArea->setGunNum($gunNum);

        $frame->setDataArea($dataArea);

        return $frame->build();
    }

    /**
     * 后台服务器下发充电桩开启充电控制命令
     * @param int $sequence 序号
     * @param $startChargeDataArea \Wormhole\Protocols\NJINT\Protocol\Server\DataArea\StartCharge $startChargeDataArea
     * @return string
     */
    public function startCharge($sequence,$gunNum,$chargeType,$chargeTactics,$chargeTacticsArgs,$userId){
        $startCharge = new StartChargeFrame();
        $startCharge->setSequence($sequence);

        $dataArea = new StartChargeDataArea();
        $dataArea->setGunNum($gunNum);
        $dataArea->setChargeType($chargeType);
        $dataArea->setChargeTactics($chargeTactics);
        $dataArea->setChargeTacticsArgs($chargeTacticsArgs);
        $dataArea->setUserId($userId);

        $startCharge->setDataArea($dataArea);

        return $startCharge->build();

    }

    public function stopCharge($sequence,$gunNum){
        $evseControl = new EvseControlFrame();
        $evseControl->setSequence($sequence);

        $dataArea = new EvseControlDataArea();
        $dataArea->setGunNum($gunNum);

        $command = new EvseControlCommand();
        $command->setArgStopCharge();

        $dataArea->setCmdControl($command);

        $evseControl->setDataArea($dataArea);

        return $evseControl->build();

    }

    public function readChargeLog($sequence, $index, $amount){
        $chargingHistory = new ChargingHistoryFrame();
        $chargingHistory->setSequence($sequence);

        $dataArea = new ChargingHistoryDataArea();
        $dataArea->setLogIndex($index);
        $dataArea->setLogAmount($amount);

        $chargingHistory->setDataArea($dataArea);

        return $chargingHistory->build();

    }

    public function eraseInstruction($sequence, $instruction){
        $eraseInstruction = new EraseInstructionFrame();
        $eraseInstruction->setSequence($sequence);

        $dataArea = new EraseInstructionDataArea();
        $dataArea->setEraseInstruction($instruction);

        $eraseInstruction->setDataArea($dataArea);

        return $eraseInstruction->build();

    }

    public function upgrateFileSize($sequence, $instruction){
        $upgrateFileSize = new UpgrateFileSizeFrame();
        $upgrateFileSize->setSequence($sequence);

        $dataArea = new UpgrateFileSizeDataArea();
        $dataArea->setUpgrateFileSize($instruction);

        $upgrateFileSize->setDataArea($dataArea);

        return $upgrateFileSize->build();

    }

    public function upgrateFileName($sequence, $instruction){
        $upgrateFileName = new UpgrateFileNameFrame();
        $upgrateFileName->setSequence($sequence);

        $dataArea = new UpgrateFileNameDataArea();
        $dataArea->setUpgrateFileName($instruction);

        $upgrateFileName->setDataArea($dataArea);

        return $upgrateFileName->build();

    }

    public function upgrateFileData($sequence, $instruction){
        $upgrateFileData = new UpgrateFileDataFrame();
        $upgrateFileData->setSequence($sequence);

        $dataArea = new UpgrateFileDataDataArea();
        $dataArea->setUpgrateFileData($instruction);

        $upgrateFileData->setDataArea($dataArea);

        return $upgrateFileData->build();

    }

    public function upgrateFileDataFinish($sequence, $reserved){
        $upgrateFileDataFinish = new UpgrateFileDataFinishFrame();
        $upgrateFileDataFinish->setSequence($sequence);

        $dataArea = new UpgrateFileDataFinishDataArea();
        $dataArea->setReserved($reserved);

        $upgrateFileDataFinish->setDataArea($dataArea);

        return $upgrateFileDataFinish->build();

    }

    public function restart($sequence, $reserved){
        $restart = new RestartFrame();
        $restart->setSequence($sequence);

        $dataArea = new RestartDataArea();
        $dataArea->setReserved($reserved);

        $restart->setDataArea($dataArea);

        return $restart->build();

    }

    public function get24HsCommodityStrategy($sequence){
        $get24HsCommodityStrategy = new Get24HsCommodityStrategyFrame();
        $get24HsCommodityStrategy->setSequence($sequence);

        //$dataArea = new Get24HsCommodityStrategyDataArea();

        //$get24HsCommodityStrategy->setDataArea($dataArea);

        return $get24HsCommodityStrategy->build();

    }

    /**
     * @param $sequence
     * @param $commodityList Commodity[]
     * @return string
     */
    public function set24HsCommodityStrategy($sequence, $commodityList=''){
        $set24HsCommodityStrategy = new Set24HsCommodityStrategyFrame();
        $set24HsCommodityStrategy->setSequence($sequence);

       $dataArea = new Set24HsCommodityStrategyDataArea();
       $dataArea->setCommodityList($commodityList);

        $set24HsCommodityStrategy->setDataArea($dataArea);

        return $set24HsCommodityStrategy->build();

    }

    ///**
    // * 后台服务器下发充电桩开启充电控制命令
    // * @param int $sequence 序号
    // * @param $startChargeDataArea \Wormhole\Protocols\NJINT\Protocol\Server\DataArea\StartCharge $startChargeDataArea
    // * @return string
    // */
    //public function startCharge($sequence,$startChargeDataArea){
    //    $startCharge = new StartCharge();
    //    $startCharge->setSequence($sequence);
    //    $startCharge->setDataArea($startChargeDataArea);
    //
    //    return $startCharge->build();
    //
    //}

    public function SignInDuration($sequence,$duration){
        $frame = new EvseSetIntParaFrame();
        $frame->setSequence($sequence);

        $dataArea = new EvseSetIntParaDataArea();
        $dataArea->setSetType(1);

        $command = new EvseSetIntParaCommand();
        $command->setSignInDuration($duration);

        $dataArea->setCmdControl($command);
        $frame->setDataArea($dataArea);
        return $frame->build();

    }
    public function HeartBeatDuration($sequence,$duration){
        $frame = new EvseSetIntParaFrame();
        $frame->setSequence($sequence);

        $dataArea = new EvseSetIntParaDataArea();
        $dataArea->setSetType(1);

        $command = new EvseSetIntParaCommand();
        $command->setHeartBeatDuration($duration);

        $dataArea->setCmdControl($command);
        $frame->setDataArea($dataArea);
        return $frame->build();

    }
    public function setQRCode($sequence,$QRCode){
        $frame = new EvseSetCharParaFrame();
        $frame->setSequence($sequence);

        $dataArea = new EvseSetCharParaDataArea();
        $dataArea->setSetType(1);

        $command = new EvseSetCharParaCommand();
        $command->setQRCode($QRCode);

        $dataArea->setCmdControl($command);
        $frame->setDataArea($dataArea);
        return $frame->build();

    }

    public function UploadBMSInfo($sequence){
        $frame = new UploadBMSInfoFrame();
        $frame->setSequence($sequence);
        $dataArea = new UploadBMSInfoDataArea();
        $frame->setDataArea($dataArea);
        return $frame->build();
    }
}