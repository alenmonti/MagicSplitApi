<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class userController extends Controller
{
    public function user(Request $request)
    {
        try {
            return response()->json(['status' => true, 'data' => $request->user()]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function groups(Request $request)
    {
        try {
            return response()->json(['status' => true, 'data' => $request->user()->groups]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function addFriend(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email'
            ]);
            $user = $request->user();
            $friend = User::where('email', $request->email)->first();
            if ($user->id === $friend->id) return response()->json(['status' => false, 'message' => 'No puedes añadirte a ti mismo como amigo'], 400);
            if ($user->friends->contains($friend)) return response()->json(['status' => false, 'message' => 'El usuario ya es tu amigo'], 400);

            $user->friends()->attach($friend->id);
            return response()->json(['status' => true, 'message' => 'Amigo añadido correctamente']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function removeFriend(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);
            $user = $request->user();
            $friend = User::find($request->user_id);
            if (!$user->friends->contains($friend)) return response()->json(['status' => false, 'message' => 'El usuario no es tu amigo'], 400);

            $user->friends()->detach($friend->id);
            return response()->json(['status' => true, 'message' => 'Amigo eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function friends(Request $request)
    {
        try {
            return response()->json(['status' => true, 'friends' => $request->user()->friends]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function balance(Request $request)
    {
        try {
            $user = $request->user();
            $balance = $user->groups->sum('pivot.balance');
            return response()->json(['status' => true, 'balance' => number_format($balance, 2, ',', '.')]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

}
