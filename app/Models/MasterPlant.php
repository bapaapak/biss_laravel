<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterPlant extends Model
{
    public $timestamps = false;
    protected $table = 'master_plants';
    protected $fillable = ['plant_code', 'plant_name'];
}
