<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'plan_id',
        'io_id',
        'cc_id',
        'parent_item_id',
        'item_code',
        'category',
        'item_name',
        'brand_spec',
        'fiscal_year',
        'qty',
        'uom',
        'currency',
        'estimated_price',
        'total_amount',
        'process',
        'application_process',
        'condition_status',
        'condition_notes',
        'target_schedule',
        'evaluation_obstacle',
        'evaluation_reason'
    ];

    public function parent()
    {
        return $this->belongsTo(BudgetItem::class, 'parent_item_id');
    }

    public function children()
    {
        return $this->hasMany(BudgetItem::class, 'parent_item_id');
    }

    public function breakdown()
    {
        return $this->hasMany(BudgetItem::class, 'parent_item_id');
    }

    public function plan()
    {
        return $this->belongsTo(BudgetPlan::class, 'plan_id');
    }

    public function setItemCodeAttribute($value)
    {
        $this->attributes['item_code'] = strtoupper($value);
    }

    public function setCategoryAttribute($value)
    {
        $this->attributes['category'] = ucwords(strtolower($value));
    }
}
