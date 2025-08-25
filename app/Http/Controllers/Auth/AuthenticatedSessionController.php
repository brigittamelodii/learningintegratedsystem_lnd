<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Get the selected role for custom redirection
        $selectedRole = $request->getSelectedRole();

        // Store the login context in session for potential use
        $request->session()->put('login_context', [
            'login_as' => $request->input('login_as'),
            'selected_role' => $selectedRole,
            'login_time' => now(),
        ]);

        // Redirect based on role
        return $this->redirectBasedOnRole($selectedRole);
    }

    /**
     * Redirect user based on their role with priority system.
     */
    protected function redirectBasedOnRole(string $role): RedirectResponse
    {
        // You can also add a priority system if user has multiple admin roles
        $user = Auth::user();
        
        // Priority-based redirection for users with multiple roles
        if ($user->hasRole('superadmin')) {
            return redirect()->intended('/dashboard');
        } elseif ($user->hasRole('manager')) {
            return redirect()->intended('/dashboard');
        } elseif ($user->hasRole('pic')) {
            return redirect()->intended('/pic-dashboard');
        } elseif ($user->hasRole('executive')) {
            return redirect()->intended('/dashboard');
        } else {
            return redirect()->intended('/participant-dashboard'); // participant or default
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}