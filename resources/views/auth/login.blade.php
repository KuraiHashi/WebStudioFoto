<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Admin — Liquif Picture</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Jost:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('Assets/css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <main class="login-page">
        <section class="login-story" aria-label="Liquif Picture">
            <img class="login-photo" src="{{ asset('Assets/images/hero-estetik.jpg') }}"
                alt="Studio fotografi Liquif Picture">
            <div class="login-story-content">
                <a class="login-brand" href="{{ url('/') }}" aria-label="Kembali ke halaman utama Liquif Picture">
                    @if ($studio?->foto_studio)
                        <img src="{{ asset('storage/' . $studio->foto_studio) }}" alt="" class="admin-studio-logo">
                    @else
                        <span class="admin-logo-placeholder" aria-label="Logo belum tersedia">Logo belum tersedia</span>
                    @endif
                    <span><b>liquif Picture</b><small>Photo &amp; Cinema</small></span>
                </a>
                <div>
                    <span class="login-kicker">RUANG STUDIO</span>
                    <h1>Selamat <em>datang</em> kembali.</h1>
                    <p>Kelola cerita, sesi, dan momen yang dipercayakan kepada Liquif Picture.</p>
                </div>
                <a class="login-home-link" href="{{ url('/') }}">← Kembali ke website</a>
            </div>
        </section>

        <section class="login-form-wrap">
            <form class="login-card" method="POST" action="{{ route('login') }}">
                @csrf
                <div class="eyebrow">Akses terbatas</div>
                <h2>Login <em>Admin</em></h2>
                <p class="login-intro">Masuk dengan akun studio untuk melanjutkan.</p>

                <div class="fld">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        autocomplete="username" placeholder="nama@email.com">
                </div>
                <div class="fld">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                        placeholder="Masukkan password">
                </div>
                <label class="login-remember" for="remember">
                    <input id="remember" type="checkbox" name="remember">
                    <span>Ingat saya di perangkat ini</span>
                </label>
                <button class="btn gold login-submit" type="submit">Masuk ke dashboard</button>
                <p class="login-security-note">Akun admin hanya dapat dibuat dan dikelola oleh super admin.</p>
            </form>
        </section>
    </main>
    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login gagal',
                text: @json($errors->first()),
                confirmButtonText: 'OK'
            });
        </script>
    @endif
</body>

</html>
