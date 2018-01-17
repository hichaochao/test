<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2016-11-08
 * Time: 11:21
 */

namespace Wormhole\Protocols\ZH;

use Illuminate\Support\Facades\Log;
use Workerman\Connection\TcpConnection;
use Wormhole\Protocols\Tools;
use Wormhole\Protocols\ZH\Protocol\Frame;

class Protocol
{
    const NAME="ZH";
    const MAX_TIMEOUT=30;

    /**
     * 包头长度
     *
     * @var int
     */
    const HEAD_LEN = 7;

    /**
     * 判断包长
     * @param string $recv_buffer
     * @param TcpConnection $connection
     * @return int
     */
    public static function input($recv_buffer, TcpConnection $connection)
    {
        Log::debug(" protocol start ");
        Log::debug("Recieve new frame : " . bin2hex($recv_buffer));

//        $result = Frame::verify($recv_buffer);
//
//        if(FALSE === $result){
//            return 0;
//        }
//
//        if(0 == $result['startPosition']){
//            return $result['endPosition'];
//        }
//
//        return $result['startPosition'];
        return strlen($recv_buffer);

    }

    /**
     * 从http数据包中解析$_POST、$_GET、$_COOKIE等
     * @param string $recv_buffer
     * @param TcpConnection $connection
     * @return string
     */
    public static function decode($recv_buffer, TcpConnection $connection)
    {
        return $recv_buffer;
    }

    /**
     * 编码，增加HTTP头
     * @param string $content
     * @param TcpConnection $connection
     * @return string
     */
    public static function encode($content, TcpConnection $connection)
    {
        return $content;

    }
}