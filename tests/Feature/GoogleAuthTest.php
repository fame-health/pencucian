<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_url_returns_redirect(): void
    {
        $response = $this->get(route('auth.google'));
        $response->assertStatus(302);
    }

    public function test_google_callback_authenticates_registered_active_user(): void
    {
        $user = User::factory()->create([
            'email' => 'budi@pencucian.com',
            'is_active' => true,
        ]);

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('google-12345');
        $abstractUser->shouldReceive('getEmail')->andReturn('budi@pencucian.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
        $user->refresh();
        $this->assertEquals('google-12345', $user->google_id);
        $this->assertEquals('https://lh3.googleusercontent.com/avatar.jpg', $user->avatar);
    }

    public function test_google_callback_redirects_unregistered_email_to_registration_form(): void
    {
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('google-new-user');
        $abstractUser->shouldReceive('getEmail')->andReturn('unknown@example.com');
        $abstractUser->shouldReceive('getName')->andReturn('Pencuci Baru');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/new-user.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('washer.register'));
        $response->assertSessionHas('washer_google_registration.email', 'unknown@example.com');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'unknown@example.com']);
    }

    public function test_new_washer_can_submit_registration_and_waits_for_owner_activation(): void
    {
        Storage::fake('private');

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('google-new-user');
        $abstractUser->shouldReceive('getEmail')->andReturn('unknown@example.com');
        $abstractUser->shouldReceive('getName')->andReturn('Pencuci Baru');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/new-user.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('washer.register'));

        $this->get(route('washer.register'))
            ->assertOk()
            ->assertSee('Lengkapi data diri')
            ->assertSee('unknown@example.com');

        $this->post(route('washer.register.store'), [
            'name' => 'Pencuci Baru',
            'phone' => '08123456789',
            'ktp_photo' => UploadedFile::fake()->image('ktp.jpg'),
        ])
            ->assertRedirect(route('filament.admin.auth.login'))
            ->assertSessionHas('status');

        $user = User::where('email', 'unknown@example.com')->firstOrFail();
        $this->assertFalse($user->is_active);
        $this->assertTrue($user->hasRole('washer'));
        $this->assertGuest();
        $this->assertDatabaseMissing('washer_commissions', ['user_id' => $user->id]);
        Storage::disk('private')->assertExists($user->ktp_photo);
    }

    public function test_google_callback_rejects_inactive_user(): void
    {
        User::factory()->create([
            'email' => 'inactive@pencucian.com',
            'is_active' => false,
        ]);

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('google-inactive-user');
        $abstractUser->shouldReceive('getEmail')->andReturn('inactive@pencucian.com');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('filament.admin.auth.login'));
        $response->assertSessionHas('error', 'Akun Anda belum aktif atau sedang dinonaktifkan. Silakan hubungi Pemilik.');
        $this->assertGuest();
    }
}
