@extends('TemplateAdmin.main')
@section('galery', 'on')
@section('konten')
    <main class="main">
        <div class="top">
            <div>
                <div class="eyebrow">Galeri Studio</div>
                <h2 style="margin-top: 12px">Kelola <em>galeri</em></h2>
            </div>
        </div>
        <div class="fc" id="gup" data-clear style="margin-bottom: 22px">
            <h3>Unggah foto</h3>
            <div class="fld">
                <label class="drop"><span class="dn">Pilih atau seret foto portofolio</span><br /><span
                        class="note">JPG / PNG · maks. 5 MB per file</span><input type="file"
                        accept=".jpg,.jpeg,.png,.pdf,image/*" multiple required /></label>
            </div>
            <div class="acts">
                <button class="btn ink" data-act="upgal" data-scope="#gup">
                    Unggah
                </button>
            </div>
        </div>
        <div class="card">
            <h3>Foto galeri</h3>
            <div class="mg big" id="gg">
                <div class="gph">
                    <input type="checkbox" class="chk" aria-label="Pilih foto" />
                    <div class="ph"></div>
                </div>
                <div class="gph">
                    <input type="checkbox" class="chk" aria-label="Pilih foto" />
                    <div class="ph b"></div>
                </div>
                <div class="gph">
                    <input type="checkbox" class="chk" aria-label="Pilih foto" />
                    <div class="ph c"></div>
                </div>
                <div class="gph">
                    <input type="checkbox" class="chk" aria-label="Pilih foto" />
                    <div class="ph d"></div>
                </div>
                <div class="gph">
                    <input type="checkbox" class="chk" aria-label="Pilih foto" />
                    <div class="ph e"></div>
                </div>
                <div class="gph">
                    <input type="checkbox" class="chk" aria-label="Pilih foto" />
                    <div class="ph b"></div>
                </div>
                <div class="gph">
                    <input type="checkbox" class="chk" aria-label="Pilih foto" />
                    <div class="ph c"></div>
                </div>
                <div class="gph">
                    <input type="checkbox" class="chk" aria-label="Pilih foto" />
                    <div class="ph d"></div>
                </div>
            </div>
            <div class="acts">
                <button class="btn sm no" data-act="delgal">
                    Hapus terpilih
                </button>
            </div>
        </div>
    </main>
@endsection
