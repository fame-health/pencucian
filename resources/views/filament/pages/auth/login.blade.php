<x-filament-panels::page.simple :heading="null" :subheading="null" class="login-page-root">
    <style>
        body:has(.login-page-root) {
            background: #f7f8fa;
        }

        body:has(.login-page-root) .fi-simple-main-ctn {
            min-height: 100svh;
            align-items: stretch;
            padding: 0;
        }

        body:has(.login-page-root) .fi-simple-main {
            display: flex;
            width: 100%;
            max-width: none;
            min-height: 100svh;
            align-items: stretch;
            margin: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            padding: 0;
        }

        .login-page-root {
            display: flex;
            width: 100%;
            min-height: 100svh;
            align-items: stretch;
            justify-content: center;
        }

        .login-page-root > .fi-simple-page-content {
            display: flex;
            width: 100%;
            max-width: none;
            min-height: 100svh;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .login-page-root > .fi-simple-page-content > .fi-simple-header {
            display: none;
        }

        .login-layout {
            display: grid;
            width: 100%;
            min-height: 100svh;
            grid-template-columns: minmax(0, 1.08fr) minmax(390px, 0.92fr);
            overflow: hidden;
            border: 0;
            border-radius: 0;
            background: #fff;
            box-shadow: none;
        }

        .login-brand-panel {
            position: relative;
            display: flex;
            min-height: 100%;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            padding: 48px;
            background:
                linear-gradient(90deg, rgb(15 35 51 / 94%) 0%, rgb(15 35 51 / 76%) 48%, rgb(15 35 51 / 36%) 100%),
                linear-gradient(0deg, rgb(15 35 51 / 32%), transparent 48%),
                url("/images/washpoint-login-hero.svg") center / cover,
                #172b3b;
            color: #fff;
        }

        .login-brand-panel::before,
        .login-brand-panel::after {
            position: absolute;
            width: 360px;
            height: 360px;
            border: 1px solid rgb(255 255 255 / 8%);
            border-radius: 50%;
            content: "";
            pointer-events: none;
        }

        .login-brand-panel::before {
            right: -170px;
            bottom: 60px;
        }

        .login-brand-panel::after {
            right: -115px;
            bottom: 115px;
        }

        .login-brand-top,
        .login-brand-copy,
        .login-brand-footer {
            position: relative;
            z-index: 1;
        }

        .login-brand-top {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .login-brand-mark {
            display: grid;
            width: 46px;
            height: 46px;
            flex: 0 0 auto;
            place-items: center;
            border: 1px solid rgb(255 255 255 / 20%);
            border-radius: 15px;
            background: rgb(255 255 255 / 10%);
            color: #ffc15c;
        }

        .login-brand-name {
            display: block;
            font-size: 15px;
            font-weight: 750;
            letter-spacing: 0.01em;
        }

        .login-brand-caption {
            display: block;
            margin-top: 3px;
            color: #b7c5cf;
            font-size: 12px;
        }

        .login-brand-copy {
            max-width: 440px;
            margin: auto 0;
            padding: 64px 0;
        }

        .login-brand-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border: 1px solid rgb(255 255 255 / 15%);
            border-radius: 999px;
            background: rgb(255 255 255 / 7%);
            color: #f3d9ae;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .login-brand-copy h2 {
            margin: 24px 0 16px;
            color: #fff;
            font-size: clamp(32px, 4vw, 48px);
            font-weight: 750;
            letter-spacing: -0.045em;
            line-height: 1.12;
        }

        .login-brand-copy p {
            max-width: 390px;
            margin: 0;
            color: #c0cbd3;
            font-size: 15px;
            line-height: 1.8;
        }

        .login-brand-footer {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #b7c5cf;
            font-size: 12px;
        }

        .login-brand-footer svg {
            color: #9dc8ae;
        }

        .login-form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 56px clamp(28px, 5vw, 68px);
        }

        .login-form-inner {
            width: 100%;
            max-width: 410px;
        }

        .login-mobile-brand {
            display: none;
        }

        .login-heading {
            margin: 0;
            color: #172b3b;
            font-size: 30px;
            font-weight: 750;
            letter-spacing: -0.04em;
            line-height: 1.2;
        }

        .login-description {
            margin: 10px 0 30px;
            color: #718096;
            font-size: 14px;
            line-height: 1.65;
        }

        .login-error {
            margin-bottom: 20px;
            padding: 13px 15px;
            border: 1px solid #fecaca;
            border-radius: 12px;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 13px;
        }

        .login-google-link {
            display: flex;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            gap: 11px;
            border: 1px solid #dce2e8;
            border-radius: 12px;
            background: #fff;
            color: #273444;
            font-size: 14px;
            font-weight: 650;
            text-decoration: none;
            transition: border-color 150ms ease, background 150ms ease, transform 150ms ease;
        }

        .login-google-link:hover {
            transform: translateY(-1px);
            border-color: #b8c2cc;
            background: #f9fafb;
        }

        .login-google-link:focus-visible {
            outline: 3px solid rgb(245 158 11 / 35%);
            outline-offset: 3px;
        }

        .login-divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 24px 0;
            color: #98a2ad;
            font-size: 11px;
            font-weight: 650;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .login-divider::before,
        .login-divider::after {
            height: 1px;
            flex: 1;
            background: #e8ebef;
            content: "";
        }

        .login-form-panel .fi-fo-field-label-content,
        .login-form-panel .fi-fo-field-label-content * {
            color: #344253 !important;
        }

        .login-form-panel .fi-fo-text-input input {
            min-height: 48px;
            border: 1px solid #dce2e8 !important;
            border-radius: 10px !important;
            background: #fff !important;
            color: #172b3b !important;
            box-shadow: none !important;
        }

        .login-form-panel .fi-fo-text-input input::placeholder {
            color: #98a2ad !important;
        }

        .login-form-panel .fi-fo-text-input:focus-within input {
            border-color: #e58a00 !important;
            box-shadow: 0 0 0 3px rgb(229 138 0 / 14%) !important;
        }

        .login-form-panel .fi-sc-actions {
            margin-top: 8px;
        }

        .login-form-panel .fi-sc-actions .fi-btn {
            min-height: 48px;
            border-radius: 11px;
            font-weight: 700;
        }

        .login-form-note {
            margin: 24px 0 0;
            color: #98a2ad;
            font-size: 12px;
            line-height: 1.6;
            text-align: center;
        }

        @media (max-width: 760px) {
            body:has(.login-page-root) {
                background: #fff;
            }

            body:has(.login-page-root) .fi-simple-main-ctn {
                min-height: 100svh;
            }

            body:has(.login-page-root) .fi-simple-main {
                margin: 0;
            }

            .login-page-root,
            .login-page-root > .fi-simple-page-content {
                min-height: 100svh;
            }

            .login-page-root > .fi-simple-page-content {
                max-width: none;
                align-items: stretch;
                padding: 0;
            }

            .login-layout {
                min-height: 100svh;
                grid-template-columns: 1fr;
                overflow: visible;
                border: 0;
                border-radius: 0;
                background: #fff;
                box-shadow: none;
            }

            .login-brand-panel {
                display: block;
                min-height: 190px;
                height: 190px;
                padding: 0;
            }

            .login-brand-panel > * {
                display: none;
            }

            .login-brand-panel::before,
            .login-brand-panel::after {
                display: none;
            }

            .login-form-panel {
                align-items: flex-start;
                padding: max(28px, env(safe-area-inset-top)) 24px max(28px, env(safe-area-inset-bottom));
            }

            .login-mobile-brand {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 28px;
            }

            .login-mobile-brand .login-brand-mark {
                border-color: #f3e4c8;
                background: #fff8eb;
                color: #b66b00;
            }

            .login-mobile-brand .login-brand-name {
                color: #172b3b;
            }

            .login-mobile-brand .login-brand-caption {
                color: #718096;
            }

            .login-heading {
                font-size: 27px;
            }

            .login-description {
                margin-bottom: 24px;
            }

            .login-divider {
                margin: 22px 0;
                letter-spacing: 0.06em;
            }

            .login-form-note {
                margin-top: 28px;
            }
        }

        @media (max-width: 380px) {
            .login-form-panel {
                padding-right: 22px;
                padding-left: 22px;
            }
        }

        @media (max-height: 700px) and (max-width: 760px) {
            .login-form-panel {
                padding-top: max(24px, env(safe-area-inset-top));
            }

            .login-mobile-brand {
                margin-bottom: 30px;
            }
        }
    </style>

    <div class="login-layout">
        <aside class="login-brand-panel" aria-label="Tentang layanan pencucian">
            <div class="login-brand-top">
                <span class="login-brand-mark" aria-hidden="true">
                    <svg width="25" height="25" viewBox="0 0 24 24" fill="none">
                        <path d="M12 3.25C9.7 6.5 6.5 10 6.5 14a5.5 5.5 0 0 0 11 0c0-4-3.2-7.5-5.5-10.75Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.5 14.5a2.5 2.5 0 0 0 2.5 2.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>
                    <span class="login-brand-name">WashPoint</span>
                    <span class="login-brand-caption">Sistem Manajemen Pencucian</span>
                </span>
            </div>

            <div class="login-brand-copy">
                <span class="login-brand-eyebrow">Perawatan kendaraan lebih mudah</span>
                <h2>Rapi mengelola.<br>Istimewa melayani.</h2>
                <p>Kelola operasional pencucian motor dan mobil dalam satu sistem yang praktis, cepat, dan terpercaya.</p>
            </div>

            <div class="login-brand-footer">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 3 19 6v5c0 4.5-2.9 8-7 10-4.1-2-7-5.5-7-10V6l7-3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                    <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Akses aman untuk tim Anda
            </div>
        </aside>

        <section class="login-form-panel">
            <div class="login-form-inner">
                <div class="login-mobile-brand">
                    <span class="login-brand-mark" aria-hidden="true">
                        <svg width="25" height="25" viewBox="0 0 24 24" fill="none">
                            <path d="M12 3.25C9.7 6.5 6.5 10 6.5 14a5.5 5.5 0 0 0 11 0c0-4-3.2-7.5-5.5-10.75Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9.5 14.5a2.5 2.5 0 0 0 2.5 2.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span>
                        <span class="login-brand-name">WashPoint</span>
                        <span class="login-brand-caption">Sistem Manajemen Pencucian</span>
                    </span>
                </div>

                <h1 class="login-heading">Selamat datang</h1>
                <p class="login-description">Masuk untuk melanjutkan ke dashboard pengelolaan Anda.</p>

                @if (session('error'))
                    <div class="login-error" role="alert">
                        {{ session('error') }}
                    </div>
                @endif
                @if (session('status'))
                    <div class="login-error" role="status" style="border-color: #b7e4c7; background: #f0fff4; color: #17663a;">
                        {{ session('status') }}
                    </div>
                @endif

                <a href="{{ route('auth.google') }}" class="login-google-link">
                    <svg width="19" height="19" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                    <span>Masuk / Daftar dengan Google</span>
                </a>

                <div class="login-divider">atau masuk dengan email</div>

                {{ $this->content }}

                <p class="login-form-note">Dengan masuk, Anda dapat mengelola layanan dan operasional pencucian dengan lebih mudah.</p>
            </div>
        </section>
    </div>
</x-filament-panels::page.simple>
