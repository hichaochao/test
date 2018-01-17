<?php
/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/24
 * Time: 10:42
 */

namespace Wormhole\Protocols\HD10\Protocol\Server\DataArea;


use Wormhole\Protocols\Tools;

class CommoditySet
{
    /**
     * @var
     * 桩编号
     */
    private $evseCode;

    /**
     * @var int
     * 类型
     */
    private $type;

    /**
     * @var array
     * 费率
     */
    private $rates;

    /**
     * @param $evseCode
     */
    public function setEvseCode($evseCode){
        $this->evseCode = $evseCode;
    }

    public function getEvseCode(){
        return $this->evseCode;
    }

    /**
     * @param $type
     */
    public function setType($type){
        $this->type = $type;
    }

    public function getTyoe(){
        return $this->type;
    }
    /**
     * @return array
    */
    public function getRates()
    {
        return $this->rates;
    }

    /**
     * @param array $rates
     */
    public function setRates($rates)
    {
        $this->rates = $rates;
    }

    public function build(){
        $frame = Tools::asciiStringToDecArray($this->evseCode);

        array_push($frame,$this->type);

        foreach ($this->rates as $rate) {
            $frame = array_merge($frame , Tools::decToArray($rate,2,FALSE));
        }

        return $frame;
    }

    public function load($dataArea){
        $offset = 0;
        $this->evseCode = Tools::decArrayToAsciiString(array_slice($dataArea,$offset,8));
        $offset+=8;
        $this->type =array_slice($dataArea,$offset,1)[0];
        $offset+=1;
        $rates = [];
        while (array_slice($dataArea,$offset,2)) {
            array_push($rates,Tools::arrayToDec(array_slice($dataArea,$offset,2),FALSE));
            $offset+=2;
        }
        $this->rates = $rates;
    }
}