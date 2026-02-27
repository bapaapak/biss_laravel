<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'project_code', 
        'project_name', 
        'description', 
        'customer',
        'category',
        'model',
        'year',
        'pic_user_id',
        'start_date', 
        'end_date', 
        'die_go',
        'to',
        'pp1',
        'pp2',
        'pp3',
        'mass_pro',
        'status'
    ];
}
