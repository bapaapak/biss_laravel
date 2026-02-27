<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetPlan extends Model
{
    protected $table = 'budget_plans';
    const UPDATED_AT = null;
    
    // Budget Approval Flow: User -> Dept Head -> Division Head -> Finance
    const APPROVAL_FLOW = [
        'Submitted' => 'Dept Head',
        'Dept Head' => 'Division Head',
        'Division Head' => 'Finance',
        'Finance' => 'Approved',
    ];

    const STATUS_DRAFT = 'Draft';
    const STATUS_SUBMITTED = 'Submitted';
    const STATUS_APPROVED = 'Approved';
    const STATUS_REJECTED = 'Rejected';

    protected $fillable = [
        'project_id',
        'fiscal_year',
        'start_year',
        'end_year',
        'department',
        'io_number',
        'cc_code',
        'investment_type',
        'customer',
        'description',
        'created_by',
        'status',
        'total_budget',
        'current_approver_role',
        'dept_head_id',
        'dept_head_approved_at',
        'div_head_id',
        'div_head_approved_at',
        'finance_id',
        'finance_approved_at',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function items()
    {
        return $this->hasMany(BudgetItem::class, 'plan_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deptHeadApprover()
    {
        return $this->belongsTo(User::class, 'dept_head_id');
    }

    public function divHeadApprover()
    {
        return $this->belongsTo(User::class, 'div_head_id');
    }

    public function financeApprover()
    {
        return $this->belongsTo(User::class, 'finance_id');
    }

    // Get next approver role based on current stage
    public function getNextApproverRole(): ?string
    {
        return self::APPROVAL_FLOW[$this->current_approver_role] ?? null;
    }

    // Check if fully approved
    public function isFullyApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    // Advance to next approval stage
    public function advanceApproval(User $approver): bool
    {
        $currentStage = $this->current_approver_role;
        $nextStage = $this->getNextApproverRole();

        if (!$nextStage) {
            return false;
        }

        // Record who approved at each stage
        switch ($currentStage) {
            case 'Dept Head':
                $this->dept_head_id = $approver->id;
                $this->dept_head_approved_at = now();
                break;
            case 'Division Head':
                $this->div_head_id = $approver->id;
                $this->div_head_approved_at = now();
                break;
            case 'Finance':
                $this->finance_id = $approver->id;
                $this->finance_approved_at = now();
                break;
        }

        // If next stage is 'Approved', finalize
        if ($nextStage === 'Approved') {
            $this->status = self::STATUS_APPROVED;
            $this->current_approver_role = null;
        } else {
            $this->current_approver_role = $nextStage;
        }

        return $this->save();
    }

    // Submit for approval
    public function submitForApproval(): bool
    {
        $this->status = self::STATUS_SUBMITTED;
        $this->current_approver_role = 'Dept Head';
        return $this->save();
    }

    // Reject the budget plan
    public function reject(User $rejector, string $reason = null): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->current_approver_role = null;
        return $this->save();
    }
}
