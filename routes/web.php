<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PurchaseRequestController;

use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.post');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/notifications/mark-read', [App\Http\Controllers\DashboardController::class, 'markNotificationsRead'])->name('notifications.markRead');
    Route::post('/notifications/clear', [App\Http\Controllers\DashboardController::class, 'clearNotifications'])->name('notifications.clear');
    // RBAC Routes
    Route::get('/rbac', [App\Http\Controllers\RbacController::class, 'index'])->name('rbac.index');
    Route::post('/rbac/permissions/update', [App\Http\Controllers\RbacController::class, 'updatePermissions'])->name('rbac.permissions.update');
    Route::post('/rbac/user', [App\Http\Controllers\RbacController::class, 'storeUser'])->name('rbac.user.store');
    Route::put('/rbac/user/{id}', [App\Http\Controllers\RbacController::class, 'updateUser'])->name('rbac.user.update');
    Route::delete('/rbac/user/{id}', [App\Http\Controllers\RbacController::class, 'destroyUser'])->name('rbac.user.destroy');

    Route::get('/admin', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.index');

    // Admin Master Data Routes
    Route::post('/admin/master/{type}', [App\Http\Controllers\AdminController::class, 'storeMaster'])->name('admin.master.store');
    Route::put('/admin/master/{type}/{id}', [App\Http\Controllers\AdminController::class, 'updateMaster'])->name('admin.master.update');
    Route::delete('/admin/master/{type}/{id}', [App\Http\Controllers\AdminController::class, 'destroyMaster'])->name('admin.master.destroy');

    Route::resource('projects', App\Http\Controllers\ProjectController::class);

    Route::resource('budget', App\Http\Controllers\BudgetController::class);
    Route::post('budget/{plan}/item', [App\Http\Controllers\BudgetController::class, 'storeItem'])->name('budget.item.store');
    Route::put('budget/item/{item}/update', [App\Http\Controllers\BudgetController::class, 'updateItem'])->name('budget.item.update');
    Route::put('budget/item/{item}/transfer', [App\Http\Controllers\BudgetController::class, 'transferItem'])->name('budget.item.transfer');
    Route::delete('budget/item/{item}', [App\Http\Controllers\BudgetController::class, 'destroyItem'])->name('budget.item.destroy');
    Route::post('budget/{id}/submit', [App\Http\Controllers\BudgetController::class, 'submitForApproval'])->name('budget.submit');
    Route::post('budget/{id}/approve', [App\Http\Controllers\BudgetController::class, 'approve'])->name('budget.approve');
    Route::post('budget/{id}/reject', [App\Http\Controllers\BudgetController::class, 'reject'])->name('budget.reject');
    Route::get('budget/{id}/print', [App\Http\Controllers\BudgetController::class, 'print'])->name('budget.print');

    Route::get('pr/approve/{pr_number}', [PurchaseRequestController::class, 'approve'])->name('pr.approve')->where('pr_number', '.*');
    Route::get('pr/reject/{pr_number}', [PurchaseRequestController::class, 'reject'])->name('pr.reject')->where('pr_number', '.*');
    Route::get('pr/print/{pr_number}', [PurchaseRequestController::class, 'print'])->name('pr.print')->where('pr_number', '.*');
    Route::post('pr/import', [PurchaseRequestController::class, 'import'])->name('pr.import');
    Route::resource('pr', PurchaseRequestController::class);

    // Purchase Order Routes
    Route::get('po/approve/{po_number}', [App\Http\Controllers\PoController::class, 'approve'])->name('po.approve')->where('po_number', '.*');
    Route::get('po/reject/{po_number}', [App\Http\Controllers\PoController::class, 'reject'])->name('po.reject')->where('po_number', '.*');
    Route::get('po/print/{po_number}', [App\Http\Controllers\PoController::class, 'print'])->name('po.print')->where('po_number', '.*');
    Route::resource('po', App\Http\Controllers\PoController::class);

    // Vendor Routes
    Route::resource('vendors', App\Http\Controllers\MasterVendorController::class);

    // Analysis Routes
    Route::get('analysis/budget-evaluation', [App\Http\Controllers\AnalysisController::class, 'budgetEvaluation'])->name('analysis.budget_evaluation');
    Route::get('analysis/budget-evaluation/{planId}', [App\Http\Controllers\AnalysisController::class, 'budgetEvaluationDetail'])->name('analysis.budget_evaluation.detail');
    Route::get('analysis/budget-evaluation/{planId}/print', [App\Http\Controllers\AnalysisController::class, 'printEvaluation'])->name('analysis.evaluation.print');
    Route::post('analysis/evaluation/save', [App\Http\Controllers\AnalysisController::class, 'saveEvaluation'])->name('analysis.evaluation.save');

    // New Analysis Routes
    Route::get('analysis/budget-absorption', [App\Http\Controllers\AnalysisController::class, 'budgetAbsorption'])->name('analysis.budget_absorption');
    Route::get('analysis/monthly-trend', [App\Http\Controllers\AnalysisController::class, 'monthlyTrend'])->name('analysis.monthly_trend');
    Route::get('analysis/vendor-analysis', [App\Http\Controllers\AnalysisController::class, 'vendorAnalysis'])->name('analysis.vendor_analysis');
    Route::get('analysis/approval-pipeline', [App\Http\Controllers\AnalysisController::class, 'approvalPipeline'])->name('analysis.approval_pipeline');
    Route::get('analysis/year-comparison', [App\Http\Controllers\AnalysisController::class, 'yearComparison'])->name('analysis.year_comparison');
    Route::get('analysis/category-investment', [App\Http\Controllers\AnalysisController::class, 'categoryInvestment'])->name('analysis.category_investment');
    Route::get('analysis/berita-acara', [App\Http\Controllers\AnalysisController::class, 'beritaAcara'])->name('analysis.berita_acara');
    // Profile Routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});

// Deployment Helpers (Temporary - remove after use)
// Deployment Helpers (Automatic via Webhook)
Route::any('/deploy-update', function (Illuminate\Http\Request $request) {
    // Ganti 'rahasia123' dengan kunci sesuka Anda
    $secretKey = 'bis-deploy-2026';

    if ($request->query('key') !== $secretKey) {
        return response("Unauthorized Access", 403);
    }

    $output = "";
    try {
        // 1. Tarik Kode Terbaru dari GitHub
        // Kita gunakan full path ke git jika perlu, tapi biasanya 'git' saja cukup
        $gitOutput = shell_exec('git fetch origin && git reset --hard origin/main 2>&1');
        $output .= "Git Pull Output:\n" . ($gitOutput ?: "No output from git") . "\n\n";

        // 2. Bersihkan Cache
        Artisan::call('optimize:clear');
        $output .= "Cache cleared successfully.\n";

        // 3. Jalankan Migrasi
        try {
            Artisan::call('migrate', ["--force" => true]);
            $output .= "Migrations run successfully.\n";
        } catch (\Exception $e) {
            $output .= "Migration skipped/failed: " . $e->getMessage() . "\n";
        }

        return "<pre>Deployment results:\n\n$output</pre>";
    } catch (\Exception $e) {
        return "Critical failure: " . $e->getMessage();
    }
});
