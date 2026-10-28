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
        <div class="fc" id="pform" style="max-width: 720px">
            <div class="fld">
                <label>Nama studio</label><input value="Liquif Picture" required />
            </div>
            <div class="fld">
                <label>Alamat</label><input value="Jl. Contoh Alamat No. 00" required />
            </div>
            <div class="r2">
                <div class="fld">
                    <label>Kontak</label><input value="081200000000" data-vt="phone" required />
                </div>
                <div class="fld">
                    <label>Jam operasional</label><input value="09.00–17.00" required />
                </div>
            </div>
            <div class="fld">
                <label>Media sosial</label><input value="@liquifpicture" />
            </div>
            <div class="fld">
                <label>Tautan Google Maps</label><input placeholder="https://maps.google.com/…" />
            </div>
            <div class="fld">
                <label>Deskripsi</label>
                <textarea rows="4" required>
    Studio fotografi untuk momen yang ingin Anda simpan selamanya.</textarea>
            </div>
            <div class="acts">
                <button class="btn ink" data-act="saveprof" data-scope="#pform">
                    Simpan perubahan
                </button>
            </div>
        </div>
    </main>
@endsection
