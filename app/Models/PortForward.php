<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;

class PortForward extends Model
{
    use HasFactory;    
    
    protected $connection = 'mongodb';
    protected $collection = 'dokodemo';
    protected $fillable = [
        'server','dport', 'client', 'sport', 'enable'
    ];
}
