<?php

namespace Wormhole\Protocols\NJINT\Models;

use Gbuckingham89\EloquentUuid\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;

class ChargeRecord extends Model
{
    use UuidForKey;

    protected $table = 'njint_charge_records';

    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'evse_id',
        'code',
        'port_id',
        'port_number',
        'port_type',
        'monitor_code',


        'order_id',
        'evse_order_id',
        'start_type',
        'start_args',
        'charge_type',
        'charge_args',


        'start_time',
        'end_time',
        'duration',
        'start_soc',
        'end_soc',
        'stop_reason',
        'charged_power',
        'fee',
        'times_power',
        'formatted_power',
        'evse_start_type',

        //其他
        'card_id',
        'meter_before',
        'meter_after',
        'card_balance_before',
        'vin',
        'plate_number',

    ];

}
