<?php

    namespace App\Http\Controllers\Auth;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;

    class LoginController extends Controller
    {
        /**
         * Display the login form.
         */
        public function showLoginForm()
        {
            return view('login');
        }

        /**
         * Handle the login process.
         */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Redirect based on role
            $role = Auth::user()->role;

            if ($role === 'admin') {
                return redirect('/');
            } elseif ($role === 'software engineer') {
                return redirect('/');
            } elseif ($role === 'project manager') {
                return redirect('/');
            } else {
                return redirect('/');
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

        /**
         * Handle the logout process.
         */
        public function logout(Request $request)
        {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login');
        }
    }