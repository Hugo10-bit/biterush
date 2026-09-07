<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Show the login/register authentication page.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login', ['mode' => 'login']);
    }

    /**
     * Show register page specifically.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login', ['mode' => 'register']);
    }

    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        // Check login by username OR by email
        $loginType = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$loginType => $credentials['username'], 'password' => $credentials['password']], $remember)) {
            $request->session()->regenerate();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login berhasil! Mengalihkan ke dashboard...',
                    'redirect' => route('dashboard'),
                ]);
            }

            return redirect()->intended(route('dashboard'));
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password yang dimasukkan salah.',
            ], 422);
        }

        throw ValidationException::withMessages([
            'username' => 'Username atau password yang dimasukkan salah.',
        ]);
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:30', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:60', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone_number' => ['nullable', 'string', 'max:15'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan, silakan pilih yang lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $user = User::create([
            'name' => $validated['username'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil! Mengalihkan ke dashboard...',
                'redirect' => route('dashboard'),
            ]);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show dashboard after login.
     */
    public function dashboard()
    {
        if (Auth::user()->isAdmin() || Auth::user()->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('menu');
    }

    /**
     * Redirect ke Google OAuth.
     */
    public function googleRedirect()
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (empty($clientId) || empty($clientSecret) || $clientSecret === 'your-google-client-secret') {
            return redirect()->route('login')->withErrors([
                'username' => 'Google Client Secret belum dikonfigurasi di file .env (GOOGLE_CLIENT_ID & GOOGLE_CLIENT_SECRET).'
            ]);
        }

        try {
            return Socialite::driver('google')
                ->stateless()
                ->redirectUrl(url('/auth/google/callback'))
                ->redirect();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth Redirect Error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'username' => 'Gagal mengarahkan ke Google OAuth: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Handle callback dari Google OAuth.
     */
    public function googleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->redirectUrl(url('/auth/google/callback'))
                ->user();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth Callback Error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'username' => 'Login Google dibatalkan atau gagal: ' . $e->getMessage()
            ]);
        }

        // Cari user berdasarkan google_id atau email
        $user = User::where('google_id', $googleUser->getId())
                    ->orWhere('email', $googleUser->getEmail())
                    ->first();

        if ($user) {
            // Update google_id jika belum ada
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
            if ($googleUser->getAvatar() && !$user->avatar) {
                $user->update(['avatar' => $googleUser->getAvatar()]);
            }
        } else {
            // Buat user baru dari akun Google
            $baseUsername = Str::slug($googleUser->getName() ?: 'user', '_');
            $username     = $baseUsername;
            $counter      = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . '_' . $counter++;
            }

            $user = User::create([
                'name'      => $googleUser->getName() ?: $username,
                'username'  => $username,
                'email'     => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar'    => $googleUser->getAvatar(),
                'role'      => 'customer',
                'password'  => Hash::make(Str::random(24)),
            ]);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->route('menu')->with('success', 'Selamat datang, ' . ($user->name ?: $user->username) . '! Berhasil masuk dengan akun Google.');
    }

    /**
     * Instant / Demo Google Sign-In (untuk testing tanpa kendala redirect URI).
     */
    public function googleInstantLogin(Request $request)
    {
        $email = $request->input('email', 'budisantoso@gmail.com');
        $name = $request->input('name');

        if (!$name) {
            $parts = explode('@', $email);
            $name = ucwords(str_replace(['.', '_', '-'], ' ', $parts[0]));
        }

        // Cari atau buat user
        $user = User::where('email', $email)->first();

        if ($user) {
            if (!$user->google_id) {
                $user->update(['google_id' => 'google_' . substr(md5($email), 0, 16)]);
            }
        } else {
            $baseUsername = Str::slug(explode('@', $email)[0], '_');
            $username = $baseUsername;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . '_' . $counter++;
            }

            $user = User::create([
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'google_id' => 'google_' . substr(md5($email), 0, 16),
                'avatar' => 'https://lh3.googleusercontent.com/a/default-user=s96-c',
                'role' => 'customer',
                'password' => Hash::make(Str::random(24)),
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('menu')->with('success', 'Berhasil masuk dengan akun Google: ' . $email);
    }
}

