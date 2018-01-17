<?php
namespace Wormhole\Protocols;

/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2015/12/4
 * Time: 12:07
 */
class Tools
{
    /***
     * 计算BCC值（异或）
     * @param array(int) $toBCC 待计算的数组
     * @return int 1byte
     */
    public static function getBCCByOr($toBCC)
    {
        $bcc = 0;
        foreach ($toBCC as $num) {
            $bcc ^= $num;
        }

        $tmp = substr(dechex($bcc), -2, 2);//转成16进制，取后两位
        $bcc = hexdec($tmp);//转为10进制值
        return $bcc;
    }

    /***
     * 计算BCC值（求和）
     * @param array(int) $toBCC 待计算的数组
     * @return int 1byte
     */
    public static function getBCCByPlus($toBCC)
    {
        $bcc = 0.0;
        foreach ($toBCC as $num) {
            $bcc += $num;
        }
        //$bcc = array_sum($toBCC);

        $tmp = substr(dechex($bcc), -2, 2);//转成16进制，取后两位
        $bcc = hexdec($tmp);//转为10进制值
        return $bcc;
    }

    /**
     * 将10进制数据转成 帧的数组格式
     * @param int $dec 待转换的数值 1234->0x4D2->array(0xd2,0x04,...{len-2})
     * @param int $len 转换后数组的长度
     * @param  bool $lowBefore 是否低位前置
     * @return array(hex)
     */
    public static function decToArray($dec, $len,$lowBefore=TRUE)
    {

        //弃用bc math
        //$tmp = self::bcDecToHexStr($dec); // 1234-> 0x4D2
        $tmp = dechex($dec);
        //在字符串左边补0. 如果$len=8则，则不13个  00 00 00 00 00 00 04 D2
        $tmp = str_pad($tmp, $len * 2, "0", STR_PAD_LEFT);

        $frame = array();

        while (!empty($tmp)) {
            $val = substr($tmp, -2, 2); //取末尾2个
            array_push($frame, hexdec($val));//放到数组最前面（低位在前）
            $tmp = substr($tmp, 0, -2);//去除最后2个 ，这样tmp会越来越短
        }

        $frame = array_slice($frame, 0, $len);

        if(FALSE === $lowBefore){  //如果 低字节 不在前，则反序输出
            $frame = array_reverse($frame);
        }

        return $frame;
    }

    /**
     * 将10进制数组to数值，默认低字节在前
     * array(2,76,159,86)-->1453280258
     * array(0x02,0x4c,0x9f,0x56)-->0x569f4c02-->1453280258
     * @param array(int) $array 十进制的数组
     * @param  bool $lowBefore 是否低位前置
     * @return int 对应的十进制数值
     */
    static function arrayToDec($array,$lowBefore=TRUE)
    {

        if(FALSE === $lowBefore){ //反序
            $array = array_reverse($array);
        }

        $t = '';
        foreach ($array as $d){
                $t = str_pad( dechex($d),2,"0",STR_PAD_LEFT).$t;
        }
        //var_dump($t);

        $result =hexdec($t);

        return $result;
        //弃用bcmath
        //
        //$dec = 0;
        //
        //if(FALSE === $lowBefore){
        //    $array = array_reverse($array);
        //}
        //
        //for ($i = 0; $i < count($array); $i++) {
        //    $dec = bcadd($dec, bcmul($array[$i], bcpow(256, $i)));
        //}
        //
        //
        //return $dec;
//        var_dump($dec);die;

//        $hex = '';
//        for ($i = 0; $i < count($array); $i++) {
//            $hex = str_pad($hex.dechex($array[$i])  , 2, "0", STR_PAD_LEFT);
//        }

//        var_dump($hex);
////        var_dump(self::bcHexStrDec("569f4c0289552255"));
//        var_dump(self::bcHexStrDec("55225589024c9f56"));
//        die;
//
//        $hexarray = array('02','4c','9f','56');
//        return self::bcHexDec($array);
//
//        return self::bcHexStrDec($hex);
    }


    /**
     * 字符串 to asscii 数组
     * @param string $str 待转换字符串
     * @return array
     */
    public static function asciiStringToDecArray($str)
    {
        $frame = array();
        if (is_string($str)) {
            for ($i = 0; $i < strlen($str); $i++) {
                array_push($frame, ord($str[$i]));
            }
        }

        return $frame;
    }

    /**
     * 字符串 to asscii 数组
     * @param string $str 待转换字符串
     * @return array
     */
    public static function asciiStringToHexString($str)
    {
//        var_dump($str);
        $frame = '';
        if (is_string($str)) {
            for ($i = 0; $i < strlen($str); $i++) {
                $frame .= str_pad(dechex(ord($str[$i])), 2, "0", STR_PAD_LEFT);
            }
        }

        return $frame;
    }

    /**
     * asscii 数组 to 字符串
     * @param array(asscii) $arr 待转换字符串
     * @return string
     */
    public static function decArrayToAsciiString($arr)
    {
        $frame = '';
        for ($i = 0; $i < count($arr); $i++) {
            $frame .= chr($arr[$i]);
        }

        return $frame;
    }


    /**
     * 10进制 数组 to 16进制 字符串
     * @param $array
     * @return string
     */
    public static function decArrayToHexString($array)
    {
        $str = '';
        foreach ($array as $dec) {
            $str .= str_pad(dechex($dec), 2, "0", STR_PAD_LEFT);
        }

        return $str;
    }


    /**
     * 帧字符串 to 数组
     * @param $frameString
     * @return array|null
     */
    public static function hexStringToDecArray($frameString)
    {

        $tmpFrame = $frameString;
        if (strlen($tmpFrame) % 2 != 0) {
            return NULL;
        }

        $arr = array();
        while (!empty($tmpFrame)) {
            $f = substr($tmpFrame, 0, 2);
            array_push($arr, hexdec($f));
            $tmpFrame = substr($tmpFrame, 2);
        }

        return $arr;

    }

    /**
     * 大数字tohex （低位在前） 0x1234=> array(0x34,0x12)
     * @param $dec 10进制字符串
     * @return array
     */
    public static function bcDecToHexArray($dec)
    {

        $hex = array();
        do {
            $last = bcmod($dec, 256);
            $lastHex = str_pad(dechex($last), 2, 0, STR_PAD_LEFT);
            array_push($hex, $lastHex);
            $dec = bcdiv(bcsub($dec, $last), 256);
        } while ($dec > 0);

        return $hex;
    }

    /**
     * 大数字tohex （低位在前） 0x1234=> array(0x34,0x12)
     * @param $dec
     * @return array
     */
    public static function bcDecToHexStr($dec)
    {

        $hex = '';
        do {

            $last = bcmod($dec, 256);
            $lastHex = str_pad(dechex($last), 2, 0, STR_PAD_LEFT);
            $hex = $lastHex . $hex;
            $dec = bcdiv(bcsub($dec, $last), 256);
        } while ($dec > 0);

        return $hex;
    }

    /**
     * 16进制to10进制，
     * @param array $hex 低位在前，高位在后
     * @return int|string
     */
    static function bcHexDec($hex)
    {
        $dec = 0;
//        var_dump($hex);
        $len = count($hex);
        for ($i = 0; $i < $len; $i++) {
//            var_dump(strval(hexdec($hex[$i - 1])));
//            var_dump( bcmul(0x30,bcpow(2,56)));
//            var_dump(bcpow('16', strval($len - $i)));
//            var_dump(strval(hexdec($hex[$i-2].$hex[$i-1])));
//            var_dump( $i*8);
//            var_dump(bcmul(strval(hexdec($hex[$i-2].$hex[$i-1])),bcpow(2,$len*16-$i*8)));
            $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i])), bcpow(2, $i * 8)));
        }

        return $dec;
    }

    /**
     * hex string to dec
     * 569f4c02 -->array('56','9f','4c','02') -->1453280258
     * @param string $hex
     * @return int|string
     */
    static function bcHexStrDec($hex)
    {
        $dec = 0;
        $len = strlen($hex) / 2;


        $hexArr = array();
        for ($i = 2; $i <= $len * 2; $i += 2) {
            array_push($hexArr, $hex[$i - 2] . $hex[$i - 1]);
        }


        for ($i = 0; $i < count($hexArr); $i++) {

            $dec = bcadd($dec, bcmul((hexdec($hexArr[$i])), bcpow(2, (count($hexArr) - 1 - $i) * 8)));
//            var_dump(hexdec('56'));
//            $dec =bcmul((hexdec('56')),bcpow(2,24));
//            var_dump($dec);die;
        }


        return $dec;


        ////好像不通了，不知道为啥
//        for ($i = 2; $i <= $len*2; $i+=2) {
////            var_dump(strval(hexdec($hex[$i - 1])));
////            var_dump( bcmul(0x30,bcpow(2,56)));
////            var_dump(bcpow('16', strval($len - $i)));
////            var_dump(strval(hexdec($hex[$i-2].$hex[$i-1])));
////            var_dump( $len*16-$i*8);
////            var_dump(bcmul(strval(hexdec($hex[$i-2].$hex[$i-1])),bcpow(2,$len*16-$i*8)));
////
////            die;
//            $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i-2].$hex[$i-1])),bcpow(2,$len*16-$i*8)));
//
//        }
//        return $dec;
    }

    /**
     * 10进制的8字节标准时间
     * @param $dateArray array [0x20,0x16,0x08,0x11,0x15,0x46,0x41,0xff] ==> 20160811154641ff
     * @return bool|string 2016-08-11 15:46:41
     */
    public static function decArrayToDate($dateArray){
        //时间格式为 20160811154641ff 的 16进制值
        $time =  Tools::asciiStringToHexString( Tools::decArrayToAsciiString( $dateArray));
        $time =preg_replace("/[^0-9.]+/i", "", $time); //移除字符
        return date("Y-m-d H:i:s", strtotime($time));
    }


    /**
     * 日期 转 10进制8字节 数组
     * @param $date  string 2016-08-11 15:46:41
     * @return array [0x20,0x16,0x08,0x11,0x15,0x46,0x41,0xff]
     */
    public static function dateToDecArray($date){
        $time = date("YmdHis", strtotime($date));
        $time .='ff';
        $time = Tools::decArrayToAsciiString(  Tools::hexStringToDecArray($time));
        return Tools::asciiStringToDecArray($time);
    }


    /**
     * 10进制数组转字符串
     * @param $dateArray array [0x20,0x16,0x08,0x11,0x15,0x46,0x41,0xff] ==> 20160811154641ff
     * @return bool|string 2016-08-11 15:46:41
     */
    public static function dbcArrayTodec($dateArray){

        $data =  Tools::asciiStringToHexString( Tools::decArrayToAsciiString( $dateArray));
        return $data;
        //$time =preg_replace("/[^0-9.]+/i", "", $time); //移除字符
        //return date("Y-m-d H:i:s", strtotime($time));
    }


    /**
     * 字符串 转 10进制 数组
     * @param $data string
     * @return array [0x20,0x16,0x08,0x11,0x15,0x46,0x41]
     */
    public static function decToDbcArray($data, $len){

        $tem = str_pad($data, $len * 2, "0", STR_PAD_RIGHT);
        $info = Tools::decArrayToAsciiString(  Tools::hexStringToDecArray($tem));
        return Tools::asciiStringToDecArray($info);

    }

}