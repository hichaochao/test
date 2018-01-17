<?php
namespace Wormhole\Protocols\HaiGe\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Evse extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'haige_evses';
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
    protected $fillable = ['id','code','is_register',
         'response_code','name','is_register','response_code',
        'carriers','worker_id'
    ];

    /**
     * 禁止批量赋值的
     * @var array
     */
    protected $guarded = [

        'evse_id'
    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function ports(){
        return $this->belongsTo(\Wormhole\Protocols\HaiGe\Models\Port::class);
    }

}
