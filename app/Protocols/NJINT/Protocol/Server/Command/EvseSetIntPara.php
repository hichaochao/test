<?php
namespace Wormhole\Protocols\NJINT\Protocol\Server\Command;
use Wormhole\Protocols\Tools;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/6/17
 * Time: 16:10
 */
class EvseSetIntPara
{
    /**
     * @var int 命令起始标志
     */
    private $cmdStartPosition;
    /**
     * @var int 命令结束标志位
     */
    private $cmdEndPosition;
    /**
     * @var int 命令个数
     */
    private $cmdNum;
    /**
     * @var array 命令参数
     */
    private $cmdArgsArray;




    public function __construct()
    {
        $this->cmdArgsArray = array_fill(1,17,-1);
        $this->cmdStartPosition =0;

    }


    /**
     * @return int
     */
    public function getCmdStartPosition()
    {
        return $this->cmdStartPosition;
    }

    /**
     * @param int $cmdPosition
     */
    private function setCmdPosition($cmdPosition)
    {
        $this->cmdStartPosition =$this->cmdStartPosition > $cmdPosition || $this->cmdStartPosition == 0 ?$cmdPosition:$this->cmdStartPosition; // 如果已有当前的起始位置在当前命令之后，则设置为当前值；

        $this->cmdEndPosition =$this->cmdEndPosition < $cmdPosition ?$cmdPosition:$this->cmdEndPosition; //

        $this->cmdNum = $this->cmdEndPosition >= $this->cmdStartPosition && $this->cmdStartPosition>0 ? ($this->cmdEndPosition-$this->cmdStartPosition +1):0;
    }

    /**
     * 获取命令参数的长度
     * @return int 根据最近位置计算出来的命令长度
     */
    public function getCmdLength()
    {
        return $this->cmdEndPosition>=$this->cmdStartPosition && $this->cmdStartPosition>=1 ?  ($this->cmdEndPosition-$this->cmdStartPosition +1)*4  :0  ;
    }

    /**
     * 根据命令起始位置计算出的内容
     * @return array
     */
    private function getCmdArgs(){

        //TODO 每次最多设置10个参数
        //$firstPosition =0;
        //$lastPosition =0;
        //for($i = 0;$i<count($this->cmdArgsArray);$i++){
        //    $cmdArg = $this->cmdArgsArray[$i];
        //    if(1==$cmdArg){
        //        if(0==$firstPosition){
        //            $firstPosition = $i+1;
        //        }
        //        $lastPosition = $i+1;
        //    }
        //}
        //
        //if($lastPosition-$firstPosition>=10){
        //
        //}

        $cmdFrame = array();
        for($i=$this->cmdStartPosition;$i<=$this->cmdEndPosition;$i++){
            $tmp  = $this->cmdArgsArray[$i];
            //var_dump( __NAMESPACE__."/".__FUNCTION__."@".__LINE__." tmp:$tmp " );DIE;
            if(-1==$tmp){
                break;
            }
            $tmp = MY_Tools::asciiToDecArrayWithLength('30',4,$this->cmdArgsArray[$i]);
            $cmdFrame = array_merge($cmdFrame,$tmp);
        }
//        $cmdFrame = [0x36,0x30,0x0,0x0];

        return $cmdFrame;
    }



    /**
     * @return int
     */
    public function getSignInDuration()
    {
        return $this->cmdArgsArray[1];
    }

    /**
     * @param int $argSignDuration
     */
    public function setSignInDuration($argSignInDuration)
    {
        //$this->argStopCharge = $argStopCharge;
        $this->cmdArgsArray[1] =$argSignInDuration;
        $this->setCmdPosition(1);
    }

    /**
     * @return int
     */
    public function getProjectType()
    {
        return $this->cmdArgsArray[2];
    }

    /**
     * @param int $argChargeControlType
     */
    public function setProjectType($argProjectType)
    {
        //$this->argChargeControlType = $argChargeControlType;
        $this->cmdArgsArray[2]  = $argProjectType;
        $this->setCmdPosition(2);
    }


    /**
     * @return int
     */
    public function getHeartBeatDuration()
    {
        return $this->cmdArgsArray[21];
    }

    /**
     * @param int $argSignDuration
     */
    public function setHeartBeatDuration($argSignInDuration)
    {
        //$this->argStopCharge = $argStopCharge;
        $this->cmdArgsArray[21] =$argSignInDuration;
        $this->setCmdPosition(21);
    }



    public function build()
    {
        $cmdFrame = Tools::decToArray($this->cmdStartPosition,4); // 命令起始标志；
        array_push($cmdFrame,$this->cmdNum);//命令个数
        //var_dump($cmdFrame);
        //var_dump( __NAMESPACE__."/".__FUNCTION__."@".__LINE__." cmd length:". $this->getCmdLength());
        $cmdFrame = array_merge($cmdFrame,Tools::decToArray($this->getCmdLength(),2)); //命令参数长度
        //var_dump($cmdFrame);
        $cmdFrame = array_merge($cmdFrame,$this->getCmdArgs()); //命令参数
        //var_dump($cmdFrame);
        return $cmdFrame;
    }

    /**
     * @param $command array
     */
    public function load($command){
        $offset =0;
        $this->cmdStartPosition=$command[$offset];
        $offset++;

        $this->cmdNum = $command[$offset];
        $offset++;

        $offset+=2;// 命令参数长度是计算出来的；

        $cmdArgs = array_slice($command,$offset);
        $cmdArgsLength = count($cmdArgs)/4; //4个字节一个
        $this->cmdEndPosition = $this->cmdStartPosition+($cmdArgsLength-1); //结束位置 是起始位置和长度之和减起始位置

        $cmdOffset = 0;
        for($i=0;$i<$cmdArgsLength;$i++){

            $cmd=array_slice($cmdArgs,$cmdOffset,4);
            $cmdOffset+=4;

            $this->cmdArgsArray[$this->cmdStartPosition+$i]=Tools::arrayToDec($cmd);
        }

    }

}