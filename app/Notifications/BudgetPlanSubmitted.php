<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\BudgetPlan;

class BudgetPlanSubmitted extends Notification
{
    use Queueable;

    public $plan;

    /**
     * Create a new notification instance.
     */
    public function __construct(BudgetPlan $plan)
    {
        $this->plan = $plan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->id,
            'title' => 'New Budget Plan Submitted',
            'message' => "Budget Plan for {$this->plan->project->project_name} has been submitted by " . ($this->plan->creator->full_name ?? 'Unknown'),
            'type' => 'budget_plan_submitted',
            'url' => route('budget.show', $this->plan->id),
            'plan_id' => $this->plan->id,
            'created_at' => now(),
        ];
    }
}
