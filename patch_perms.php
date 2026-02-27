<?php
// Patch perms
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$defaults = [
    'Super Admin' => ['Full Access', 'Manage Users', 'Manage Master Data', 'Approve All', 'Menu: Dashboard', 'Menu: Budget Plan', 'Menu: Purchase Request', 'Menu: Purchase Order', 'Menu: Projects'],
    'Admin' => ['Approve Budget', 'Manage Master Data (Partial)', 'View All Reports', 'Menu: Dashboard', 'Menu: Budget Plan', 'Menu: Purchase Request', 'Menu: Purchase Order', 'Menu: Projects'],
    'Dept Head' => ['Approve PR (Dept Level)', 'Create Budget Plan', 'Menu: Dashboard', 'Menu: Budget Plan', 'Menu: Purchase Request', 'Menu: Projects'],
    'Division Head' => ['Approve PR (Div Level)', 'View Division Reports', 'Menu: Dashboard', 'Menu: Purchase Request', 'Menu: Projects'],
    'Finance' => ['Approve Payments', 'View Financial Reports', 'Menu: Dashboard', 'Menu: Purchase Request', 'Menu: Purchase Order', 'Menu: Projects'],
    'Purchasing' => ['Process PR', 'Manage Suppliers', 'Menu: Dashboard', 'Menu: Purchase Request', 'Menu: Purchase Order', 'Menu: Projects'],
    'User' => ['Create PR', 'View Own Status', 'Menu: Dashboard', 'Menu: Purchase Request'],
];

foreach (\App\Models\RolePermission::all() as $rp) {
    if (isset($defaults[$rp->role])) {
        $existing = $rp->permissions ?? [];
        $newPerms = array_values(array_unique(array_merge($existing, $defaults[$rp->role])));
        $rp->permissions = $newPerms;
        $rp->save();
        echo 'Updated ' . $rp->role . PHP_EOL;
    }
}
echo "Done.";
