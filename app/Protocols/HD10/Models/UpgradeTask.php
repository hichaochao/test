<?php

namespace Wormhole\Protocols\HD10\Models;

use Gbuckingham89\EloquentUuid\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
class UpgradeTask extends Model
{
    //use SoftDeletes;
    use UuidForKey;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hd10_upgrade_task';
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
    protected $fillable = ['id', 'code', 'file_id','task_id',
         'start_date', 'status', 'packet_size'
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
