@extends('TemplateAdmin.main')
@section('dashboard', 'on')

@section('konten')
    <main class="main">
        <!-- Top Header Section -->
        <div class="top flex-between wrap gap-md">
            <div>
                <div class="eyebrow">Dashboard</div>
                <h2>Selamat datang, <em>Admin</em></h2>
            </div>
            <a class="btn sm" href="{{ route('admin.laporan') }}">Lihat laporan</a>
        </div>
        
        <!-- 4 Cards Statistik Ringkas -->
        <div class="kp" id="kp">
            <div class="kp-card">
                <span>Reservasi Baru</span>
                <b>{{ str_pad($reservasiBaru ?? 0, 2, '0', STR_PAD_LEFT) }}</b>
            </div>
            <div class="kp-card">
                <span>Menunggu Verifikasi</span>
                <b>{{ str_pad($menungguVerifikasi ?? 0, 2, '0', STR_PAD_LEFT) }}</b>
            </div>
            <div class="kp-card">
                <span>Sesi Mendatang</span>
                <b>{{ str_pad($sesiMendatang ?? 0, 2, '0', STR_PAD_LEFT) }}</b>
            </div>
            <div class="kp-card">
                <span>Menunggu Hasil Foto</span>
                <b>{{ str_pad($menungguHasilFoto ?? 0, 2, '0', STR_PAD_LEFT) }}</b>
            </div>
        </div>

        <!-- Main Grid Layout (Dashboard Content & Calendar) -->
        <div class="cols responsive-grid">
            <!-- Kelola Reservasi & Pembayaran -->
            <div class="card">
                <h3>Kelola reservasi &amp; pembayaran</h3>
                
                <!-- Filter & Search Bar -->
                <form method="GET" action="{{ route('admin.dashboard') }}" id="filterForm" class="flt">
                    <input type="hidden" name="date" id="filterDateInput" value="{{ request('date') }}">
                    <div class="fld">
                        <label>Cari nama / kode</label>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik untuk mencari…">
                    </div>
                    <div class="fld">
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

                @if(request('date'))
                    <div class="note filter-active-note">
                        Filter Tanggal: <strong>{{ request('date') }}</strong> 
                        <a href="{{ route('admin.dashboard') }}" class="lnk">[Reset Filter]</a>
                    </div>
                @endif

                <!-- Tabel Reservasi yang Responsif -->
                <div class="tw rt table-wrapper">
                    <table id="rtab">
                        <thead>
                            <tr class="th">
                                <th>KODE</th>
                                <th>PELANGGAN</th>
                                <th>PAKET</th>
                                <th>JADWAL</th>
                                <th>STATUS</th>
                                <th class="text-center">AKSI PILIHAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reservations as $item)
                                @php
                                    $stVal = optional($item->payment)->status ?? 'Masa konsultasi';
                                    $pillClass = 'wa';
                                    if($stVal === 'Selesai' || $stVal === 'Lunas') $pillClass = 'ok';
                                    elseif($stVal === 'Dibatalkan') $pillClass = 'rj';

                                    $latestPaymentHistory = optional($item->payment)->histories()->latest()->first();
                                    $receiptUrl = ($latestPaymentHistory && $latestPaymentHistory->receipt_photo) ? asset('storage/' . $latestPaymentHistory->receipt_photo) : '';
                                @endphp
                                <tr>
                                    <td data-l="Kode"><code>{{ $item->token }}</code></td>
                                    <td data-l="Pelanggan"><strong>{{ $item->name }}</strong><br><small>{{ $item->whatsapp }}</small></td>
                                    <td data-l="Paket">{{ optional($item->package)->name ?? '-' }}</td>
                                    <td data-l="Jadwal">{{ $item->date }} <br><small>{{ $item->time }}</small></td>
                                    <td data-l="Status">
                                        <span class="pill {{ $pillClass }}">{{ $stVal }}</span>
                                    </td>
                                    <td data-l="Aksi" class="action-cell">
                                        <div class="action-btn-group">
                                            <button type="button" class="btn sm ink btn-verify" 
                                                data-id="{{ $item->id }}"
                                                data-token="{{ $item->token }}"
                                                data-name="{{ $item->name }}"
                                                data-status="{{ $stVal }}"
                                                data-notes="{{ optional($item->payment)->notes ?? '' }}"
                                                data-receipt="{{ $receiptUrl }}">
                                                Verifikasi
                                            </button>

                                            <button type="button" class="btn sm btn-tagihan" 
                                                data-id="{{ $item->id }}"
                                                data-token="{{ $item->token }}"
                                                data-tagihan="{{ optional($item->payment)->tagihan ?? 0 }}"
                                                data-paid="{{ optional($item->payment)->total_paid ?? 0 }}">
                                                Tagihan
                                            </button>

                                            <button type="button" class="btn sm btn-reservasi" 
                                                data-id="{{ $item->id }}"
                                                data-token="{{ $item->token }}"
                                                data-name="{{ $item->name }}"
                                                data-whatsapp="{{ $item->whatsapp }}"
                                                data-date="{{ $item->date }}"
                                                data-time="{{ $item->time }}">
                                                Reservasi
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="note text-center">Data reservasi tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Widget Kalender -->
            <div class="card calendar-card-wrapper">
                <h3>Jadwal</h3>
                <div class="cl">
                    <button id="cp" aria-label="Bulan sebelumnya" type="button">‹</button>
                    <b id="cm"></b>
                    <button id="cn" aria-label="Bulan berikutnya" type="button">›</button>
                </div>
                <div class="cal" id="cal"></div>
                <p class="note calendar-note">Tanggal gelap sudah terisi — klik tanggal untuk memfilter tabel.</p>
            </div>
        </div>
    </main>

    <!-- 1. MODAL 1: EDIT VERIFIKASI PEMBAYARAN & BUKTI TRANSFER -->
    <div class="ov" id="modalVerify">
        <div class="md w">
            <button type="button" class="x" onclick="closeModal('modalVerify')">×</button>
            <div class="eyebrow">Verifikasi</div>
            <h3>Edit Verifikasi Pembayaran</h3>
            
            <div class="kv modal-info-box">
                <i>Token</i><span id="vToken">-</span>
                <i>Pelanggan</i><span id="vName">-</span>
            </div>

            <!-- Pratinjau Bukti Pembayaran -->
            <div id="vReceiptContainer" class="receipt-preview-container" style="display: none;">
                <label class="receipt-label">Bukti Transfer Pelanggan:</label>
                <a id="vReceiptLink" href="#" target="_blank">
                    <img id="vReceiptImg" src="" alt="Bukti Transfer" class="receipt-preview-img" />
                </a>
            </div>
            <div id="vNoReceipt" class="receipt-empty-note" style="display: none;">
                * Belum ada lampiran bukti pembayaran.
            </div>

            <form id="formVerify" method="POST" class="modal-form-spacing">
                @csrf
                @method('PUT')
                <div class="fld">
                    <label>Status Pembayaran / Reservasi</label>
                    <select name="status" id="vStatus" required>
                        <option value="Masa konsultasi">Masa Konsultasi</option>
                        <option value="Menunggu pembayaran DP">Menunggu Pembayaran DP</option>
                        <option value="Konfirmasi pembayaran">Konfirmasi Pembayaran (Perlu Cek DP)</option>
                        <option value="Menunggu pelunasan">Menunggu Pelunasan</option>
                        <option value="Verifikasi pelunasan">Verifikasi Pelunasan (Perlu Cek Lunas)</option>
                        <option value="Selesai">Selesai / Lunas</option>
                        <option value="Dibatalkan">Dibatalkan</option>
                    </select>
                </div>
                <div class="fld field-spacing-top">
                    <label>Catatan Admin</label>
                    <textarea name="notes" id="vNotes" rows="2" placeholder="Catatan untuk pelanggan..."></textarea>
                </div>
                <div class="acts modal-action-group">
                    <button type="button" class="btn sm" onclick="closeModal('modalVerify')">Batal</button>
                    <button type="submit" class="btn sm ink">Simpan Verifikasi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. MODAL 2: EDIT TAGIHAN -->
    <div class="ov" id="modalTagihan">
        <div class="md w">
            <button type="button" class="x" onclick="closeModal('modalTagihan')">×</button>
            <div class="eyebrow">Keuangan</div>
            <h3>Edit Nominal Tagihan &amp; Pembayaran</h3>
            
            <div class="kv modal-info-box">
                <i>Token</i><span id="tToken">-</span>
            </div>

            <form id="formTagihan" method="POST" class="modal-form-spacing">
                @csrf
                @method('PUT')
                <div class="fld">
                    <label>Total Tagihan Akhir (Rp)</label>
                    <input type="number" step="0.01" name="tagihan" id="inputTagihan" required />
                </div>
                <div class="fld field-spacing-top">
                    <label>Akumulasi Sudah Dibayar (Rp)</label>
                    <input type="number" step="0.01" name="total_paid" id="inputPaid" required />
                </div>
                <div class="acts modal-action-group">
                    <button type="button" class="btn sm" onclick="closeModal('modalTagihan')">Batal</button>
                    <button type="submit" class="btn sm ink">Simpan Tagihan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. MODAL 3: EDIT DATA RESERVASI -->
    <div class="ov" id="modalReservasi">
        <div class="md w">
            <button type="button" class="x" onclick="closeModal('modalReservasi')">×</button>
            <div class="eyebrow">Jadwal &amp; Kontak</div>
            <h3>Edit Detail Reservasi</h3>
            
            <div class="kv modal-info-box">
                <i>Token</i><span id="rToken">-</span>
            </div>

            <form id="formReservasi" method="POST" class="modal-form-spacing">
                @csrf
                @method('PUT')
                <div class="fld">
                    <label>Nama Pelanggan</label>
                    <input type="text" name="name" id="rName" required />
                </div>
                <div class="fld field-spacing-top">
                    <label>No. WhatsApp</label>
                    <input type="text" name="whatsapp" id="rWhatsapp" required />
                </div>
                <div class="r2 form-grid-datetime">
                    <div class="fld">
                        <label>Tanggal Sesi</label>
                        <input type="date" name="date" id="rDate" required />
                    </div>
                    <div class="fld">
                        <label>Jam Sesi</label>
                        <input type="time" name="time" id="rTime" required />
                    </div>
                </div>
                <div class="acts modal-action-group">
                    <button type="button" class="btn sm" onclick="closeModal('modalReservasi')">Batal</button>
                    <button type="submit" class="btn sm ink">Simpan Reservasi</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.add('on');
            el.style.display = 'flex';
        }
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.remove('on');
            el.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // 1. Tombol Verifikasi
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-verify');
            if (btn) {
                e.preventDefault();
                const id = btn.dataset.id;
                document.getElementById('vToken').innerHTML = `<code>${btn.dataset.token}</code>`;
                document.getElementById('vName').textContent = btn.dataset.name;
                document.getElementById('vStatus').value = btn.dataset.status;
                document.getElementById('vNotes').value = btn.dataset.notes;

                // Handle Bukti Foto
                const receiptUrl = btn.dataset.receipt;
                const container = document.getElementById('vReceiptContainer');
                const noReceipt = document.getElementById('vNoReceipt');
                if (receiptUrl) {
                    document.getElementById('vReceiptImg').src = receiptUrl;
                    document.getElementById('vReceiptLink').href = receiptUrl;
                    container.style.display = 'block';
                    noReceipt.style.display = 'none';
                } else {
                    container.style.display = 'none';
                    noReceipt.style.display = 'block';
                }

                document.getElementById('formVerify').action = `/admin/reservasi/verify/${id}`;
                openModal('modalVerify');
            }
        });

        // 2. Tombol Tagihan
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-tagihan');
            if (btn) {
                e.preventDefault();
                const id = btn.dataset.id;
                document.getElementById('tToken').innerHTML = `<code>${btn.dataset.token}</code>`;
                document.getElementById('inputTagihan').value = btn.dataset.tagihan;
                document.getElementById('inputPaid').value = btn.dataset.paid;

                document.getElementById('formTagihan').action = `/admin/reservasi/tagihan/${id}`;
                openModal('modalTagihan');
            }
        });

        // 3. Tombol Reservasi
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-reservasi');
            if (btn) {
                e.preventDefault();
                const id = btn.dataset.id;
                document.getElementById('rToken').innerHTML = `<code>${btn.dataset.token}</code>`;
                document.getElementById('rName').value = btn.dataset.name;
                document.getElementById('rWhatsapp').value = btn.dataset.whatsapp;
                document.getElementById('rDate').value = btn.dataset.date;
                document.getElementById('rTime').value = btn.dataset.time;

                document.getElementById('formReservasi').action = `/admin/reservasi/detail/${id}`;
                openModal('modalReservasi');
            }
        });

        // Tutup modal jika klik area luar latar belakang
        window.addEventListener('click', function(e) {
            ['modalVerify', 'modalTagihan', 'modalReservasi'].forEach(modalId => {
                const mEl = document.getElementById(modalId);
                if (e.target === mEl) closeModal(modalId);
            });
        });

        // Script Kalender Admin
        let currentDate = new Date();
        let bookedDatesList = [];
        const currentSelectedDate = "{{ request('date') }}";

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

            let firstDayIndex = (new Date(year, month, 1).getDay() + 6) % 7;
            let totalDays = new Date(year, month + 1, 0).getDate();

            let h = ['S', 'S', 'R', 'K', 'J', 'S', 'M'].map(d => `<span class="h">${d}</span>`).join('') + '<span></span>'.repeat(firstDayIndex);

            for (let day = 1; day <= totalDays; day++) {
                let formattedMonth = String(month + 1).padStart(2, '0');
                let formattedDay = String(day).padStart(2, '0');
                let dateString = `${year}-${formattedMonth}-${formattedDay}`;

                let isBooked = bookedDatesList.some(item => item.date === dateString);
                let cls = isBooked ? 'b' : (dateString === currentSelectedDate ? 'o' : '');
                
                h += `<span class="${cls}" data-t="${dateString}" style="cursor:pointer;">${day}</span>`;
            }
            calContainer.innerHTML = h;

            calContainer.querySelectorAll('span[data-t]').forEach(span => {
                span.addEventListener('click', function() {
                    const dt = this.dataset.t;
                    document.getElementById('filterDateInput').value = dt;
                    document.getElementById('filterForm').submit();
                });
            });
        }

        document.getElementById('cp')?.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendarGrid();
        });

        document.getElementById('cn')?.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendarGrid();
        });

        loadAdminCalendar();
    });
    </script>
    @endpush
@endsection