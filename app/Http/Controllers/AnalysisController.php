<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterCategory;

class AnalysisController extends Controller
{
    /**
     * Get filter data shared by all analysis pages (years, customers, categories)
     */
    private function getFilterData(Request $request)
    {
        $user = Auth::user();
        $isRestricted = in_array($user->role, ['User', 'Dept Head']);
        $allowedCodes = [];
        $allowedNames = [];

        if ($isRestricted) {
            $allowedCodes = $user->customers()->pluck('customer_code')->toArray();
            $allowedNames = $user->customers()->pluck('customer_name')->toArray();
        }

        $businessCategories = MasterCategory::orderBy('category_name')->get();
        $selectedCategory = $request->get('category');
        $canSeeAllCategories = true;
        $allowedCategories = [];

        if ($isRestricted && count($allowedCodes) > 0) {
            $allowedCategories = DB::table('projects')
                ->whereIn('customer', array_merge($allowedCodes, $allowedNames))
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category')
                ->toArray();

            $businessCategories = $businessCategories->filter(function ($cat) use ($allowedCategories) {
                return in_array($cat->category_name, $allowedCategories);
            })->values();

            $canSeeAllCategories = false;

            if ($businessCategories->count() === 1 && !$selectedCategory) {
                $selectedCategory = $businessCategories->first()->category_name;
            }
        }

        if ($isRestricted && !empty($allowedCategories)) {
            $restrictedProjectIds = DB::table('projects')
                ->whereIn('category', $allowedCategories)
                ->whereIn('customer', array_merge($allowedCodes, $allowedNames))
                ->pluck('id')
                ->toArray();

            $years = DB::table('budget_plans')
                ->whereIn('project_id', $restrictedProjectIds)
                ->distinct()
                ->orderBy('fiscal_year', 'desc')
                ->pluck('fiscal_year');

            $customers = DB::table('projects')
                ->whereIn('id', $restrictedProjectIds)
                ->whereNotNull('customer')
                ->distinct()
                ->orderBy('customer')
                ->pluck('customer');
        } else {
            $years = DB::table('budget_plans')->distinct()->orderBy('fiscal_year', 'desc')->pluck('fiscal_year');
            $customers = DB::table('projects')->distinct()->whereNotNull('customer')->orderBy('customer')->pluck('customer');
        }

        $selectedYear = $request->get('year');
        $selectedCustomer = $request->get('customer');
        $selectedProject = $request->get('project');

        // Build projects list based on selected filters
        $projectsQuery = DB::table('projects')->whereNotNull('project_name')->orderBy('project_name');
        if ($isRestricted && !empty($allowedCategories)) {
            $projectsQuery->whereIn('customer', array_merge($allowedCodes, $allowedNames));
        }
        if ($selectedCategory) {
            $projectsQuery->where('category', $selectedCategory);
        }
        if ($selectedCustomer) {
            $projectsQuery->where('customer', $selectedCustomer);
        }
        $projects = $projectsQuery->select('id', 'project_name', 'project_code')->distinct()->get();

        return [
            'years' => $years,
            'customers' => $customers,
            'businessCategories' => $businessCategories,
            'canSeeAllCategories' => $canSeeAllCategories,
            'selectedYear' => $selectedYear,
            'selectedCustomer' => $selectedCustomer,
            'selectedCategory' => $selectedCategory,
            'selectedProject' => $selectedProject,
            'projects' => $projects,
            'isRestricted' => $isRestricted,
            'allowedCodes' => $allowedCodes,
            'allowedNames' => $allowedNames,
        ];
    }

    /**
     * Apply user restriction + customer/category filters to a budget_items base query
     */
    private function applyFilters($query, array $filters)
    {
        if ($filters['selectedYear']) {
            $query->where('bp.fiscal_year', $filters['selectedYear']);
        }
        if ($filters['selectedCustomer']) {
            $query->where(function ($q) use ($filters) {
                $q->where('p.customer', $filters['selectedCustomer'])
                    ->orWhere('bp.customer', $filters['selectedCustomer']);
            });
        }
        if ($filters['selectedCategory']) {
            $query->where('p.category', $filters['selectedCategory']);
        }
        if (!empty($filters['selectedProject'])) {
            $query->where('p.id', $filters['selectedProject']);
        }
        if ($filters['isRestricted']) {
            $query->where(function ($q) use ($filters) {
                $q->whereIn('p.customer', $filters['allowedCodes'])
                    ->orWhereIn('bp.customer', $filters['allowedCodes'])
                    ->orWhereIn('bp.customer', $filters['allowedNames']);
            });
        }
        return $query;
    }

    /**
     * Budget Evaluation - Customer/Project Folder List
     */
    public function budgetEvaluation()
    {
        // Get approved budget plans grouped by customer
        $plans = DB::table('budget_plans as bp')
            ->join('projects as p', 'bp.project_id', '=', 'p.id')
            ->select(
                'bp.id as plan_id',
                'bp.fiscal_year',
                'bp.status',
                'p.project_name',
                'p.project_code',
                'p.category',
                'bp.customer as customer_name',
                'bp.model as model_name',
                DB::raw('(SELECT COALESCE(SUM(bi.qty * bi.estimated_price), 0) FROM budget_items bi WHERE bi.plan_id = bp.id AND bi.parent_item_id IS NULL) as total_budget'),
                DB::raw('(SELECT COALESCE(SUM(
                    (SELECT COALESCE(SUM(pr.qty_req * pr.estimated_price), 0) FROM purchase_requests pr WHERE pr.budget_item_id = bi2.id AND pr.status != \'Rejected\')
                ), 0) FROM budget_items bi2 WHERE bi2.plan_id = bp.id) as total_realized'),
                DB::raw('(SELECT COUNT(*) FROM budget_items bi3 WHERE bi3.plan_id = bp.id) as item_count')
            )
            ->where('bp.status', '!=', 'Rejected')
            ->when(in_array(Auth::user()->role, ['User', 'Dept Head']), function ($q) {
                $user = Auth::user();
                $codes = $user->customers()->pluck('customer_code')->toArray();
                $names = $user->customers()->pluck('customer_name')->toArray();
                $q->where(function ($sub) use ($codes, $names) {
                    $sub->whereIn('p.customer', $codes)
                        ->orWhereIn('bp.customer', $codes)
                        ->orWhereIn('bp.customer', $names);
                });
            })
            ->where(function ($query) {
                $query->where('bp.status', '!=', 'Draft')
                    ->orWhere('bp.created_by', Auth::id());
            })
            ->orderBy('p.customer')
            ->orderBy('p.model')
            ->orderBy('bp.id', 'desc')
            ->get();

        // Group by customer, then by model
        $customerGroups = $plans->groupBy(function ($plan) {
            return $plan->customer_name ?? 'Tanpa Customer';
        })->map(function ($customerPlans) {
            return $customerPlans->groupBy(function ($plan) {
                return $plan->model_name ?? 'Tanpa Model';
            });
        });

        return view('analysis.budget_evaluation_index', compact('customerGroups'));
    }

    /**
     * Budget Evaluation Detail - Single Plan Spreadsheet View
     */
    public function budgetEvaluationDetail($planId)
    {
        // Get the specific plan
        $plan = DB::table('budget_plans as bp')
            ->join('projects as p', 'bp.project_id', '=', 'p.id')
            ->join('users as u', 'bp.created_by', '=', 'u.id')
            ->select(
                'bp.*', // Get bp data for full context
                'bp.id as plan_id',
                'bp.fiscal_year',
                'p.project_name',
                'p.project_code',
                'p.category',
                'u.full_name as creator',
                'bp.customer as customer_name'
            )
            ->where('bp.id', $planId)
            ->where('bp.status', '!=', 'Rejected')
            ->where(function ($query) {
                $query->where('bp.status', '!=', 'Draft')
                    ->orWhere('bp.created_by', Auth::id());
            })
            ->first();

        if (!$plan) {
            return redirect()->route('analysis.budget_evaluation')->with('error', 'Budget Plan tidak ditemukan.');
        }

        // Get items with PR data
        $items = DB::table('budget_items as bi')
            ->leftJoin('master_cost_center as cc', 'bi.cc_id', '=', 'cc.id')
            ->select(
                'bi.*',
                'cc.cc_code',
                DB::raw('(SELECT COALESCE(SUM(qty_req), 0) 
                         FROM purchase_requests pr 
                         WHERE pr.budget_item_id = bi.id AND pr.status != \'Rejected\') as pr_qty'),
                DB::raw('(SELECT COALESCE(SUM(qty_req * estimated_price), 0) 
                         FROM purchase_requests pr 
                         WHERE pr.budget_item_id = bi.id AND pr.status != \'Rejected\') as realized_amount'),
                DB::raw('(SELECT pr.estimated_price 
                         FROM purchase_requests pr 
                         WHERE pr.budget_item_id = bi.id AND pr.status != \'Rejected\' 
                         ORDER BY pr.id DESC LIMIT 1) as pr_price')
            )
            ->where('bi.plan_id', $planId)
            ->orderBy('bi.process')
            ->orderBy('bi.id')
            ->get();

        $categoryMapping = [
            'Machine' => 'A',
            'Machine (standard And Spm)' => 'A',
            'Machine (Standard and SPM)' => 'A',
            'Tooling And Equipment' => 'B',
            'Tooling and Equipment' => 'B',
            'Testing And Equipment' => 'A',
            'Facility Equipment Investment Plan' => 'C',
            'Building & Supporting' => 'D',
        ];

        $plan->itemsByProcess = $items->groupBy(function ($item) use ($categoryMapping) {
            $code = $item->item_code;
            if (!$code && isset($categoryMapping[$item->category])) {
                $code = $categoryMapping[$item->category];
            }
            $codeStr = $code ? $code . '. ' : '';
            $catStr = $item->category ? $item->category . ' - ' : '';
            return $codeStr . $catStr . $item->process;
        });
        $plan->items = $items;

        return view('analysis.budget_evaluation', compact('plan'));
    }

    /**
     * Print Budget Evaluation
     */
    public function printEvaluation($planId)
    {
        // Reuse detail logic but return print view
        $plan = DB::table('budget_plans as bp')
            ->join('projects as p', 'bp.project_id', '=', 'p.id')
            ->join('users as u', 'bp.created_by', '=', 'u.id')
            ->select(
                'bp.id as plan_id',
                'bp.*',
                'bp.fiscal_year',
                'p.project_name',
                'p.project_code',
                'p.category',
                'u.full_name as creator',
                'bp.customer as customer_name'
            )
            ->where('bp.id', $planId)
            ->first();

        $items = DB::table('budget_items as bi')
            ->leftJoin('master_cost_center as cc', 'bi.cc_id', '=', 'cc.id')
            ->select(
                'bi.*',
                'cc.cc_code',
                DB::raw('(SELECT COALESCE(SUM(qty_req), 0) FROM purchase_requests pr WHERE pr.budget_item_id = bi.id AND pr.status != \'Rejected\') as pr_qty'),
                DB::raw('(SELECT COALESCE(SUM(qty_req * estimated_price), 0) FROM purchase_requests pr WHERE pr.budget_item_id = bi.id AND pr.status != \'Rejected\') as realized_amount'),
                DB::raw('(SELECT pr.estimated_price FROM purchase_requests pr WHERE pr.budget_item_id = bi.id AND pr.status != \'Rejected\' ORDER BY pr.id DESC LIMIT 1) as pr_price')
            )
            ->where('bi.plan_id', $planId)
            ->orderBy('bi.process')
            ->orderBy('bi.id')
            ->get();

        $categoryMapping = [
            'Machine' => 'A',
            'Machine (standard And Spm)' => 'A',
            'Machine (Standard and SPM)' => 'A',
            'Tooling And Equipment' => 'B',
            'Tooling and Equipment' => 'B',
            'Testing And Equipment' => 'A',
            'Facility Equipment Investment Plan' => 'C',
            'Building & Supporting' => 'D',
        ];

        $plan->itemsByProcess = $items->groupBy(function ($item) use ($categoryMapping) {
            $code = $item->item_code;
            if (!$code && isset($categoryMapping[$item->category])) {
                $code = $categoryMapping[$item->category];
            }
            $codeStr = $code ? $code . '. ' : '';
            $catStr = $item->category ? $item->category . ' - ' : '';
            return $codeStr . $catStr . $item->process;
        });

        return view('analysis.print_evaluation', compact('plan'));
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

    // =====================================================================
    // NEW ANALYSIS FEATURES
    // =====================================================================

    /**
     * 1. Budget Absorption Rate per Department / Customer
     */
    public function budgetAbsorption(Request $request)
    {
        $filters = $this->getFilterData($request);

        $baseQuery = DB::table('budget_items as bi')
            ->join('budget_plans as bp', 'bi.plan_id', '=', 'bp.id')
            ->join('projects as p', 'bp.project_id', '=', 'p.id')
            ->where('bp.status', 'Approved')
            ->whereNull('bi.parent_item_id');

        $this->applyFilters($baseQuery, $filters);

        // By Department
        $byDepartment = (clone $baseQuery)
            ->select(
                'bp.department',
                DB::raw('SUM(bi.qty * bi.estimated_price) as total_budget'),
                DB::raw('SUM((SELECT COALESCE(SUM(pr.qty_req * pr.estimated_price), 0) FROM purchase_requests pr WHERE pr.budget_item_id = bi.id AND pr.status != \'Rejected\')) as total_realization')
            )
            ->groupBy('bp.department')
            ->orderByDesc('total_budget')
            ->get()
            ->map(function ($item) {
                $item->absorption_rate = $item->total_budget > 0 ? ($item->total_realization / $item->total_budget) * 100 : 0;
                $item->remaining = $item->total_budget - $item->total_realization;
                return $item;
            });

        // By Customer
        $byCustomer = (clone $baseQuery)
            ->select(
                DB::raw('COALESCE(bp.customer, p.customer) as customer_name'),
                DB::raw('SUM(bi.qty * bi.estimated_price) as total_budget'),
                DB::raw('SUM((SELECT COALESCE(SUM(pr.qty_req * pr.estimated_price), 0) FROM purchase_requests pr WHERE pr.budget_item_id = bi.id AND pr.status != \'Rejected\')) as total_realization')
            )
            ->groupBy(DB::raw('COALESCE(bp.customer, p.customer)'))
            ->orderByDesc('total_budget')
            ->get()
            ->map(function ($item) {
                $item->absorption_rate = $item->total_budget > 0 ? ($item->total_realization / $item->total_budget) * 100 : 0;
                $item->remaining = $item->total_budget - $item->total_realization;
                return $item;
            });

        // Grand totals
        $grandBudget = $byDepartment->sum('total_budget');
        $grandRealization = $byDepartment->sum('total_realization');
        $grandAbsorption = $grandBudget > 0 ? ($grandRealization / $grandBudget) * 100 : 0;

        return view('analysis.budget_absorption', array_merge($filters, compact(
            'byDepartment',
            'byCustomer',
            'grandBudget',
            'grandRealization',
            'grandAbsorption'
        )));
    }

    /**
     * 2. Monthly Spending Trend
     */
    public function monthlyTrend(Request $request)
    {
        $filters = $this->getFilterData($request);
        $selectedYear = $filters['selectedYear'] ?: date('Y');
        $filters['selectedYear'] = $selectedYear;

        // PR spending by month
        $prQuery = DB::table('purchase_requests as pr2')
            ->select(
                DB::raw('MONTH(pr2.created_at) as month'),
                DB::raw('SUM(pr2.qty_req * pr2.estimated_price) as total_amount'),
                DB::raw('COUNT(DISTINCT pr2.pr_number) as pr_count')
            )
            ->where('pr2.status', '!=', 'Rejected')
            ->whereYear('pr2.created_at', $selectedYear);

        if ($filters['selectedCustomer'] || $filters['selectedCategory'] || !empty($filters['selectedProject']) || $filters['isRestricted']) {
            $prQuery->join('budget_items as bi', 'pr2.budget_item_id', '=', 'bi.id')
                ->join('budget_plans as bp', 'bi.plan_id', '=', 'bp.id')
                ->join('projects as p', 'bp.project_id', '=', 'p.id');
            if ($filters['selectedCustomer']) {
                $prQuery->where(function ($q) use ($filters) {
                    $q->where('p.customer', $filters['selectedCustomer'])
                        ->orWhere('bp.customer', $filters['selectedCustomer']);
                });
            }
            if ($filters['selectedCategory']) {
                $prQuery->where('p.category', $filters['selectedCategory']);
            }
            if (!empty($filters['selectedProject'])) {
                $prQuery->where('p.id', $filters['selectedProject']);
            }
            if ($filters['isRestricted']) {
                $prQuery->where(function ($q) use ($filters) {
                    $q->whereIn('p.customer', $filters['allowedCodes'])
                        ->orWhereIn('bp.customer', $filters['allowedCodes'])
                        ->orWhereIn('bp.customer', $filters['allowedNames']);
                });
            }
        }

        $prMonthly = $prQuery->groupBy(DB::raw('MONTH(pr2.created_at)'))->get()->keyBy('month');

        // PO spending by month
        $poMonthly = DB::table('purchase_orders')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('COUNT(*) as po_count')
            )
            ->whereYear('created_at', $selectedYear)
            ->where('status', '!=', 'Rejected')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get()
            ->keyBy('month');

        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $months = [];
        $prData = [];
        $poData = [];
        $prCounts = [];
        $poCounts = [];

        for ($i = 1; $i <= 12; $i++) {
            $months[] = $monthNames[$i - 1];
            $prData[] = isset($prMonthly[$i]) ? (float) $prMonthly[$i]->total_amount : 0;
            $poData[] = isset($poMonthly[$i]) ? (float) $poMonthly[$i]->total_amount : 0;
            $prCounts[] = isset($prMonthly[$i]) ? (int) $prMonthly[$i]->pr_count : 0;
            $poCounts[] = isset($poMonthly[$i]) ? (int) $poMonthly[$i]->po_count : 0;
        }

        // Summary stats
        $totalPR = array_sum($prData);
        $totalPO = array_sum($poData);
        $activeMonths = count(array_filter($prData, fn($v) => $v > 0)) ?: 1;
        $avgMonthlyPR = $totalPR / $activeMonths;
        $maxPrVal = max($prData) ?: 0;
        $peakMonthIndex = $maxPrVal > 0 ? array_search($maxPrVal, $prData) : 0;
        $peakMonth = $monthNames[$peakMonthIndex];
        $totalTransactions = array_sum($prCounts) + array_sum($poCounts);

        return view('analysis.monthly_trend', array_merge($filters, compact(
            'months',
            'prData',
            'poData',
            'prCounts',
            'poCounts',
            'totalPR',
            'totalPO',
            'avgMonthlyPR',
            'peakMonth',
            'totalTransactions'
        )));
    }

    /**
     * 3. Vendor Performance / Top Vendors
     */
    public function vendorAnalysis(Request $request)
    {
        $filters = $this->getFilterData($request);

        $poQuery = DB::table('purchase_orders as po')
            ->join('master_vendors as v', 'po.vendor_id', '=', 'v.id')
            ->where('po.status', '!=', 'Rejected');

        if ($filters['selectedYear']) {
            $poQuery->whereYear('po.po_date', $filters['selectedYear']);
        }

        // Apply customer/category/project filters via PO items -> PR -> budget_items -> projects
        if ($filters['selectedCustomer'] || $filters['selectedCategory'] || !empty($filters['selectedProject']) || $filters['isRestricted']) {
            $poQuery->whereExists(function ($sub) use ($filters) {
                $sub->select(DB::raw(1))
                    ->from('po_items')
                    ->join('purchase_requests as pr', 'po_items.pr_number', '=', 'pr.pr_number')
                    ->join('budget_items as bi', 'pr.budget_item_id', '=', 'bi.id')
                    ->join('budget_plans as bp', 'bi.plan_id', '=', 'bp.id')
                    ->join('projects as p', 'bp.project_id', '=', 'p.id')
                    ->whereColumn('po_items.po_id', 'po.id');
                if ($filters['selectedCustomer']) {
                    $sub->where(function ($q) use ($filters) {
                        $q->where('p.customer', $filters['selectedCustomer'])
                            ->orWhere('bp.customer', $filters['selectedCustomer']);
                    });
                }
                if ($filters['selectedCategory']) {
                    $sub->where('p.category', $filters['selectedCategory']);
                }
                if (!empty($filters['selectedProject'])) {
                    $sub->where('p.id', $filters['selectedProject']);
                }
                if ($filters['isRestricted']) {
                    $sub->where(function ($q) use ($filters) {
                        $q->whereIn('p.customer', $filters['allowedCodes'])
                            ->orWhereIn('bp.customer', $filters['allowedCodes'])
                            ->orWhereIn('bp.customer', $filters['allowedNames']);
                    });
                }
            });
        }

        $topVendors = (clone $poQuery)
            ->select(
                'v.id',
                'v.vendor_code',
                'v.vendor_name',
                DB::raw('COUNT(po.id) as total_orders'),
                DB::raw('SUM(po.total_amount) as total_amount'),
                DB::raw('AVG(po.total_amount) as avg_order_value'),
                DB::raw('MIN(po.po_date) as first_order'),
                DB::raw('MAX(po.po_date) as last_order')
            )
            ->groupBy('v.id', 'v.vendor_code', 'v.vendor_name')
            ->orderByDesc('total_amount')
            ->limit(20)
            ->get();

        $totalSpending = $topVendors->sum('total_amount');
        $totalOrders = $topVendors->sum('total_orders');
        $totalVendors = $topVendors->count();

        $topVendors->transform(function ($vendor) use ($totalSpending) {
            $vendor->share_percentage = $totalSpending > 0 ? ($vendor->total_amount / $totalSpending) * 100 : 0;
            return $vendor;
        });

        // Chart data
        $chartLabels = $topVendors->take(10)->pluck('vendor_name')->toArray();
        $chartValues = $topVendors->take(10)->pluck('total_amount')->toArray();

        return view('analysis.vendor_analysis', array_merge($filters, compact(
            'topVendors',
            'totalSpending',
            'totalOrders',
            'totalVendors',
            'chartLabels',
            'chartValues'
        )));
    }

    /**
     * 4. Approval Pipeline & Bottleneck Analysis
     */
    public function approvalPipeline(Request $request)
    {
        $filters = $this->getFilterData($request);

        // Helper: build a base PR query with filters applied via budget_items → projects
        $buildFilteredPR = function () use ($filters) {
            $q = DB::table('purchase_requests as pr')
                ->join('budget_items as bi', 'pr.budget_item_id', '=', 'bi.id')
                ->join('budget_plans as bp', 'bi.plan_id', '=', 'bp.id')
                ->join('projects as p', 'bp.project_id', '=', 'p.id');

            if ($filters['selectedYear']) {
                $q->where('bp.fiscal_year', $filters['selectedYear']);
            }
            if ($filters['selectedCustomer']) {
                $q->where(function ($sub) use ($filters) {
                    $sub->where('p.customer', $filters['selectedCustomer'])
                        ->orWhere('bp.customer', $filters['selectedCustomer']);
                });
            }
            if ($filters['selectedCategory']) {
                $q->where('p.category', $filters['selectedCategory']);
            }
            if ($filters['isRestricted']) {
                $q->where(function ($sub) use ($filters) {
                    $sub->whereIn('p.customer', $filters['allowedCodes'])
                        ->orWhereIn('bp.customer', $filters['allowedCodes'])
                        ->orWhereIn('bp.customer', $filters['allowedNames']);
                });
            }
            return $q;
        };

        // PR Status distribution
        $prStatuses = $buildFilteredPR()
            ->select('pr.status', DB::raw('COUNT(DISTINCT pr.pr_number) as count'))
            ->groupBy('pr.status')
            ->get()
            ->keyBy('status');

        // PR in each approval stage
        $prStages = $buildFilteredPR()
            ->select('pr.current_approver_role', DB::raw('COUNT(DISTINCT pr.pr_number) as count'))
            ->where('pr.status', 'Submitted')
            ->whereNotNull('pr.current_approver_role')
            ->groupBy('pr.current_approver_role')
            ->get()
            ->keyBy('current_approver_role');

        // Average approval time per stage
        $avgTimes = [];

        $deptHeadAvg = $buildFilteredPR()
            ->whereNotNull('pr.dept_head_approved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, pr.created_at, pr.dept_head_approved_at)) as avg_hours')
            ->first();
        $avgTimes['Dept Head'] = $deptHeadAvg->avg_hours ? round($deptHeadAvg->avg_hours / 24, 1) : 0;

        $financeAvg = $buildFilteredPR()
            ->whereNotNull('pr.finance_approved_at')
            ->whereNotNull('pr.dept_head_approved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, pr.dept_head_approved_at, pr.finance_approved_at)) as avg_hours')
            ->first();
        $avgTimes['Finance'] = $financeAvg->avg_hours ? round($financeAvg->avg_hours / 24, 1) : 0;

        $divHeadAvg = $buildFilteredPR()
            ->whereNotNull('pr.div_head_approved_at')
            ->whereNotNull('pr.finance_approved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, pr.finance_approved_at, pr.div_head_approved_at)) as avg_hours')
            ->first();
        $avgTimes['Division Head'] = $divHeadAvg->avg_hours ? round($divHeadAvg->avg_hours / 24, 1) : 0;

        $purchasingAvg = $buildFilteredPR()
            ->whereNotNull('pr.purchasing_executed_at')
            ->whereNotNull('pr.div_head_approved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, pr.div_head_approved_at, pr.purchasing_executed_at)) as avg_hours')
            ->first();
        $avgTimes['Purchasing'] = $purchasingAvg->avg_hours ? round($purchasingAvg->avg_hours / 24, 1) : 0;

        // Total average process time
        $totalAvgTime = $buildFilteredPR()
            ->where('pr.status', 'Approved')
            ->whereNotNull('pr.purchasing_executed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, pr.created_at, pr.purchasing_executed_at)) as avg_hours')
            ->first();
        $totalAvgDays = $totalAvgTime->avg_hours ? round($totalAvgTime->avg_hours / 24, 1) : 0;

        // Budget Plan status distribution (also filtered)
        $bpQuery = DB::table('budget_plans as bp')
            ->join('projects as p', 'bp.project_id', '=', 'p.id');
        if ($filters['selectedYear']) {
            $bpQuery->where('bp.fiscal_year', $filters['selectedYear']);
        }
        if ($filters['selectedCustomer']) {
            $bpQuery->where(function ($sub) use ($filters) {
                $sub->where('p.customer', $filters['selectedCustomer'])
                    ->orWhere('bp.customer', $filters['selectedCustomer']);
            });
        }
        if ($filters['selectedCategory']) {
            $bpQuery->where('p.category', $filters['selectedCategory']);
        }
        if ($filters['isRestricted']) {
            $bpQuery->where(function ($sub) use ($filters) {
                $sub->whereIn('p.customer', $filters['allowedCodes'])
                    ->orWhereIn('bp.customer', $filters['allowedCodes'])
                    ->orWhereIn('bp.customer', $filters['allowedNames']);
            });
        }
        $bpStatuses = $bpQuery
            ->select('bp.status', DB::raw('COUNT(*) as count'))
            ->groupBy('bp.status')
            ->get()
            ->keyBy('status');

        // Recent pending PRs (oldest first = bottleneck)
        $pendingPRs = $buildFilteredPR()
            ->select(
                'pr.pr_number',
                'pr.current_approver_role',
                'pr.purpose',
                'pr.created_at',
                DB::raw('SUM(pr.qty_req * pr.estimated_price) as total_value')
            )
            ->where('pr.status', 'Submitted')
            ->whereNotNull('pr.current_approver_role')
            ->groupBy('pr.pr_number', 'pr.current_approver_role', 'pr.purpose', 'pr.created_at')
            ->orderBy('pr.created_at', 'asc')
            ->limit(10)
            ->get();

        // Funnel data
        $totalSubmitted = $buildFilteredPR()->distinct('pr.pr_number')->count('pr.pr_number');
        $passedDeptHead = $buildFilteredPR()->whereNotNull('pr.dept_head_approved_at')->distinct('pr.pr_number')->count('pr.pr_number');
        $passedFinance = $buildFilteredPR()->whereNotNull('pr.finance_approved_at')->distinct('pr.pr_number')->count('pr.pr_number');
        $passedDivHead = $buildFilteredPR()->whereNotNull('pr.div_head_approved_at')->distinct('pr.pr_number')->count('pr.pr_number');
        $fullyApproved = $buildFilteredPR()->where('pr.status', 'Approved')->distinct('pr.pr_number')->count('pr.pr_number');
        $rejected = $buildFilteredPR()->where('pr.status', 'Rejected')->distinct('pr.pr_number')->count('pr.pr_number');

        $funnelData = [
            ['stage' => 'Submitted', 'count' => $totalSubmitted],
            ['stage' => 'Dept Head Approved', 'count' => $passedDeptHead],
            ['stage' => 'Finance Approved', 'count' => $passedFinance],
            ['stage' => 'Div Head Approved', 'count' => $passedDivHead],
            ['stage' => 'Fully Approved', 'count' => $fullyApproved],
        ];

        return view('analysis.approval_pipeline', array_merge($filters, compact(
            'prStatuses',
            'prStages',
            'avgTimes',
            'totalAvgDays',
            'bpStatuses',
            'pendingPRs',
            'funnelData',
            'rejected'
        )));
    }

    /**
     * 5. Year-over-Year Comparison
     */
    public function yearComparison(Request $request)
    {
        $filters = $this->getFilterData($request);
        $user = Auth::user();

        $yearA = $request->input('year_a', $filters['years']->first());
        $yearB = $request->input('year_b', $filters['years']->skip(1)->first() ?? $filters['years']->first());

        $getYearData = function ($year) use ($filters) {
            $query = DB::table('budget_items as bi')
                ->join('budget_plans as bp', 'bi.plan_id', '=', 'bp.id')
                ->join('projects as p', 'bp.project_id', '=', 'p.id')
                ->where('bp.status', 'Approved')
                ->where('bp.fiscal_year', $year)
                ->whereNull('bi.parent_item_id');

            if ($filters['selectedCustomer']) {
                $query->where(function ($q) use ($filters) {
                    $q->where('p.customer', $filters['selectedCustomer'])
                        ->orWhere('bp.customer', $filters['selectedCustomer']);
                });
            }
            if ($filters['selectedCategory']) {
                $query->where('p.category', $filters['selectedCategory']);
            }
            if (!empty($filters['selectedProject'])) {
                $query->where('p.id', $filters['selectedProject']);
            }
            if ($filters['isRestricted']) {
                $query->where(function ($q) use ($filters) {
                    $q->whereIn('p.customer', $filters['allowedCodes'])
                        ->orWhereIn('bp.customer', $filters['allowedCodes'])
                        ->orWhereIn('bp.customer', $filters['allowedNames']);
                });
            }

            $totalBudget = (clone $query)->sum(DB::raw('bi.qty * bi.estimated_price'));
            $totalRealization = (clone $query)
                ->selectRaw('SUM((SELECT COALESCE(SUM(pr.qty_req * pr.estimated_price), 0) FROM purchase_requests pr WHERE pr.budget_item_id = bi.id AND pr.status != \'Rejected\')) as total')
                ->first()->total ?? 0;

            $projectCount = (clone $query)->distinct('bp.project_id')->count('bp.project_id');
            $planCount = (clone $query)->distinct('bi.plan_id')->count('bi.plan_id');

            $byCategory = (clone $query)
                ->select(
                    'bi.category',
                    DB::raw('SUM(bi.qty * bi.estimated_price) as total_budget'),
                    DB::raw('SUM((SELECT COALESCE(SUM(pr.qty_req * pr.estimated_price), 0) FROM purchase_requests pr WHERE pr.budget_item_id = bi.id AND pr.status != \'Rejected\')) as total_realization')
                )
                ->groupBy('bi.category')
                ->orderByDesc('total_budget')
                ->get();

            $byCustomer = (clone $query)
                ->select(
                    DB::raw('COALESCE(bp.customer, p.customer) as customer_name'),
                    DB::raw('SUM(bi.qty * bi.estimated_price) as total_budget'),
                    DB::raw('SUM((SELECT COALESCE(SUM(pr.qty_req * pr.estimated_price), 0) FROM purchase_requests pr WHERE pr.budget_item_id = bi.id AND pr.status != \'Rejected\')) as total_realization')
                )
                ->groupBy(DB::raw('COALESCE(bp.customer, p.customer)'))
                ->orderByDesc('total_budget')
                ->get();

            return [
                'year' => $year,
                'total_budget' => (float) $totalBudget,
                'total_realization' => (float) $totalRealization,
                'absorption' => $totalBudget > 0 ? ($totalRealization / $totalBudget) * 100 : 0,
                'project_count' => $projectCount,
                'plan_count' => $planCount,
                'by_category' => $byCategory,
                'by_customer' => $byCustomer,
            ];
        };

        $dataA = $getYearData($yearA);
        $dataB = $getYearData($yearB);

        $budgetGrowth = $dataB['total_budget'] > 0 ? (($dataA['total_budget'] - $dataB['total_budget']) / $dataB['total_budget']) * 100 : 0;
        $realizationGrowth = $dataB['total_realization'] > 0 ? (($dataA['total_realization'] - $dataB['total_realization']) / $dataB['total_realization']) * 100 : 0;

        return view('analysis.year_comparison', array_merge($filters, compact(
            'yearA',
            'yearB',
            'dataA',
            'dataB',
            'budgetGrowth',
            'realizationGrowth'
        )));
    }

    /**
     * 6. Category Investment Breakdown
     */
    public function categoryInvestment(Request $request)
    {
        $filters = $this->getFilterData($request);

        $baseQuery = DB::table('budget_items as bi')
            ->join('budget_plans as bp', 'bi.plan_id', '=', 'bp.id')
            ->join('projects as p', 'bp.project_id', '=', 'p.id')
            ->where('bp.status', 'Approved')
            ->whereNull('bi.parent_item_id');

        $this->applyFilters($baseQuery, $filters);

        // By investment category (Machine, Tooling, Facility, Building)
        $byCategory = (clone $baseQuery)
            ->select(
                'bi.category',
                DB::raw('COUNT(DISTINCT bi.id) as item_count'),
                DB::raw('SUM(bi.qty * bi.estimated_price) as total_budget'),
                DB::raw('SUM((SELECT COALESCE(SUM(pr.qty_req * pr.estimated_price), 0) FROM purchase_requests pr WHERE pr.budget_item_id = bi.id AND pr.status != \'Rejected\')) as total_realization')
            )
            ->whereNotNull('bi.category')
            ->where('bi.category', '!=', '')
            ->groupBy('bi.category')
            ->orderByDesc('total_budget')
            ->get()
            ->map(function ($item) {
                $item->absorption_rate = $item->total_budget > 0 ? ($item->total_realization / $item->total_budget) * 100 : 0;
                $item->remaining = $item->total_budget - $item->total_realization;
                return $item;
            });

        // By customer within categories (stacked)
        $byCategoryCustomer = (clone $baseQuery)
            ->select(
                'bi.category',
                DB::raw('COALESCE(bp.customer, p.customer) as customer_name'),
                DB::raw('SUM(bi.qty * bi.estimated_price) as total_budget')
            )
            ->whereNotNull('bi.category')
            ->where('bi.category', '!=', '')
            ->groupBy('bi.category', DB::raw('COALESCE(bp.customer, p.customer)'))
            ->orderBy('bi.category')
            ->orderByDesc('total_budget')
            ->get()
            ->groupBy('category');

        // By year trend (if no year filter)
        $yearTrend = collect();
        if (!$filters['selectedYear']) {
            $yearTrend = DB::table('budget_items as bi')
                ->join('budget_plans as bp', 'bi.plan_id', '=', 'bp.id')
                ->select(
                    'bp.fiscal_year',
                    'bi.category',
                    DB::raw('SUM(bi.qty * bi.estimated_price) as total_budget')
                )
                ->where('bp.status', 'Approved')
                ->whereNull('bi.parent_item_id')
                ->whereNotNull('bi.category')
                ->where('bi.category', '!=', '')
                ->groupBy('bp.fiscal_year', 'bi.category')
                ->orderBy('bp.fiscal_year')
                ->get()
                ->groupBy('fiscal_year');
        }

        $grandBudget = $byCategory->sum('total_budget');
        $grandRealization = $byCategory->sum('total_realization');

        return view('analysis.category_investment', array_merge($filters, compact(
            'byCategory',
            'byCategoryCustomer',
            'yearTrend',
            'grandBudget',
            'grandRealization'
        )));
    }

    /**
     * 7. Berita Acara (Transfer Documents)
     */
    public function beritaAcara(Request $request)
    {
        // Filters
        $selectedYear = $request->input('year');
        $selectedCustomer = $request->input('customer');
        $selectedCategory = $request->input('category');
        $selectedProject = $request->input('project');

        // Available filter options
        $years = DB::table('budget_transfers')->distinct()->pluck('fiscal_year')->filter()->sort()->values();
        $customers = DB::table('budget_transfers')->distinct()->pluck('customer')->filter()->sort()->values();
        $categories = DB::table('budget_transfers')->distinct()->pluck('business_category')->filter()->sort()->values();
        $projects = DB::table('budget_transfers')
            ->select('source_project_name')
            ->distinct()
            ->pluck('source_project_name')
            ->merge(
                DB::table('budget_transfers')
                    ->select('target_project_name')
                    ->distinct()
                    ->pluck('target_project_name')
            )
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Build query
        $query = DB::table('budget_transfers')
            ->leftJoin('users', 'budget_transfers.transferred_by', '=', 'users.id')
            ->select('budget_transfers.*', 'users.full_name as user_name');

        if ($selectedYear) {
            $query->where('budget_transfers.fiscal_year', $selectedYear);
        }
        if ($selectedCustomer) {
            $query->where('budget_transfers.customer', $selectedCustomer);
        }
        if ($selectedCategory) {
            $query->where('budget_transfers.business_category', $selectedCategory);
        }
        if ($selectedProject) {
            $query->where(function ($q) use ($selectedProject) {
                $q->where('budget_transfers.source_project_name', $selectedProject)
                    ->orWhere('budget_transfers.target_project_name', $selectedProject);
            });
        }

        // User restriction
        $user = Auth::user();
        if (in_array($user->role, ['User', 'Dept Head'])) {
            $allowedCodes = $user->customers()->pluck('customer_code')->toArray();
            $allowedNames = $user->customers()->pluck('customer_name')->toArray();
            $query->where(function ($q) use ($allowedCodes, $allowedNames) {
                $q->whereIn('budget_transfers.customer', $allowedCodes)
                    ->orWhereIn('budget_transfers.customer', $allowedNames);
            });
        }

        $transfers = $query->orderBy('budget_transfers.created_at', 'desc')->get();

        // Group by fiscal year for folder display
        $groupedTransfers = $transfers->groupBy('fiscal_year');

        // Stats
        $totalTransfers = $transfers->count();
        $totalYears = $transfers->pluck('fiscal_year')->unique()->count();

        return view('analysis.berita_acara', compact(
            'transfers',
            'groupedTransfers',
            'totalTransfers',
            'totalYears',
            'years',
            'customers',
            'categories',
            'projects',
            'selectedYear',
            'selectedCustomer',
            'selectedCategory',
            'selectedProject'
        ));
    }
}
