<?php
/**
 * Patch: Add 'Menu: Analysis' permission to Super Admin and Admin roles.
 * Run once: php patch_analysis_perm.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\RolePermission;

$roles = ['Super Admin', 'Admin'];

foreach ($roles as $role) {
    $rp = RolePermission::where('role', $role)->first();
    if ($rp) {
        $perms = $rp->permissions ?? [];
        if (!in_array('Menu: Analysis', $perms)) {
            $perms[] = 'Menu: Analysis';
            $rp->permissions = $perms;
            $rp->save();
            echo "✅ Added 'Menu: Analysis' to {$role}\n";
        } else {
            echo "ℹ️  {$role} already has 'Menu: Analysis'\n";
        }
    } else {
        echo "⚠️  {$role} role not found in role_permissions table\n";
    }
}

echo "\nDone! You can now manage 'Menu: Analysis' from the Access Control page.\n";
