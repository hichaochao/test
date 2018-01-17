<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-27
 * Time: 10:00
 */

namespace Wormhole\Protocols\NJINT\Protocol;

use Wormhole\Protocols\NJINT\Protocol\Base\DataArea as BaseDataArea;

use \Wormhole\Protocols\NJINT\Protocol\Evse\Frame\EvseControl as EvseControlFrame;
use \Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EvseControl as EvseControlDataArea;

use \Wormhole\Protocols\NJINT\Protocol\Evse\Frame\StartCharge as StartChargeFrame;
use \Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\StartCharge as StartChargeDataArea;

use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\Heartbeat as HeartbeatFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\Heartbeat as HeartbeatDataArea;

use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\EvseStatus as EvseStatusFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EvseStatus as EvseStatusDataArea;

use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\SignIn as SignInFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\SignIn as SignInDataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\Frame;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\UploadChargingLog as UploadCharingLogFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\ChargingHistory as ChargingHistoryFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\EvseSetIntPara as EvseSetIntParaFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EvseSetIntPara as EvseSetIntParaDataArea;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\EvseSetCharPara as EvseSetCharParaFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EvseSetCharPara as EvseSetCharParaDataArea;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\EvseUploadCommand as EvseUploadCommandFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\EvseUploadCommand as EvseUploadCommandDataArea;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\UploadUserAccountInquery as UploadUserAccountInqueryFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\UploadUserAccountInquery as UploadUserAccountInqueryDataArea;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\UploadUserPwdVerify as UploadUserPwdVerifyFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\UploadUserPwdVerify as UploadUserPwdVerifyDataArea;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\UploadBMSInfo as UploadBMSInfoFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\DataArea\UploadBMSInfo as UploadBMSInfoDataArea;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\EraseInstruction as EraseInstructionFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\UpgrateFileSize as UpgrateFileSizeFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\UpgrateFileName as UpgrateFileNameFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\UpgrateFileData as UpgrateFileDataFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\UpgrateFileDataFinish as UpgrateFileDataFinishFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\Restart as RestartFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\Get24HsCommodityStrategy as Get24HsCommodityStrategyFrame;
use Wormhole\Protocols\NJINT\Protocol\Evse\Frame\Set24HsCommodityStrategy as Set24HsCommodityStrategyFrame;


class EvseFrame
{
    public function loadCommonFrame($frame){
        $cmd = new Frame();
        $cmd->load($frame);


        return $cmd;
    }



    public function signIn($sequence,$dataArea){
        $frame = new SignInFrame();
        $frame->setSequence($sequence);
        $frame->setDataArea($dataArea);
        return $frame->build();
    }
    public function heartbeat($sequence,$evseCode){
        $dataArea = new HeartbeatDataArea();
        $dataArea->setEvseCode($evseCode);
        $dataArea->setHeartbeatSequence($sequence);

        return $this->getFrame($sequence,$dataArea);

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

    /**
     * @param $sequence
     * @param $evseCode EvseStatusDataArea
     */
    public function evseStatus($sequence, $dataArea){
        $frame = new EvseStatusFrame();
        $frame->setSequence($sequence);
        $frame->setDataArea($dataArea);
        return $frame->build();
    }


    /**
     * @param $sequence
     * @param $dataArea StartChargeDataArea
     * @return string
     */
    public function startCharge($sequence,$dataArea){
        $frame = new StartChargeFrame();
        $frame->setSequence($sequence);
        $frame->setDataArea($dataArea);
        return $frame->build();
    }
    /**
     * @param $sequence
     * @param $dataArea EvseControlDataArea
     */
    public function evseControlCMD($sequence,$dataArea){
        $evseContorl = new EvseControlFrame();
        $evseContorl->setSequence($sequence);
        $evseContorl->setDataArea($dataArea);

        return $evseContorl->build();

    }

    /**
     * 
     * @param unknown $sequence
     * @param unknown $evseCode
     * @param unknown $workStatus
     * @param unknown $carConnectStatus
     */
    public function evseChargeRecord($sequence,$dataArea){
        $evseContorl = new EvseChargingData();
        $evseContorl->setSequence($sequence);
        $evseContorl->setDataArea($dataArea);
        return $evseContorl->build();
    }
    public function UploadBMSInfo($sequence,$evseCode,$workStatus,$carConnectStatus){
        $dataArea = new UploadBMSInfoDataArea();
        $dataArea->setPoleId($evseCode);
        $dataArea->setWorkStatus($workStatus);
        $dataArea->setCarConnectStatus($carConnectStatus);

        return $this->getFrame($sequence,$dataArea);
    }




    public function loadSignIn($frame){
        $cmd = new SignInFrame();
        $cmd->loadFrame($frame);
        return $cmd;

    }
    public function loadHeartbeat($frame){
        $cmd = new HeartbeatFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }


    public function loadEvseStatus($frame){
        $cmd = new EvseStatusFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }

    public function loadEvseControlCMD($frame){
        $cmd = new EvseControlFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }

    public function loadStartCharge($frame){
        $cmd = new StartChargeFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }

    public function loadStopCharge($frame){
        $cmd = new EvseControlFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }

    public function loadUploadChargeLog($frame){
        $cmd = new UploadCharingLogFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }


    public function loadChargingHistory($frame){
        $cmd = new ChargingHistoryFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }


    public function loadEraseInstruction($frame){
        $cmd = new EraseInstructionFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }


    public function loadUpgrateFileSize($frame){
        $cmd = new UpgrateFileSizeFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }


    public function loadUpgrateFileData($frame){
        $cmd = new UpgrateFileDataFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }


    public function loadUpgrateFileDataFinish($frame){
        $cmd = new UpgrateFileDataFinishFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }


    public function loadRestart($frame){
        $cmd = new RestartFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }


    public function loadGet24HsCommodityStrategy($frame){
        $cmd = new Get24HsCommodityStrategyFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }


    public function loadSet24HsCommodityStrategy($frame){
        $cmd = new Set24HsCommodityStrategyFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }


    public function loadUpgrateFileName($frame){
        $cmd = new UpgrateFileNameFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }


    public function loadSignInDuration($frame){
        $cmd = new EvseSetIntParaFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }

    public function loadHeartBeatDuration($frame){
        $cmd = new EvseSetIntParaFrame();
        $cmd->loadFrame($frame);
        return $cmd;
    }

    public function loadQRCode($frame){
        $cmd = new EvseSetCharParaFrame();
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

    public function loadUploadBMSInfo($frame){
        $cmd = new UploadBMSInfoFrame();
        $cmd->loadFrame($frame);
        return $cmd;

    }





    /**
     * 获取帧
     * @param $sequence int 序号
     * @param \Wormhole\Protocols\NJINT\Protocol\Base\DataArea $dataArea
     * @return string
     */
    public function getFrame($sequence,$dataArea){
        $frame = new HeartbeatFrame();
        $frame->setSequence($sequence);
        $frame->setDataArea($dataArea);
        return $frame->build();
    }

}