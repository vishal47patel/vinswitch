<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AclNode extends Model
{
    use HasFactory;
    public $timestamps = false;
    //protected $table = 'acl_nodes';
    protected $fillable = [
        'cidr',
        'type',
        'list_id',
        'is_endpoint',
        'delete'
    ];
}
