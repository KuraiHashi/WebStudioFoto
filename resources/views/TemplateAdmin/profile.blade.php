@extends('TemplateAdmin.main')
@section('profile', 'on')
@section('konten')
    <main class="main">
        <div class="top">
            <div>
                <div class="eyebrow">Profil Studio</div>
                <h2 style="margin-top: 12px">Ubah <em>profil</em></h2>
            </div>
        </div>

        {{-- Notifikasi Sukses --}}
        @if (session('success'))
            <div class="alert alert-success" style="margin-bottom: 20px; color: green; font-weight: bold;">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="fc"
            id="pform" style="max-width: 720px">
            @csrf
            <div class="fld">
                <label>Nama studio</label>
                <input type="text" name="name" value="{{ old('name', $studio->nama_studio ?? 'Liquif Picture') }}"
                    required />
            </div>
            <div class="fld">
                <label>Alamat</label>
                <input type="text" name="address" value="{{ old('address', $studio->alamat ?? '') }}" required />
            </div>
            <div class="r2">
                <div class="fld">
                    <label>Kontak</label>
                    <input type="text" name="phone" value="{{ old('phone', $studio->kontak ?? '') }}" data-vt="phone"
                        required />
                </div>
                <div class="fld">
                    <label>Jam operasional</label>
                    <input type="text" name="operational_hours"
                        value="{{ old('operational_hours', $studio->jam_operasional ?? '') }}" required />
                </div>
            </div>
            <div class="fld">
                <label>Media sosial</label>
                <input type="text" name="social_media" value="{{ old('social_media', $studio->media_sosial ?? '') }}" />
            </div>
            <div class="fld">
                <label>Tautan Google Maps (Embed Iframe)</label>
                <textarea name="maps_link" rows="3"
                    placeholder="Tempel kode iframe Google Maps di sini, contoh: <iframe src=... ></iframe>">{{ old('maps_link', $studio->google_maps ?? '') }}</textarea>
            </div>
            <div class="fld">
                <label>Deskripsi</label>
                <textarea name="description" rows="4" required>{{ old('description', $studio->deskripsi ?? 'Studio fotografi untuk momen yang ingin Anda simpan selamanya.') }}</textarea>
            </div>

            <div class="acts" style="margin-top: 25px;">
                <button type="submit" class="btn ink">
                    Simpan perubahan
                </button>
            </div>
        </form>
    </main>
@endsection
