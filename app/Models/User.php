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
        'email',
        'nip',
        'role',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
                    ->withPivot('is_granted')
                    ->withTimestamps();
    }

    /**
     * Check if user has specific permission (PBAC)
     */
    public function hasPermission(string $slug): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Admin has all permissions
        if ($this->role === 'admin') {
            return true;
        }

        // Check explicit granted user permissions
        $userPerm = UserPermission::where('user_id', $this->id)
            ->whereHas('permission', function ($q) use ($slug) {
                $q->where('slug', $slug);
            })
            ->first();

        if ($userPerm) {
            return (bool) $userPerm->is_granted;
        }

        return false;
    }

    /**
     * Get array of granted permission slugs
     */
    public function getPermissionSlugs(): array
    {
        if ($this->role === 'admin') {
            return Permission::pluck('slug')->toArray();
        }

        return UserPermission::where('user_id', $this->id)
            ->where('is_granted', true)
            ->whereHas('permission')
            ->get()
            ->map(fn($up) => $up->permission->slug)
            ->toArray();
    }
}
