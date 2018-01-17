<?php
namespace Wormhole\Protocols\HaiGe\Protocol;
use Wormhole\Protocols\Tools;
use Wormhole\Protocols\FrameInterface;
class Frame{

    //启动字符 1字节
    private $begin = [0x68];

    //报文长度 2字节
    private $length = 0;

    //是否注册 1字节 0x00未注册 0x01已注册
    private $register=0x00;

    //响应码 3字节
    private $responseCode = 0;

    //运营商 2字节
    private $carriers = 0;

    //充电设备地址 8字节
    private $deviceAddress = 0;

    //帧类型 1字节
    private $operator=0;

    //流水号 7字节
    private $number = 0;

    //数据
    private $dataArea = array();


    /**
     * 帧格式化结果
     * @var boolean 格式化结果 （true/flase）
     */
    private $correctFormat ;

    /**
     * 格式化错误的错误消息
     * @var string 错误消息
     */
    private $formatMsg;

    /**
     * @var string 帧ascii字符串
     */
    private $frameString;

    public function __construct()
    {
        $this->correctFormat =FALSE;
        $this->formatMsg='';
    }

    /**
     * @return array
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }


    /**
     * @return int
     */
    public function getRegister()
    {
        return $this->register;
    }

    /**
     * @param int $register
     */
    public function setRegister($register)
    {
        $this->register = $register;
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
     * @return string
     */
    public function getCarriers()
    {
        return $this->carriers;
    }

    /**
     * @param string $carriers
     */
    public function setCarriers($carriers)
    {
        $this->carriers = $carriers;
    }


    /**
     * @return string
     */
    public function getDeviceAddress()
    {
        return $this->deviceAddress;
    }

    /**
     * @param string $deviceAddress
     */
    public function setDeviceAddress($deviceAddress)
    {
        $this->deviceAddress = $deviceAddress;
    }

    /**
     * 获取操作码
     * @return int
     */
    public function getOperator(){
        return $this->operator;
    }

    /**
     * 设置操作码
     * @param int $operator
     */
    public function setOpeartor($operator){
        $this->operator =$operator;
    }


    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }


    /**
     * @return array
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @param array $dataArea
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
    }






    /**
     * @return string
     */
    public function getFrameString()
    {
        if(empty($this->frameString)){
            $this->build();
        }
        return $this->frameString;
    }

    /**
     * @param string $frameString
     */
    public function setFrameString($frameString)
    {
        $this->frameString = $frameString;
    }


    /**
     * @return boolean
     */
    public function isCorrectFormat()
    {
        return $this->correctFormat;
    }


    /**
     * @param boolean $correctFormat
     */
    public function setCorrectFormat($correctFormat)
    {
        $this->correctFormat = $correctFormat;
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

        //根据当前的protocol 配置内容，获取所对应的帧string
        //启动字符
        $frame = $this->begin;

        //报文长度
        $frame = array_merge($frame, Tools::decToArray(count($this->dataArea), 2));

        //是否注册
        array_push($frame,  $this->register);

        //响应码
        $frame = array_merge($frame,  Tools::decToArray($this->responseCode, 3, false));

        //运营商
        $frame = array_merge($frame, Tools::decToDbcArray($this->carriers, 2));

        //充电设备地址
        $frame = array_merge($frame,  Tools::decToDbcArray($this->deviceAddress, 8));

        //帧类型
        array_push($frame, $this->operator);

        //流水号
        $frame = array_merge($frame,  Tools::decToDbcArray($this->number, 7));

        //参数
        $frame = array_merge($frame,  $this->dataArea);

        //以ascii呈现
        $frame = Tools::decArrayToAsciiString($frame);

        $this->frameString = $frame;
        return $frame;

    }


    /**
     * 载入并解析帧
     * @param string $frame 待解析帧
     * @return Frame
     */
    public static function load($frame){

        $frame = Tools::asciiStringToDecArray($frame);

        $framePosition = 0;

        while(count($frame) >= $framePosition+25) {

                $exFrame = new Frame();

                $head = $frame[$framePosition++];

                $diffBegin = in_array($head, $exFrame->getBegin()) ? false : true;

                if ($diffBegin) {
                    $exFrame->setFormatMsg( "11111起始域,数据错误.协议起始域 :" . implode($exFrame->getBegin()) ." ". implode($exFrame->getBegin()). ",实际传入:". $head);
                    continue;
                }
                $begin_key = $framePosition-1;

                //获取长度
                $length[0] =  $frame[$framePosition++];
                $length[1] =  $frame[$framePosition++];

                $exFrame->setLength(Tools::arrayToDec($length));
                //数据域长度
                $areaLen = count($frame)-1-2-1-3-2-8-1-7-$begin_key;

                if($areaLen < $exFrame->getLength()){
                    $exFrame->setFormatMsg("22222帧长度不符合要求.帧长度域 :" . $exFrame->getLength(). ",实际传入帧长度:". count($frame));
                    break;
                }

                //是否注册
                $register = $frame[$framePosition++];
                $exFrame->setRegister($register);

                //响应码
                $responseCode[0] = $frame[$framePosition++];
                $responseCode[1] = $frame[$framePosition++];
                $responseCode[2] = $frame[$framePosition++];
                $exFrame->setResponseCode(Tools::arrayToDec($responseCode));

                //运营商
                $carriers[0] = $frame[$framePosition++];
                $carriers[1] = $frame[$framePosition++];
                $exFrame->setCarriers(Tools::dbcArrayTodec( $carriers));

                //充电设备地址
                $deviceAddress[0] = $frame[$framePosition++];
                $deviceAddress[1] = $frame[$framePosition++];
                $deviceAddress[2] = $frame[$framePosition++];
                $deviceAddress[3] = $frame[$framePosition++];
                $deviceAddress[4] = $frame[$framePosition++];
                $deviceAddress[5] = $frame[$framePosition++];
                $deviceAddress[6] = $frame[$framePosition++];
                $deviceAddress[7] = $frame[$framePosition++];

                $exFrame->setDeviceAddress(Tools::dbcArrayTodec( $deviceAddress));

                //帧类型
                $operator =  $frame[$framePosition++];

                $exFrame->setOpeartor($operator);

                //流水号
                $number[0] = $frame[$framePosition++];
                $number[1] = $frame[$framePosition++];
                $number[2] = $frame[$framePosition++];
                $number[3] = $frame[$framePosition++];
                $number[4] = $frame[$framePosition++];
                $number[5] = $frame[$framePosition++];
                $number[6] = $frame[$framePosition++];

                $exFrame->setNumber(Tools::dbcArrayTodec($number));

                //数据域
                $exFrame->setDataArea(array_slice($frame,$framePosition,$areaLen));

            return $exFrame;

        }

    }









}

