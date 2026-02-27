<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterAsset extends Model
{
    protected $table = 'master_assets';
    
    protected $fillable = [
        'asset_no',
        'asset_name',
        'description',
        'status'
    ];
}
