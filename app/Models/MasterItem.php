<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterItem extends Model
{
    public $timestamps = false;
    protected $table = 'master_items';
    protected $fillable = ['item_code', 'item_name', 'uom', 'price'];

    public function setItemCodeAttribute($value)
    {
        $this->attributes['item_code'] = strtoupper($value);
    }
}
