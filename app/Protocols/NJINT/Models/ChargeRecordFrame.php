<?php

namespace Wormhole\Protocols\NJINT\Models;

use Gbuckingham89\EloquentUuid\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Model;

class ChargeRecordFrame extends Model
{
    use UuidForKey;

    protected $table = 'njint_charge_record_frames';

    protected $primaryKey='id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'evse_id','port_id',
        'code','port_number','monitor_code',
        'frame'
    ];

}
