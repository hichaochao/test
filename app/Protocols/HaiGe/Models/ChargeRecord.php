<?php

namespace Wormhole\Protocols\haige\Models;

use Gbuckingham89\EloquentUuid\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;

class ChargeRecord extends Model
{
    use UuidForKey;

    protected $table = 'haige_charge_records';

    protected $primaryKey = 'charge_records_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'evse_id',
        'evse_code',
        'port_id',
        'port_number',
        'port_type',
        'monitor_code',


        //'order_id',
        //'evse_order_id',
        'start_type',
        //'start_args',
        'charge_type',
        'charge_args',


        'start_time',
        'end_time',
        'duration',
        'start_soc',
        'end_soc',
        'stop_reason',
        'charged_power',
        'charged_fee',
        'times_power',
        //'evse_start_type',

        //其他
        'card_id',
        'meter_before',
        'meter_after',
        'card_balance_before',
        'vin',
        //'plate_number',

    ];

}
