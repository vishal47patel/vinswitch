<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillPlan extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'bill_plan';
    protected $fillable = [
        'name',
        'description',
        'type',
        'currency',
        'pulse_rate',
        'initial_increment',
        'bill_period',
        'monthly_payment',
        'monthly_mins',
        'sip_account_price',
        'end_point_price',
        'did_price',
        'inbound_min_rate',
        'inbound_sms_price',
        'outbound_sms_price',
        'cnam_price',
        'e911_price',
        'per_channel_price',
        'method',
        'status',
        'modified_at',
        'created_at',
    ];
}
