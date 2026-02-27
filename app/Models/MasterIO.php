<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterIO extends Model
{
    public $timestamps = false;
    protected $table = 'master_io';
    protected $fillable = ['io_number', 'description', 'category'];
}
