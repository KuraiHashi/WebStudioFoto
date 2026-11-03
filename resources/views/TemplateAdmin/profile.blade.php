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

            <!-- INFORMASI PEMBAYARAN STUDIO -->
            <h4 style="margin-top: 30px; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">
                Informasi Pembayaran (Rekening & QRIS)
            </h4>

            <div class="r2" style="display: flex; gap: 15px; margin-top: 15px;">
                <div class="fld" style="flex: 1;">
                    <label>NAMA BANK</label>
                    <input type="text" name="nama_bank" placeholder="Cth: BCA / Mandiri"
                        value="{{ old('nama_bank', $studio->nama_bank ?? '') }}"
                        style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" />
                </div>
                <div class="fld" style="flex: 1;">
                    <label>NOMOR REKENING</label>
                    <input type="text" name="no_rekening" placeholder="Cth: 1234567890"
                        value="{{ old('no_rekening', $studio->no_rekening ?? '') }}"
                        style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" />
                </div>
            </div>

            <div class="r2" style="display: flex; gap: 15px; margin-top: 15px;">
                <div class="fld" style="flex: 1;">
                    <label>NAMA PEMILIK REKENING (a.n)</label>
                    <input type="text" name="nama_pemilik" placeholder="Cth: Liquif Picture"
                        value="{{ old('nama_pemilik', $studio->nama_pemilik ?? '') }}"
                        style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" />
                </div>
                <div class="fld" style="flex: 1;">
                    <label>UNGGAH FOTO QRIS</label>
                    <input type="file" name="foto_qris" accept="image/*"
                        style="display: block !important; visibility: visible !important; opacity: 1 !important; width: 100%; padding: 8px; border: 1px solid #ccc; background: #fff;" />
                </div>
            </div>

            <div class="acts" style="margin-top: 25px;">
                <button type="submit" class="btn ink">
                    Simpan perubahan
                </button>
            </div>
        </form>
    </main>
@endsection
