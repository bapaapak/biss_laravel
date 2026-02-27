<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\BudgetPlan;
use App\Models\BudgetItem;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Budget Statistics (only from Approved budget plans)
        $totalBudget = BudgetItem::join('budget_plans', 'budget_items.plan_id', '=', 'budget_plans.id')
            ->where('budget_plans.status', 'Approved')
            ->sum('budget_items.total_amount') ?? 0;
        
        // Realization = Approved PRs total (only from Approved budget plans)
        $totalRealization = PurchaseRequest::join('budget_items', 'purchase_requests.budget_item_id', '=', 'budget_items.id')
            ->join('budget_plans', 'budget_items.plan_id', '=', 'budget_plans.id')
            ->where('budget_plans.status', 'Approved')
            ->where('purchase_requests.status', 'Approved')
            ->sum(DB::raw('purchase_requests.qty_req * purchase_requests.estimated_price')) ?? 0;
        
        $remainingBalance = $totalBudget - $totalRealization;

        // PR Counts by Status
        $prApproved = PurchaseRequest::where('status', 'Approved')->distinct('pr_number')->count('pr_number');
        $prCompleted = PurchaseRequest::where('status', 'Completed')->distinct('pr_number')->count('pr_number');
        $prDraft = PurchaseRequest::where('status', 'Draft')->distinct('pr_number')->count('pr_number');
        
        // PR On Progress = PRs that are Submitted but not yet fully approved
        $prOnProgress = PurchaseRequest::where('status', 'Submitted')->distinct('pr_number')->count('pr_number');

        // Top Projects with Budget and Realization (only Approved budget plans)
        $topProjects = DB::table('projects')
            ->select('projects.id', 'projects.project_name', 'projects.status')
            ->selectRaw('(SELECT COALESCE(SUM(bi.total_amount), 0) FROM budget_plans bp 
                          JOIN budget_items bi ON bp.id = bi.plan_id 
                          WHERE bp.project_id = projects.id AND bp.status = "Approved") as total_budget')
            ->selectRaw('COALESCE((SELECT SUM(pr.qty_req * pr.estimated_price) FROM purchase_requests pr 
                          JOIN budget_items bi2 ON pr.budget_item_id = bi2.id
                          JOIN budget_plans bp2 ON bi2.plan_id = bp2.id
                          WHERE bp2.project_id = projects.id AND bp2.status = "Approved" AND pr.status = "Approved"), 0) as total_realization')
            ->orderByDesc('total_budget')
            ->limit(3)
            ->get();

        // Budget vs Realization per Project (for chart - only Approved budget plans)
        $chartProjects = DB::table('projects')
            ->select('projects.project_name')
            ->selectRaw('COALESCE((SELECT SUM(bi.total_amount) FROM budget_plans bp 
                          JOIN budget_items bi ON bp.id = bi.plan_id 
                          WHERE bp.project_id = projects.id AND bp.status = "Approved"), 0) as budget')
            ->selectRaw('COALESCE((SELECT SUM(pr.qty_req * pr.estimated_price) FROM purchase_requests pr 
                          JOIN budget_items bi2 ON pr.budget_item_id = bi2.id
                          JOIN budget_plans bp2 ON bi2.plan_id = bp2.id
                          WHERE bp2.project_id = projects.id AND bp2.status = "Approved" AND pr.status = "Approved"), 0) as realized')
            ->orderByDesc('budget')
            ->limit(4)
            ->get();

        $chartData = [
            'labels' => $chartProjects->pluck('project_name')->map(function($name) {
                // Shorten long names
                return strlen($name) > 10 ? substr($name, 0, 10) . '...' : $name;
            })->toArray(),
            'budget' => $chartProjects->pluck('budget')->toArray(),
            'realized' => $chartProjects->pluck('realized')->toArray(),
        ];

        return view('dashboard.index', compact(
            'totalBudget', 
            'totalRealization', 
            'remainingBalance',
            'prApproved',
            'prCompleted',
            'prDraft',
            'prOnProgress',
            'topProjects',
            'chartData'
        ));
    }

    public function markNotificationsRead(Request $request)
    {
        $user = auth()->user();
        $user->last_notification_read_at = now();
        $user->save();

        return response()->json(['success' => true]);
    }

    public function clearNotifications(Request $request)
    {
        $user = auth()->user();
        $user->last_notification_read_at = now();
        $user->save();

        return response()->json(['success' => true, 'cleared' => true]);
    }
}
