<?php
namespace Wormhole\Protocols\HD10\Protocol;

use Wormhole\Protocols\Tools;

/**
 * Created by PhpStorm.
 * User: djspys
 * Date: 2016/10/20
 * Time: 15:42
 */
class Frame
{
    //帧头固定
    public static $begin = [0x48];

    //命令码
    private $commandCode;

    //功能码
    private $functionCode;

    //数据域长度
    private $length;

    //桩编号
    private $evseCode;

    /**
     * @var array
     */
    private $dataArea;

    //校验码
    private $bcc;


    /**
     * @var string 帧ascii字符串
     */
    private $frameString;

    //帧尾固定
    private $end = 0x44;

    /**
     * @var string 格式化错误信息
     */
    private $formatMsg;

    /**
     * @var boolean 格式化结果
     */
    private $correctFormat;

    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getFrameString()
    {
        return $this->frameString;
    }

    /**
     * @param string $frameString
     */
    public function setFrameString($frameString)
    {
        $this->frameString = $frameString;
    }


    /**
     * @param boolean $correctFormat
     */
    public function setCorrectFormat($correctFormat)
    {
        $this->correctFormat = $correctFormat;
    }

    /**
     * @return boolean
     */
    public function isCorrectFormat()
    {
        return $this->correctFormat;
    }

    /**
     * @return string
     */
    public function getFormatMsg()
    {
        return $this->formatMsg;
    }

    /**
     * @param string $formatMsg
     */
    public function setFormatMsg($formatMsg)
    {
        $this->formatMsg = $formatMsg;
    }

    /**
     * @return array
     */
    public function getBegin()
    {
        return Frame::$begin;
    }

    /**
     * @param $evseCode
     */
    public function setEvseCode($evseCode)
    {
        $this->evseCode = $evseCode;
    }

    public function getEvseCode()
    {
        return $this->evseCode;
    }

    /**
     * @param $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param $dataArea array
     */
    public function setDataArea($dataArea)
    {
        $this->dataArea = $dataArea;
    }

    /**
     * @return array $dataArea
     */
    public function getDataArea()
    {
        return $this->dataArea;
    }

    /**
     * @return mixed
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @param $commandCode
     */
    public function setCommandCode($commandCode)
    {
        $this->commandCode = $commandCode;
    }

    public function getCommandCode()
    {
        return $this->commandCode;
    }

    /**
     * @param $functionCode
     */
    public function setFunctionCode($functionCode)
    {
        $this->functionCode = $functionCode;
    }

    public function getFunctionCode()
    {
        return $this->functionCode;
    }

    public function build()
    {
        $frame = Frame::$begin;

        array_push($frame, $this->commandCode);
        array_push($frame, $this->functionCode);

        //长度为数据域长度
        $this->length = count($this->dataArea);
        $frame = array_merge($frame, Tools::decToArray($this->length, 2, FALSE));

        //bcc校验范围
        $frame = array_merge($frame, $this->dataArea);

        $this->bcc = Tools::getBCCByOr($frame);
        array_push($frame, $this->bcc);

        array_push($frame, $this->end);

        $frame = Tools::decArrayToAsciiString($frame);

        return $frame;
    }

    /**
     * @param $frame
     * @return Frame
     * 载入帧
     */
    public static function load($frame)
    {
        $frame = Tools::asciiStringToDecArray($frame);
        $framePosition = 0;


            $thisFramePosition = $framePosition;

            $exFrame = new Frame();

            if (count($frame) > 12) {
                $head[0] = $frame[$framePosition++];

                $diffBegin = array_diff($head, $exFrame->getBegin()) || array_diff($exFrame->getBegin(), $head);

                if ($diffBegin) {
                    $exFrame->setFormatMsg("STX WRONG");
                    return $exFrame;
                }

                $commandCode = $frame[$framePosition++];
                $functionCode = $frame[$framePosition++];

                /**
                 * 命令操作码进行set
                 */
                $exFrame->commandCode = $commandCode;
                $exFrame->functionCode = $functionCode;

                $length[0] = $frame[$framePosition++];
                $length[1] = $frame[$framePosition++];

                $exFrame->setLength(Tools::arrayToDec($length, FALSE));

                if (count($frame) < $exFrame->getLength()) {
                    $exFrame->setFormatMsg("Frame length wrong,length should be : " . $exFrame->getLength());
                }

            } else {
                $exFrame->setFormatMsg("The Frame Is Too Short");
                return $exFrame;
            }

            $bcc = $frame[$framePosition + $exFrame->getLength()];
            $calcBcc = Tools::getBCCByOr(array_slice($frame, 0, $exFrame->getLength() + 1 + 1 + 1 + 2));

            if ($bcc != $calcBcc) {
                $exFrame->setFormatMsg("Bcc is not match to : " . $bcc);
                return $exFrame;
            } else {
                $exFrame->setCorrectFormat(true);
            }

            $exFrame->setDataArea(array_slice($frame, $framePosition, $exFrame->getLength()));

            $framePosition += count($exFrame->dataArea);
            $framePosition++;//bbc
            $framePosition++;//end
            $frameString = Tools::decArrayToAsciiString(array_slice($frame, $thisFramePosition, $framePosition));
            $exFrame->setFrameString($frameString);

            return $exFrame;
    }


    /**
     * @param $frame
     * @return array|bool
     */
    public static function verify($frame)
    {
        $frame = Tools::asciiStringToDecArray($frame);
        $startPosition = 0;
        $thisFramePosition = 0;
        $length = 0;
        $countFrame = count($frame);
        $hasFrame = FALSE;

        while ($countFrame > $startPosition + 1) {
            $thisFramePosition = $startPosition;

            if ($countFrame - $thisFramePosition > 12) {
                $head[0] = $frame[$thisFramePosition++];

                $diffBegin = array_diff($head, Frame::$begin) || array_diff(Frame::$begin, $head);

                if ($diffBegin) {
                    $startPosition = $thisFramePosition;
                    continue;
                }

                $thisFramePosition++; //command code
                $thisFramePosition++; //function code

                $tmpLength[0] = $frame[$thisFramePosition++];
                $tmpLength[1] = $frame[$thisFramePosition++];

                $length = Tools::arrayToDec($tmpLength, FALSE);

                if ($countFrame <= $length + $startPosition) {
                    break;
                }

            } else {
                break;
            }
            if(count($frame) < $thisFramePosition + $length+2){
                break;
            }

            $bcc = $frame[$thisFramePosition + $length];
            $calcBcc = Tools::getBCCByOr(array_slice($frame, $startPosition, $length + 1 + 1 + 1 + 2));//帧长度+开头+cmd+function + bcc +end

            if ($bcc != $calcBcc) {
                $startPosition ++;
                continue;
            } else {
                $hasFrame = TRUE;
            }


            $thisFramePosition += $length ;
            $thisFramePosition++;//bbc
            $thisFramePosition++;//end

            break;
        }

        $result =  FALSE;
        if (TRUE == $hasFrame) {
            $result = [
                'startPosition' => $startPosition,
                'endPosition' => $thisFramePosition
            ];
        }

        return $result;

    }
}