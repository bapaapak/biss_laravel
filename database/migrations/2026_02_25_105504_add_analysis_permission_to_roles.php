<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Add 'Menu: Analysis' permission to Super Admin and Admin roles.
     */
    public function up(): void
    {
        $roles = ['Super Admin', 'Admin'];

        foreach ($roles as $role) {
            $rp = DB::table('role_permissions')->where('role', $role)->first();
            if ($rp) {
                $perms = json_decode($rp->permissions, true) ?? [];
                if (!in_array('Menu: Analysis', $perms)) {
                    $perms[] = 'Menu: Analysis';
                    DB::table('role_permissions')
                        ->where('role', $role)
                        ->update(['permissions' => json_encode($perms)]);
                }
            }
        }
    }

    /**
     * Remove 'Menu: Analysis' permission from all roles.
     */
    public function down(): void
    {
        $roles = DB::table('role_permissions')->get();

        foreach ($roles as $rp) {
            $perms = json_decode($rp->permissions, true) ?? [];
            $perms = array_values(array_filter($perms, fn($p) => $p !== 'Menu: Analysis'));
            DB::table('role_permissions')
                ->where('id', $rp->id)
                ->update(['permissions' => json_encode($perms)]);
        }
    }
};
