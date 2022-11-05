<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'agent';
    protected $fillable = [
        'account_code',
        'join_date',
        'firstname',
        'lastname',
        'email',
        'contact_no',
        'company_name',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'status',
        'suspended'
    ]; 
}
