<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/6
 * Time: 14:19
 */
namespace Wormhole\Protocols\NJINT\Protocol\Server\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\Tools;

//TODO 协议处理
class EraseInstruction extends DataArea
{
    /**
     * @var string 擦除/查询指令
     */
    private $eraseInstruction;
    public function __construct()
    {

    }


    /**
     * @return string
     */
    public function getEraseInstruction()
    {
        return $this->eraseInstruction;
    }

    /**
     * @param string $eraseInstruction
     */
    public function setEraseInstruction($eraseInstruction)
    {
        $this->eraseInstruction = $eraseInstruction;
    }


    public function build(){
        $frame =Tools::decToArray($this->eraseInstruction,4);

        return $frame;


    }

    public function load($dataArea){
        $offset = 0;
        $this->eraseInstruction = Tools::arrayToDec(array_slice($dataArea,$offset,4));
        $offset+=4;


    }
}