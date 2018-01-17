<?php

namespace Wormhole\Protocols\ZH\Models;

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
    protected $table = 'zh_evses';
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
    protected $fillable = ['id','version','protocol_name','port_num','worker_id',
        'division_terminal_address','master_group_address','online','last_update_status_time',
        'auth_password','main_ip','main_port','spare_ip','spare_port','rate_type','total_electricity',
        'rate_one','rate_two','rate_three','rate_four','appointment_rate','status_frequency','data_frequency',
        'duty_cycle','modular_one_num','modular_two_num','voltage_level','current_limit','voltage_cap',
        'voltage_lower','restart_status','unlock_status','alarm_date','alarm_information_code','alarm_information_type',
        'modular_alarm_date','modular_no','exception_information_type','exception_status_type','parameter_change_tate',
        'flag','element','evse_type','run_status','max_voltage','max_current','port_a_status','port_b_status','communication',
        'battery','battery_capacity','battery_total_voltage','manufacturer_name','battery_charge_num','vin','port_type','card_num'

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
        return $this->belongsTo(\Wormhole\Protocols\ZH\Models\Port::class);
    }

}
