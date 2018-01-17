<?php

namespace Wormhole\Protocols\NJINT\Models;

use Gbuckingham89\EloquentUuid\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Evse extends Model
{
    use SoftDeletes;
    use UuidForKey;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'njint_evses';
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
    protected $fillable = [
        'id',
        'code',
        'port_quantity'
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

    public function ports(){
        return $this->hasMany(\Wormhole\Protocols\NJINT\Models\Port::class);
    }

}
