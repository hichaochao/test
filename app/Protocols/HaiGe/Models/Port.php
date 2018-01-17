<?php

namespace Wormhole\Protocols\HaiGe\Models;

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
    protected $table = 'haige_ports';
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
    protected $fillable = ['id','evse_id','evse_code','port_number','monitor_evse_code','order_id','left_time','charged_power',
        'charge_money','voltage','electric_current','duration','power', 'charge_status','net_status','user_id',
        'evse_order_id','task_status','last_update_status_time','charge_type','end_chrge_time','start_chrge_time'

    ];

    /**
     * 禁止自动赋值的
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
        return $this->belongsTo(\Wormhole\Protocols\HaiGe\Models\Evse::class);
    }
}
