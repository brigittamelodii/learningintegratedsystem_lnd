<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by search keyword (email or NIK)
        if ($request->has('search') && $request->search !== null) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('email', 'like', "%{$keyword}%")
                  ->orWhere('karyawan_nik', 'like', "%{$keyword}%");
            });
        }

        $users = $query->orderBy('email')->get();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $selectedRole = $request->role;
        
        // Use the new helper method from the User model
        $success = $user->assignRoleWithLogic($selectedRole);
        
        if ($success) {
            $adminRoles = ['pic', 'manager', 'executive', 'superadmin'];
            if (in_array($selectedRole, $adminRoles)) {
                $message = "Role {$selectedRole} assigned successfully (with participant access).";
            } else {
                $message = "Role {$selectedRole} assigned successfully.";
            }
            return back()->with('success', $message);
        } else {
            return back()->with('error', 'Failed to assign role.');
        }
    }

    /**
     * Remove a specific role from user (while maintaining participant if needed)
     */
    public function removeRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $roleToRemove = $request->role;
        $adminRoles = ['pic', 'manager', 'executive', 'superadmin'];
        
        // Remove the specified role
        $user->removeRole($roleToRemove);
        
        // If removing an admin role, check if user still has other admin roles
        if (in_array($roleToRemove, $adminRoles)) {
            $stillHasAdminRole = $user->hasAnyRole($adminRoles);
            
            // If no more admin roles, you can choose to keep or remove participant
            // Here we keep participant role even if no admin roles
            if (!$stillHasAdminRole && !$user->hasRole('participant')) {
                $user->assignRole('participant');
            }
        }

        return back()->with('success', "Role {$roleToRemove} removed successfully.");
    }

    /**
     * Get user's display roles (showing primary role in UI)
     */
    public function getUserDisplayRole(User $user): string
    {
        $adminRoles = ['superadmin', 'manager', 'pic', 'executive'];
        
        foreach ($adminRoles as $role) {
            if ($user->hasRole($role)) {
                return $role;
            }
        }
        
        return 'participant';
    }

    /**
     * Bulk assign roles to multiple users
     */
    public function bulkAssignRole(Request $request)
    {
        $request->validate([
            'users' => 'required|array',
            'users.*' => 'exists:users,id',
            'role' => 'required|exists:roles,name',
        ]);

        $selectedRole = $request->role;
        $userIds = $request->users;
        $successCount = 0;
        
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            
            if ($user && $user->assignRoleWithLogic($selectedRole)) {
                $successCount++;
            }
        }

        if ($successCount > 0) {
            return back()->with('success', "Role {$selectedRole} assigned to {$successCount} users successfully.");
        } else {
            return back()->with('error', 'Failed to assign roles to users.');
        }
    }
}