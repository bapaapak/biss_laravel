<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BudgetPlan;
use App\Models\BudgetItem;
use App\Models\Project;
use App\Models\MasterIO;
use App\Models\MasterCostCenter;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index()
    {
        // Get all budget plans with their items
        $plans = BudgetPlan::with(['items', 'project'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function($plan) {
                $plan->total_budget = $plan->items->sum('total_amount');
                return $plan;
            });

        return view('budget.index', compact('plans'));
    }

    public function create()
    {
        $projects = Project::orderBy('project_name')->get();
        $ios = MasterIO::orderBy('io_number')->get();
        $ccs = MasterCostCenter::orderBy('cc_name')->get();
        $departments = \App\Models\MasterDepartment::orderBy('dept_name')->get();
        $customers = \Illuminate\Support\Facades\DB::table('master_customers')->orderBy('customer_name')->get();
        
        return view('budget.create', compact('projects', 'ios', 'ccs', 'departments', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'fiscal_year' => 'required|integer',
            'description' => 'required',
        ]);

        $plan = BudgetPlan::create([
            'project_id' => $request->project_id,
            'fiscal_year' => $request->fiscal_year,
            'description' => $request->description,
            'status' => 'Draft',
            'created_by' => Auth::id() ?? 1
        ]);

        return redirect()->route('budget.show', $plan->id)->with('success', 'Budget Plan created. Add items now.');
    }

    public function show($id)
    {
        $plan = BudgetPlan::with(['project', 'items'])->findOrFail($id);
        $projects = Project::orderBy('project_name')->get();
        $ios = MasterIO::orderBy('io_number')->get();
        $ccs = MasterCostCenter::orderBy('cc_name')->get();
        $departments = \App\Models\MasterDepartment::orderBy('dept_name')->get();
        $customers = \Illuminate\Support\Facades\DB::table('master_customers')->orderBy('customer_name')->get();
        
        return view('budget.show', compact('plan', 'projects', 'ios', 'ccs', 'departments', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $plan = BudgetPlan::findOrFail($id);
        
        $plan->update([
            'project_id' => $request->project_id ?? $plan->project_id,
            'fiscal_year' => $request->fiscal_year ?? $plan->fiscal_year,
            'start_year' => $request->start_year,
            'end_year' => $request->end_year,
            'department' => $request->department,
            'io_number' => $request->io_number,
            'cc_code' => $request->cost_center,
            'investment_type' => $request->investment_type,
            'customer' => $request->customer,
            'description' => $request->description ?? $plan->description,
        ]);

        // Handle items from client-side management
        if ($request->has('items')) {
            // Delete all existing items for this plan
            BudgetItem::where('plan_id', $id)->delete();
            
            // Create new items from the submitted array
            foreach ($request->items as $itemData) {
                BudgetItem::create([
                    'plan_id' => $id,
                    'item_name' => $itemData['name'],
                    'fiscal_year' => $itemData['year'] ?? date('Y'),
                    'qty' => $itemData['qty'] ?? 1,
                    'uom' => $itemData['uom'] ?? 'Unit',
                    'estimated_price' => $itemData['price'] ?? 0,
                    'total_amount' => ($itemData['qty'] ?? 1) * ($itemData['price'] ?? 0),
                    'process' => $itemData['process'] ?? 'Preparation',
                    'currency' => $itemData['currency'] ?? 'IDR',
                    'io_id' => null,
                    'cc_id' => null
                ]);
            }
        }

        return redirect()->route('budget.index')->with('success', 'Budget Plan updated successfully.');
    }

    public function storeItem(Request $request, $planId)
    {
        $request->validate([
            'item_name' => 'required',
            'qty' => 'required|numeric',
            'estimated_price' => 'required|numeric'
        ]);

        BudgetItem::create([
            'plan_id' => $planId,
            'io_id' => null,
            'cc_id' => null,
            'item_name' => $request->item_name,
            'qty' => $request->qty,
            'uom' => $request->uom ?? 'Unit',
            'currency' => $request->currency ?? 'IDR',
            'estimated_price' => $request->estimated_price,
            'total_amount' => $request->qty * $request->estimated_price
        ]);

        return back()->with('success', 'Item added to budget.');
    }

    public function destroyItem($itemId)
    {
        BudgetItem::destroy($itemId);
        return back()->with('success', 'Item removed.');
    }

    public function updateItem(Request $request, $itemId)
    {
        $request->validate([
            'item_name' => 'required',
            'qty' => 'required|numeric',
            'estimated_price' => 'required|numeric',
        ]);

        $item = BudgetItem::findOrFail($itemId);
        $item->update([
            'item_name' => $request->item_name,
            'qty' => $request->qty,
            'uom' => $request->uom ?? 'Unit',
            'currency' => $request->currency ?? 'IDR',
            'process' => $request->process ?? 'Preparation',
            'fiscal_year' => $request->fiscal_year ?? date('Y'),
            'estimated_price' => $request->estimated_price,
            'total_amount' => $request->qty * $request->estimated_price
        ]);

        return back()->with('success', 'Item updated successfully.');
    }

    public function transferItem(Request $request, $itemId)
    {
        $request->validate([
            'target_plan' => 'required',
            'transfer_reason' => 'required'
        ]);

        $item = BudgetItem::findOrFail($itemId);
        $targetPlan = BudgetPlan::find($request->target_plan);
        
        // Update item's plan_id and set io_id to target plan's io_number
        $item->update([
            'plan_id' => $request->target_plan,
            'io_id' => $targetPlan ? $targetPlan->io_number : null
        ]);

        return back()->with('success', 'Item transferred successfully. Reason: ' . $request->transfer_reason);
    }

    // Submit Budget Plan for Approval
    public function submitForApproval($id)
    {
        $plan = BudgetPlan::findOrFail($id);
        
        if ($plan->status !== 'Draft') {
            return back()->with('error', 'Only Draft plans can be submitted for approval.');
        }

        $plan->status = 'Submitted';
        $plan->current_approver_role = 'Dept Head'; // First approval stage
        $plan->save();

        return back()->with('success', 'Budget Plan submitted for approval. Waiting for Dept Head review.');
    }

    // Approve Budget Plan
    public function approve($id)
    {
        $user = Auth::user();
        $plan = BudgetPlan::findOrFail($id);
        
        $currentStage = $plan->current_approver_role;
        
        // Check if user can approve at this stage
        if (!$user->canApproveBudget($currentStage)) {
            return back()->with('error', "You don't have permission to approve at this stage. Current stage: $currentStage");
        }

        $plan->advanceApproval($user);
        
        $nextStage = BudgetPlan::APPROVAL_FLOW[$currentStage] ?? 'Approved';
        $message = $nextStage === 'Approved' 
            ? 'Budget Plan has been fully approved!' 
            : "Budget Plan approved and forwarded to $nextStage for review.";
        
        return back()->with('success', $message);
    }

    // Reject Budget Plan
    public function reject($id)
    {
        $user = Auth::user();
        $plan = BudgetPlan::findOrFail($id);
        
        $currentStage = $plan->current_approver_role;
        
        // Check if user can reject at this stage
        if (!$user->canApproveBudget($currentStage)) {
            return back()->with('error', "You don't have permission to reject at this stage.");
        }

        $plan->reject($user);
        
        return back()->with('success', 'Budget Plan has been rejected.');
    }
}

