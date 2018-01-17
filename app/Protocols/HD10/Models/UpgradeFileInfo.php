<?php

namespace Wormhole\Protocols\HD10\Models;

use Gbuckingham89\EloquentUuid\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
class UpgradeFileInfo extends Model
{
    //use SoftDeletes;
   // use UuidForKey;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hd10_upgrade_file_info';
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
    protected $fillable = ['id','file_id','package_number',
        'packet_size','content','monitor_task_id'

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
   // protected $dates = ['deleted_at'];
}
