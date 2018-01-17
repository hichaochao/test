<?php

namespace Wormhole\Protocols\ZH\Models;

use Gbuckingham89\EloquentUuid\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ChargeRecord extends Model
{
    //use SoftDeletes;
    use UuidForKey;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'zh_charge_records';
    /**
     * Indicates if the model should be timestamped.
     *  created_at and updated_at
     * @var bool
     */
    public $timestamps = TRUE;


    protected $primaryKey='id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','evse_id','division_terminal_address',
         'port_number','port_id',
        'monitor_code','order_id',
        'evse_order_id','charge_type','card_id', 'duration','start_time',
        'end_time',
        'stop_reason','charged_power','charged_fee','card_balance_before','plate_number','start_type'
    ];

    /**
     * 禁止批量赋值的
     * @var array
     */
    protected $guarded = [

    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    //protected $dates = ['deleted_at'];
}
