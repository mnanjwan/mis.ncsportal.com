<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\Officer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
        ]);

        $user = null;

        // Try to find user by email first
        if (filter_var($request->username, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $request->username)->first();
        } else {
            // Try service number
            $officer = Officer::where('service_number', $request->username)->first();
            if ($officer && $officer->user_id) {
                $user = User::find($officer->user_id);
            }
        }

        if (!$user) {
            // Don't reveal if user exists or not for security
            return back()->with('success', 'If an account exists with that email or service number, a password reset link has been sent.');
        }

        if (!$user->is_active) {
            return back()->withErrors(['username' => 'Your account is inactive. Please contact HRD.']);
        }

        // Generate reset token
        $token = Str::random(64);
        
        // Store token in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // Send reset email
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
        
        // Load officer relationship for email
        $user->load('officer');
        
        // Log email attempt
        Log::info('Attempting to send password reset email', [
            'user_id' => $user->id,
            'email' => $user->email,
            'mail_driver' => config('mail.default'),
        ]);
        
        try {
            $mailSent = Mail::to($user->email)->send(new PasswordResetMail($user, $resetUrl));
            
            Log::info('Password reset email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            // Log the error but don't reveal it to the user for security
            Log::error('Failed to send password reset email: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
                'exception' => $e->getTraceAsString(),
            ]);
            
            // Still return success message for security (don't reveal if email failed)
            return back()->with('success', 'If an account exists with that email or service number, a password reset link has been sent.');
        }

        return back()->with('success', 'If an account exists with that email or service number, a password reset link has been sent.');
    }

    /**
     * Show the reset password form
     */
    public function showResetForm(Request $request, $token)
    {
        $email = $request->query('email');
        
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Reset the password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Find the reset token record
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['email' => 'Invalid reset token.']);
        }

        // Check if token is valid (not expired - 60 minutes)
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'This password reset link has expired. Please request a new one.']);
        }

        // Verify token
        if (!Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors(['email' => 'Invalid reset token.']);
        }

        // Find user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        // Update password (Laravel will automatically hash it via the model cast)
        $user->password = $request->password;
        $user->save();

        // Delete the reset token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Your password has been reset successfully. You can now login with your new password.');
    }
}

