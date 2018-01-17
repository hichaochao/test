<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-02
 * Time: 18:14
 */

namespace Wormhole\Protocols\Library;


class BitArray implements \JsonSerializable
{
    use Tools;
    private $value=0;
    public $length;
    protected $statusBit = [];
    protected $status = [BIN::class,1];
    protected $data;
    public function __construct($bitArr=[], $length=1, $data='')
    {
        $this->length=$length;
        foreach ($bitArr as $k=>$v){
            $this->statusBit[$v[0]] = $v[1];
        }
        $this->data = $data;
    }
    public function getValue(){
        return $this->statusBit;
    }
    public function getClassNamw(){
        return BIN::class;
    }
    public function __invoke($value="", $position)
    {

        if(is_string($value)){

            $bin = new $this->status[0]($this->status[1]); //实例化bin
            //call_user_func($bin, $value, $position); //解析状态帧
            $bin($value, $position);
            $info = $bin->getValue(); //取得值
            $status = decbin($info);  //转换为二进制
            $status = str_pad($status,8,0,STR_PAD_LEFT);
            $len = strlen($status);

            foreach($this->statusBit as $k=>$v){
                //$this->statusBit[$k] = substr($status,--$len,$v); str_pad(substr($status,$len=$len-$v,$v),8-$len-1,0,STR_PAD_LEFT);RIGHT
                //$status = str_pad($status,8,0,STR_PAD_RIGHT);  //$len-($len-$v+1)

//                $status2 = str_pad(substr($status,$len=$len-$v,$v),8-$num,0,STR_PAD_LEFT);
//                $status2 = str_pad($status2,8,0,STR_PAD_RIGHT);
//                $this->statusBit[$k] = bindec($status2);

                $this->statusBit[$k] = bindec(substr($status,$len=$len-$v,$v));


            }
            $this->statusBit['value'] = $info; //未解析的值
            //return $position;
        }

        
    }

    public function __toString()
    {
        $str = '';
        foreach ($this->statusBit as $k=>$v){

            if($v > 1){
                //$str .= str_pad(decbin($this->data[$k]),$v,0,STR_PAD_LEFT);
                $val = str_pad(decbin($this->data[$k]),$v,0,STR_PAD_LEFT);
                $str = $val.$str;
            }else{
                //$str .= $this->data[$k];
                $str = $this->data[$k] .= $str;

            }

        }

        $str = str_pad($str,8,0,STR_PAD_LEFT);
        $str = strval(bindec($str));
        //return self::decArrayToAsciiString(self::decToArray($this->value,$this->length,$this->dir));
        return $str;
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