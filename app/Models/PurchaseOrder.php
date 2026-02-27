<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function vendor()
    {
        return $this->belongsTo(\App\Models\MasterVendor::class, 'vendor_id');
    }

    public function items()
    {
        return $this->hasMany(\App\Models\PoItem::class, 'po_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
