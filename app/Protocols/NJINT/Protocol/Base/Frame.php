<?php
namespace Wormhole\Protocols\NJINT\Protocol\Base;
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-05-24
 * Time: 10:49
 */


use Wormhole\Protocols\FrameInterface;
use  Wormhole\Protocols\Tools;


/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2015/11/6
 * Time: 18:57
 */
class Frame implements FrameInterface
{
    private $begin = [0xaa,0xf5];

  //起始域 2字节
    private $length = 0;

   //长度域 2字节
    CONST VERSION=0x10;      //版本域 1字节
    private $sequence=0x00;

       //序列号域 1字节
    private $operator=0; //命令字 CMD

    /**
     * @var array
     */
    private $dataArea=array();
//    public $appData;
    private $bcc=0;
    const BREAK_CODE = 0x0A;

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
     * @return int
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @param int $sequence
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
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




    public function getBcc()
    {
        return $this->bcc;
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

    public function getPoleId(){}
    
    public function getUserId(){}
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
     * @return string
     */
    public function build()
    {

        //根据当前的protocol 配置内容，获取所对应的帧string
        $frame = $this->begin;

        //长度
        $lenStr = 2 + 2 +1+1+2 + count($this->dataArea) + 1 ; //起始域2+长度2+版本域1+序列号1+命令字2+数据域N+校验1
        $this->length = Tools::decToArray($lenStr, 2);
        $frame = array_merge($frame, $this->length);
        //var_dump($frame);

        //版本
        array_push($frame,  Frame::VERSION);

        //序列号
        array_push($frame,$this->sequence);

        $bccArea = array_merge(  Tools::decToArray( $this->operator,2),$this->dataArea);

        ////操作码
        //$frame = array_merge($frame, Tools::decToArray( $this->operator,2));
        ////var_dump($frame);
        ////数据域
        //$frame = array_merge($frame,$this->dataArea);
        ////var_dump($frame);

        //操作码+数据域
        $frame= array_merge($frame,$bccArea);

        $this->bcc =Tools::getBCCByPlus($bccArea);
        array_push($frame, $this->bcc);

        //以ascii呈现
        $frame = Tools::decArrayToAsciiString($frame);


        $this->frameString = $frame;
        return $frame;

    }

    /**
     * 载入并解析帧
     * @param string $frame 待解析帧
     * @return Frame[]
     */
    public static function load($frame)
    {
        $frame = Tools::asciiStringToDecArray($frame);
        $framePosition = 0;

        $exFrameList=array();

        while(count($frame)>$framePosition+1){
            $thisFramePosition = $framePosition;
            $exFrame = new Frame();

            if (count($frame) > 9) {
                $head[0] = $frame[$framePosition++];
                $head[1] = $frame[$framePosition++];

                $diffBegin = array_diff($head,$exFrame->getBegin()) || array_diff($exFrame->getBegin(),$head);

                if ($diffBegin) {
                    $exFrame->setFormatMsg( "起始域,数据错误.协议起始域 :" . $exFrame->getBegin()[0] ." ". $exFrame->getBegin()[1]. ",实际传入:". $head[0]." ".$head[1]);
                    continue;
                }
                //获取版本信息
                $length[0] = $frame[$framePosition++];
                $length[1] = $frame[$framePosition++];
                //$this->length = Tools::arrayToDec( $length);
                $exFrame->setLength(Tools::arrayToDec( $length));

                if(count($frame)< $exFrame->getLength()){
                    $exFrame->setFormatMsg("帧长度不符合要求.帧长度域 :" . $exFrame->getLength(). ",实际传入帧长度:". count($frame));
                    break;
                }


                //版本号
                $version = $frame[$framePosition++];
                if(Frame::VERSION != $version){ //验证版本号
                    $exFrame->setFormatMsg("版本号错误。当前协议版本号：" .Frame::VERSION ." ". ",实际传入: $version");
                    break;
                }
            } else {
                $exFrame->setFormatMsg("输入帧长度过小，当前长度:".count($frame));
                break;
            }

            //获取序列号
            $exFrame->setSequence($frame[$framePosition++]);

            //验证 BCC
            $bcc = $frame[$framePosition+$exFrame->getLength()-2-2-1-1-1];
            $calcBcc = Tools::getBCCByPlus(array_slice($frame,$framePosition,$exFrame->getLength()-2-2-1-1-1));  //仅仅计算数据域的长度


            if($bcc != $calcBcc){
                $exFrame->setFormatMsg("BCC不匹配，协议校验域： $bcc , 计算值：$calcBcc");
                break;
            }else{
                $exFrame->setCorrectFormat( TRUE);
            }



            //命令字
            $operator[0] =  $frame[$framePosition++];
            $operator[1] =  $frame[$framePosition++];

            $exFrame->setOpeartor( Tools::arrayToDec($operator));

            $exFrame->setDataArea(array_slice($frame,$framePosition,$exFrame->getLength() -2-2-1-1-1-2));
            $framePosition+=count($exFrame->dataArea);

            $framePosition++;//bbc


            $frameString = Tools::decArrayToAsciiString(array_slice($frame,$thisFramePosition,$framePosition));
            $exFrame->setFrameString($frameString);


            $exFrameList[count($exFrameList)]=$exFrame;


        }
        return $exFrameList;
    }

    //public function getPileCode()
    //{
    //    // TODO: Implement getPileCode() method.
    //}
    //
    //public function getUserId()
    //{
    //    // TODO: Implement getUserId() method.
    //}
}