<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/5
 * Time: 17:34
 */


namespace Wormhole\Protocols\NJINT\Protocol\Server\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;

//TODO 协议处理
class UploadUserPwdVerify extends DataArea
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

    public function build(){
        $frame = array_merge($this->reserved1,$this->reserved2); //预留1 预留2

        $frame=array_merge($frame,Tools::decToArray($this->responseCode,4));
        $frame=array_merge($frame,Tools::decToArray($this->remainedSum,4));

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

    }

}