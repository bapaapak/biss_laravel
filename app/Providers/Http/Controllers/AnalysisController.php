<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalysisController extends Controller
{
    /**
     * Plan vs Realization Report
     */
    public function planRealization()
    {
        // Get budget items with realized amounts from PR
        $items = DB::table('budget_items as bi')
            ->join('budget_plans as bp', 'bi.plan_id', '=', 'bp.id')
            ->join('projects as p', 'bp.project_id', '=', 'p.id')
            ->select(
                'bi.*',
                'p.project_name',
                'p.project_code',
                'bp.io_number',
                'bp.cc_code as cc_code',
                DB::raw('(SELECT COALESCE(SUM(qty_req * estimated_price), 0) 
                         FROM purchase_requests pr 
                         WHERE pr.budget_item_id = bi.id AND pr.status != \'Rejected\') as realized_amount')
            )
            ->where('bp.status', 'Approved')
            ->orderBy('bi.id', 'desc')
            ->get();

        // Calculate totals
        $grandPlan = $items->sum('total_amount');
        $grandReal = $items->sum('realized_amount');
        $grandBalance = $grandPlan - $grandReal;
        $grandUtilization = $grandPlan > 0 ? ($grandReal / $grandPlan) * 100 : 0;

        // Get PRs for each item
        $itemsWithPRs = $items->map(function($item) {
            $prs = DB::table('purchase_requests')
                ->where('budget_item_id', $item->id)
                ->orderBy('id', 'desc')
                ->get();
            $item->prs = $prs;
            return $item;
        });

        return view('analysis.plan_realization', compact(
            'itemsWithPRs', 'grandPlan', 'grandReal', 'grandBalance', 'grandUtilization'
        ));
    }

    /**
     * Budget Evaluation vs PR
     */
    public function budgetEvaluation()
    {
        // Get approved budget plans with project info
        $plans = DB::table('budget_plans as bp')
            ->join('projects as p', 'bp.project_id', '=', 'p.id')
            ->join('users as u', 'bp.created_by', '=', 'u.id')
            ->select(
                'bp.id as plan_id',
                'bp.fiscal_year',
                'p.project_name',
                'p.project_code',
                'p.category',
                'u.full_name as creator'
            )
            ->where('bp.status', 'Approved')
            ->orderBy('bp.id', 'desc')
            ->get();

        // Get items for each plan with realized amounts
        $plansWithItems = $plans->map(function($plan) {
            $items = DB::table('budget_items as bi')
                ->leftJoin('master_cost_center as cc', 'bi.cc_id', '=', 'cc.id')
                ->select(
                    'bi.*',
                    'cc.cc_code',
                    DB::raw('(SELECT COALESCE(SUM(qty_req * estimated_price), 0) 
                             FROM purchase_requests pr 
                             WHERE pr.budget_item_id = bi.id AND pr.status != \'Rejected\') as realized_amount')
                )
                ->where('bi.plan_id', $plan->plan_id)
                ->get();
            $plan->items = $items;
            return $plan;
        });

        return view('analysis.budget_evaluation', compact('plansWithItems'));
    }

    /**
     * Save evaluation data
     */
    public function saveEvaluation(Request $request)
    {
        $itemIds = $request->item_id;
        $obstacles = $request->obstacle;
        $reasons = $request->reason;

        if ($itemIds) {
            foreach ($itemIds as $index => $itemId) {
                DB::table('budget_items')
                    ->where('id', $itemId)
                    ->update([
                        'evaluation_obstacle' => $obstacles[$index] ?? null,
                        'evaluation_reason' => $reasons[$index] ?? null
                    ]);
            }
        }

        return back()->with('success', 'Evaluation data saved successfully.');
    }
}
