<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\MasterDepartment;
use App\Models\MasterCustomer;
use App\Models\RolePermission;

class RbacController extends Controller
{
    public function index()
    {
        $query = User::with('customers')->orderBy('role')->orderBy('full_name');

        if ($role = request('role')) {
            $query->where('role', $role);
        }

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10)->appends(request()->query());
        $roles = User::ROLES;
        $depts = MasterDepartment::orderBy('dept_name')->get();

        // Group Customers by Category (Inferred from Projects)
        $categories = \App\Models\MasterCategory::orderBy('category_name')->pluck('category_name');

        // Map customer_code -> [categories]
        $projectMap = \Illuminate\Support\Facades\DB::table('projects')
            ->select('customer', 'category')
            ->distinct()
            ->get();

        $customerCategoryMap = [];
        foreach ($projectMap as $p) {
            if ($p->customer && $p->category) {
                $customerCategoryMap[$p->customer][] = $p->category;
            }
        }

        $allCustomersRaw = MasterCustomer::orderBy('customer_code')->get();
        $groupedCustomers = [];

        // Initialize groups
        foreach ($categories as $cat) {
            $groupedCustomers[$cat] = collect([]);
        }
        $groupedCustomers['Others'] = collect([]);

        foreach ($allCustomersRaw as $cust) {
            $cats = $customerCategoryMap[$cust->customer_code] ?? [];

            // Filter valid categories
            $validCats = array_filter($cats, fn($c) => isset($groupedCustomers[$c]));

            if (empty($validCats)) {
                $groupedCustomers['Others']->push($cust);
            } else {
                // Determine unique categories for this customer to avoid duplicates in same group (unlikely but safe)
                $uniqueCats = array_unique($validCats);
                foreach ($uniqueCats as $c) {
                    $groupedCustomers[$c]->push($cust);
                }
            }
        }

        // Convert to array of collections or keep as is.
        // We also need flat list for some logic? No, just for view.
        // Actually, let's keep $allCustomers if needed, but view mostly needs grouped.
        $allCustomers = $allCustomersRaw; // generic fallback

        // Count stats
        $statTotal = User::count();
        $statAdmin = User::where('role', 'Admin')->count();
        $statUser = User::where('role', 'User')->count();

        // Check if DB permissions exist, if not seed them
        if (RolePermission::count() === 0) {
            $this->seedPermissions();
        }

        // Get permissions keyed by role
        $dbPermissions = RolePermission::all()->keyBy('role');

        // Transform for view
        $permissions = [];
        foreach ($roles as $role) {
            $permissions[$role] = isset($dbPermissions[$role]) ? $dbPermissions[$role]->permissions : [];
        }

        $allPermissions = RolePermission::AVAILABLE_PERMISSIONS;

        return view('rbac.index', compact(
            'users',
            'roles',
            'depts',
            'allCustomers',
            'groupedCustomers',
            'statTotal',
            'statAdmin',
            'statUser',
            'permissions',
            'allPermissions'
        ));
    }

    private function seedPermissions()
    {
        $defaults = [
            'Super Admin' => ['Full Access', 'Manage Users', 'Manage Master Data', 'Approve All', 'Menu: Dashboard', 'Menu: Budget Plan', 'Menu: Purchase Request', 'Menu: Purchase Order', 'Menu: Projects', 'Menu: Analysis'],
            'Admin' => ['Approve Budget', 'Manage Master Data (Partial)', 'View All Reports', 'Menu: Dashboard', 'Menu: Budget Plan', 'Menu: Purchase Request', 'Menu: Purchase Order', 'Menu: Projects', 'Menu: Analysis'],
            'Dept Head' => ['Approve PR (Dept Level)', 'Create Budget Plan', 'Menu: Dashboard', 'Menu: Budget Plan', 'Menu: Purchase Request', 'Menu: Projects'], // Adjusted key case
            'Division Head' => ['Approve PR (Div Level)', 'View Division Reports', 'Menu: Dashboard', 'Menu: Purchase Request', 'Menu: Projects'], // Adjusted key case
            'Finance' => ['Approve Payments', 'View Financial Reports', 'Menu: Dashboard', 'Menu: Purchase Request', 'Menu: Purchase Order', 'Menu: Projects'], // Adjusted key case
            'Purchasing' => ['Process PR', 'Manage Suppliers', 'Menu: Dashboard', 'Menu: Purchase Request', 'Menu: Purchase Order', 'Menu: Projects'], // Adjusted key case
            'User' => ['Create PR', 'View Own Status', 'Menu: Dashboard', 'Menu: Purchase Request'],
        ];

        // Also map constants if keys differ (User::ROLES has 'Dept Head' but array had 'dept_head')
        // Let's use the exact strings from User::ROLES

        foreach ($defaults as $role => $perms) {
            RolePermission::create([
                'role' => $role,
                'permissions' => $perms
            ]);
        }
    }

    public function updatePermissions(Request $request)
    {
        $role = $request->input('role');
        $perms = $request->input('permissions', []);

        RolePermission::updateOrCreate(
            ['role' => $role],
            ['permissions' => $perms]
        );

        return redirect()->route('rbac.index', ['tab' => 'permissions'])->with('success', "Permissions for $role updated.");
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'username' => 'required|min:3|alpha_dash|unique:users,username',
            'password' => 'required|min:3',
            'department' => 'required',
            'role' => 'required'
        ], [
            'username.min' => 'Username harus minimal 3 karakter.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, dash, dan underscore (tanpa spasi).',
        ]);

        User::create([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'department' => $request->department,
            'role' => $request->role
        ]);

        return redirect()->route('rbac.index', ['tab' => 'users'])->with('success', 'User added successfully.');
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = [
            'full_name' => $request->full_name ?? $user->full_name,
            'department' => $request->department ?? $user->department,
            'role' => $request->role ?? $user->role
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Sync customers
        if ($request->has('customers')) {
            $user->customers()->sync($request->input('customers'));
        } else {
            // If customers field is present in request (even empty array/null), sync
            // But if it's not in request (e.g. partial update), careful. 
            // The edit modal will send it.
            // Check if we are handling the edit user form
            if ($request->has('role')) { // Assuming role is always there in edit form
                $user->customers()->sync([]);
            }
        }

        return redirect()->route('rbac.index', ['tab' => 'users'])->with('success', 'User updated successfully.');
    }

    public function destroyUser($id)
    {
        if (auth()->user()->role !== 'Super Admin') {
            return redirect()->back()->with('error', 'Only Super Admin can delete users.');
        }

        try {
            $user = User::findOrFail($id);
            $user->delete();
            return redirect()->route('rbac.index', ['tab' => 'users'])->with('success', 'User deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return back()->with('error', 'Cannot delete this user because they have associated records.');
            }
            return back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}
