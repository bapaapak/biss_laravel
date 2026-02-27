<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\MasterDepartment;
use App\Models\MasterCategory;
use App\Models\MasterIO;
use App\Models\MasterCostCenter;
use App\Models\MasterPlant;
use App\Models\MasterItem;
use App\Models\MasterAsset;
use App\Models\MasterStorageLocation;
use App\Models\Project;

class AdminController extends Controller
{
    public function index()
    {
        // Users (Moved to RbacController)
        // $users = User::orderBy('role')->orderBy('full_name')->get();
        // $statTotal = User::count();
        // $statAdmin = User::where('role', 'Admin')->count();
        // $statUser = User::where('role', 'User')->count();

        // Master Data
        $depts = MasterDepartment::orderBy('dept_name')->get();
        $cats = MasterCategory::orderBy('category_name')->get();
        $ios = MasterIO::orderBy('io_number')->get();
        $ccs = MasterCostCenter::orderBy('cc_name')->get();
        $plants = MasterPlant::orderBy('plant_name')->get();
        $items = MasterItem::orderBy('item_name')->limit(100)->get();
        $projects = Project::orderBy('project_name')->get();

        // Suppliers and Customers (check if tables exist)
        $suppliers = DB::table('master_suppliers')->orderBy('supplier_name')->get();
        $customers = DB::table('master_customers')->orderBy('customer_name')->get();
        $currencies = DB::table('master_currencies')->orderBy('currency_code')->get();
        $assets = MasterAsset::orderBy('asset_no')->get();
        $storageLocations = MasterStorageLocation::orderBy('sloc')->get();

        return view('admin.index', compact(
            // 'users',
            // 'statTotal',
            // 'statAdmin',
            // 'statUser',
            'depts',
            'cats',
            'ios',
            'ccs',
            'plants',
            'items',
            'projects',
            'suppliers',
            'customers',
            'currencies',
            'assets',
            'storageLocations'
        ));
    }

    // User CRUD (Moved to RbacController)

    // Generic Master Data CRUD
    public function storeMaster(Request $request, $type)
    {
        $table = $this->getTable($type);
        $data = $request->except(['_token', 'type']);

        // Identify the 'code' column for uniqueness check (heuristic based on type)
        $codeColumn = $this->getCodeColumn($type);

        if ($codeColumn && isset($data[$codeColumn])) {
            $exists = DB::table($table)->where($codeColumn, $data[$codeColumn])->exists();
            if ($exists) {
                return redirect()->route('admin.index', ['tab' => $this->getTabName($type)])->with('error', 'Error: ' . $codeColumn . ' already exists!');
            }
        }

        try {
            DB::table($table)->insert($data);
            return redirect()->route('admin.index', ['tab' => $this->getTabName($type)])->with('success', ucfirst($type) . ' added successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.index', ['tab' => $this->getTabName($type)])->with('error', 'Failed to add ' . $type . ': ' . $e->getMessage());
        }
    }

    public function updateMaster(Request $request, $type, $id)
    {
        $table = $this->getTable($type);
        $data = $request->except(['_token', '_method', 'type']);

        // Uniqueness check for update (exclude self)
        $codeColumn = $this->getCodeColumn($type);
        if ($codeColumn && isset($data[$codeColumn])) {
            $exists = DB::table($table)
                ->where($codeColumn, $data[$codeColumn])
                ->where('id', '!=', $id)
                ->exists();
            if ($exists) {
                return redirect()->route('admin.index', ['tab' => $this->getTabName($type)])->with('error', 'Error: ' . $codeColumn . ' already exists!');
            }
        }

        try {
            DB::table($table)->where('id', $id)->update($data);
            return redirect()->route('admin.index', ['tab' => $this->getTabName($type)])->with('success', ucfirst($type) . ' updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.index', ['tab' => $this->getTabName($type)])->with('error', 'Failed to update ' . $type . ': ' . $e->getMessage());
        }
    }

    public function destroyMaster($type, $id)
    {
        if (auth()->user()->role !== 'Super Admin') {
            return redirect()->back()->with('error', 'Only Super Admin can delete master data.');
        }

        // The switch statement was not fully provided in the instruction,
        // so only the role check is added as per the explicit instruction.
        // The existing deletion logic remains.
        $table = $this->getTable($type);
        DB::table($table)->where('id', $id)->delete();

        return redirect()->route('admin.index', ['tab' => $this->getTabName($type)])->with('success', ucfirst($type) . ' deleted successfully.');
    }

    private function getTable($type)
    {
        $tables = [
            'department' => 'master_departments',
            'category' => 'master_categories',
            'io' => 'master_io',
            'cc' => 'master_cost_center',
            'plant' => 'master_plants',
            'item' => 'master_items',
            'supplier' => 'master_suppliers',
            'customer' => 'master_customers',
            'currency' => 'master_currencies',
            'project' => 'projects',
            'asset' => 'master_assets',
            'storage_location' => 'master_storage_locations',
        ];
        return $tables[$type] ?? 'master_departments';
    }

    private function getCodeColumn($type)
    {
        $columns = [
            'department' => 'dept_code',
            'category' => 'category_code',
            'io' => 'io_number',
            'cc' => 'cc_code',
            'plant' => 'plant_code',
            'item' => 'item_code',
            'supplier' => 'supplier_code',
            'customer' => 'customer_code',
            'currency' => 'currency_code',
            'project' => 'project_code',
            'asset' => 'asset_no',
            'storage_location' => 'sloc',
        ];
        return $columns[$type] ?? null;
    }

    private function getTabName($type)
    {
        $tabs = [
            'department' => 'depts',
            'category' => 'cats',
            'io' => 'ios',
            'cc' => 'ccs',
            'plant' => 'plants',
            'item' => 'items',
            'supplier' => 'suppliers',
            'customer' => 'customers',
            'currency' => 'currencies',
            'project' => 'projects',
            'asset' => 'assets',
            'storage_location' => 'slocs',
        ];
        return $tabs[$type] ?? 'users';
    }
}
