<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentComission extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'agent_commission';
}
