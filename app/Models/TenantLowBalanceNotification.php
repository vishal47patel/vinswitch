<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantLowBalanceNotification extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'tenant_low_balance_notification';
    protected $fillable = [
        'tenant_account_code',
        'Isnotification',
        'notification_threshold',
        'created_at',
        'modified_at'                
    ];
}
