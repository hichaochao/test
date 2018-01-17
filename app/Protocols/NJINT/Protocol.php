<?php 
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Wormhole\Protocols\NJINT;

use Workerman\Connection\TcpConnection;
use Wormhole\Protocols\Tools;
use Wormhole\Protocols\NJINT\Protocol\Frame AS NjintFrame;
/**
 * njint protocol
 */
class Protocol
{
    const NAME="NJINT";
    const MAX_TIMEOUT=30;

    /**
     * 判断包长
     * @param string $recv_buffer
     * @param TcpConnection $connection
     * @return int
     */
    public static function input($recv_buffer, TcpConnection $connection)
    {
        if(strlen($recv_buffer)>=TcpConnection::$maxPackageSize)
        {
            $connection->close();
            return 0;
        }

        $result = NjintFrame::getFrameLength($recv_buffer);
        
        if(FALSE === $result){

            return 0;
        }


        return  FALSE === $result?0:$result;

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





