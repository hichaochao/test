<?php

namespace Wormhole\Protocols\Unicharge\Protocol\Base;
use Wormhole\Protocols\Tools;

class UpgradeFrame
{

    //起始域
    private $begin = [0x68];
    //长度域
    private $length;
    //命令字
    private $operator = 9901;
    //数据域
    private $dataArea = [];
    //校验位
    private $check = 0;

    /**
     * 格式化错误的错误消息
     * @var string 错误消息
     */
    private $formatMsg;

    public function __construct()
    {
    }

    /**
     * @return array
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @return string
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param string $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }


    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }


    /**
     * @return string
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param string $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
    }


    /**
     * @return string
     */
    public function getCheck()
    {
        return $this->check;
    }

    /**
     * @param string $check
     */
    public function setCheck($check)
    {
        $this->check = $check;
    }



    /**
     * @return string
     */
    public function getFormatMsg()
    {
        return $this->formatMsg;
    }

    /**
     * @param string $formatMsg
     */
    public function setFormatMsg($formatMsg)
    {
        $this->formatMsg = $formatMsg;
    }




    /**
     * @return string
     */
    public function build(){

        $frame = $this->begin;  //起始域

        $str = $this->dataArea;
        $lenStr = 1+2+2+count($str)+1;

        $frame = array_merge($frame, Tools::decToArray($lenStr, 2)); //长度域

        $frame = array_merge($frame, Tools::decToArray($this->operator, 2)); //指令

        $frame = array_merge($frame,  $this->dataArea);  //数据域

        $this->check = Tools::getBCCByOr($frame);

        array_push($frame,  $this->check);  //校验位

        //以ascii呈现
        $frame = Tools::decArrayToAsciiString($frame);

        return $frame;


    }


    /**
     * 载入并解析帧
     * @param string $frame 待解析帧
     * @return UpgradeFrame
     */
    public static function load($frame){

        $frame = Tools::asciiStringToDecArray($frame);

        $framePosition = 0;

        while(count($frame) >= $framePosition+6) {

            $exFrame = new UpgradeFrame();

            $head = $frame[$framePosition++]; //起始域

            $diffBegin = in_array($head, $exFrame->getBegin()) ? false : true;

            if ($diffBegin) {
                $exFrame->setFormatMsg( "起始域,数据错误.协议起始域 :" . implode($exFrame->getBegin()) ." ". implode($exFrame->getBegin()). ",实际传入:". $head);
                //continue;
                break;
            }

            $begin_key = $framePosition-1;

            //获取长度
            $length[0] =  $frame[$framePosition++];
            $length[1] =  $frame[$framePosition++];
            $exFrame->setLength(Tools::arrayToDec( $length));
            //命令字
            $operator[0] = $frame[$framePosition++];
            $operator[1] = $frame[$framePosition++];
            $exFrame->setOperator(Tools::arrayToDec( $operator));
            //数据域
            $exFrame->setDataArea(array_slice($frame,$framePosition,$exFrame->getLength() -1-2-2-1));
            $framePosition+=count($exFrame->dataArea);

            $framew = $frame;

            //去掉错误的帧$begin_key
            array_splice($frame,0,$begin_key);
            //去掉参数后面的数据
            array_splice($frame,$framePosition-$begin_key);

            $bcc = Tools::getBCCByOr($frame);
            array_push($frame,  $bcc);

            //校验位
            $check = $frame[$framePosition++];
            //$diffCheck = array_diff($bcc_arr,$check) || array_diff($check,$bcc_arr);
            if($check != $bcc){
                $exFrame->setFormatMsg("校验出错：" .$bcc ." ". ",实际传入: ".$check);
                break;
            }else{
                $exFrame->setCheck( $check);
                return $exFrame;

            }


        }


    }






}