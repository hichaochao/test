<?php

namespace Wormhole\Protocols\ZH\Models;

use Gbuckingham89\EloquentUuid\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Port extends Model
{
    use UuidForKey;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'zh_ports';
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
    protected $fillable = ['id','evse_id','division_terminal_address','port_number',
        'order_id','monitor_evse_code','card_num','user_balance','start_time','start_type',
        'work_status','real_time_data','output_voltage','output_current','total_power','rate_one_power',
        'rate_two_power','rate_three_power','rate_four_power','ammeter_degree', 'port_status','last_operator_time',
        'evse_order_id'
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
    protected $dates = ['deleted_at'];

    public function evse(){
        return $this->belongsTo(\Wormhole\Protocols\zh\Models\Evse::class);
    }

}
