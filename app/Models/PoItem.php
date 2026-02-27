<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PoItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function po()
    {
        return $this->belongsTo(\App\Models\PurchaseOrder::class, 'po_id');
    }

    public function prItem()
    {
        return $this->belongsTo(\App\Models\PurchaseRequest::class, 'pr_item_id');
    }
}
