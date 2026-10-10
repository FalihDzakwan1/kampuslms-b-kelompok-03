<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class PasswordResetController extends Controller
{
    /**
     * Tampilkan formulir permintaan tautan reset kata sandi.
     */
    public function showForgotPasswordForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.forgot-password');
    }

    /**
     * Kirim tautan reset kata sandi ke email pengguna.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.exists' => 'Alamat email tidak terdaftar di sistem KampusLMS.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Kami tidak dapat menemukan pengguna dengan alamat email tersebut.',
            ]);
        }

        // Buat token reset resmi dari broker password Laravel
        $token = Password::broker()->createToken($user);

        // Kirim notifikasi email via Laravel Notification
        try {
            $user->sendPasswordResetNotification($token);
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email reset password: ' . $e->getMessage());
        }

        return back()->with([
            'status' => 'Tautan untuk mengatur ulang kata sandi telah dikirim ke ' . $user->email . '. Silakan periksa kotak masuk (Inbox) atau folder Spam email Anda.',
            'email_sent' => $user->email,
        ]);
    }

    /**
     * Tampilkan formulir pengaturan ulang kata sandi.
     */
    public function showResetPasswordForm(Request $request, $token = null)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', old('email')),
        ]);
    }

    /**
     * Proses pengaturan ulang kata sandi pengguna.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ], [
            'token.required' => 'Token pengaturan ulang kata sandi tidak valid.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        // Jalankan reset password melalui Laravel Password Broker
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with(
                'success',
                'Kata sandi Anda berhasil diperbarui! Silakan masuk menggunakan kata sandi baru Anda.'
            );
        }

        $errorMessage = match ($status) {
            Password::INVALID_TOKEN => 'Token reset kata sandi tidak valid atau telah kedaluwarsa.',
            Password::INVALID_USER => 'Pengguna dengan email tersebut tidak ditemukan.',
            default => 'Gagal mengatur ulang kata sandi. Silakan periksa kembali tautan Anda.',
        };

        return back()->withErrors(['email' => $errorMessage]);
    }
}
