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
                            <option value="KONSULTASI">Konsultasi</option>
                            <option value="DP">DP (Menunggu Pelunasan)</option>
                            <option value="LUNAS">Lunas / Selesai</option>
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


    @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fq = document.getElementById('fq');
    const fs = document.getElementById('fs');
    const rtab = document.getElementById('rtab');

    // Fungsi untuk memuat data tabel secara dinamis via API
    function loadReservations() {
        let query = fq.value;
        let status = fs.value;

        let url = `{{ route('admin.api.reservations') }}?q=${encodeURIComponent(query)}&status=${encodeURIComponent(status)}`;

        fetch(url)
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    renderTable(res.data);
                }
            })
            .catch(err => console.error('Gagal memuat data reservasi:', err));
    }

    // Render baris tabel HTML
    function renderTable(data) {
        if (data.length === 0) {
            rtab.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 20px;">Tidak ada data reservasi ditemukan.</td></tr>`;
            return;
        }

        let html = `
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Pemesan</th>
                    <th>Paket</th>
                    <th>Tanggal & Waktu</th>
                    <th>WhatsApp</th>
                    <th>Status Pembayaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
        `;

        data.forEach(item => {
    let paymentStatus = item.payment ? item.payment.status : 'KONSULTASI';
    let packageName = item.package ? item.package.name : '-'; // Diperbaiki dari photography_package_id
    html += `
        <tr>
            <td><code>${item.token}</code></td>
            <td><strong>${item.name}</strong></td>
            <td>${packageName}</td>
            <td>${item.date} ${item.time}</td>
            <td><a href="https://wa.me/${item.whatsapp}" target="_blank">${item.whatsapp}</a></td>
            <td><span class="badge">${paymentStatus}</span></td>
            <td>
                <a href="#" class="btn sm">Detail</a>
            </td>
        </tr>
    `;
});

        html += `</tbody>`;
        rtab.innerHTML = html;
    }

    // Event listener untuk input pencarian dan dropdown status
    if (fq) fq.addEventListener('input', loadReservations);
    if (fs) fs.addEventListener('change', loadReservations);

    // Load data pertama kali saat halaman dimuat
    loadReservations();

    // Memuat data jadwal kalender
    fetch(`{{ route('admin.api.schedule') }}`)
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                console.log('Jadwal terisi (Tanggal Gelap):', res.data);
                // Anda bisa menyambungkan data res.data ke script kalender yang sudah ada di template Anda
            }
        });
});
</script>
@endpush
@endsection
