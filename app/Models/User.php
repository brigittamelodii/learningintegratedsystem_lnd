<?php

namespace App\Models;

/**
 * @method bool hasRole(string|int|array|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection $roles, string|null $guard = null)
 */

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'karyawan_nik',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relasi ke data participant (jika kamu mau akses data peserta)
    public function participant()
    {
        return $this->hasMany(Participant::class, 'user_id');
    }

    public function participant_temp()
    {
        return $this->hasMany(ParticipantsTemp::class, 'user_id');
    }

    // Ambil semua kelas yang user ikuti lewat model Participant
    public function classes()
    {
        return $this->hasManyThrough(
            Classes::class,    // model tujuan
            Participant::class, // model perantara
            'user_id',         // foreign key di participant mengarah ke user
            'id',              // primary key di classes
            'id',              // local key di user
            'class_id'         // foreign key di participant mengarah ke classes
        );
    }

    public function participantClasses()
    {
        return $this->belongsToMany(Classes::class, 'participants', 'user_id', 'class_id');
    }

    public function invitedClasses()
    {
        return $this->belongsToMany(Classes::class, 'participants_temp', 'user_id', 'class_id');
    }

    /**
     * Boot method to handle automatic participant role assignment
     */
    protected static function boot()
    {
        parent::boot();

        // When a user is created, assign participant role by default
        static::created(function ($user) {
            if (!$user->hasAnyRole(['superadmin', 'manager', 'pic', 'executive', 'participant'])) {
                $user->assignRole('participant');
            }
        });
    }

    /**
     * Custom method to assign role with admin logic
     * This is a helper method, not overriding the trait method
     */
    public function assignRoleWithLogic(string $selectedRole): bool
    {
        $adminRoles = ['pic', 'manager', 'executive', 'superadmin'];
        
        try {
            if (in_array($selectedRole, $adminRoles)) {
                $rolesToSync = [$selectedRole, 'participant'];
            } else {
                $rolesToSync = [$selectedRole];
            }
            
            $this->syncRoles($rolesToSync);
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Role assignment error', [
                'error' => $e->getMessage(),
                'user_id' => $this->id,
                'role' => $selectedRole
            ]);
            
            return false;
        }
    }

    /**
     * Get user's primary role based on priority.
     * Higher priority roles take precedence.
     */
    public function getPrimaryRole(): string
    {
        $rolePriority = [
            'superadmin' => 4,
            'manager' => 3,
            'pic' => 2,
            'executive' => 1,
            'participant' => 0,
        ];

        $userRoles = $this->getRoleNames()->toArray();
        $highestPriority = -1;
        $primaryRole = 'participant'; // default

        foreach ($userRoles as $role) {
            if (isset($rolePriority[$role]) && $rolePriority[$role] > $highestPriority) {
                $highestPriority = $rolePriority[$role];
                $primaryRole = $role;
            }
        }

        return $primaryRole;
    }

    /**
     * Check if user is admin (has any admin role).
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['superadmin', 'manager', 'pic', 'executive']);
    }

    /**
     * Check if user is participant only (no admin roles).
     */
    public function isParticipantOnly(): bool
    {
        return $this->hasRole('participant') && !$this->isAdmin();
    }

    /**
     * Get user's dashboard route based on primary role.
     */
    public function getDashboardRoute(): string
    {
        $primaryRole = $this->getPrimaryRole();

        return match($primaryRole) {
            'superadmin' => '/admin/dashboard',
            'manager' => '/manager/dashboard',
            'pic' => '/pic/dashboard',
            'executive' => '/executive/dashboard',
            default => '/dashboard',
        };
    }

    /**
     * Get user's role display name.
     */
    public function getRoleDisplayName(): string
    {
        $primaryRole = $this->getPrimaryRole();

        return match($primaryRole) {
            'superadmin' => 'Super Administrator',
            'manager' => 'Manager',
            'pic' => 'Person In Charge',
            'executive' => 'Executive',
            'participant' => 'Participant',
            default => 'User',
        };
    }

    /**
     * Get all user roles with display names.
     */
    public function getAllRolesWithNames(): array
    {
        $roleNames = [
            'superadmin' => 'Super Administrator',
            'manager' => 'Manager',
            'pic' => 'Person In Charge',
            'executive' => 'Executive',
            'participant' => 'Participant',
        ];

        $userRoles = $this->getRoleNames()->toArray();
        $result = [];

        foreach ($userRoles as $role) {
            $result[$role] = $roleNames[$role] ?? ucfirst($role);
        }

        return $result;
    }

    /**
     * Get user's primary administrative role (excludes participant from display)
     */
    public function getPrimaryAdminRole(): ?string
    {
        $adminRoles = ['superadmin', 'manager', 'pic', 'executive'];
        
        foreach ($adminRoles as $role) {
            if ($this->hasRole($role)) {
                return $role;
            }
        }
        
        return null;
    }

    /**
     * Check if user should be treated as admin in the UI
     */
    public function shouldShowAsAdmin(): bool
    {
        return $this->hasAnyRole(['superadmin', 'manager', 'pic', 'executive']);
    }

    /**
     * Get roles for display (excludes participant if user has admin roles)
     */
    public function getDisplayRoles(): array
    {
        $roles = $this->getRoleNames()->toArray();
        
        // If user has admin roles, don't show participant in UI
        if ($this->shouldShowAsAdmin()) {
            $roles = array_filter($roles, fn($role) => $role !== 'participant');
        }
        
        return $roles;
    }

    /**
     * Check if user can access learning activities (has participant role)
     */
    public function canAccessLearning(): bool
    {
        return $this->hasRole('participant');
    }

    /**
     * Get user's accessible classes through participant relationship
     * This method combines both direct class access and admin privileges
     */
    public function getAccessibleClasses()
    {
        if ($this->isAdmin()) {
            // Admin users can potentially access all classes
            // You can customize this based on your business logic
            return Classes::all();
        }
        
        // Regular participants only access their enrolled classes
        return $this->classes;
    }
}