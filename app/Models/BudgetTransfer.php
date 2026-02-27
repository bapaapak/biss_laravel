<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetTransfer extends Model
{
    protected $fillable = [
        'budget_item_id',
        'item_name',
        'source_plan_id',
        'source_io_number',
        'source_project_name',
        'target_plan_id',
        'target_io_number',
        'target_project_name',
        'customer',
        'business_category',
        'fiscal_year',
        'reason',
        'berita_acara_path',
        'berita_acara_filename',
        'transferred_by',
    ];

    public function budgetItem()
    {
        return $this->belongsTo(BudgetItem::class, 'budget_item_id');
    }

    public function sourcePlan()
    {
        return $this->belongsTo(BudgetPlan::class, 'source_plan_id');
    }

    public function targetPlan()
    {
        return $this->belongsTo(BudgetPlan::class, 'target_plan_id');
    }

    public function transferredByUser()
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
