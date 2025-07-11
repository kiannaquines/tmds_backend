<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ResetPasswordNotification;

class AuthController extends Controller
{

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function registerFaculty(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'role' => ['required', 'string', 'max:255', 'exists:roles,name'],
                'password' => ['required', 'min:8', 'confirmed'],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole($validated['role']);

            return response()->json([
                'message' => 'User registered successfully',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed, something went wrong.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => 'Something went wrong during registration.',
            ], 500);
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'min:8', 'confirmed'],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole('Student');

            return response()->json([
                'message' => 'User registered successfully',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed, something went wrong.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => 'Something went wrong during registration.',
            ], 500);
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! is_string($user->password) || ! Hash::check($request->password, $user->password)) return response()->json(['message' => 'Invalid email or password.'], 401);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'You have successfully logged in!',
            'access_token' => $token,
            'user' => $user,
            'role' => $user->getRoleNames()->first(),
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.'
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => [
                'name' => $request->user()->name,
                'role' => $request->user()->getRoleNames()->first(),
            ],
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function forgot(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $otp = rand(100000, 999999);

        Cache::put('password_reset_otp_' . $request->email, $otp, now()->addMinutes(10));

        Notification::route('mail', $request->email)
            ->notify(new ResetPasswordNotification($otp));

        return response()->json([
            'message' => 'An OTP has been sent to your email.',
        ], 200);
    }


    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
            'password' => 'required|confirmed|min:8',
        ]);

        $cachedOtp = Cache::get('password_reset_otp_' . $request->email);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json([
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        $user->tokens()->delete();

        Cache::forget('password_reset_otp_' . $request->email);

        return response()->json([
            'message' => 'Password has been reset successfully.',
        ]);
    }
}
