<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request)
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        $user = Socialite::driver('google')->stateless()->user();

        $user = User::updateOrCreate([
            'google_id' => $user->id,
        ], [
            'name' => $user->name,
            'email' => $user->email,
            'password' => bcrypt($user->id),
        ]);

        $token = $user->createToken('token')->plainTextToken;

        

        return redirect("http://localhost:5173/login?token=".$token."&name=".$user->name."&email=".$user->email."&id=".$user->id);
    }
}
