@extends('TemplateAdmin.main')
@section('dashboard', 'on')

@push('styles')
<style>
.cal { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; text-align: center; }
.cal-day { padding: 10px 0; background: #f8f9fa; border-radius: 6px; font-size: 14px; font-weight: 500; color: #333; }
.cal-day.empty { background: transparent; }
.cal-day.booked { background: #e74c3c !important; color: #fff !important; font-weight: bold; }
.cl { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.cl button { background: #f1f1f1; border: none; padding: 4px 12px; border-radius: 4px; cursor: pointer; }
</style>
@endpush

@section('konten')
    <main class="main">
        <div class="top">
            <div>
                <div class="eyebrow">Dashboard</div>
                <h2 style="margin-top:12px">Selamat datang, <em>Admin</em></h2>
            </div>
            <a class="btn sm" href="{{ route('admin.laporan') }}">Lihat laporan</a>
        </div>
        
        <!-- Kotak Ringkasan Statistik (KP) Menggunakan Data Server -->
        <div class="kp" id="kp">
            <div class="card">
                <div class="eyebrow">RESERVASI BARU</div>
                <h2 style="margin-top:8px">{{ str_pad($reservasiBaru, 2, '0', STR_PAD_LEFT) }}</h2>
            </div>
            <div class="card">
                <div class="eyebrow">MENUNGGU VERIFIKASI</div>
                <h2 style="margin-top:8px">{{ str_pad($menungguVerifikasi, 2, '0', STR_PAD_LEFT) }}</h2>
            </div>
            <div class="card">
                <div class="eyebrow">TOTAL RESERVASI</div>
                <h2 style="margin-top:8px">{{ str_pad($total, 2, '0', STR_PAD_LEFT) }}</h2>
            </div>
            <div class="card">
                <div class="eyebrow">LUNAS / SELESAI</div>
                <h2 style="margin-top:8px">{{ str_pad($lunasSelesai, 2, '0', STR_PAD_LEFT) }}</h2>
            </div>
        </div>

        <div class="cols">
            <div class="card">
                <h3>Kelola reservasi &amp; pembayaran</h3>
                
                <!-- Form Filter & Pencarian menggunakan GET -->
                <form method="GET" action="{{ route('admin.dashboard') }}" class="flt" style="margin-bottom:12px">
                    <div class="fld" style="margin:0">
                        <label>Cari nama / kode</label>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik untuk mencari…">
                    </div>
                    <div class="fld" style="margin:0">
                        <label>Status</label>
                        <select name="status" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="Masa konsultasi" {{ request('status') == 'Masa konsultasi' ? 'selected' : '' }}>Masa Konsultasi</option>
                            <option value="Menunggu pembayaran DP" {{ request('status') == 'Menunggu pembayaran DP' ? 'selected' : '' }}>Menunggu Pembayaran DP</option>
                            <option value="Konfirmasi pembayaran" {{ request('status') == 'Konfirmasi pembayaran' ? 'selected' : '' }}>Konfirmasi Pembayaran</option>
                            <option value="Menunggu pelunasan" {{ request('status') == 'Menunggu pelunasan' ? 'selected' : '' }}>Menunggu Pelunasan</option>
                            <option value="Verifikasi pelunasan" {{ request('status') == 'Verifikasi pelunasan' ? 'selected' : '' }}>Verifikasi Pelunasan</option>
                            <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai / Lunas</option>
                            <option value="Dibatalkan" {{ request('status') == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                </form>

                <div class="tw rt">
                    <table id="rtab">
                        <thead>
                            <tr>
                                <th>KODE</th>
                                <th>PELANGGAN</th>
                                <th>PAKET</th>
                                <th>JADWAL</th>
                                <th>STATUS</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reservations as $item)
                                <tr>
                                    <td><code>{{ $item->token }}</code></td>
                                    <td><strong>{{ $item->name }}</strong><br><small style="color:#666">{{ $item->whatsapp }}</small></td>
                                    <td>{{ optional($item->package)->name ?? '-' }}</td>
                                    <td>{{ $item->date }} <br><small>{{ $item->time }}</small></td>
                                    <td>
                                        <span class="badge" style="padding: 3px 8px; background: #f4efe6; border-radius: 4px; font-size: 11px;">
                                            {{ optional($item->payment)->status ?? 'Masa konsultasi' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/admin/reservasi/detail/{{ $item->token }}" class="btn sm" style="text-decoration: none;">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center; padding: 20px; color: #888;">Data reservasi tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3>Jadwal</h3>
                <div class="cl">
                    <button id="cp" aria-label="Bulan sebelumnya" type="button">‹</button>
                    <b id="cm"></b>
                    <button id="cn" aria-label="Bulan berikutnya" type="button">›</button>
                </div>
                <div class="cal" id="cal"></div>
                <p class="note" style="margin-top:14px">Tanggal gelap sudah terisi — dipakai untuk mencegah bentrok jadwal.</p>
            </div>
        </div>
    </main>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        let currentDate = new Date();
        let bookedDatesList = [];

        function loadAdminCalendar() {
            fetch(`{{ route('admin.api.schedule') }}`)
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        bookedDatesList = res.data;
                        renderCalendarGrid();
                    }
                })
                .catch(err => console.error('Gagal memuat jadwal kalender:', err));
        }

        function renderCalendarGrid() {
            const calContainer = document.getElementById('cal');
            const monthLabel = document.getElementById('cm');
            if (!calContainer) return;

            calContainer.innerHTML = '';

            let year = currentDate.getFullYear();
            let month = currentDate.getMonth();

            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            if (monthLabel) monthLabel.textContent = `${monthNames[month]} ${year}`;

            let firstDayIndex = new Date(year, month, 1).getDay();
            let totalDays = new Date(year, month + 1, 0).getDate();

            for (let i = 0; i < firstDayIndex; i++) {
                let emptyCell = document.createElement('div');
                emptyCell.className = 'cal-day empty';
                calContainer.appendChild(emptyCell);
            }

            for (let day = 1; day <= totalDays; day++) {
                let dayCell = document.createElement('div');
                dayCell.className = 'cal-day';

                let formattedMonth = String(month + 1).padStart(2, '0');
                let formattedDay = String(day).padStart(2, '0');
                let dateString = `${year}-${formattedMonth}-${formattedDay}`;

                dayCell.textContent = day;

                let isBooked = bookedDatesList.some(item => item.date === dateString);
                if (isBooked) {
                    dayCell.classList.add('booked');
                }

                calContainer.appendChild(dayCell);
            }
        }

        const prevBtn = document.getElementById('cp');
        const nextBtn = document.getElementById('cn');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendarGrid();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendarGrid();
            });
        }

        loadAdminCalendar();
    });
    </script>
    @endpush
@endsection