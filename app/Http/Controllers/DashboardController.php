<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\BudgetPlan;
use App\Models\BudgetItem;
use App\Models\MasterCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
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

        // Restrict business categories for restricted users
        if ($isRestricted && count($allowedCodes) > 0) {
            // Get categories from projects that belong to user's allowed customers
            $allowedCategories = DB::table('projects')
                ->whereIn('customer', array_merge($allowedCodes, $allowedNames))
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category')
                ->toArray();

            // Filter categories to only show allowed ones
            $businessCategories = $businessCategories->filter(function ($cat) use ($allowedCategories) {
                return in_array($cat->category_name, $allowedCategories);
            })->values();

            $canSeeAllCategories = false;

            // Auto-select if user has only one category
            if ($businessCategories->count() === 1 && !$selectedCategory) {
                $selectedCategory = $businessCategories->first()->category_name;
            }

            // Ensure selected category is within allowed categories
            if ($selectedCategory && !in_array($selectedCategory, $allowedCategories)) {
                $selectedCategory = $businessCategories->count() > 0 ? $businessCategories->first()->category_name : null;
            }
        }

        // Filter years and customers based on user's allowed categories
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

        // Apply restriction for relations involving budget_plans and projects
        $applyRestriction = function ($query) use ($isRestricted, $allowedCodes, $allowedNames) {
            if ($isRestricted) {
                $query->where(function ($q) use ($allowedCodes, $allowedNames) {
                    $q->whereIn('projects.customer', $allowedCodes)
                        ->orWhereIn('budget_plans.customer', $allowedCodes)
                        ->orWhereIn('budget_plans.customer', $allowedNames);
                });
            }
        };

        $topProjectsQuery = DB::table('projects')
            ->select('projects.id', 'projects.project_name', 'projects.start_date', 'projects.end_date', 'projects.status')
            ->selectRaw('(SELECT status FROM budget_plans WHERE project_id = projects.id AND (status != "Draft" OR created_by = ?) ORDER BY id DESC LIMIT 1) as budget_status', [auth()->id()])
            ->selectRaw('(SELECT COALESCE(SUM(bi.qty * bi.estimated_price), 0) FROM budget_plans bp 
                          JOIN budget_items bi ON bp.id = bi.plan_id 
                          WHERE bp.project_id = projects.id AND bp.status = "Approved" AND bi.parent_item_id IS NULL) as total_budget')
            ->selectRaw('COALESCE((SELECT SUM(pr.qty_req * pr.estimated_price) FROM purchase_requests pr 
                          JOIN budget_items bi2 ON pr.budget_item_id = bi2.id
                          JOIN budget_plans bp2 ON bi2.plan_id = bp2.id
                          WHERE bp2.project_id = projects.id AND bp2.status = "Approved" AND pr.status = "Approved"), 0) as total_realization');

        if ($isRestricted) {
            $topProjectsQuery->where(function ($q) use ($allowedCodes, $allowedNames) {
                $q->whereIn('projects.customer', $allowedCodes)
                    ->orWhereExists(function ($ex) use ($allowedCodes, $allowedNames) {
                        $ex->select(DB::raw(1))
                            ->from('budget_plans')
                            ->whereColumn('budget_plans.project_id', 'projects.id')
                            ->where(function ($sub_ex) use ($allowedCodes, $allowedNames) {
                                $sub_ex->whereIn('budget_plans.customer', $allowedCodes)
                                    ->orWhereIn('budget_plans.customer', $allowedNames);
                            });
                    });
            });
        }

        if ($selectedCategory) {
            $topProjectsQuery->where('projects.category', $selectedCategory);
        }

        if ($selectedCustomer) {
            $topProjectsQuery->where('projects.customer', $selectedCustomer);
        }

        if ($selectedYear) {
            $topProjectsQuery->whereExists(function ($q) use ($selectedYear) {
                $q->select(DB::raw(1))
                    ->from('budget_plans')
                    ->whereColumn('budget_plans.project_id', 'projects.id')
                    ->where('budget_plans.fiscal_year', $selectedYear);
            });
        }

        $topProjectsQueryBase = clone $topProjectsQuery;

        $topProjects = $topProjectsQuery->having('total_budget', '>', 0)
            ->orderByDesc('total_budget')
            ->limit(20) // Show more projects to match pie chart
            ->get();

        $selectedProjectId = $request->filled('project_id') ? $request->query('project_id') : null;

        $selectedProject = null;
        $projectTasks = collect();
        $selectedProjectBudget = 0;
        $selectedProjectRealization = 0;
        $remainingBalance = 0;

        if ($selectedProjectId) {
            $selectedProject = $topProjects->firstWhere('id', $selectedProjectId);

            if (!$selectedProject) {
                // Use the base query that isn't limited to top 20
                $selectedProject = (clone $topProjectsQueryBase)->where('projects.id', $selectedProjectId)->first();
            }

            if ($selectedProject) {
                $selectedProjectBudget = $selectedProject->total_budget;
                $selectedProjectRealization = $selectedProject->total_realization;
                $remainingBalance = $selectedProjectBudget - $selectedProjectRealization;

                $activePlan = BudgetPlan::where('project_id', $selectedProject->id)->where('status', 'Approved')->first();
                if ($activePlan) {
                    $categories = [
                        'A' => 'Machine',
                        'B' => 'Tooling and equipment',
                        'C' => 'Testing and equipment',
                        'D' => 'Facility equipment investment plan'
                    ];

                    $categoryData = [];
                    foreach ($categories as $code => $name) {
                        $categoryData[$code] = [
                            'name' => $code . '. ' . $name,
                            'budget' => 0,
                            'expenditure' => 0,
                            'remarks' => [],
                            'pr_numbers' => [],
                            'po_numbers' => [],
                        ];
                    }

                    $allBudgetItems = BudgetItem::where('plan_id', $activePlan->id)->get();

                    foreach ($allBudgetItems as $item) {
                        $catMatch = null;
                        if ($item->item_code && preg_match('/^[A-D]/i', $item->item_code, $matches)) {
                            $catMatch = strtoupper($matches[0]);
                        } elseif ($item->category && preg_match('/^[A-D]/i', $item->category, $matches)) {
                            $catMatch = strtoupper($matches[0]);
                        }

                        if ($catMatch && isset($categoryData[$catMatch])) {
                            $budget = $item->qty * $item->estimated_price;
                            $categoryData[$catMatch]['budget'] += $budget;

                            $prs = PurchaseRequest::where('budget_item_id', $item->id)
                                ->where('status', 'Approved')
                                ->get(['id', 'pr_number', 'qty_req', 'estimated_price']);

                            foreach ($prs as $pr) {
                                $categoryData[$catMatch]['expenditure'] += $pr->qty_req * $pr->estimated_price;
                                if ($pr->pr_number && !in_array($pr->pr_number, $categoryData[$catMatch]['pr_numbers'])) {
                                    $categoryData[$catMatch]['pr_numbers'][] = $pr->pr_number;
                                    if (!in_array($pr->pr_number, $categoryData[$catMatch]['remarks'])) {
                                        $categoryData[$catMatch]['remarks'][] = $pr->pr_number;
                                    }
                                }

                                // Collect PO numbers linked to this PR
                                if (Schema::hasTable('po_items')) {
                                    $poNumbers = DB::table('purchase_orders')
                                        ->join('po_items', 'purchase_orders.id', '=', 'po_items.po_id')
                                        ->where('po_items.pr_item_id', $pr->id)
                                        ->distinct()
                                        ->pluck('purchase_orders.po_number')
                                        ->toArray();
                                    foreach ($poNumbers as $poNum) {
                                        if ($poNum && !in_array($poNum, $categoryData[$catMatch]['po_numbers'])) {
                                            $categoryData[$catMatch]['po_numbers'][] = $poNum;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    foreach ($categoryData as $data) {
                        if ($data['budget'] > 0 || $data['expenditure'] > 0) {
                            $allRemarks = $data['remarks'];
                            $projectTasks->push((object) [
                                'task_name'       => $data['name'],
                                'nominal_budget'  => $data['budget'],
                                'expenditure'     => $data['expenditure'],
                                'remaining'       => $data['budget'] - $data['expenditure'],
                                'percentage_used' => $data['budget'] > 0 ? ($data['expenditure'] / $data['budget']) * 100 : 0,
                                'remarks'         => count($allRemarks) > 0 ? implode(', ', array_slice($allRemarks, 0, 2)) . (count($allRemarks) > 2 ? '...' : '') : '-',
                                'pr_count'        => count($data['pr_numbers']),
                                'po_count'        => count($data['po_numbers']),
                            ]);
                        }
                    }
                }
            }
        }

        // --- Summary Stats (Synchronization) ---
        // We calculate totals for ALL projects that match the current category/restriction
        $allProjectsQuery = DB::table('projects')
            ->select('projects.id', 'projects.project_name', 'projects.category')
            ->selectRaw('(SELECT COALESCE(SUM(bi.qty * bi.estimated_price), 0) FROM budget_plans bp 
                          JOIN budget_items bi ON bp.id = bi.plan_id 
                          WHERE bp.project_id = projects.id AND bp.status = "Approved" AND bi.parent_item_id IS NULL) as total_budget')
            ->selectRaw('COALESCE((SELECT SUM(pr.qty_req * pr.estimated_price) FROM purchase_requests pr 
                          JOIN budget_items bi2 ON pr.budget_item_id = bi2.id
                          JOIN budget_plans bp2 ON bi2.plan_id = bp2.id
                          WHERE bp2.project_id = projects.id AND bp2.status = "Approved" AND pr.status = "Approved"), 0) as total_realization')
            ->having('total_budget', '>', 0);

        if ($isRestricted) {
            $allProjectsQuery->where(function ($q) use ($allowedCodes, $allowedNames) {
                $q->whereIn('projects.customer', $allowedCodes)
                    ->orWhereExists(function ($ex) use ($allowedCodes, $allowedNames) {
                        $ex->select(DB::raw(1))
                            ->from('budget_plans')
                            ->whereColumn('budget_plans.project_id', 'projects.id')
                            ->where(function ($sub_ex) use ($allowedCodes, $allowedNames) {
                                $sub_ex->whereIn('budget_plans.customer', $allowedCodes)
                                    ->orWhereIn('budget_plans.customer', $allowedNames);
                            });
                    });
            });
        }

        if ($selectedCategory) {
            $allProjectsQuery->where('projects.category', $selectedCategory);
        }

        if ($selectedCustomer) {
            $allProjectsQuery->where('projects.customer', $selectedCustomer);
        }

        if ($selectedYear) {
            $allProjectsQuery->whereExists(function ($q) use ($selectedYear) {
                $q->select(DB::raw(1))
                    ->from('budget_plans')
                    ->whereColumn('budget_plans.project_id', 'projects.id')
                    ->where('budget_plans.fiscal_year', $selectedYear);
            });
        }

        $allProjects = $allProjectsQuery->get();
        $summaryTotalBudget = $allProjects->sum('total_budget');
        $summaryTotalRealization = $allProjects->sum('total_realization');
        $summaryRemainingBalance = $summaryTotalBudget - $summaryTotalRealization;

        // --- Total PO Usage (sum of PO amounts linked to projects) ---
        $summaryTotalPoUsage = 0;
        $selectedProjectPoUsage = 0;
        $allProjectIds = $allProjects->pluck('id')->toArray();

        if (Schema::hasTable('po_items') && count($allProjectIds) > 0) {
            $summaryTotalPoUsage = DB::table('purchase_orders')
                ->join('po_items', 'purchase_orders.id', '=', 'po_items.po_id')
                ->join('purchase_requests', 'po_items.pr_item_id', '=', 'purchase_requests.id')
                ->join('budget_items', 'purchase_requests.budget_item_id', '=', 'budget_items.id')
                ->join('budget_plans', 'budget_items.plan_id', '=', 'budget_plans.id')
                ->whereIn('budget_plans.project_id', $allProjectIds)
                ->sum('po_items.total_price');

            if ($selectedProject) {
                $selectedProjectPoUsage = DB::table('purchase_orders')
                    ->join('po_items', 'purchase_orders.id', '=', 'po_items.po_id')
                    ->join('purchase_requests', 'po_items.pr_item_id', '=', 'purchase_requests.id')
                    ->join('budget_items', 'purchase_requests.budget_item_id', '=', 'budget_items.id')
                    ->join('budget_plans', 'budget_items.plan_id', '=', 'budget_plans.id')
                    ->where('budget_plans.project_id', $selectedProject->id)
                    ->sum('po_items.total_price');
            }
        }

        // --- Per-Category Stats (when "All Business Categories" is selected) ---
        $categoryStats = collect();
        $totalProjectCount = 0;
        $totalPrCount = 0;
        $totalPoCount = 0;

        // Always compute totals for the current filter (all categories or specific)
        $filteredProjectIds = $allProjects->pluck('id')->toArray();
        $totalProjectCount = count($filteredProjectIds);

        if (count($filteredProjectIds) > 0) {
            // Count all PRs linked to these projects
            $totalPrCount = DB::table('purchase_requests')
                ->join('budget_items', 'purchase_requests.budget_item_id', '=', 'budget_items.id')
                ->join('budget_plans', 'budget_items.plan_id', '=', 'budget_plans.id')
                ->whereIn('budget_plans.project_id', $filteredProjectIds)
                ->distinct('purchase_requests.pr_number')
                ->count('purchase_requests.pr_number');

            // Count all POs linked to these projects
            if (Schema::hasTable('po_items')) {
                $totalPoCount = DB::table('purchase_orders')
                    ->join('po_items', 'purchase_orders.id', '=', 'po_items.po_id')
                    ->join('purchase_requests', 'po_items.pr_item_id', '=', 'purchase_requests.id')
                    ->join('budget_items', 'purchase_requests.budget_item_id', '=', 'budget_items.id')
                    ->join('budget_plans', 'budget_items.plan_id', '=', 'budget_plans.id')
                    ->whereIn('budget_plans.project_id', $filteredProjectIds)
                    ->distinct('purchase_orders.po_number')
                    ->count('purchase_orders.po_number');
            }
        }

        if (!$selectedCategory) {
            // Group projects by category for per-category cards
            $groupedByCategory = $allProjects->groupBy('category');

            foreach ($groupedByCategory as $catName => $projects) {
                $catProjectIds = $projects->pluck('id')->toArray();

                $prCount = DB::table('purchase_requests')
                    ->join('budget_items', 'purchase_requests.budget_item_id', '=', 'budget_items.id')
                    ->join('budget_plans', 'budget_items.plan_id', '=', 'budget_plans.id')
                    ->whereIn('budget_plans.project_id', $catProjectIds)
                    ->distinct('purchase_requests.pr_number')
                    ->count('purchase_requests.pr_number');

                $poCount = 0;
                if (Schema::hasTable('po_items')) {
                    $poCount = DB::table('purchase_orders')
                        ->join('po_items', 'purchase_orders.id', '=', 'po_items.po_id')
                        ->join('purchase_requests', 'po_items.pr_item_id', '=', 'purchase_requests.id')
                        ->join('budget_items', 'purchase_requests.budget_item_id', '=', 'budget_items.id')
                        ->join('budget_plans', 'budget_items.plan_id', '=', 'budget_plans.id')
                        ->whereIn('budget_plans.project_id', $catProjectIds)
                        ->distinct('purchase_orders.po_number')
                        ->count('purchase_orders.po_number');
                }

                $categoryStats->push((object) [
                    'category_name' => $catName ?: 'Uncategorized',
                    'project_count' => count($catProjectIds),
                    'pr_count' => $prCount,
                    'po_count' => $poCount,
                    'total_budget' => $projects->sum('total_budget'),
                    'total_realization' => $projects->sum('total_realization'),
                ]);
            }

            // Sort by total_budget descending
            $categoryStats = $categoryStats->sortByDesc('total_budget')->values();
        }

        // Prepare data for the pie chart and category summary
        if (!$selectedCategory) {
            $categorySummary = $allProjects->groupBy('category')->map(function ($group, $category) use ($categoryStats) {
                $totalBudget = $group->sum('total_budget');
                $totalRealization = $group->sum('total_realization');
                $stat = $categoryStats->firstWhere('category_name', $category ?: 'Uncategorized');
                return (object) [
                    'item_name' => $category ?: 'Uncategorized',
                    'total_budget' => $totalBudget,
                    'total_realization' => $totalRealization,
                    'remaining' => $totalBudget - $totalRealization,
                    'percentage_used' => $totalBudget > 0 ? ($totalRealization / $totalBudget) * 100 : 0,
                    'project_count' => $stat ? $stat->project_count : $group->count(),
                    'pr_count' => $stat ? $stat->pr_count : 0,
                    'po_count' => $stat ? $stat->po_count : 0,
                ];
            })->values();
        } else {
            $projectIds = $allProjects->pluck('id')->toArray();

            $prCountsPerProject = DB::table('purchase_requests')
                ->join('budget_items', 'purchase_requests.budget_item_id', '=', 'budget_items.id')
                ->join('budget_plans', 'budget_items.plan_id', '=', 'budget_plans.id')
                ->whereIn('budget_plans.project_id', $projectIds)
                ->groupBy('budget_plans.project_id')
                ->selectRaw('budget_plans.project_id, COUNT(DISTINCT purchase_requests.pr_number) as pr_count')
                ->pluck('pr_count', 'project_id');

            $poCountsPerProject = collect();
            if (Schema::hasTable('po_items')) {
                $poCountsPerProject = DB::table('purchase_orders')
                    ->join('po_items', 'purchase_orders.id', '=', 'po_items.po_id')
                    ->join('purchase_requests', 'po_items.pr_item_id', '=', 'purchase_requests.id')
                    ->join('budget_items', 'purchase_requests.budget_item_id', '=', 'budget_items.id')
                    ->join('budget_plans', 'budget_items.plan_id', '=', 'budget_plans.id')
                    ->whereIn('budget_plans.project_id', $projectIds)
                    ->groupBy('budget_plans.project_id')
                    ->selectRaw('budget_plans.project_id, COUNT(DISTINCT purchase_orders.po_number) as po_count')
                    ->pluck('po_count', 'project_id');
            }

            $categorySummary = $allProjects->map(function ($project) use ($prCountsPerProject, $poCountsPerProject) {
                return (object) [
                    'item_name' => $project->project_name,
                    'total_budget' => $project->total_budget,
                    'total_realization' => $project->total_realization,
                    'remaining' => $project->total_budget - $project->total_realization,
                    'percentage_used' => $project->total_budget > 0 ? ($project->total_realization / $project->total_budget) * 100 : 0,
                    'project_count' => 1,
                    'pr_count' => $prCountsPerProject[$project->id] ?? 0,
                    'po_count' => $poCountsPerProject[$project->id] ?? 0,
                ];
            });
        }

        $pieChartData = $categorySummary->map(function ($item) {
            return [
                'label' => $item->item_name,
                'budget' => $item->total_budget,
                'realization' => $item->total_realization
            ];
        });

        if ($projectTasks->isNotEmpty()) {
            $chartData = [
                'labels' => $projectTasks->pluck('task_name')->toArray(),
                'budget' => $projectTasks->pluck('nominal_budget')->toArray(),
                'realized' => $projectTasks->pluck('expenditure')->toArray(),
            ];
        } else {
            $chartData = [
                'labels' => $categorySummary->pluck('category_name')->toArray(),
                'budget' => $categorySummary->pluck('total_budget')->toArray(),
                'realized' => $categorySummary->pluck('total_realization')->toArray(),
            ];
        }

        return view('dashboard.index', compact(
            'summaryTotalBudget',
            'summaryTotalRealization',
            'summaryRemainingBalance',
            'topProjects',
            'selectedProject',
            'selectedProjectBudget',
            'selectedProjectRealization',
            'projectTasks',
            'chartData',
            'allProjects',
            'pieChartData',
            'categorySummary',
            'businessCategories',
            'selectedCategory',
            'years',
            'selectedYear',
            'customers',
            'selectedCustomer',
            'categoryStats',
            'totalProjectCount',
            'totalPrCount',
            'totalPoCount',
            'summaryTotalPoUsage',
            'selectedProjectPoUsage',
            'canSeeAllCategories'
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
