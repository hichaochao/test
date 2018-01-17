<?php

namespace Wormhole\Protocols\HD10\Models;

use Gbuckingham89\EloquentUuid\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Evse extends Model
{
    use UuidForKey;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hd10_evses';
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
    protected $fillable = ['id','name',
        'code','monitor_code',
         'protocol_name',
        'is_charging','port_type'
//        'charge_user_id','is_billing','charge_tactics','charge_args','last_operator_time',
//        'start_time','charged_power','charged_duration','voltage','current','power','amount',
//        'is_charging','car_connect_status','warning_status','online'
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
        return $this->belongsTo(\Wormhole\Protocols\HD10\Models\Evse::class);
    }

}
