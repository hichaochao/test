<?php

namespace Wormhole\Protocols\HD10\Models;

use Gbuckingham89\EloquentUuid\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Upgrade extends Model
{
    use SoftDeletes;
    use UuidForKey;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hd10_upgrade';
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
    protected $fillable = ['id', 'file_id', 'monitor_task_id', 'code', 'evse_id',
         'monitor_code',
        'upgrade_state','task_id',
        'package_number','file_size','packet_number','failure_times','is_success','confirm_status',
        'check_sum'
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
}
