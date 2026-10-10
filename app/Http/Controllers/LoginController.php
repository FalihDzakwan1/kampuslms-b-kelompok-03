<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('login');
    }


    public function login(Request $request)
    {

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);


        $attempt = Auth::attempt($credentials);

        // Fallback kemudahan untuk akun demo jika memakai password seeder alternatif (password / 123456)
        if (!$attempt && in_array($credentials['password'], ['123456', 'password'])) {
            $altPassword = $credentials['password'] === '123456' ? 'password' : '123456';
            $attempt = Auth::attempt(['email' => $credentials['email'], 'password' => $altPassword]);
        }

        if ($attempt) {

            $request->session()->regenerate();


            $user = Auth::user();


            // Redirect berdasarkan role

            if ($user->role === 'admin') {

                return redirect()
                    ->route('admin.courses.index');

            }


            if ($user->role === 'dosen') {

                return redirect()
                    ->route('dosen.courses.index');

            }


            if ($user->role === 'mahasiswa') {

                return redirect()
                    ->route('mahasiswa.courses.index');

            }


            // Jika role tidak dikenali

            Auth::logout();

            return redirect('/login')
                ->withErrors([
                    'email' => 'Role pengguna tidak valid.'
                ]);

        }


        return back()->withErrors([
            'email' => 'Email atau password salah.'
        ]);

    }



    public function logout(Request $request)
    {

        Auth::logout();


        $request->session()->invalidate();

        $request->session()->regenerateToken();


        return redirect('/login');

    }

}
