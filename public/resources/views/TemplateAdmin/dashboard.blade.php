@extends('TemplateAdmin.main')
@section('dashboard', 'on')
@section('konten')
    <main class="main">
        <div class="top">
            <div>
                <div class="eyebrow">Dashboard</div>
                <h2 style="margin-top:12px">Selamat datang, <em>Admin</em></h2>
            </div>
            <a class="btn sm" href="laporan.html">Lihat laporan</a>
        </div>
        <div class="kp" id="kp"></div>
        <div class="cols">
            <div class="card">
                <h3>Kelola reservasi &amp; pembayaran</h3>
                <div class="flt" style="margin-bottom:12px">
                    <div class="fld" style="margin:0"><label>Cari nama / kode</label>
                        <input id="fq" placeholder="Ketik untuk mencari…">
                    </div>
                    <div class="fld" style="margin:0">
                        <label>Status</label>
                        <select id="fs">
                            <option value="">Semua</option>
                            <option value="bayar">Menunggu pembayaran DP</option>
                            <option value="verif">Konfirmasi pembayaran</option>
                            <option value="lunasi">Menunggu pelunasan</option>
                            <option value="verif2">Verifikasi pelunasan</option>
                            <option value="selesai">Selesai</option>
                            <option value="batal">Dibatalkan</option>
                        </select>
                    </div>
                </div>
                <div class="tw rt">
                    <table id="rtab"></table>
                </div>
            </div>
            <div class="card">
                <h3>Jadwal</h3>
                <div class="cl"><button id="cp" aria-label="Bulan sebelumnya">‹</button><b
                        id="cm"></b><button id="cn" aria-label="Bulan berikutnya">›</button></div>
                <div class="cal" id="cal"></div>
                <p class="note" style="margin-top:14px">Tanggal gelap sudah terisi — dipakai untuk mencegah
                    bentrok jadwal.</p>
            </div>
        </div>
    </main>
@endsection
