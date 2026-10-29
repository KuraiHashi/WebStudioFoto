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
        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom: 20px; color: green; font-weight: bold;">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.profile.update') }}" method="POST" class="fc" id="pform" style="max-width: 720px">
            @csrf
            <div class="fld">
                <label>Nama studio</label>
                {{-- Tambahkan name="name" --}}
                <input type="text" name="name" value="{{ old('name', $profile->name ?? 'Liquif Picture') }}" required />
            </div>
            <div class="fld">
                <label>Alamat</label>
                {{-- Tambahkan name="address" --}}
                <input type="text" name="address" value="{{ old('address', $profile->address ?? '') }}" required />
            </div>
            <div class="r2">
                <div class="fld">
                    <label>Kontak</label>
                    {{-- Tambahkan name="phone" --}}
                    <input type="text" name="phone" value="{{ old('phone', $profile->phone ?? '') }}" data-vt="phone" required />
                </div>
                <div class="fld">
                    <label>Jam operasional</label>
                    {{-- Tambahkan name="operational_hours" --}}
                    <input type="text" name="operational_hours" value="{{ old('operational_hours', $profile->operational_hours ?? '') }}" required />
                </div>
            </div>
            <div class="fld">
                <label>Media sosial</label>
                {{-- Tambahkan name="social_media" --}}
                <input type="text" name="social_media" value="{{ old('social_media', $profile->social_media ?? '') }}" />
            </div>
            <div class="fld">
                <label>Tautan Google Maps (Embed Iframe)</label>
                {{-- Ubah dari input text ke textarea agar muat menampung kode iframe --}}
                <textarea name="maps_link" rows="3" placeholder="Tempel kode iframe Google Maps di sini, contoh: <iframe src=... ></iframe>">{{ old('maps_link', $profile->maps_link ?? '') }}</textarea>
            </div>
            <div class="fld">
                <label>Deskripsi</label>
                {{-- Tambahkan name="description" --}}
                <textarea name="description" rows="4" required>{{ old('description', $profile->description ?? 'Studio fotografi untuk momen yang ingin Anda simpan selamanya.') }}</textarea>
            </div>
            <div class="acts">
                {{-- Ubah button agar berfungsi sebagai submit form --}}
                <button type="submit" class="btn ink">
                    Simpan perubahan
                </button>
            </div>
        </form>
    </main>
@endsection