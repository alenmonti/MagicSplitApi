<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|confirmed'
            ]);
    
            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);
    
            $user->save();
    
            return response()->json(['status' => true, 'message' => 'Successfully created user'], 201);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string'
            ]);
    
            if (!auth()->attempt($request->only('email', 'password'))) {
                return response()->json(['status' => false, 'message' => 'Bad credentials'], 401);
            }
    
            $user = User::where('email', $request->email)->firstOrFail();
            $token = $user->createToken('token')->plainTextToken;
    
            return response()->json(['status' => true, 'token' => $token]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json(['status' => true, 'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

}
