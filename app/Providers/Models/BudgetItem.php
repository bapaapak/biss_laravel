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
        'item_name',
        'qty',
        'uom',
        'currency',
        'estimated_price',
        'total_amount',
        'process',
        'fiscal_year',
        'evaluation_obstacle',
        'evaluation_reason'
    ];

    public function plan()
    {
        return $this->belongsTo(BudgetPlan::class, 'plan_id');
    }
}
