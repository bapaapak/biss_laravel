<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\BudgetPlan;

class BudgetPlanApproved extends Notification
{
    use Queueable;

    public $plan;
    public $nextRole;

    public function __construct(BudgetPlan $plan, ?string $nextRole)
    {
        $this->plan = $plan;
        $this->nextRole = $nextRole;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        if ($this->nextRole === 'Approved') {
            $message = "Your Budget Plan for {$this->plan->project->project_name} has been fully APPROVED.";
            $title = "Budget Plan Approved";
            $type = 'budget_plan_approved';
        } else {
            $message = "Budget Plan for {$this->plan->project->project_name} needs your approval.";
            $title = "Budget Plan Approval Required";
            $type = 'budget_plan_approval_required';
        }

        return [
            'id' => $this->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'url' => route('budget.show', $this->plan->id),
            'plan_id' => $this->plan->id,
            'created_at' => now(),
        ];
    }
}
