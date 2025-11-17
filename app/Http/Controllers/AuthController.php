<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Mail\VerifySignUpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    public function signup(SignupRequest $request)
    {
        // Generate verification token
        $verification_token = Str::random(60);

        // Create user with verification token
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'verification_token' => $verification_token,
        ]);

        // Create verification URL
        $verifyUrl = url('/verify-email/' . $verification_token);

        // Send verification email
        Mail::to($user->email)->send(new VerifySignUpMail($user, $verifyUrl));

        return response()->json([
            'message' => 'Registration successful. Please check your email to verify your account.'
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::withTrashed()->where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'Invalid Password or Email'], 404);
        } else if ($user->trashed()) {
            $user->restore();
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            if (is_null($user->email_verified_at)) {
                return response()->json(['error' => 'Please verify your email before logging in.'], 403);
            }
            $token = $user->createToken('myApp')->accessToken;
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user->name,
            ], 200);
        };
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens->each(function ($token) {
            $token->revoke();
            $token->delete();
        });
        Auth::guard('web')->logout();        // hoặc Auth::logout() nếu dùng mặc định
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function verifyEmail($token)
    {
        $user = User::where('verification_token', $token)->first();
        if (!$user) {
            return redirect('/page-login')->with('error', 'Invalid or expired verification link.');
        }
        $user->email_verified_at = now();
        $user->verification_token = null;
        $user->save();
        $message = 'Your email has been verified successfully. You can now log in.';
        return redirect('/page-login')->with(compact('message'));
    }
}
