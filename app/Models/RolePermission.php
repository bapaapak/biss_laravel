<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;

    protected $fillable = ['role', 'permissions'];

    protected $casts = [
        'permissions' => 'array',
    ];

    const AVAILABLE_PERMISSIONS = [
        'Full Access',
        'Manage Users',
        'Manage Master Data',
        'Approve All',
        'Approve Budget',
        'Manage Master Data (Partial)',
        'View All Reports',
        'Approve PR (Dept Level)',
        'Create Budget Plan',
        'Approve PR (Div Level)',
        'View Division Reports',
        'Approve Payments',
        'View Financial Reports',
        'Process PR',
        'Manage Suppliers',
        'Create PR',
        'View Own Status',
        'Menu: Dashboard',
        'Menu: Budget Plan',
        'Menu: Purchase Request',
        'Menu: Purchase Order',
        'Menu: Projects',
        'Menu: Analysis'
    ];
}
