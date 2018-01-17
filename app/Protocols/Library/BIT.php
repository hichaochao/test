<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-02
 * Time: 18:14
 */

namespace Wormhole\Protocols\Library;


class BIT implements \JsonSerializable
{
    use Tools;

    protected $length = 0;
    protected $data;

    public function __construct()
    {

    }






    public function __invoke($value = "", $position)
    {
     
    }

    public function __toString()
    {

        return "";
    }

    function jsonSerialize()
    {
        $array = [];
        foreach ($this as $key=>$value){

            $array[$key]= $value;
        }
        return $array;
    }

}