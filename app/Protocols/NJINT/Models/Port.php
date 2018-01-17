<?php

namespace Wormhole\Protocols\NJINT\Models;

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
    protected $table = 'njint_ports';
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
    protected $fillable = ['id','evse_id','worker_id',
        'code','port_number','monitor_code',
        'heartbeat_period','start_time',
        'is_charging'

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
        return $this->belongsTo(\Wormhole\Protocols\NJINT\Models\Evse::class);
    }
}
