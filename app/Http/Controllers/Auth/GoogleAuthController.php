<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect user to Google OAuth page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback from Google OAuth.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'Gagal terhubung dengan Google. Silakan coba lagi.');
        }

        $email = $googleUser->getEmail();
        $googleId = $googleUser->getId();
        if (!$email || !$googleId) {
            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'Google tidak mengembalikan identitas akun yang valid.');
        }

        $user = User::query()
            ->where('email', $email)
            ->orWhere('google_id', $googleId)
            ->first();

        if (!$user) {
            session(['washer_google_registration' => [
                'google_id' => $googleId,
                'email' => $email,
                'name' => $googleUser->getName(),
                'avatar' => $googleUser->getAvatar(),
            ]]);

            return redirect()->route('washer.register');
        }

        if (!$user->is_active) {
            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'Akun Anda belum aktif atau sedang dinonaktifkan. Silakan hubungi Pemilik.');
        }

        $user->update([
            'google_id' => $googleId,
            'avatar' => $googleUser->getAvatar() ?? $user->avatar,
        ]);

        Auth::login($user, true);

        // Redirect to Filament Panel Dashboard
        return redirect()->intended('/admin');
    }

    public function showRegistration(): View|RedirectResponse
    {
        $profile = session('washer_google_registration');

        if (!is_array($profile) || empty($profile['google_id']) || empty($profile['email'])) {
            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'Silakan mulai pendaftaran dengan akun Google Anda.');
        }

        return view('auth.washer-register', compact('profile'));
    }

    public function registerWasher(Request $request): RedirectResponse
    {
        $profile = session('washer_google_registration');

        if (!is_array($profile) || empty($profile['google_id']) || empty($profile['email'])) {
            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'Sesi pendaftaran berakhir. Silakan mulai kembali dengan akun Google.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'ktp_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'ktp_photo.required' => 'Foto KTP wajib diunggah.',
            'ktp_photo.image' => 'File KTP harus berupa gambar.',
            'ktp_photo.mimes' => 'Format foto KTP harus JPG, PNG, atau WEBP.',
            'ktp_photo.max' => 'Ukuran foto KTP maksimal 5 MB.',
        ]);

        if (User::query()
            ->where('email', $profile['email'])
            ->orWhere('google_id', $profile['google_id'])
            ->exists()) {
            session()->forget('washer_google_registration');

            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'Akun Google ini sudah terdaftar. Silakan masuk atau hubungi Pemilik.');
        }

        DB::transaction(function () use ($validated, $profile, $request): void {
            $ktpPath = $request->file('ktp_photo')->store('washer-ktp', 'private');

            if (! $ktpPath) {
                throw ValidationException::withMessages([
                    'ktp_photo' => 'Foto KTP gagal disimpan. Silakan coba unggah kembali.',
                ]);
            }

            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $profile['email'],
                'google_id' => $profile['google_id'],
                'avatar' => $profile['avatar'],
                'phone' => $validated['phone'],
                'ktp_photo' => $ktpPath,
                'is_active' => false,
            ]);

            $user->assignRole(Role::firstOrCreate([
                'name' => 'washer',
                'guard_name' => 'web',
            ]));
        });

        session()->forget('washer_google_registration');

        return redirect()->route('filament.admin.auth.login')
            ->with('status', 'Pendaftaran berhasil dikirim. Akun Anda menunggu pemeriksaan dan aktivasi oleh Pemilik.');
    }
}
