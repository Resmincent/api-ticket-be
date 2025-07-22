<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterStoreRequest;
use App\Http\Resources\UserResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            // Attempt to authenticate the user
            if (!Auth::guard()->attempt($request->only('email', 'password'))) {
                return response()->json(
                    ['error' => 'Unauthorized', 'data' => null],
                    401,
                );
            }
            // If auth is successful
            $user = Auth::user();
            $token = $user->createToken('Personal Access Token')->plainTextToken;
            return response()->json([
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => $user,
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred during login',
            ], 401);
        }
    }

    public function me()
    {
        try {
            $user = Auth::user();
            return response()->json([
                'message' => 'User retrieved successfully',
                'data' => new UserResource($user),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred while retrieving user',
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user) {
                $user->currentAccessToken()->delete();
                return response()->json([
                    'message' => 'Logout successful',
                    'data' => null,
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred during logout',
            ], 500);
        }
    }

    public function register(RegisterStoreRequest $request)
    {

        $data = $request->validated();
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'user',
            ]);

            $user->save();

            $token = $user->createToken('Personal Access Token')->plainTextToken;

            DB::commit();
            return response()->json([
                'message' => 'Registration successful',
                'data' => [
                    'token' => $token,
                    'user' => new UserResource($user),
                ]
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred during registration',
            ], 500);
        }
    }
}
