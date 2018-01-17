<?php
/**
 * Created by PhpStorm.
 * User: lingf
 * Date: 2017-03-31
 * Time: 23:04
 */

namespace Wormhole\Protocols;


class EvseConstant
{
    //急停状态:1：正常；2，过压；3，过流；4，漏电；5，急停；6，拔枪；7，插抢


    /**
     * 工作状态：正常
     */
    const WORK_STATUS_NORMAL = 1;
    /**
     * 工作状态：过压
     */
    const WORK_STATUS_OVER_VOLTAGE = 2;
    /**
     * 工作状态：过流
     */
    const WORK_STATUS_OVERCURRENT = 3;
    /**
     * 工作状态：漏电
     */
    const WORK_STATUS_LEAKAGE = 4;
    ///**
    // * 工作状态：欠压
    // */
    //const WORK_STATUS_UNDERVOLTAGE = 4;
    /**
     * 工作状态：急停
     */
    const WORK_STATUS_EMERGENCY_STOP = 5;
    /**
     * 工作状态：其他
     */
    const WORK_STATUS_OTHER = 0;

}