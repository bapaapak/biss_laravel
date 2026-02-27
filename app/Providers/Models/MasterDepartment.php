<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterDepartment extends Model
{
    public $timestamps = false;
    protected $table = 'master_departments';
    protected $fillable = ['dept_name'];
}
