<?php
namespace Wormhole\Protocols\Library;

/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2015/12/4
 * Time: 12:07
 */
trait Tools
{
    /***
     * 计算BCC值（异或）
     * @param array(int) $toBCC 待计算的数组
     * @param int $length 异或校验长度
     * @return int 1byte
     */
    public static function getBCCByOr($toBCC,$length=1)
    {
        $bcc = 0;
        foreach ($toBCC as $num) {
            $bcc ^= $num;
        }

        $tmp = substr(dechex($bcc), -2*$length);//转成16进制，取后两*$length 位
        $bcc = hexdec($tmp);//转为10进制值
        return $bcc;
    }

    /***
     * 计算BCC值（求和）
     * @param array(int) $toBCC 待计算的数组
     * @param int $length bcc校验和的长度
     * @return int 1byte
     */
    public static function getBCCByPlus($toBCC,$length = 1)
    {
        $bcc = 0.0;
        foreach ($toBCC as $num) {
            $bcc += $num;
        }
        //$bcc = array_sum($toBCC);

        $tmp = substr(dechex($bcc), -2*$length);//转成16进制，取后两*$length位
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



    //CRC16-MODBUS算法
    public static function crc16($nData, $wLength)
    {
        $wCRCTable = array(

            0X0000, 0XC0C1, 0XC181, 0X0140, 0XC301, 0X03C0, 0X0280, 0XC241,
            0XC601, 0X06C0, 0X0780, 0XC741, 0X0500, 0XC5C1, 0XC481, 0X0440,
            0XCC01, 0X0CC0, 0X0D80, 0XCD41, 0X0F00, 0XCFC1, 0XCE81, 0X0E40,
            0X0A00, 0XCAC1, 0XCB81, 0X0B40, 0XC901, 0X09C0, 0X0880, 0XC841,
            0XD801, 0X18C0, 0X1980, 0XD941, 0X1B00, 0XDBC1, 0XDA81, 0X1A40,
            0X1E00, 0XDEC1, 0XDF81, 0X1F40, 0XDD01, 0X1DC0, 0X1C80, 0XDC41,
            0X1400, 0XD4C1, 0XD581, 0X1540, 0XD701, 0X17C0, 0X1680, 0XD641,
            0XD201, 0X12C0, 0X1380, 0XD341, 0X1100, 0XD1C1, 0XD081, 0X1040,
            0XF001, 0X30C0, 0X3180, 0XF141, 0X3300, 0XF3C1, 0XF281, 0X3240,
            0X3600, 0XF6C1, 0XF781, 0X3740, 0XF501, 0X35C0, 0X3480, 0XF441,
            0X3C00, 0XFCC1, 0XFD81, 0X3D40, 0XFF01, 0X3FC0, 0X3E80, 0XFE41,
            0XFA01, 0X3AC0, 0X3B80, 0XFB41, 0X3900, 0XF9C1, 0XF881, 0X3840,
            0X2800, 0XE8C1, 0XE981, 0X2940, 0XEB01, 0X2BC0, 0X2A80, 0XEA41,
            0XEE01, 0X2EC0, 0X2F80, 0XEF41, 0X2D00, 0XEDC1, 0XEC81, 0X2C40,
            0XE401, 0X24C0, 0X2580, 0XE541, 0X2700, 0XE7C1, 0XE681, 0X2640,
            0X2200, 0XE2C1, 0XE381, 0X2340, 0XE101, 0X21C0, 0X2080, 0XE041,
            0XA001, 0X60C0, 0X6180, 0XA141, 0X6300, 0XA3C1, 0XA281, 0X6240,
            0X6600, 0XA6C1, 0XA781, 0X6740, 0XA501, 0X65C0, 0X6480, 0XA441,
            0X6C00, 0XACC1, 0XAD81, 0X6D40, 0XAF01, 0X6FC0, 0X6E80, 0XAE41,
            0XAA01, 0X6AC0, 0X6B80, 0XAB41, 0X6900, 0XA9C1, 0XA881, 0X6840,
            0X7800, 0XB8C1, 0XB981, 0X7940, 0XBB01, 0X7BC0, 0X7A80, 0XBA41,
            0XBE01, 0X7EC0, 0X7F80, 0XBF41, 0X7D00, 0XBDC1, 0XBC81, 0X7C40,
            0XB401, 0X74C0, 0X7580, 0XB541, 0X7700, 0XB7C1, 0XB681, 0X7640,
            0X7200, 0XB2C1, 0XB381, 0X7340, 0XB101, 0X71C0, 0X7080, 0XB041,
            0X5000, 0X90C1, 0X9181, 0X5140, 0X9301, 0X53C0, 0X5280, 0X9241,
            0X9601, 0X56C0, 0X5780, 0X9741, 0X5500, 0X95C1, 0X9481, 0X5440,
            0X9C01, 0X5CC0, 0X5D80, 0X9D41, 0X5F00, 0X9FC1, 0X9E81, 0X5E40,
            0X5A00, 0X9AC1, 0X9B81, 0X5B40, 0X9901, 0X59C0, 0X5880, 0X9841,
            0X8801, 0X48C0, 0X4980, 0X8941, 0X4B00, 0X8BC1, 0X8A81, 0X4A40,
            0X4E00, 0X8EC1, 0X8F81, 0X4F40, 0X8D01, 0X4DC0, 0X4C80, 0X8C41,
            0X4400, 0X84C1, 0X8581, 0X4540, 0X8701, 0X47C0, 0X4680, 0X8641,
            0X8201, 0X42C0, 0X4380, 0X8341, 0X4100, 0X81C1, 0X8081, 0X4040

        );


        $nTemp = '';
        $wCRCWord = 0xFFFF;
        $i = 0;

        while ( ($wLength--) > 0) {

            $nTemp =  $nData[$i++] ^ $wCRCWord;

            $nTemp = $nTemp % 256;
            $wCRCWord >>= 8;

            $wCRCWord ^= $wCRCTable[$nTemp];

        }

        return $wCRCWord;
    }


    /**
     * 字符串 to asscii 数组
     * @param string $str 待转换字符串
     * @return array
     */
    public static function asciiStringToHexArray($str, $lowBefore=FALSE)
    {
        $frame = array();
        if (is_string($str)) {
            for ($i = 0; $i < strlen($str); $i++) {
                //$frame .= str_pad(dechex(ord($str[$i])), 2, "0", STR_PAD_LEFT);
                array_push($frame, str_pad(dechex(ord($str[$i])), 2, "0", STR_PAD_LEFT));
            }
        }
        if(FALSE === $lowBefore){  //如果 低字节 不在前，则反序输出
            $frame = array_reverse($frame);
        }

        $str = implode($frame);
        return $str;
    }



    /**
     * BCD压缩
     * @param int $data
     */
    public static function bcd_compress($data){

        $str = '';
        $len = strlen($data);
        for($i=0;$i<$len;$i++){

            $tmp = decbin(substr($data, $i, 1));
            $str .= str_pad($tmp, 4, "0", STR_PAD_LEFT);


        }
        $dec = bindec($str);
        return $dec;
    }
















}