<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/6/16
 * Time: 10:59
 */


namespace Wormhole\Protocols\NJINT\Protocol\Server\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;

use Wormhole\Protocols\NJINT\Protocol\Server\Command\EvseSetCharPara as EvseSetCharParaCommand;
/**
 *
 * @package smartpd\DataArea
 */
class EvseSetCharPara extends DataArea
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
     * @var int 设置类型
     */
    private $setType;



    /**
     * @var Wormhole\Protocols\NJINT\Protocol\Server\Command\EvseSetCharPara
     */
    private $cmdControl;

    public function __construct()
    {
        $this->reserved1=[0x00,0x00];
        $this->reserved2=[0x00,0x00];
        $this->cmdControl = new EvseSetCharParaCommand();
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
    public function getSetType()
    {
        return $this->setType;
    }

    /**
     * @param int $gunNum
     */
    public function setSetType($setType)
    {
        $this->setType = $setType;
    }



    /**
     * @return \Wormhole\Protocols\NJINT\Protocol\Server\Command\EvseControl
     */
    public function getCmdControl()
    {
        return $this->cmdControl;
    }

    /**
     * @param \Wormhole\Protocols\NJINT\Protocol\Server\Command\EvseSetCharPara $cmdControl
     */
    public function setCmdControl($cmdControl)
    {
        $this->cmdControl = $cmdControl; //设置命令字内容
    }


    /**
     * 获取帧
     * @return array|int
     */
    public function  build()
    {
        //TODO 如果出现超过10个参数的情况需要做调整；


        $frame = array_merge($this->reserved1,$this->reserved2); //预留1 预留2

        array_push($frame,$this->setType);//设置类型
        //var_dump($frame);

        $frame = array_merge($frame,$this->cmdControl->build());

        return $frame;
    }

    /**
     * @param $dataArea array
     * @return bool
     */
    public function load($dataArea){
        $offset = 0;
        $this->reserved1 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->reserved2 = array_slice(  $dataArea,$offset,2);
        $offset+=2;

        $this->setType = $dataArea[$offset];
        $offset++;

        $cmdData = array_slice($dataArea,$offset);

        $evseControl = new EvseSetCharParaCommand();
        $evseControl->load($cmdData);

        $this->cmdControl = $evseControl;

        return TRUE;
    }



}