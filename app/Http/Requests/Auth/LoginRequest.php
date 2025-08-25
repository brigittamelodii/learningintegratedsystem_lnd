<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'login_as' => ['required', 'in:admin,user'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'login_as.required' => 'Please select login type.',
            'login_as.in' => 'Invalid login type selected.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Validate user has the required role after successful authentication
        $this->validateUserRole();

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Validate that the authenticated user has the required role.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateUserRole(): void
    {
        $user = Auth::user();
        $loginAs = $this->input('login_as');

        if ($loginAs === 'admin') {
            // Check if user has any admin role
            $adminRoles = ['superadmin', 'manager', 'pic', 'executive'];
            
            if (!$user->hasAnyRole($adminRoles)) {
                Auth::logout();
                
                throw ValidationException::withMessages([
                    'email' => 'You do not have administrative privileges. Please use User Login or contact administrator.',
                ]);
            }
        } else {
            // For user login, check if user is participant or doesn't have admin roles
            $adminRoles = ['superadmin', 'manager', 'pic', 'executive'];
            $hasAdminRole = $user->hasAnyRole($adminRoles);
            
            // Allow login if user has participant role OR no admin roles (regular user)
            if ($hasAdminRole && !$user->hasRole('participant')) {
                Auth::logout();
                
                throw ValidationException::withMessages([
                    'email' => 'You have administrative privileges. Please use Admin Login option instead.',
                ]);
            }
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    /**
     * Get the user's highest admin role for redirection purposes.
     */
    public function getSelectedRole(): string
    {
        if ($this->input('login_as') === 'user') {
            return 'participant';
        }

        $user = Auth::user();
        $adminRoles = ['superadmin', 'manager', 'pic', 'executive'];
        
        // Return the first admin role found (you can customize this priority)
        foreach ($adminRoles as $role) {
            if ($user->hasRole($role)) {
                return $role;
            }
        }
        
        return 'participant'; // fallback
    }
}