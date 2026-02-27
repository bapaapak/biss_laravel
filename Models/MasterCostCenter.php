<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterCostCenter extends Model
{
    public $timestamps = false;
    protected $table = 'master_cost_center';
    protected $fillable = ['cc_code', 'cc_name', 'department'];
}
