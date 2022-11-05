<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantLog extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'tenant_log';
    protected $fillable = [
        'account_number',
        'summary',
        'debit',
        'credit',
        'balance',
        'referenceno',
        'created_date'                
    ];
}
