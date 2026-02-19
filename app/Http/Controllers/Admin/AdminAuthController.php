<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;

class AdminAuthController extends Controller
{
    public function loginPage()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        //validate input
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required']);

        // Retreving the user 
        $user = User::where('email', $request->email)->first();

        // checking for the trashed user
        if (!$user || $user->is_trash) {
            return back()->with('error', 'Your accout has been disabled.')->withInput();
        }

        // checking the login
        if (Auth::attempt($credentials)) {
            // $request->session()->put('user', Auth::user());
            return redirect()->route('admin.dashboard');
        }

        // return if the login fail
        return back()->withErrors(['error' => 'Invalid credentials.'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.logout');
    }
}
