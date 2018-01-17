<?php
/**
 * Created by PhpStorm.
 * User: Jihailiang
 * Date: 2016/7/6
 * Time: 16:03
 */
namespace Wormhole\Protocols\NJINT\Protocol\Server\DataArea;


use Wormhole\Protocols\NJINT\Protocol\Base\DataArea;
use Wormhole\Protocols\Tools;

//TODO 协议处理
class UpgrateFileName extends DataArea
{
    /**
     * @var string 文件名
     */
    private $upgrateFileName;
    public function __construct()
    {

    }




    /**
     * @return string
     */
    public function getUpgrateFileName()
    {
        return $this->upgrateFileName;
    }

    /**
     * @param string $upgrateFileName
     */
    public function setUpgrateFileName($upgrateFileName)
    {
        $this->upgrateFileName = $upgrateFileName;
    }

    public function build(){
        $frame =Tools::decToArray($this->upgrateFileName,127);

        return $frame;


    }

    public function load($dataArea){
        $offset = 0;
        $this->upgrateFileName = Tools::arrayToDec(array_slice($dataArea,$offset,127));
        $offset+=127;


    }
}