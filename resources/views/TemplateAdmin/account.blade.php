@extends('TemplateAdmin.main')
@section('account', 'on')
@section('konten')
    <main class="main">
        <div class="top">
            <div>
                <div class="eyebrow">Keamanan / akun</div>
                <h2>Pengaturan <em>akun</em></h2>
                <p>Perbarui email dan password untuk menjaga keamanan akses studio.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="dashboard-feedback success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="dashboard-feedback error" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @if ($user->must_change_password)
            <div class="dashboard-feedback error" role="alert">
                Password awal wajib diganti sebelum Anda dapat menggunakan fitur admin.
            </div>
        @endif

        <div class="account-grid">
            <section class="card account-panel">
                <div class="eyebrow">Akun saya</div>
                <h3>Ubah email dan password</h3>
                <p class="account-description">Masukkan password saat ini untuk mengonfirmasi perubahan akun.</p>

                <form action="{{ route('admin.account.update') }}" method="POST" class="account-form">
                    @csrf
                    @method('PUT')

                    <div class="fld">
                        <label for="account-email">Email login</label>
                        <input id="account-email" name="email" type="email" value="{{ old('email', $user->email) }}"
                            autocomplete="username" required>
                    </div>
                    <div class="fld">
                        <label for="account-current-password">Password saat ini</label>
                        <input id="account-current-password" name="current_password" type="password"
                            autocomplete="current-password" required>
                    </div>
                    <div class="fld">
                        <label for="account-password">Password baru <span>(minimal 12 karakter)</span></label>
                        <input id="account-password" name="password" type="password" autocomplete="new-password"
                            @required($user->must_change_password)>
                    </div>
                    <div class="fld">
                        <label for="account-password-confirmation">Konfirmasi password baru</label>
                        <input id="account-password-confirmation" name="password_confirmation" type="password"
                            autocomplete="new-password" @required($user->must_change_password)>
                    </div>
                    <button class="btn ink" type="submit">Simpan perubahan akun</button>
                </form>
            </section>

            @if ($user->isSuperAdmin())
                <section class="card account-panel">
                    <div class="eyebrow">Akses super admin</div>
                    <h3>Kelola akun admin</h3>
                    <p class="account-description">
                        Perubahan password akan mengharuskan admin terkait menggantinya saat login berikutnya.
                    </p>

                    @forelse ($admins as $admin)
                        <form action="{{ route('admin.accounts.update', $admin) }}" method="POST"
                            class="account-form admin-account-form">
                            @csrf
                            @method('PUT')
                            <h4>{{ $admin->name }}</h4>
                            <div class="fld">
                                <label for="admin-email-{{ $admin->id }}">Email login</label>
                                <input id="admin-email-{{ $admin->id }}" name="email" type="email"
                                    value="{{ $admin->email }}" autocomplete="username" required>
                            </div>
                            <div class="fld">
                                <label for="admin-password-{{ $admin->id }}">Password baru</label>
                                <input id="admin-password-{{ $admin->id }}" name="password" type="password"
                                    autocomplete="new-password" minlength="12" required>
                            </div>
                            <div class="fld">
                                <label for="admin-password-confirmation-{{ $admin->id }}">Konfirmasi password baru</label>
                                <input id="admin-password-confirmation-{{ $admin->id }}" name="password_confirmation"
                                    type="password" autocomplete="new-password" minlength="12" required>
                            </div>
                            <button class="btn sm" type="submit">Perbarui akun admin</button>
                        </form>
                    @empty
                        <p class="note">Belum ada akun admin untuk dikelola.</p>
                    @endforelse
                </section>
            @endif
        </div>
    </main>
@endsection
