<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterItem extends Model
{
    public $timestamps = false;
    protected $table = 'master_items';
    protected $fillable = ['item_code', 'item_name', 'uom', 'price']; // Guessing columns based on usage
}
