<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterVendor extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function purchaseOrders()
    {
        return $this->hasMany(\App\Models\PurchaseOrder::class, 'vendor_id');
    }
}
