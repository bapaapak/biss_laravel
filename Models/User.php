<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Role Constants
    const ROLE_SUPER_ADMIN = 'Super Admin';
    const ROLE_ADMIN = 'Admin';
    const ROLE_USER = 'User';
    const ROLE_FINANCE = 'Finance';
    const ROLE_PURCHASING = 'Purchasing';
    const ROLE_DEPT_HEAD = 'Dept Head';
    const ROLE_DIV_HEAD = 'Division Head';

    // All available roles
    const ROLES = [
        self::ROLE_SUPER_ADMIN,
        self::ROLE_ADMIN,
        self::ROLE_USER,
        self::ROLE_FINANCE,
        self::ROLE_PURCHASING,
        self::ROLE_DEPT_HEAD,
        self::ROLE_DIV_HEAD,
    ];

    public $timestamps = false;
    const UPDATED_AT = null;

    protected $fillable = [
        'username',
        'password',
        'full_name',
        'role',
        'department',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Role Check Helpers
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN]);
    }

    public function isDeptHead(): bool
    {
        return $this->role === self::ROLE_DEPT_HEAD;
    }

    public function isDivHead(): bool
    {
        return $this->role === self::ROLE_DIV_HEAD;
    }

    public function isFinance(): bool
    {
        return $this->role === self::ROLE_FINANCE;
    }

    public function isPurchasing(): bool
    {
        return $this->role === self::ROLE_PURCHASING;
    }

    public function canManageRoles(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function canManageUsers(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN]);
    }

    // Check if user can approve at given stage
    public function canApprovePR(string $currentStage): bool
    {
        $approvalMap = [
            'Dept Head' => self::ROLE_DEPT_HEAD,
            'Finance' => self::ROLE_FINANCE,
            'Division Head' => self::ROLE_DIV_HEAD,
            'Purchasing' => self::ROLE_PURCHASING,
        ];

        // Super Admin and Admin can approve at any stage
        if ($this->isAdmin()) {
            return true;
        }

        return isset($approvalMap[$currentStage]) && $this->role === $approvalMap[$currentStage];
    }

    public function canApproveBudget(string $currentStage): bool
    {
        $approvalMap = [
            'Dept Head' => self::ROLE_DEPT_HEAD,
            'Division Head' => self::ROLE_DIV_HEAD,
            'Finance' => self::ROLE_FINANCE,
        ];

        if ($this->isAdmin()) {
            return true;
        }

        return isset($approvalMap[$currentStage]) && $this->role === $approvalMap[$currentStage];
    }
}
