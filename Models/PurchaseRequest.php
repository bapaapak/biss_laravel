<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'pr_number',
        'budget_item_id',
        'request_date',
        'requester_id',
        'qty_req',
        'estimated_price',
        'status',
        'notes',
        'purpose',
        'asset_no',
        'gl_account',
        'storage_location',
        'due_date',
        'current_approver_role',
        'dept_head_id',
        'dept_head_approved_at',
        'finance_id',
        'finance_approved_at',
        'div_head_id',
        'div_head_approved_at',
        'purchasing_id',
        'purchasing_executed_at',
    ];
    
    const UPDATED_AT = null;

    // PR Approval Flow: User -> Dept Head -> Finance -> Division Head -> Purchasing
    const APPROVAL_FLOW = [
        'Submitted' => 'Dept Head',
        'Dept Head' => 'Finance',
        'Finance' => 'Division Head',
        'Division Head' => 'Purchasing',
        'Purchasing' => 'Approved',
    ];

    const STATUS_SUBMITTED = 'Submitted';
    const STATUS_APPROVED = 'Approved';
    const STATUS_REJECTED = 'Rejected';

    public function item()
    {
        return $this->belongsTo(BudgetItem::class, 'budget_item_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function deptHeadApprover()
    {
        return $this->belongsTo(User::class, 'dept_head_id');
    }

    public function financeApprover()
    {
        return $this->belongsTo(User::class, 'finance_id');
    }

    public function divHeadApprover()
    {
        return $this->belongsTo(User::class, 'div_head_id');
    }

    public function purchasingExecutor()
    {
        return $this->belongsTo(User::class, 'purchasing_id');
    }

    // Get next approver role based on current status
    public function getNextApproverRole(): ?string
    {
        return self::APPROVAL_FLOW[$this->current_approver_role] ?? null;
    }

    // Check if approval flow is complete
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
            case 'Finance':
                $this->finance_id = $approver->id;
                $this->finance_approved_at = now();
                break;
            case 'Division Head':
                $this->div_head_id = $approver->id;
                $this->div_head_approved_at = now();
                break;
            case 'Purchasing':
                $this->purchasing_id = $approver->id;
                $this->purchasing_executed_at = now();
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

    // Submit for approval (initial submission)
    public function submitForApproval(): bool
    {
        $this->status = self::STATUS_SUBMITTED;
        $this->current_approver_role = 'Dept Head'; // First approver
        return $this->save();
    }

    // Reject the PR
    public function reject(User $rejector, string $reason = null): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->current_approver_role = null;
        return $this->save();
    }
}
