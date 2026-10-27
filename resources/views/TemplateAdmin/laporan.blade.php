@extends('TemplateAdmin.main')
@section('laporan', 'on')
@section('konten')
    <main class="main">
        <div class="top">
            <div>
                <div class="eyebrow">Laporan</div>
                <h2 style="margin-top: 12px">
                    Reservasi &amp; <em>pembayaran</em>
                </h2>
            </div>
        </div>
        <div class="fc" id="lform">
            <div class="flt">
                <div class="fld">
                    <label>Dari</label><input id="ld" type="date" />
                </div>
                <div class="fld">
                    <label>Sampai</label><input id="lt" type="date" />
                </div>
                <div class="fld"><label>Nama / kode</label><input id="lq" /></div>
                <div class="fld">
                    <label>Status</label><select id="ls">
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
            <div class="acts" style="margin-top: 18px">
                <button class="btn ink" data-act="lap" data-scope="#lform">
                    Cari
                </button>
            </div>
        </div>
        <div class="sum" id="lsum"></div>
        
        <div class="card">
            <div class="tw rt">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Pelanggan</th>
                            <th>Paket</th>
                            <th>Jadwal</th>
                            <th>Total</th>
                            <th>Diterima</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Contoh Data Dummy Statis -->
                        <tr>
                            <td>13424</td>
                            <td>ZIZI</td>
                            <td>Weding</td>
                            <td>20 oct 2026</td>
                            <td>Rp. 20k</td>
                            <td>Rp. 20k</td>
                            <td><span class="badge">Selesai</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
@endsection
