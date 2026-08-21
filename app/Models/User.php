<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'employee_id',
        'role',
        'department',
        'permissions',
        'can_view_user_monitoring',
        'admin_note',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'can_view_user_monitoring' => 'boolean',
        ];
    }

    public function purchasingLogs()
    {
        return $this->hasMany(PurchasingLog::class);
    }

    public function isAdmin(): bool
    {
        return strtolower($this->role) === 'admin' || strtolower($this->role) === 'administrator';
    }

    public function isSupervisor(): bool
    {
        return $this->isAdmin() || strtolower($this->role) === 'supervisor';
    }

    public function isLeader(): bool
    {
        return $this->isSupervisor() || strtolower($this->role) === 'leader';
    }

    public function isStaff(): bool
    {
        return strtolower($this->role) === 'staff';
    }

    public function canViewUserMonitoring(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return (bool) $this->can_view_user_monitoring || $this->hasPermission('view_user_monitoring');
    }

    public function hasPermission(string $permissionKey): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $perms = $this->permissions;
        if (!is_array($perms)) {
            return false;
        }

        return in_array('*', $perms, true) || in_array($permissionKey, $perms, true);
    }
}
