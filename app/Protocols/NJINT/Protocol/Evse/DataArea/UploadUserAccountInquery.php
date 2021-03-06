<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/5
 * Time: 15:24
 */


namespace Wormhole\Protocols\NJINT\Protocol\Evse\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\NJINT\Protocol\Base\MY_Tools;
use Wormhole\Protocols\Tools;
class UploadUserAccountInquery  extends DataArea
{

    /**
     * @var array 协议预留1
     */
    private $reserved1;
    /**
     * @var array 协议预留2
     */
    private $reserved2;
    private $poleId;
    private $cardId;
    private $cardRemainedSum;
    private $blackCardSign;

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
     * @return mixed
     */
    public function getPoleId()
    {
        return $this->poleId;
    }

    /**
     * @param mixed $poleId
     */
    public function setPoleId($poleId)
    {
        $this->poleId = $poleId;
    }

    /**
     * @return mixed
     */
    public function getCardId()
    {
        return $this->cardId;
    }

    /**
     * @param mixed $cardId
     */
    public function setCardId($cardId)
    {
        $this->cardId = $cardId;
    }


    /**
     * @return mixed
     */
    public function getCardRemainedSum()
    {
        return $this->cardRemainedSum;
    }

    /**
     * @param mixed $cardRemainedSum
     */
    public function setCarPlateNumber($cardRemainedSum)
    {
        $this->cardRemainedSum = $cardRemainedSum;
    }

    /**
     * @return mixed
     */
    public function getBlackCardSign()
    {
        return $this->blackCardSign;
    }

    /**
     * @param mixed $blackCardSign
     */
    public function setBlackCardSign($blackCardSign)
    {
        $this->blackCardSign = $blackCardSign;
    }

    public function build(){
        $frame = array_merge($this->reserved1,$this->reserved2); //预留1 预留2

        $frame = array_merge($frame,MY_Tools::asciiToDecArrayWithLength($this->poleId,32,0));


        $frame = array_merge($frame,MY_Tools::asciiToDecArrayWithLength($this->cardId,32,0));

        $frame=array_merge($frame,Tools::decToArray($this->cardRemainedSum,4));
        $frame=array_merge($frame,Tools::decToArray($this->blackCardSign,1));

        return $frame;

    }

    public function load($dataArea){
        $offset = 0;
        $this->reserved1 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->reserved2 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->poleId = trim(Tools::decArrayToAsciiString(array_slice($dataArea,$offset,32)));
        $offset+=32;


        $this->cardId = trim(Tools::decArrayToAsciiString(array_slice($dataArea,$offset,32)));
        $offset+=32;

        $this->cardRemainedSum = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;

        $this->blackCardSign = Tools::arrayToDec(array_slice($dataArea,$offset,1));
        $offset+=1;

    }

}