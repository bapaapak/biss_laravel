<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\BudgetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Assuming Auth is set up or we fetch user from session/legacy

class PurchaseRequestController extends Controller
{
    public function index()
    {
        // Group by PR Number with full details
        $query = DB::table('purchase_requests as pr')
            ->leftJoin('budget_items as bi', 'pr.budget_item_id', '=', 'bi.id')
            ->leftJoin('budget_plans as bp', 'bi.plan_id', '=', 'bp.id')
            ->leftJoin('projects as p', 'bp.project_id', '=', 'p.id')
            ->leftJoin('master_customers as c', 'p.customer', '=', 'c.customer_code')
            ->leftJoin('users as u', 'pr.requester_id', '=', 'u.id');

        // Filter by Customer Scope for User/Dept Head
        $user = Auth::user();
        if (in_array($user->role, ['User', 'Dept Head'])) {
            $allowedCodes = $user->customers()->pluck('customer_code')->toArray();
            $allowedNames = $user->customers()->pluck('customer_name')->toArray();

            $query->where(function ($q) use ($allowedCodes, $allowedNames) {
                // Check Project Customer (Code)
                $q->whereIn('p.customer', $allowedCodes)
                    // Check Budget Plan Customer (Code or Name)
                    ->orWhereIn('bp.customer', $allowedCodes)
                    ->orWhereIn('bp.customer', $allowedNames);
            });
        }

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('pr.pr_number', 'like', "%{$search}%")
                    ->orWhere('pr.purpose', 'like', "%{$search}%")
                    ->orWhere('u.full_name', 'like', "%{$search}%")
                    ->orWhere('bi.item_name', 'like', "%{$search}%")
                    ->orWhere('p.project_code', 'like', "%{$search}%")
                    ->orWhere('p.project_name', 'like', "%{$search}%")
                    ->orWhere('c.customer_name', 'like', "%{$search}%")
                    ->orWhere('c.customer_code', 'like', "%{$search}%")
                    ->orWhere('pr.status', 'like', "%{$search}%")
                    ->orWhere('bp.io_number', 'like', "%{$search}%")
                    ->orWhere('pr.department', 'like', "%{$search}%");
            });
        }

        $prs = $query->select(
            'pr.pr_number',
            DB::raw('MAX(pr.request_date) as request_date'),
            DB::raw('MAX(pr.due_date) as due_date'),
            DB::raw('MAX(pr.status) as status'),
            DB::raw('MAX(pr.current_approver_role) as current_approver_role'),
            DB::raw('MAX(pr.id) as id'),
            DB::raw('MAX(pr.requester_id) as requester_id'),
            DB::raw('MAX(pr.purpose) as purpose'),
            DB::raw('MAX(p.project_code) as project_code'),
            DB::raw('MAX(p.project_name) as project_name'),
            DB::raw('MAX(c.customer_code) as customer_code'),
            DB::raw('MAX(c.customer_name) as customer_name'),
            DB::raw('MAX(pr.business_category) as business_category'),
            DB::raw('MAX(COALESCE(pr.department, bp.department)) as department_code'),
            DB::raw('MAX(u.full_name) as requester_name'),
            DB::raw('MAX(bi.item_name) as sample_item'),
            DB::raw('MAX(pr.notes) as notes'),
            DB::raw('COUNT(pr.id) as item_count'),
            DB::raw('SUM(pr.qty_req * pr.estimated_price) as total_amount')
        )
            ->groupBy('pr.pr_number')
            ->orderByDesc(DB::raw('MAX(pr.id)'))
            ->paginate(10)
            ->appends(request()->query());

        return view('pr.index', compact('prs'));
    }

    public function create()
    {
        // Fetch Master Data
        $depts = DB::table('master_departments')->orderBy('dept_name')->get();
        $cats = DB::table('master_categories')->orderBy('category_name')->get();
        $ccs = DB::table('master_cost_center')->orderBy('cc_name')->get();
        $plants = DB::table('master_plants')->orderBy('plant_name')->get();
        $m_items = DB::table('master_items')->orderBy('item_name')->get();
        $slocs = DB::table('master_storage_locations')->where('status', 'Active')->orderBy('sloc')->get();

        // IO Numbers - Only from Approved Budget Plans with remaining budget
        $ios = DB::table('budget_plans')
            ->join('master_io', 'budget_plans.io_id', '=', 'master_io.id')
            ->join('projects', 'budget_plans.project_id', '=', 'projects.id')
            ->where('budget_plans.status', 'Approved')
            ->select(
                'master_io.io_number',
                'projects.project_name',
                'budget_plans.id as plan_id'
            )
            ->selectRaw('(SELECT COALESCE(SUM(bi.qty * bi.estimated_price), 0) FROM budget_items bi WHERE bi.plan_id = budget_plans.id AND bi.parent_item_id IS NULL) as total_budget')
            ->selectRaw('COALESCE((SELECT SUM(pr.qty_req * pr.estimated_price) FROM purchase_requests pr 
                          JOIN budget_items bi2 ON pr.budget_item_id = bi2.id
                          WHERE bi2.plan_id = budget_plans.id AND pr.status = "Approved"), 0) as realized')
            ->distinct()
            ->orderBy('master_io.io_number')
            ->get()
            ->map(function ($io) {
                $io->remaining = $io->total_budget - $io->realized;
                return $io;
            });

        // Budget Items (Approved Plans only)
        // Join budget_items -> budget_plans -> projects
        $budget_items = DB::table('budget_items')
            ->join('budget_plans', 'budget_items.plan_id', '=', 'budget_plans.id')
            ->join('projects', 'budget_plans.project_id', '=', 'projects.id')
            ->join('master_io', 'budget_plans.io_id', '=', 'master_io.id')
            ->where('budget_plans.status', 'Approved')
            ->select(
                'budget_items.*',
                'projects.project_name',
                'master_io.io_number',
                'budget_plans.model',
                DB::raw('(SELECT COALESCE(SUM(pr.qty_req * pr.estimated_price), 0) FROM purchase_requests pr WHERE pr.budget_item_id = budget_items.id AND pr.status != "Rejected") as realized_amount')
            )
            ->orderBy('projects.project_name')
            ->orderBy('budget_items.item_name')
            ->get();

        foreach ($budget_items as $b) {
            $b->remaining = $b->total_amount - $b->realized_amount;
        }

        return view('pr.create', compact('depts', 'cats', 'ios', 'ccs', 'plants', 'm_items', 'budget_items', 'slocs'));
    }

    public function store(Request $request)
    {
        // Validation
        // Items is an array
        $request->validate([
            'department' => 'required',
            'io_number' => 'required',
            'plant' => 'required',
            'items' => 'required|array|min:1',
            'items.*.qty' => 'required|numeric|min:1',
        ]);

        $dept = $request->department;
        $io = $request->io_number;
        $plant = $request->plant;
        $cat = $request->category;
        $cc = $request->cost_center;
        $main_note = "Dept: $dept | IO: $io | CC: $cc | Cat: $cat | Plant: $plant";

        // Generate PR Number
        $bulan = date('m');
        $tahun = date('Y');
        $prefix = "PR/$tahun/$bulan/";

        // Find last PR number with this prefix
        $last_pr = PurchaseRequest::where('pr_number', 'like', "$prefix%")
            ->orderBy('id', 'desc') // Assuming higher ID means later PR
            ->value('pr_number');

        $new_urutan = "001";
        if ($last_pr) {
            $last_digit = (int) substr($last_pr, -3);
            $new_urutan = str_pad($last_digit + 1, 3, "0", STR_PAD_LEFT);
        }
        $pr_number = $prefix . $new_urutan;

        // Requester ID
        // For migration, we might rely on a fixed user or Auth::id() if login is migrated.
        // If not logged in via Laravel Auth, we might fail.
        // For now, let's hardcode user ID 1 or use Auth check.
        $user_id = 1; // Default fallback
        if (Auth::check()) {
            $user_id = Auth::id();
        }
        // Note: The legacy app puts user_id in session. Laravel might not see it unless sharing session.
        // We'll assume user will login to Laravel app.

        $today = date('Y-m-d');
        $status = 'Submitted';

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                // $item structure: master_item_code, budget_item_id, description, uom, qty, estimated_price, purpose, pic

                $pr = new PurchaseRequest();
                $pr->pr_number = $pr_number;
                $pr->io_number = $io;
                $pr->budget_item_id = !empty($item['budget_item_id']) ? $item['budget_item_id'] : null;
                $pr->item_code = $item['master_item_code'] ?? null;
                $pr->request_date = $today;
                $pr->requester_id = $user_id;
                $pr->qty_req = $item['qty'];
                $pr->uom = $item['uom'] ?? null;
                $pr->estimated_price = $item['estimated_price'] ?? 0;
                $pr->status = $status;
                $pr->current_approver_role = 'Dept Head'; // First approval stage

                // Asset No (AUC), G/L Account, Storage Location, Due Date, PIC
                $pr->asset_no = $item['asset_no'] ?? null;
                $pr->gl_account = $item['gl_account'] ?? null;
                $pr->storage_location = $request->storage_location ?? null;
                $pr->pic = $item['pic'] ?? null;
                $pr->due_date = $request->due_date ?? null;
                $pr->purpose = $item['purpose'] ?? null;

                // Description/Notes
                $desc = $item['description'] ?? '-';
                $pr->notes = "$main_note | Item: $desc"; // Replicate legacy format

                $pr->save();
            }

            // Log History: Created
            \App\Models\PrWorkflowHistory::create([
                'pr_number' => $pr_number,
                'action' => 'Created',
                'notes' => 'PR Created by ' . Auth::user()->full_name . ' (' . Auth::user()->role . ').',
                'actor_id' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('pr.index')->with('success', "PR Created: $pr_number");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating PR: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        // $id is currently treated as the ID of one of the items or we might need to change route to use pr_number
        // For now, consistent with logic: find item, get pr_number, get all items.
        // Assuming the route passed an ID of one item (e.g. from index delete/show link)
        // Check if $id is 'pr_number' or 'id'. Index link passes ?pr_number=XXX, but route is resource.
        // If route is resource 'pr/{pr}', $id is the parameter.
        // If index uses route('pr.show', ['pr' => 1])?pr_number=..., the ID denotes a placeholder or specific item.
        // Let's rely on request query 'pr_number' OR lookup by ID.

        $pr_number = request('pr_number');
        if (!$pr_number && is_numeric($id)) {
            $item = PurchaseRequest::find($id);
            if ($item)
                $pr_number = $item->pr_number;
        }

        if (!$pr_number) {
            // Fallback: assume $id IS the pr_number (string) if not found as ID?
            // Or just fail.
            $pr_number = $id;
        }

        $items = PurchaseRequest::with('item', 'requester')
            ->where('pr_number', $pr_number)
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('pr.index')->with('error', 'PR found.');
        }

        // Extract Header Info directly from database fields
        $firstItem = $items->first();
        $header = [
            'department' => $firstItem->department ?? '-',
            'io_number' => $firstItem->io_number ?? '-',
            'plant' => $firstItem->plant ?? '-',
            'category' => $firstItem->business_category ?? '-',
            'cost_center' => $firstItem->cost_center ?? '-',
            'asset_no' => $firstItem->asset_no ?? '',
            'gl_account' => $firstItem->gl_account ?? '',
            'storage_location' => $firstItem->storage_location ?? '',
            'due_date' => $firstItem->due_date ?? '',
        ];

        // Fetch Master Data for Editing
        $depts = DB::table('master_departments')->orderBy('dept_name')->get();
        $cats = DB::table('master_categories')->orderBy('category_name')->get();
        $ios = DB::table('master_io')->orderBy('io_number')->get();
        $ccs = DB::table('master_cost_center')->orderBy('cc_name')->get();
        $plants = DB::table('master_plants')->orderBy('plant_name')->get();
        $slocs = DB::table('master_storage_locations')->where('status', 'Active')->orderBy('sloc')->get();

        // Fetch Items and Budget Links for "Add Item" form
        $m_items = DB::table('master_items')->orderBy('item_name')->get();
        // Join with budget_plans AND projects to get the project name
        // Also calculate used amount
        $budget_items = DB::table('budget_items')
            ->join('budget_plans', 'budget_items.plan_id', '=', 'budget_plans.id')
            ->join('projects', 'budget_plans.project_id', '=', 'projects.id')
            ->join('master_io', 'budget_plans.io_id', '=', 'master_io.id')
            ->select(
                'budget_items.id',
                'budget_items.item_code',
                'budget_items.item_name',
                'projects.project_name',
                'master_io.io_number',
                'budget_plans.model',
                'budget_items.estimated_price',
                'budget_items.total_amount',
                DB::raw('(SELECT COALESCE(SUM(pr.qty_req * pr.estimated_price), 0) 
                                          FROM purchase_requests pr 
                                          WHERE pr.budget_item_id = budget_items.id 
                                          AND pr.status != "Rejected") as used_amount')
            )
            ->get();

        // Calculate remaining for each
        foreach ($budget_items as $b) {
            $b->remaining = $b->total_amount - $b->used_amount;
        }

        // Check Access
        $user = Auth::user();
        $is_editable = false;

        // Logic: Admin can edit. Creator can edit if NOT Approved.
        if ($user && ($user->role === 'Admin' || $user->role === 'Super Admin')) {
            $is_editable = true;
        } elseif ($user && $items->isNotEmpty() && $items->first()->requester_id == $user->id && ($items->first()->status == 'Rejected' || $items->first()->current_approver_role == 'Dept Head')) {
            $is_editable = true;
        }

        // Fetch Workflow History
        $history = \App\Models\PrWorkflowHistory::with('actor')
            ->where('pr_number', $pr_number)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pr.show', compact('pr_number', 'items', 'header', 'depts', 'cats', 'ios', 'ccs', 'plants', 'slocs', 'm_items', 'budget_items', 'is_editable', 'history'));
    }

    public function update(Request $request, $id)
    {
        // Validation
        $request->validate([
            'department' => 'required',
            'io_number' => 'required',
            'plant' => 'required',
            'items' => 'required|array',
        ]);

        $pr_number = $request->pr_number;

        // Header Info
        $dept = $request->department;
        $io = $request->io_number;
        $plant = $request->plant;
        $cat = $request->category;
        $cc = $request->cost_center;
        $main_note = "Dept: $dept | IO: $io | CC: $cc | Cat: $cat | Plant: $plant";

        DB::beginTransaction();
        try {
            // 1. Handle Deleted Items
            if ($request->has('deleted_items')) {
                PurchaseRequest::whereIn('id', $request->deleted_items)->delete();
            }

            // 2. Handle Insert/Update Items
            foreach ($request->items as $itemId => $data) {
                // Formatting: Remove dots from price "1.000.000" -> "1000000"
                $cleanPrice = str_replace('.', '', $data['price']);

                if (str_starts_with($itemId, 'new_')) {
                    // Create New Item
                    // Need to fill all required fields. Some come from header, some from form.
                    // Assuming requester_id etc are same as first item or current user?
                    // Best to copy requester from an existing item or Auth.
                    $existing = PurchaseRequest::where('pr_number', $pr_number)->first();

                    PurchaseRequest::create([
                        'pr_number' => $pr_number,
                        'budget_item_id' => !empty($data['budget_item_id']) ? $data['budget_item_id'] : null,
                        'request_date' => $existing ? $existing->request_date : now(),
                        'requester_id' => $existing ? $existing->requester_id : Auth::id(),
                        'qty_req' => $data['qty'],
                        'estimated_price' => $cleanPrice,
                        'status' => $existing ? $existing->status : 'Submitted', // Keep status sync
                        'notes' => "$main_note | Item: " . ($data['description'] ?? '-'),
                        'asset_no' => $data['asset_no'] ?? null,
                        'gl_account' => $data['gl_account'] ?? null,
                        'storage_location' => $request->storage_location,
                        'due_date' => $request->due_date,
                        'purpose' => $data['purpose'] ?? null,
                    ]);
                } else {
                    // Update Existing Item
                    $prItem = PurchaseRequest::find($itemId);
                    if ($prItem) {
                        $prItem->budget_item_id = !empty($data['budget_item_id']) ? $data['budget_item_id'] : null;
                        $prItem->item_code = $data['item_code'] ?? $prItem->item_code;
                        $prItem->qty_req = $data['qty'];
                        $prItem->estimated_price = $cleanPrice;
                        $prItem->purpose = $data['purpose'] ?? $prItem->purpose;

                        // Update header info in notes
                        $desc = $data['description'] ?? '-';
                        $prItem->notes = "$main_note | Item: $desc";

                        // Update new fields
                        $prItem->asset_no = $data['asset_no'] ?? $prItem->asset_no;
                        $prItem->gl_account = $data['gl_account'] ?? $prItem->gl_account;
                        $prItem->storage_location = $request->storage_location;
                        $prItem->due_date = $request->due_date;

                        $prItem->save();
                    }
                }
            }
            // Log History
            \App\Models\PrWorkflowHistory::create([
                'pr_number' => $pr_number,
                'action' => 'Edited',
                'notes' => 'PR details updated by ' . Auth::user()->full_name . ' (' . Auth::user()->role . ').',
                'actor_id' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('pr.index')->with('success', 'PR updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating PR: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $item = PurchaseRequest::findOrFail($id);
        $pr_number = $item->pr_number;

        $isAdmin = $user->role === 'Admin' || $user->role === 'Super Admin';
        $isCreator = $item->requester_id == $user->id;
        $canDelete = $isAdmin || ($isCreator && ($item->status === 'Rejected' || $item->status === 'Submitted'));

        if (!$canDelete) {
            return back()->with('error', 'Unauthorized. You do not have permission to delete this PR.');
        }

        // Delete ALL items with this PR number
        PurchaseRequest::where('pr_number', $pr_number)->delete();

        // Delete Workflow History
        \App\Models\PrWorkflowHistory::where('pr_number', $pr_number)->delete();

        return redirect()->route('pr.index')->with('success', "PR $pr_number deleted successfully.");
    }
    public function approve($pr_number)
    {
        $user = Auth::user();
        $pr = PurchaseRequest::where('pr_number', $pr_number)->first();

        if (!$pr) {
            return back()->with('error', 'PR not found.');
        }

        $currentStage = $pr->current_approver_role;

        // Check if user can approve at this stage
        if (!$user->canApprovePR($currentStage)) {
            return back()->with('error', "You don't have permission to approve at this stage. Current stage: $currentStage");
        }

        DB::transaction(function () use ($pr_number, $pr, $user, $currentStage) {
            // Get all items with same PR number
            /** @var \App\Models\PurchaseRequest[] $items */
            $items = PurchaseRequest::where('pr_number', $pr_number)->get();

            foreach ($items as $item) {
                $item->advanceApproval($user);
            }

            // Log history
            $nextStage = PurchaseRequest::APPROVAL_FLOW[$currentStage] ?? 'Approved';
            $actionText = $nextStage === 'Approved' ? 'Final Approval' : "Approved by $currentStage";

            \App\Models\PrWorkflowHistory::create([
                'pr_number' => $pr_number,
                'action' => $actionText,
                'notes' => "Approved by {$user->full_name} ({$user->role}). " .
                    ($nextStage !== 'Approved' ? "Forwarded to: $nextStage" : "PR is now fully approved."),
                'actor_id' => $user->id
            ]);
        });

        $nextStage = PurchaseRequest::APPROVAL_FLOW[$currentStage] ?? 'Approved';
        $message = $nextStage === 'Approved'
            ? 'PR has been fully approved!'
            : "PR approved and forwarded to $nextStage for review.";

        return back()->with('success', $message);
    }

    public function reject($pr_number)
    {
        $user = Auth::user();
        $pr = PurchaseRequest::where('pr_number', $pr_number)->first();

        if (!$pr) {
            return back()->with('error', 'PR not found.');
        }

        $currentStage = $pr->current_approver_role;

        // Check if user can reject at this stage
        if (!$user->canApprovePR($currentStage)) {
            return back()->with('error', "You don't have permission to reject at this stage.");
        }

        DB::transaction(function () use ($pr_number, $user) {
            PurchaseRequest::where('pr_number', $pr_number)->update([
                'status' => 'Rejected',
                'current_approver_role' => null
            ]);

            \App\Models\PrWorkflowHistory::create([
                'pr_number' => $pr_number,
                'action' => 'Rejected',
                'notes' => "Rejected by {$user->full_name} ({$user->role}).",
                'actor_id' => $user->id
            ]);
        });

        return back()->with('success', 'PR has been rejected.');
    }

    public function print($pr_number)
    {
        $items = PurchaseRequest::with('item', 'requester')
            ->where('pr_number', $pr_number)
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('pr.index')->with('error', 'PR not found.');
        }

        // Extract Header Info directly from database fields
        $firstItem = $items->first();
        $header = [
            'pr_number' => $pr_number,
            'request_date' => $firstItem->request_date,
            'due_date' => $firstItem->due_date,
            'status' => $firstItem->status,
            'requester' => $firstItem->requester->full_name ?? '-',
            'department' => $firstItem->department ?? '-',
            'io_number' => $firstItem->io_number ?? '-',
            'plant' => $firstItem->plant ?? '-',
            'category' => $firstItem->business_category ?? '-',
            'cost_center' => $firstItem->cost_center ?? '-',
            'asset_no' => $firstItem->asset_no ?? '',
            'gl_account' => $firstItem->gl_account ?? '',
            'storage_location' => $firstItem->storage_location ?? '',
        ];

        return view('pr.print', compact('items', 'header'));
    }
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            $importer = new \App\Imports\PurchaseRequestImport();
            $count = $importer->import($request->file('file'));
            return redirect()->back()->with('success', "Data PR berhasil diimport! ($count item)");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}
