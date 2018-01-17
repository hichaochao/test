<?php

namespace Wormhole\Protocols\HD10\Models;

use Gbuckingham89\EloquentUuid\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ChargeRecord extends Model
{
    use SoftDeletes;
    use UuidForKey;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hd10_charge_records';
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
    protected $fillable = ['id','evse_id','code',
         'monitor_code','port_type',
        'order_id','evse_order_id',
        'start_time','end_time','charged_power', 'duration','fee',
        'formatted_power',
        'is_billing','start_type','start_args','charge_type','charge_args','stop_reason',
    ];

    /**
     * 禁止批量赋值的
     * @var array
     */
    protected $guarded = [
            'push_monitor_result'
    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}
