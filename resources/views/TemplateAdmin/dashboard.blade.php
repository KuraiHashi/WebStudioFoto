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

        @if (session('success'))
            <div class="dashboard-feedback success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="dashboard-feedback error" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="kp dashboard-kpis" id="kp">
            <a class="kp-card {{ request('filter') === 'new' ? 'active' : '' }}"
                href="{{ route('admin.dashboard', array_filter(['filter' => 'new', 'q' => request('q')])) }}">
                <span>Reservasi baru</span>
                <b>{{ str_pad($reservasiBaru ?? 0, 2, '0', STR_PAD_LEFT) }}</b>
                <small>Konsultasi / menunggu DP</small>
            </a>
            <a class="kp-card {{ request('filter') === 'verification' ? 'active' : '' }}"
                href="{{ route('admin.dashboard', array_filter(['filter' => 'verification', 'q' => request('q')])) }}">
                <span>Cek bukti pembayaran</span>
                <b>{{ str_pad($menungguVerifikasi ?? 0, 2, '0', STR_PAD_LEFT) }}</b>
                <small>Periksa bukti DP / pelunasan</small>
            </a>
            <a class="kp-card {{ request('filter') === 'completed' ? 'active' : '' }}"
                href="{{ route('admin.dashboard', array_filter(['filter' => 'completed', 'q' => request('q')])) }}">
                <span>Lunas / selesai</span>
                <b>{{ str_pad($reservasiSelesai ?? 0, 2, '0', STR_PAD_LEFT) }}</b>
                <small>Pembayaran telah lunas</small>
            </a>
        </div>

        <!-- Main Grid Layout (Dashboard Content & Calendar) -->
        <div class="cols responsive-grid">
            <!-- Kelola Reservasi & Pembayaran -->
            <div class="card">
                <h3>Kelola reservasi &amp; pembayaran</h3>
                
                <!-- Filter & Search Bar -->
                <form method="GET" action="{{ route('admin.dashboard') }}" id="filterForm" class="flt">
                    <input type="hidden" name="date" id="filterDateInput" value="{{ request('date') }}">
                    <input type="hidden" name="filter" id="dashboardFilterInput" value="{{ request('filter') }}">
                    <div class="fld">
                        <label>Cari nama / kode</label>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik untuk mencari…">
                    </div>
                    <div class="fld">
                        <label>Status</label>
                        <select name="status" onchange="document.getElementById('dashboardFilterInput').value = ''; this.form.submit()">
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

                @if(request('date') || request('filter'))
                    <div class="note filter-active-note">
                        @if(request('date'))
                            Filter tanggal: <strong>{{ request('date') }}</strong>
                        @endif
                        @if(request('filter'))
                            Filter: <strong>{{ ['new' => 'Reservasi baru', 'verification' => 'Cek bukti pembayaran', 'completed' => 'Lunas / selesai'][request('filter')] ?? request('filter') }}</strong>
                        @endif
                        <a href="{{ route('admin.dashboard', request()->only('q')) }}" class="lnk">[Hapus filter]</a>
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
                                <th>TAHAP SAAT INI</th>
                                <th>LANGKAH</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reservations as $item)
                                @php
                                    $stVal = $item->payment?->status ?? 'Masa konsultasi';
                                    $workflowLabel = match ($stVal) {
                                        'Masa konsultasi' => 'Konsultasi',
                                        'Menunggu pembayaran DP' => 'Menunggu DP',
                                        'Konfirmasi pembayaran' => 'Cek bukti DP',
                                        'Menunggu pelunasan' => $item->session_completed_at
                                            ? 'Menunggu pelunasan'
                                            : ($item->canBeMarkedComplete() ? 'Sesi perlu ditandai selesai' : 'Sesi mendatang'),
                                        'Verifikasi pelunasan' => 'Cek bukti pelunasan',
                                        'Selesai' => match ($item->photo_status) {
                                            'diproses' => 'Foto sedang diproses',
                                            'siap' => 'Foto siap dikirim',
                                            default => 'Menunggu hasil foto',
                                        },
                                        'Dibatalkan' => 'Dibatalkan',
                                        default => $stVal,
                                    };
                                    $pillClass = in_array($stVal, ['Selesai'], true) ? 'ok' : ($stVal === 'Dibatalkan' ? 'rj' : 'wa');

                                    $latestPaymentHistory = $item->payment?->histories->first();
                                    $receiptUrl = $latestPaymentHistory?->receipt_photo
                                        ? (str_starts_with($latestPaymentHistory->receipt_photo, 'uploads/receipts/')
                                            ? asset($latestPaymentHistory->receipt_photo)
                                            : asset('storage/' . $latestPaymentHistory->receipt_photo))
                                        : '';
                                @endphp
                                <tr>
                                    <td data-l="Kode"><code>{{ $item->token }}</code></td>
                                    <td data-l="Pelanggan"><strong>{{ $item->name }}</strong><br><small>{{ $item->whatsapp }}</small></td>
                                    <td data-l="Paket">{{ optional($item->package)->name ?? '-' }}</td>
                                    <td data-l="Jadwal">{{ $item->date }} <br><small>{{ $item->time }}</small></td>
                                    <td data-l="Status">
                                        <span class="pill {{ $pillClass }}">{{ $workflowLabel }}</span>
                                        <small class="workflow-substatus">Pembayaran: {{ $stVal }}</small>
                                    </td>
                                    <td data-l="Aksi" class="action-cell">
                                        <div class="action-btn-group">
                                            <button type="button" class="btn sm ink btn-workflow"
                                                data-token="{{ $item->token }}"
                                                data-name="{{ $item->name }}"
                                                data-status="{{ $stVal }}"
                                                data-notes="{{ optional($item->payment)->notes ?? '' }}"
                                                data-receipt="{{ $receiptUrl }}"
                                                data-total-bill="{{ optional($item->payment)->tagihan ?? 0 }}"
                                                data-total-paid="{{ optional($item->payment)->total_paid ?? 0 }}"
                                                data-proof-amount="{{ $item->payment?->histories->where('status', 'menunggu_verifikasi')->sum('amount') ?? 0 }}"
                                                data-session-completed="{{ $item->session_completed_at ? '1' : '0' }}"
                                                data-session-ended="{{ $item->canBeMarkedComplete() ? '1' : '0' }}"
                                                data-photo-status="{{ $item->photo_status }}"
                                                data-photo-url="{{ $item->photo_delivery_url ?? '' }}"
                                                data-verify-action="{{ route('admin.reservasi.verify', $item->id) }}"
                                                data-session-action="{{ route('admin.reservasi.session-complete', $item->id) }}"
                                                data-photo-action="{{ route('admin.reservasi.photos', $item->id) }}">
                                                Proses
                                            </button>

                                            <button type="button" class="btn sm btn-tagihan" 
                                                data-token="{{ $item->token }}"
                                                data-tagihan="{{ optional($item->payment)->tagihan ?? 0 }}"
                                                data-paid="{{ optional($item->payment)->total_paid ?? 0 }}"
                                                data-pending-proof="{{ $item->payment?->histories->where('status', 'menunggu_verifikasi')->sum('amount') ?? 0 }}"
                                                data-action="{{ route('admin.reservasi.tagihan', $item->id) }}">
                                                Tagihan
                                            </button>

                                            <button type="button" class="btn sm btn-reservasi" 
                                                data-token="{{ $item->token }}"
                                                data-name="{{ $item->name }}"
                                                data-whatsapp="{{ $item->whatsapp }}"
                                                data-date="{{ $item->date }}"
                                                data-time="{{ $item->time }}"
                                                data-action="{{ route('admin.reservasi.detail', $item->id) }}">
                                                Jadwal
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
                <div class="cal" id="cal" aria-live="polite"></div>
                <p class="note calendar-note">Tanggal gelap sudah terisi — klik tanggal untuk memfilter tabel.</p>
            </div>
        </div>
    </main>

    <!-- Modal alur kerja reservasi -->
    <div class="ov" id="modalVerify">
        <div class="md w">
            <button type="button" class="x" onclick="closeModal('modalVerify')">×</button>
            <div class="eyebrow">Proses reservasi</div>
            <h3 id="workflowTitle">Langkah berikutnya</h3>
            
            <div class="kv modal-info-box">
                <i>Token</i><span id="vToken">-</span>
                <i>Pelanggan</i><span id="vName">-</span>
                <i>Tahap pembayaran</i><span id="vPaymentStatus">-</span>
                <i>Total tagihan</i><span id="vTotalBill">-</span>
                <i>Sudah diverifikasi</i><span id="vTotalPaid">-</span>
                <i>Bukti menunggu verifikasi</i><span id="vProofAmount">-</span>
            </div>
            <p class="workflow-instruction" id="workflowInstruction"></p>
            <p class="dashboard-feedback error" id="paymentMismatchWarning" role="alert" style="display:none;"></p>

            <div id="vReceiptContainer" class="receipt-preview-container" style="display: none;">
                <label class="receipt-label">Bukti pembayaran terbaru:</label>
                <a id="vReceiptLink" href="#" target="_blank" rel="noopener">
                    <img id="vReceiptImg" src="" alt="Bukti Transfer" class="receipt-preview-img" />
                </a>
            </div>
            <div id="vNoReceipt" class="receipt-empty-note" style="display: none;">
                Belum ada bukti pembayaran yang dikirim.
            </div>

            <form id="formVerify" method="POST" class="modal-form-spacing" style="display:none;">
                @csrf
                @method('PUT')
                <div class="fld">
                    <label>Tindakan</label>
                    <select name="status" id="vStatus" required></select>
                </div>
                <div class="fld field-spacing-top">
                    <label>Catatan untuk pelanggan (wajib saat menolak bukti)</label>
                    <textarea name="notes" id="vNotes" rows="2" placeholder="Jelaskan tindakan atau alasan penolakan..."></textarea>
                </div>
                <div class="acts modal-action-group">
                    <button type="button" class="btn sm" onclick="closeModal('modalVerify')">Batal</button>
                    <button type="submit" class="btn sm ink" id="verifySubmit">Simpan tindakan</button>
                </div>
            </form>

            <form id="formSessionComplete" method="POST" class="modal-form-spacing" style="display:none;">
                @csrf
                @method('PUT')
                <div class="acts modal-action-group">
                    <button type="button" class="btn sm" onclick="closeModal('modalVerify')">Nanti</button>
                    <button type="submit" class="btn sm ink">Tandai sesi selesai</button>
                </div>
            </form>

            <form id="formPhotoWorkflow" method="POST" class="modal-form-spacing" style="display:none;">
                @csrf
                @method('PUT')
                <div class="fld">
                    <label for="photoDeliveryUrl">Tautan hasil foto (Google Drive)</label>
                    <input type="url" name="photo_delivery_url" id="photoDeliveryUrl" placeholder="https://drive.google.com/..." required />
                </div>
                <p class="note" id="photoWorkflowNote"></p>
                <div class="acts modal-action-group">
                    <button type="button" class="btn sm" onclick="closeModal('modalVerify')">Nanti</button>
                    <button type="submit" class="btn sm ink" id="photoSubmit">Kirim hasil foto</button>
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
                    <label>Total Pembayaran Terverifikasi (Rp)</label>
                    <input type="number" step="0.01" id="inputPaid" readonly />
                </div>
                <div class="fld field-spacing-top">
                    <label>Bukti Menunggu Verifikasi (Rp)</label>
                    <input type="number" step="0.01" id="inputPendingProof" readonly />
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
        const setToken = (elementId, tokenValue) => {
            const token = document.createElement('code');
            token.textContent = tokenValue;
            document.getElementById(elementId).replaceChildren(token);
        };

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-workflow');
            if (btn) {
                e.preventDefault();
                setToken('vToken', btn.dataset.token);
                document.getElementById('vName').textContent = btn.dataset.name;
                document.getElementById('vNotes').value = btn.dataset.notes;
                document.getElementById('vPaymentStatus').textContent = btn.dataset.status;
                const formatCurrency = value => `Rp ${new Intl.NumberFormat('id-ID').format(Number(value || 0))}`;
                const totalBill = Number(btn.dataset.totalBill || 0);
                const totalPaid = Number(btn.dataset.totalPaid || 0);
                const proofAmount = Number(btn.dataset.proofAmount || 0);
                const remaining = Math.max(0, totalBill - totalPaid);
                document.getElementById('vTotalBill').textContent = formatCurrency(totalBill);
                document.getElementById('vTotalPaid').textContent = formatCurrency(totalPaid);
                document.getElementById('vProofAmount').textContent = formatCurrency(proofAmount);
                const paymentWarning = document.getElementById('paymentMismatchWarning');
                paymentWarning.style.display = 'none';
                paymentWarning.textContent = '';
                if (btn.dataset.status === 'Verifikasi pelunasan' && proofAmount !== remaining) {
                    paymentWarning.textContent = proofAmount > remaining
                        ? `Bukti lebih besar dari sisa tagihan (${formatCurrency(remaining)}). Perbaiki total tagihan melalui tombol Tagihan sebelum menyetujui.`
                        : `Bukti belum menutup sisa tagihan (${formatCurrency(remaining)}). Jangan setujui sebelum nominal sesuai.`;
                    paymentWarning.style.display = 'block';
                }

                const receiptUrl = btn.dataset.receipt;
                const container = document.getElementById('vReceiptContainer');
                const noReceipt = document.getElementById('vNoReceipt');
                const requiresReceipt = ['Konfirmasi pembayaran', 'Verifikasi pelunasan'].includes(btn.dataset.status);
                if (receiptUrl && requiresReceipt) {
                    document.getElementById('vReceiptImg').src = receiptUrl;
                    document.getElementById('vReceiptLink').href = receiptUrl;
                    container.style.display = 'block';
                    noReceipt.style.display = 'none';
                } else {
                    container.style.display = 'none';
                    noReceipt.style.display = requiresReceipt ? 'block' : 'none';
                }

                const formVerify = document.getElementById('formVerify');
                const formSessionComplete = document.getElementById('formSessionComplete');
                const formPhotoWorkflow = document.getElementById('formPhotoWorkflow');
                const options = {
                    'Masa konsultasi': [
                        ['Menunggu pembayaran DP', 'Konsultasi selesai — aktifkan pembayaran DP'],
                    ],
                    'Konfirmasi pembayaran': [
                        ['Menunggu pelunasan', 'Setujui bukti DP — kunci jadwal'],
                        ['Menunggu pembayaran DP', 'Tolak bukti DP — minta pelanggan kirim ulang'],
                    ],
                    'Verifikasi pelunasan': [
                        ['Selesai', 'Setujui pelunasan — pembayaran lunas'],
                        ['Menunggu pelunasan', 'Tolak bukti pelunasan — minta kirim ulang'],
                    ],
                };
                const statusSelect = document.getElementById('vStatus');
                statusSelect.replaceChildren();
                (options[btn.dataset.status] || []).forEach(([value, label]) => {
                    statusSelect.add(new Option(label, value));
                });
                if (!['Selesai', 'Dibatalkan'].includes(btn.dataset.status)) {
                    statusSelect.add(new Option('Batalkan reservasi', 'Dibatalkan'));
                }

                const rejectionSelected = () => btn.dataset.status === 'Konfirmasi pembayaran'
                    ? statusSelect.value === 'Menunggu pembayaran DP'
                    : btn.dataset.status === 'Verifikasi pelunasan'
                        && statusSelect.value === 'Menunggu pelunasan';
                document.getElementById('vNotes').required = false;
                statusSelect.onchange = () => {
                    document.getElementById('vNotes').required = rejectionSelected()
                        && ['Konfirmasi pembayaran', 'Verifikasi pelunasan'].includes(btn.dataset.status);
                };
                formVerify.action = btn.dataset.verifyAction;
                formVerify.style.display = statusSelect.options.length ? 'block' : 'none';

                const sessionComplete = btn.dataset.sessionCompleted === '1';
                const eligibleSession = ['Menunggu pelunasan', 'Verifikasi pelunasan'].includes(btn.dataset.status);
                const sessionCanEnd = btn.dataset.sessionEnded === '1';
                const instruction = document.getElementById('workflowInstruction');
                let message = '';

                if (btn.dataset.status === 'Masa konsultasi') {
                    message = 'Reservasi baru meminta konsultasi. Setelah kesepakatan selesai, aktifkan DP agar pelanggan dapat membayar.';
                } else if (btn.dataset.status === 'Menunggu pembayaran DP') {
                    message = 'Tunggu pelanggan membayar DP dan mengirim bukti. Setelah bukti masuk, periksa nominal dan nama pengirim di sini.';
                } else if (btn.dataset.status === 'Konfirmasi pembayaran') {
                    message = 'Periksa bukti DP. Jika sesuai, setujui agar jadwal terkunci; jika tidak, tolak dan jelaskan alasannya.';
                } else if (btn.dataset.status === 'Menunggu pelunasan' && !sessionComplete) {
                    message = sessionCanEnd
                        ? 'Sesi sudah melewati jadwal. Tandai selesai setelah pemotretan benar-benar dilakukan; pelanggan kemudian dapat melunasi.'
                        : 'DP sudah disetujui. Jadwal pemotretan masih akan datang; setelah sesi berakhir, tandai sesi selesai.';
                } else if (btn.dataset.status === 'Menunggu pelunasan' && sessionComplete) {
                    message = 'Sesi selesai. Tunggu pelanggan membayar sisa tagihan dan mengirim bukti pelunasan.';
                } else if (btn.dataset.status === 'Verifikasi pelunasan' && !sessionComplete) {
                    message = sessionCanEnd
                        ? 'Sesi sudah melewati jadwal. Tandai sesi selesai sebelum memverifikasi bukti pelunasan.'
                        : 'Bukti pelunasan sudah dikirim. Setelah sesi pemotretan selesai, tandai sesi selesai sebelum memverifikasi.';
                } else if (btn.dataset.status === 'Verifikasi pelunasan') {
                    message = 'Sesi telah selesai. Periksa bukti dan nominal pelunasan sebelum menyetujui pembayaran.';
                } else if (btn.dataset.status === 'Selesai' && btn.dataset.photoStatus === 'belum_diproses') {
                    message = 'Pembayaran lunas. Masukkan tautan Google Drive untuk langsung mengirim hasil foto kepada pelanggan.';
                } else if (btn.dataset.status === 'Selesai' && btn.dataset.photoStatus === 'diproses') {
                    message = 'Masukkan tautan Google Drive untuk mengirim hasil foto kepada pelanggan.';
                } else if (btn.dataset.photoStatus === 'siap') {
                    message = 'Tautan hasil foto sudah dikirim ke halaman cek reservasi pelanggan.';
                } else if (btn.dataset.status === 'Dibatalkan') {
                    message = 'Reservasi ini sudah dibatalkan.';
                }

                instruction.textContent = message;
                document.getElementById('workflowTitle').textContent = btn.dataset.name;
                formSessionComplete.action = btn.dataset.sessionAction;
                formSessionComplete.style.display = eligibleSession && !sessionComplete && sessionCanEnd
                    ? 'block'
                    : 'none';

                const canManagePhotos = btn.dataset.status === 'Selesai'
                    && btn.dataset.photoStatus !== 'siap';
                document.getElementById('photoDeliveryUrl').value = btn.dataset.photoUrl;
                document.getElementById('photoWorkflowNote').textContent =
                    'Setelah disimpan, pelanggan dapat membuka tautan ini dari halaman cek reservasi.';
                formPhotoWorkflow.action = btn.dataset.photoAction;
                formPhotoWorkflow.style.display = canManagePhotos ? 'block' : 'none';

                openModal('modalVerify');
            }
        });

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-tagihan');
            if (btn) {
                e.preventDefault();
                setToken('tToken', btn.dataset.token);
                document.getElementById('inputTagihan').value = btn.dataset.tagihan;
                document.getElementById('inputPaid').value = btn.dataset.paid;
                document.getElementById('inputPendingProof').value = btn.dataset.pendingProof;

                document.getElementById('formTagihan').action = btn.dataset.action;
                openModal('modalTagihan');
            }
        });

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-reservasi');
            if (btn) {
                e.preventDefault();
                setToken('rToken', btn.dataset.token);
                document.getElementById('rName').value = btn.dataset.name;
                document.getElementById('rWhatsapp').value = btn.dataset.whatsapp;
                document.getElementById('rDate').value = btn.dataset.date;
                document.getElementById('rTime').value = btn.dataset.time;

                document.getElementById('formReservasi').action = btn.dataset.action;
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
        const currentSelectedDate = "{{ request('date') }}";
        let currentDate = currentSelectedDate
            ? new Date(`${currentSelectedDate}T12:00:00`)
            : new Date();
        let bookedDatesList = [];

        function loadAdminCalendar() {
            fetch(`{{ route('admin.api.schedule') }}`)
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`Permintaan jadwal gagal (${res.status})`);
                    }
                    return res.json();
                })
                .then(res => {
                    if (!res.success || !Array.isArray(res.data)) {
                        throw new Error('Data jadwal kalender tidak valid');
                    }
                    bookedDatesList = res.data;
                    renderCalendarGrid();
                })
                .catch(err => {
                    console.error('Gagal memuat jadwal kalender:', err);
                    document.getElementById('cal').textContent = 'Jadwal tidak dapat dimuat. Muat ulang halaman untuk mencoba kembali.';
                });
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
                let classes = ['cal-day'];
                if (isBooked) classes.push('b');
                if (dateString === currentSelectedDate) classes.push('selected');
                h += `<button type="button" class="${classes.join(' ')}" data-t="${dateString}" aria-pressed="${dateString === currentSelectedDate}" aria-label="${day} ${monthNames[month]} ${year}${isBooked ? ', sudah terisi' : ''}">${day}</button>`;
            }
            calContainer.innerHTML = h;

            calContainer.querySelectorAll('button[data-t]').forEach(button => {
                button.addEventListener('click', function() {
                    const dt = this.dataset.t;
                    document.getElementById('filterDateInput').value = dt;
                    document.getElementById('filterForm').submit();
                });
            });
        }

        document.getElementById('cp').addEventListener('click', () => {
            currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 1);
            renderCalendarGrid();
        });

        document.getElementById('cn').addEventListener('click', () => {
            currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);
            renderCalendarGrid();
        });

        loadAdminCalendar();
    });
    </script>
    @endpush
@endsection