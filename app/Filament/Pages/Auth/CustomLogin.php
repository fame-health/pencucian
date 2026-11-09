<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class CustomLogin extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';

    public function getHeading(): string|Htmlable
    {
        return 'Sistem Manajemen Pencucian Motor & Mobil';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Silakan masuk menggunakan akun Anda atau Login dengan Google';
    }
}
