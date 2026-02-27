<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BudgetPlan;
use App\Models\BudgetItem;
use App\Models\Project;
use App\Models\MasterIO;
use App\Models\MasterCostCenter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\BudgetPlanSubmitted;
use App\Notifications\BudgetPlanApproved;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        // Start query with joins
        $query = BudgetPlan::leftJoin('master_customers', 'budget_plans.customer', '=', 'master_customers.customer_name')
            ->leftJoin('projects', 'budget_plans.project_id', '=', 'projects.id')
            ->select('budget_plans.*', 'master_customers.customer_code')
            ->with(['items', 'project']);

        // Filter by Customer Scope for User/Dept Head
        $user = Auth::user();
        if (in_array($user->role, ['User', 'Dept Head'])) {
            $allowedCodes = $user->customers()->pluck('customer_code')->toArray();
            $allowedNames = $user->customers()->pluck('customer_name')->toArray();
            $query->where(function ($q) use ($allowedCodes, $allowedNames) {
                $q->whereIn('budget_plans.customer', $allowedCodes)
                    ->orWhereIn('budget_plans.customer', $allowedNames);
            });
        }

        // Apply search filter
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('budget_plans.io_number', 'like', "%{$search}%")
                    ->orWhere('budget_plans.customer', 'like', "%{$search}%")
                    ->orWhere('master_customers.customer_code', 'like', "%{$search}%")
                    ->orWhere('projects.project_name', 'like', "%{$search}%")
                    ->orWhere('projects.project_code', 'like', "%{$search}%")
                    ->orWhere('budget_plans.status', 'like', "%{$search}%")
                    ->orWhere('budget_plans.department', 'like', "%{$search}%")
                    ->orWhere('budget_plans.model', 'like', "%{$search}%")
                    ->orWhere('budget_plans.fiscal_year', 'like', "%{$search}%");
            });
        }

        // Apply status/auth filter and pagination
        $plans = $query->where(function ($q) {
            $q->where('budget_plans.status', '!=', 'Draft')
                ->orWhere('budget_plans.created_by', \Illuminate\Support\Facades\Auth::id());
        })
            ->orderBy('budget_plans.id', 'desc')
            ->paginate(10);

        // Compute total_budget for each plan while preserving pagination
        $plans->getCollection()->transform(function ($plan) {
            $plan->total_budget = $plan->items->where('parent_item_id', null)->sum('total_amount');
            return $plan;
        });

        // Preserve search query parameter in pagination links
        $plans->appends($request->query());

        return view('budget.index', compact('plans'));
    }

    public function create()
    {
        // Get list of used IO numbers
        $usedIos = BudgetPlan::pluck('io_number')->filter()->toArray();

        // Fetch IOs with Project details for the merged dropdown
        // Exclude IOs that are already used in other budget plans
        $ios = MasterIO::leftJoin('projects', 'master_io.project', '=', 'projects.project_code')
            ->select(
                'master_io.io_number',
                'master_io.description as io_description',
                'projects.id as project_id',
                'projects.project_name',
                'projects.category as project_category',
                'projects.project_code'
            )
            ->whereNotIn('master_io.io_number', $usedIos)
            ->orderBy('master_io.io_number')
            ->get();

        $ccs = MasterCostCenter::orderBy('cc_name')->get();
        $departments = \App\Models\MasterDepartment::orderBy('dept_name')->get();
        $customers = \Illuminate\Support\Facades\DB::table('master_customers')->orderBy('customer_name')->get();

        // Get unique categories for dropdown
        $categories = BudgetItem::whereNotNull('category')->distinct()->pluck('category');

        return view('budget.create', compact('ios', 'ccs', 'departments', 'customers', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'fiscal_year' => 'required|integer',
        ]);

        // Lookup io_id from master_io table based on io_number
        $ioId = null;
        if ($request->io_number) {
            $ioId = MasterIO::where('io_number', $request->io_number)->value('id');
        }

        $plan = BudgetPlan::create([
            'project_id' => $request->project_id,
            'fiscal_year' => $request->fiscal_year,
            'start_year' => $request->start_year,
            'end_year' => $request->end_year,
            'department' => $request->department,
            'io_number' => $request->io_number,
            'io_id' => $ioId,
            'cc_code' => $request->cost_center,
            'investment_type' => $request->investment_type,
            'customer' => $request->customer,
            'model' => $request->model,
            'purpose' => $request->purpose,
            'description' => $request->description ?? 'Budget Plan ' . $request->fiscal_year,
            'status' => $request->action === 'submit' ? 'Submitted' : 'Draft',
            'submitted_at' => $request->action === 'submit' ? now() : null,
            'created_by' => Auth::id() ?? 1
        ]);


        // Create items if submitted
        if ($request->has('items')) {
            foreach ($request->items as $itemData) {
                $item = BudgetItem::create([
                    'plan_id' => $plan->id,
                    'item_code' => $itemData['code'] ?? null,
                    'category' => $itemData['category'] ?? null,
                    'item_name' => $itemData['name'],
                    'brand_spec' => $itemData['brand_spec'] ?? null,
                    'fiscal_year' => $itemData['year'] ?? date('Y'),
                    'qty' => $itemData['qty'] ?? 1,
                    'uom' => $itemData['uom'] ?? 'Unit',
                    'estimated_price' => $itemData['price'] ?? 0,
                    'total_amount' => ($itemData['qty'] ?? 1) * ($itemData['price'] ?? 0),
                    'process' => $itemData['process'] ?? 'Preparation',
                    'application_process' => $itemData['application_process'] ?? null,
                    'condition_status' => $itemData['condition_status'] ?? null,
                    'condition_notes' => $itemData['condition_notes'] ?? null,
                    'target_schedule' => $itemData['target_schedule'] ?? null,
                    'currency' => $itemData['currency'] ?? 'IDR',
                    'io_id' => null,
                    'cc_id' => null
                ]);

                // Handle Sub-items (Breakdown)
                if (isset($itemData['breakdown']) && is_array($itemData['breakdown'])) {
                    foreach ($itemData['breakdown'] as $subData) {
                        BudgetItem::create([
                            'plan_id' => $plan->id,
                            'parent_item_id' => $item->id,
                            'item_name' => $subData['name'],
                            'brand_spec' => $subData['brand'] ?? null,
                            'qty' => $subData['qty'] ?? 1,
                            'estimated_price' => $subData['price'] ?? 0,
                            'total_amount' => ($subData['qty'] ?? 1) * ($subData['price'] ?? 0),
                            'uom' => 'Unit', // Default for sub-items
                            'currency' => 'IDR',
                            'application_process' => $subData['app_process'] ?? null, // Added app_process
                            // Inherit some fields for context if needed, or leave null
                            'item_code' => null, // Sub-items don't have code usually
                            'category' => $itemData['category'] ?? null, // Keep category for grouping if needed, but usually not needed for sub-items display which relies on parent
                        ]);
                    }
                }
            }
        }

        return redirect()->route('budget.index')->with('success', 'Budget Plan created successfully.');
    }

    public function show($id)
    {
        $plan = BudgetPlan::with(['project', 'items'])->findOrFail($id);

        // Get list of used IO numbers, but exclude the current plan's IO
        $usedIos = BudgetPlan::where('id', '!=', $id)
            ->pluck('io_number')
            ->filter()
            ->toArray();

        // Fetch IOs with Project details for the merged dropdown
        $ios = MasterIO::leftJoin('projects', 'master_io.project', '=', 'projects.project_code')
            ->select(
                'master_io.io_number',
                'master_io.description as io_description',
                'projects.id as project_id',
                'projects.project_name',
                'projects.category as project_category',
                'projects.project_code'
            )
            ->whereNotIn('master_io.io_number', $usedIos)
            ->orderBy('master_io.io_number')
            ->get();

        $ccs = MasterCostCenter::orderBy('cc_name')->get();
        $departments = \App\Models\MasterDepartment::orderBy('dept_name')->get();
        $customers = \Illuminate\Support\Facades\DB::table('master_customers')->orderBy('customer_name')->get();

        // Get unique categories for dropdown
        $categories = BudgetItem::whereNotNull('category')->distinct()->pluck('category');

        // Get PR usage per budget item (non-rejected PRs linked to this plan's items)
        $planItemIds = $plan->items->pluck('id')->toArray();
        $prUsagePerItem = [];
        if (!empty($planItemIds)) {
            $usageData = \Illuminate\Support\Facades\DB::table('purchase_requests')
                ->select(
                    'budget_item_id',
                    \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(qty_req * estimated_price), 0) as total_used')
                )
                ->whereIn('budget_item_id', $planItemIds)
                ->where('status', '!=', 'Rejected')
                ->groupBy('budget_item_id')
                ->get();

            foreach ($usageData as $usage) {
                $prUsagePerItem[$usage->budget_item_id] = $usage->total_used;
            }
        }

        return view('budget.show', compact('plan', 'ios', 'ccs', 'departments', 'customers', 'categories', 'prUsagePerItem'));
    }

    public function update(Request $request, $id)
    {
        $plan = BudgetPlan::findOrFail($id);

        // Lookup io_id from master_io table based on io_number
        $ioId = $plan->io_id;
        if ($request->io_number) {
            $ioId = MasterIO::where('io_number', $request->io_number)->value('id') ?? $plan->io_id;
        }

        $plan->update([
            'project_id' => $request->project_id ?? $plan->project_id,
            'fiscal_year' => $request->fiscal_year ?? $plan->fiscal_year,
            'start_year' => $request->start_year,
            'end_year' => $request->end_year,
            'department' => $request->department,
            'io_number' => $request->io_number,
            'io_id' => $ioId,
            'cc_code' => $request->cost_center,
            'investment_type' => $request->investment_type,
            'customer' => $request->customer,
            'model' => $request->model,
            'purpose' => $request->purpose ?? $plan->purpose,
            'description' => $request->description ?? $plan->description,
            'status' => match ($request->action) {
                'submit' => 'Submitted',
                'draft' => 'Draft',
                'update' => $plan->status,
                default => $plan->status,
            },
            'submitted_at' => $request->action === 'submit' ? now() : $plan->submitted_at,
            'current_approver_role' => match ($request->action) {
                'submit' => 'Dept Head',
                'update' => $plan->current_approver_role,
                default => $plan->current_approver_role,
            },
        ]);

        // Handle items from client-side management
        if ($request->has('items')) {
            // Delete all existing items for this plan
            BudgetItem::where('plan_id', $id)->delete();

            // Create new items from the submitted array
            foreach ($request->items as $itemData) {
                $item = BudgetItem::create([
                    'plan_id' => $id,
                    'item_code' => $itemData['code'] ?? null,
                    'category' => $itemData['category'] ?? null,
                    'item_name' => $itemData['name'],
                    'brand_spec' => $itemData['brand_spec'] ?? null,
                    'fiscal_year' => $itemData['year'] ?? date('Y'),
                    'qty' => $itemData['qty'] ?? 1,
                    'uom' => $itemData['uom'] ?? 'Unit',
                    'estimated_price' => $itemData['price'] ?? 0,
                    'total_amount' => ($itemData['qty'] ?? 1) * ($itemData['price'] ?? 0),
                    'process' => $itemData['process'] ?? 'Preparation',
                    'application_process' => $itemData['application_process'] ?? null,
                    'condition_status' => $itemData['condition_status'] ?? null,
                    'condition_notes' => $itemData['condition_notes'] ?? null,
                    'target_schedule' => $itemData['target_schedule'] ?? null,
                    'currency' => $itemData['currency'] ?? 'IDR',
                    'io_id' => null,
                    'cc_id' => null
                ]);

                // Handle Sub-items (Breakdown)
                if (isset($itemData['breakdown']) && is_array($itemData['breakdown'])) {
                    foreach ($itemData['breakdown'] as $subData) {
                        BudgetItem::create([
                            'plan_id' => $id,
                            'parent_item_id' => $item->id,
                            'item_name' => $subData['name'],
                            'brand_spec' => $subData['brand'] ?? null,
                            'qty' => $subData['qty'] ?? 1,
                            'estimated_price' => $subData['price'] ?? 0,
                            'total_amount' => ($subData['qty'] ?? 1) * ($subData['price'] ?? 0),
                            'uom' => 'Unit',
                            'currency' => 'IDR',
                            'application_process' => $subData['app_process'] ?? null, // Added app_process
                        ]);
                    }
                }
            }
        }

        return redirect()->route('budget.index')->with('success', 'Budget Plan updated successfully.');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'Super Admin') {
            return redirect()->back()->with('error', 'Only Super Admin can delete budget plans.');
        }

        $plan = BudgetPlan::findOrFail($id);
        // Delete associated items first
        BudgetItem::where('plan_id', $id)->delete();
        $plan->delete();

        return redirect()->route('budget.index')->with('success', 'Budget Plan deleted successfully.');
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
            'item_code' => $request->item_code,
            'category' => $request->category,
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
        if (auth()->user()->role !== 'Super Admin') {
            return response()->json(['success' => false, 'message' => 'Only Super Admin can delete items.']);
        }

        $item = BudgetItem::findOrFail($itemId);
        $item->delete(); // Ensure the item is actually deleted
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
            'item_code' => $request->item_code,
            'category' => $request->category,
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
            'transfer_reason' => 'required',
            'berita_acara' => 'required|file|mimes:pdf|max:10240', // PDF only, max 10MB
        ], [
            'berita_acara.required' => 'Berita Acara wajib dilampirkan.',
            'berita_acara.mimes' => 'File Berita Acara harus dalam format PDF.',
            'berita_acara.max' => 'Ukuran file Berita Acara maksimal 10MB.',
        ]);

        $item = BudgetItem::findOrFail($itemId);
        $sourcePlan = BudgetPlan::with('project')->find($item->plan_id);
        $targetPlan = BudgetPlan::with('project')->find($request->target_plan);

        // Store Berita Acara PDF
        $file = $request->file('berita_acara');
        $fileName = 'BA_Transfer_' . $item->id . '_' . date('Ymd_His') . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('berita_acara', $fileName, 'public');

        // Log transfer to budget_transfers table
        \App\Models\BudgetTransfer::create([
            'budget_item_id' => $item->id,
            'item_name' => $item->item_name,
            'source_plan_id' => $sourcePlan->id ?? null,
            'source_io_number' => $sourcePlan->io_number ?? null,
            'source_project_name' => $sourcePlan->project->project_name ?? null,
            'target_plan_id' => $targetPlan->id ?? null,
            'target_io_number' => $targetPlan->io_number ?? null,
            'target_project_name' => $targetPlan->project->project_name ?? null,
            'customer' => $sourcePlan->customer ?? null,
            'business_category' => $sourcePlan->project->category ?? null,
            'fiscal_year' => $sourcePlan->fiscal_year ?? date('Y'),
            'reason' => $request->transfer_reason,
            'berita_acara_path' => $filePath,
            'berita_acara_filename' => $fileName,
            'transferred_by' => Auth::id(),
        ]);

        // Update item's plan_id and set io_id to target plan's io_number
        $item->update([
            'plan_id' => $request->target_plan,
            'io_id' => $targetPlan ? $targetPlan->io_number : null
        ]);

        return back()->with('success', 'Item transferred successfully. Berita Acara: ' . $fileName . '. Reason: ' . $request->transfer_reason);
    }

    // Submit Budget Plan for Approval
    public function submitForApproval($id)
    {
        $plan = BudgetPlan::findOrFail($id);

        if ($plan->status !== 'Draft') {
            return back()->with('error', 'Only Draft plans can be submitted for approval.');
        }

        $plan->submitForApproval();

        // Notify Dept Heads
        $deptHeads = User::where('role', 'Dept Head')
            ->where('department', $plan->department)
            ->get();

        Notification::send($deptHeads, new BudgetPlanSubmitted($plan));

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

        // Send Notification
        if ($nextStage === 'Approved') {
            // Notify Creator
            if ($plan->creator) {
                Notification::send($plan->creator, new BudgetPlanApproved($plan, 'Approved'));
            }
        } else {
            // Notify next approvers
            $nextApprovers = User::where('role', $nextStage)
                ->where('department', $plan->department)
                ->get();

            Notification::send($nextApprovers, new BudgetPlanApproved($plan, $nextStage));
        }

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

    public function print($id)
    {
        $plan = BudgetPlan::with([
            'project',
            'items' => function ($query) {
                $query->whereNull('parent_item_id')->with('breakdown');
            }
        ])->findOrFail($id);

        $categoryMapping = [
            'Machine' => 'A',
            'Machine (standard And Spm)' => 'A',
            'Machine (Standard and SPM)' => 'A',
            'Tooling And Equipment' => 'B',
            'Tooling and Equipment' => 'B',
            'Testing And Equipment' => 'B', // Group testing with tooling for now
            'Facility Equipment Investment Plan' => 'C',
            'Building & Supporting' => 'D',
        ];

        $groupedItems = [
            'A' => ['title' => 'MACHINE (STANDARD AND SPM)', 'items' => []],
            'B' => ['title' => 'TOOLING AND EQUIPMENT', 'items' => []],
            'C' => ['title' => 'FACILITY EQUIPMENT INVESTMENT PLAN', 'items' => []],
            'D' => ['title' => 'BUILDING & SUPPORTING', 'items' => []],
        ];

        foreach ($plan->items as $item) {
            $cat = $item->category;
            $headerLetter = $categoryMapping[$cat] ?? 'A'; // Default to A if unknown
            $groupedItems[$headerLetter]['items'][] = $item;
        }

        return view('budget.print', compact('plan', 'groupedItems'));
    }
}

