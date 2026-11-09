<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Pencuci - WashPoint</title>
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            padding: 32px 18px;
            display: grid;
            place-items: center;
            background: #f4f6f8;
            color: #172b3b;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .card {
            width: min(100%, 560px);
            padding: clamp(24px, 5vw, 42px);
            border: 1px solid #e7ebef;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 18px 55px rgb(23 43 59 / 9%);
        }
        .brand { margin: 0 0 8px; color: #a45e00; font-size: 13px; font-weight: 750; letter-spacing: .08em; text-transform: uppercase; }
        h1 { margin: 0; font-size: clamp(25px, 5vw, 32px); letter-spacing: -.035em; }
        .intro { margin: 10px 0 24px; color: #627181; line-height: 1.65; }
        .notice { margin: 0 0 23px; padding: 13px 15px; border: 1px solid #f4dda9; border-radius: 12px; background: #fff9e9; color: #76500c; font-size: 14px; line-height: 1.55; }
        .field { margin-top: 17px; }
        label { display: block; margin-bottom: 7px; font-size: 14px; font-weight: 650; }
        input {
            width: 100%;
            min-height: 46px;
            padding: 11px 13px;
            border: 1px solid #ccd5de;
            border-radius: 10px;
            background: #fff;
            color: #172b3b;
            font: inherit;
        }
        input:focus { border-color: #d58a19; outline: 3px solid rgb(213 138 25 / 16%); }
        input[readonly] { background: #f4f6f8; color: #536273; }
        .hint { margin: 7px 0 0; color: #6b7785; font-size: 12px; line-height: 1.5; }
        .error { margin: 6px 0 0; color: #b42318; font-size: 13px; }
        button {
            width: 100%;
            min-height: 48px;
            margin-top: 23px;
            border: 0;
            border-radius: 11px;
            background: #d58a19;
            color: #fff;
            cursor: pointer;
            font: inherit;
            font-weight: 700;
        }
        button:hover { background: #b66b00; }
        .back { display: block; margin-top: 17px; color: #566879; font-size: 14px; text-align: center; text-decoration: none; }
        .back:hover { color: #172b3b; }
    </style>
</head>
<body>
    <main class="card">
        <p class="brand">WashPoint · Pendaftaran Pencuci</p>
        <h1>Lengkapi data diri</h1>
        <p class="intro">Isi data berikut untuk mengajukan akun pencuci. Email sudah diambil dari akun Google Anda.</p>
        <div class="notice" role="status">
            Setelah formulir disimpan, akun Anda berstatus <strong>menunggu aktivasi</strong>.
            Pemilik akan memeriksa data, menetapkan komisi, lalu mengaktifkan akun.
        </div>

        <form method="POST" action="{{ route('washer.register.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="field">
                <label for="name">Nama lengkap</label>
                <input id="name" name="name" type="text" value="{{ old('name', $profile['name'] ?? '') }}" maxlength="255" autocomplete="name" required>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="email">Email Google</label>
                <input id="email" type="email" value="{{ $profile['email'] }}" readonly>
                <p class="hint">Email diambil dari Google dan tidak dapat diubah di formulir ini.</p>
            </div>

            <div class="field">
                <label for="phone">Nomor telepon</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="30" autocomplete="tel" required>
                @error('phone') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="ktp_photo">Foto KTP</label>
                <input id="ktp_photo" name="ktp_photo" type="file" accept="image/jpeg,image/png,image/webp" required>
                <p class="hint">Format JPG, PNG, atau WEBP; ukuran maksimal 5 MB. Foto KTP hanya dapat diakses melalui panel admin.</p>
                @error('ktp_photo') <p class="error">{{ $message }}</p> @enderror
            </div>

            <button type="submit">Kirim pendaftaran</button>
        </form>

        <a class="back" href="{{ route('filament.admin.auth.login') }}">Kembali ke halaman masuk</a>
    </main>
</body>
</html>
