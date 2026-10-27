@extends('TemplateAdmin.main')
@section('fotografy', 'on')
@section('konten')
    <main class="main">
        <div class="top">
            <div>
                <div class="eyebrow">Paket Fotografi</div>
                <h2 style="margin-top: 12px">Kelola <em>paket</em></h2>
            </div>
            <button class="btn gold" data-act="newpk">
                + Tambah paket
            </button>
        </div>

        <div class="card bare">
            <div class="tw rt" style="overflow-x: auto;">
                <!-- Tabel Manual -->
                <table class="table" id="ptab-manual" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="padding: 12px; text-align: left;">Nama Paket</th>
                            <th style="padding: 12px; text-align: left;">Harga</th>
                            <th style="padding: 12px; text-align: left;">Durasi</th>
                            <th style="padding: 12px; text-align: left;">Deskripsi</th>
                            <th style="padding: 12px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Contoh data statis / loop dari Controller --}}
                        {{-- @foreach ($pakets as $index => $item) --}}
                        <tr>
                            <td style="padding: 12px;">Paket Wedding Standard</td>
                            <td style="padding: 12px;">Rp 3.500.000</td>
                            <td style="padding: 12px;">6 Jam</td>
                            <td style="padding: 12px;">Liputan akad dan resepsi</td>
                            <td style="padding: 12px; text-align: center;">
                                <button class="btn sm" style="margin-right: 4px;">Edit</button>
                                <button class="btn sm ink" style="background-color: #dc3545; color: white;">Hapus</button>
                            </td>
                        </tr>
                        {{-- @endforeach --}}
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="ov" id="m-pk">
        <div class="md w" data-clear>
            <button class="x" data-close>×</button>
            <div class="eyebrow">Paket fotografi</div>
            <h3 id="pkt">Tambah paket</h3>
            <div class="r2">
                <div class="fld">
                    <label>Nama paket</label><input id="pn" required />
                </div>
                <div class="fld">
                    <label>Harga (Rp)</label><input id="ph" inputmode="numeric" data-vt="num" required />
                </div>
            </div>
            <div class="r2">
                <div class="fld">
                    <label>Durasi</label><input id="pd" placeholder="mis. 2 jam" required />
                </div>
                <div class="fld">
                    <label>Bonus (opsional)</label><input id="pb" />
                </div>
            </div>
            <div class="fld">
                <label>Deskripsi</label><input id="px" placeholder="Ringkasan singkat paket" />
            </div>
            <div class="fld">
                <label>Fasilitas (pisahkan dengan koma)</label><input id="pf"
                    placeholder="20 foto sunting, 1 lokasi, galeri privat" required />
            </div>
            <div class="acts">
                <button class="btn sm" data-close>Batal</button><button class="btn sm ink" data-act="savepk">
                    Simpan paket
                </button>
            </div>
        </div>
    </div>
@endsection
