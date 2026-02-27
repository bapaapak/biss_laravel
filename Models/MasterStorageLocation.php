<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterStorageLocation extends Model
{
    protected $table = 'master_storage_locations';
    
    protected $fillable = ['sloc', 'description', 'status'];
}
