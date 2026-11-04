@extends('template.main')

@section('konten_detail')
    <main class="pg"
        style="background: #F3EDE3;
    background: linear-gradient(180deg, rgba(37, 35, 31, 1) 80px, #F3EDE3 80px);">
        <div class="wrap" style="max-width: 760px">
            <div class="crumb">
                <a href="{{ route('reservasi.cek') }}">Cek Reservasi</a> / Pembayaran
            </div>
            <h1>Pem<em>bayaran</em></h1>
            <p class="lead">
                Selesaikan administrasi reservasi Anda dengan aman melalui transfer bank resmi atau QRIS studio kami.
            </p>

            @if (!$payment)
                <div class="fc" id="bmiss"
                    style="padding: 30px; background: #fbf9f5; border: 1px solid #e6dec5; border-radius: 8px; text-align: center;">
                    <h3>Pembayaran tidak tersedia</h3>
                    <p style="margin-bottom: 18px; color: #666;">Kode reservasi tidak valid atau tidak ditemukan.</p>
                    <a class="btn ink" href="{{ route('reservasi.cek') }}"
                        style="background: #191512; color: #fff; padding: 12px 24px; border-radius: 30px; text-decoration: none;">Ke
                        Cek Reservasi</a>
                </div>
            @else
                <div class="fc"
                    style="padding: 30px; background: #fbf9f5; border: 1px solid #e6dec5; border-radius: 8px;">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                        <h3 style="margin: 0;">Token: {{ $payment->token }}</h3>
                        <span
                            style="padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 600; letter-spacing: 1px;
                            @if ($payment->status == 'Masa konsultasi') background: #fff8e1; color: #f57f17; border: 1px solid #ffe082;
                            @else background: #e0f7fa; color: #00838f; border: 1px solid #80deea; @endif">
                            {{ strtoupper($payment->status) }}
                        </span>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger"
                            style="margin-bottom: 20px; color: #721c24; background: #f8d7da; padding: 12px; border-radius: 6px;">
                            <ul style="margin: 0; padding-left: 15px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="kv" style="margin-bottom: 24px;">
                        <i>Kode Token</i><span>{{ $payment->token }}</span>
                        <i>Status Saat Ini</i><span><strong>{{ $payment->status }}</strong></span>
                        <i>Total Tagihan</i><span>Rp {{ number_format($payment->tagihan, 0, ',', '.') }}</span>
                        <i>Sudah Dibayar</i><span>Rp {{ number_format($payment->total_paid, 0, ',', '.') }}</span>
                        <i>Sisa Kekurangan</i><span style="font-weight: bold; color: #d9534f;">Rp
                            {{ number_format(max(0, $payment->tagihan - $payment->total_paid), 0, ',', '.') }}</span>
                    </div>

                    @if ($payment->status === 'Masa konsultasi')
                        <div
                            style="background: #fff8e1; border: 1px solid #ffe082; padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px;">
                            <h4 style="color: #f57f17; margin-top: 0; font-size: 16px;">💬 Reservasi Anda Sedang dalam Masa
                                Konsultasi</h4>
                            <p style="font-size: 14px; color: #666; margin-bottom: 15px;">
                                Karena Anda mengajukan sesi konsultasi konsep, fitur pembayaran DP saat ini dikunci
                                sementara. Admin kami akan segera menghubungi Anda via WhatsApp untuk mendiskusikan konsep
                                pemotretan. Pembayaran akan diaktifkan setelah sesi konsultasi disetujui.
                            </p>
                            <a href="{{ route('reservasi.result', ['token' => $payment->token]) }}" class="btn ink"
                                style="background: #191512; color: #fff; padding: 10px 24px; border-radius: 20px; text-decoration: none; font-size: 13px;">
                                ← Kembali ke Cek Status
                            </a>
                        </div>
                    @elseif ($payment->status === 'Menunggu pelunasan' && !$payment->reservation->session_completed_at)
                        <div class="note" style="padding: 18px; background: #f3ede3; border-radius: 8px;">
                            DP sudah diverifikasi. Form pelunasan akan tersedia setelah sesi pemotretan selesai dan ditandai
                            admin.
                        </div>
                    @elseif (in_array($payment->status, ['Konfirmasi pembayaran', 'Verifikasi pelunasan'], true))
                        <div class="note" style="padding: 18px; background: #f3ede3; border-radius: 8px;">
                            Bukti pembayaran sedang diperiksa admin. Mohon tunggu hasil verifikasi sebelum mengirim
                            pembayaran berikutnya.
                        </div>
                    @elseif ($payment->status === 'Selesai')
                        <div class="note" style="padding: 18px; background: #f3ede3; border-radius: 8px;">
                            Pembayaran sudah lunas. @if ($payment->reservation->session_completed_at)
                                Hasil foto akan tersedia setelah selesai diproses studio.
                            @else
                                Silakan datang sesuai jadwal pemotretan.
                            @endif
                        </div>
                    @elseif ($payment->status === 'Dibatalkan')
                        <div class="note" style="padding: 18px; background: #f3ede3; border-radius: 8px;">
                            Reservasi ini dibatalkan sehingga pembayaran baru tidak dapat dikirim.
                        </div>
                    @else
                        <form action="{{ route('reservasi.payment.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="token" value="{{ $payment->token }}">

                            <div class="fld" style="margin-bottom: 20px;">
                                <label
                                    style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 12px; letter-spacing: 1px; color: #8c8275;">METODE
                                    PEMBAYARAN</label>
                                <select name="payment_method" id="payment_method" required onchange="togglePaymentDetails()"
                                    style="width: 100%; padding: 12px; border: 1px solid #d1c7bd; background: #fff; border-radius: 6px; font-family: inherit;">
                                    <option value="" selected disabled>-- Pilih Metode Pembayaran --</option>
                                    <option value="Transfer Bank">Transfer Bank (Pilih Rekening)</option>
                                    <option value="QRIS">QRIS Studio</option>
                                    <option value="Cash">Cash / Tunai di Studio</option>
                                </select>
                            </div>

                            <!-- Dropdown Pilihan Bank (Muncul jika pilih Transfer Bank) -->
                            <div id="bank_selection_container" style="display: none; margin-bottom: 20px;">
                                <label
                                    style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 12px; letter-spacing: 1px; color: #8c8275;">PILIH
                                    REKENING TUJUAN</label>
                                <select name="rekening_id" id="rekening_id" onchange="updateBankInfo()"
                                    style="width: 100%; padding: 12px; border: 1px solid #d1c7bd; background: #fff; border-radius: 6px; font-family: inherit;">
                                    <option value="" selected disabled>-- Pilih Bank --</option>
                                    @foreach ($rekenings as $rek)
                                        <option value="{{ $rek->id }}" data-bank="{{ $rek->nama_bank }}"
                                            data-acc="{{ $rek->no_rekening }}" data-holder="{{ $rek->nama_pemilik }}">
                                            {{ $rek->nama_bank }} - {{ $rek->no_rekening }} (a.n.
                                            {{ $rek->nama_pemilik }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Detail Informasi Rekening (Muncul setelah bank dipilih) -->
                            <div id="bank_details_container"
                                style="display: none; margin-bottom: 20px; padding: 16px; background: #f3ede3; border: 1px solid #d1c7bd; border-radius: 6px;">
                                <h4 style="margin-top: 0; font-size: 15px; color: #191512;">Informasi Rekening Bank</h4>
                                <p style="margin-bottom: 4px; color: #555; font-size: 13px;">Silakan transfer ke rekening
                                    resmi berikut:</p>
                                <strong id="display_acc_number"
                                    style="font-size: 18px; display: block; color: #191512; letter-spacing: 0.5px;">-</strong>
                                <span id="display_acc_holder" style="font-size: 13px; color: #666;">-</span>
                            </div>

                            <!-- Detail QRIS Dinamis -->
<div id="qris_details_container" style="display: none; margin-bottom: 20px; padding: 20px; background: #f3ede3; border: 1px solid #d1c7bd; border-radius: 8px; text-align: center;">
    <h4 style="margin-top: 0; font-size: 15px; color: #191512; font-weight: 600;">Scan QRIS Liquif Picture</h4>
    
    <div style="margin: 15px 0; background: #fff; display: inline-block; padding: 10px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        @if(isset($qrisData) && $qrisData->foto_qris)
            <img src="{{ asset('storage/' . $qrisData->foto_qris) }}" alt="QRIS Liquif Picture" style="max-width: 200px; height: auto; display: block; border-radius: 4px;">
        @else
            <div style="padding: 20px; color: #888; font-size: 13px;">
                ⚠️ QRIS belum diatur oleh admin.
            </div>
        @endif
    </div>

    <p style="font-size: 12px; color: #666; margin: 0;">Dapat di-scan dengan seluruh aplikasi e-Wallet & Mobile Banking</p>
</div>

                            <div id="cash_details_container"
                                style="display: none; margin-bottom: 20px; padding: 16px; background: #f3ede3; border: 1px solid #d1c7bd; border-radius: 6px;">
                                <h4 style="margin-top: 0; font-size: 15px; color: #191512;">Pembayaran tunai di studio</h4>
                                <p style="margin: 0; color: #555; font-size: 13px;">Bayar langsung kepada admin studio, lalu
                                    unggah foto tanda terima yang diberikan.</p>
                            </div>

                            <div class="fld" style="margin-bottom: 20px;">
                                <label
                                    style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 12px; letter-spacing: 1px; color: #8c8275;">
                                    {{ $payment->status === 'Menunggu pembayaran DP' ? 'NOMINAL DP (MINIMAL 50%)' : 'NOMINAL PELUNASAN' }}
                                    (RP)
                                </label>
                                <input type="number" name="amount"
                                    min="{{ $payment->status === 'Menunggu pembayaran DP' ? $payment->tagihan / 2 : max(0, $payment->tagihan - $payment->total_paid) }}"
                                    max="{{ max(0, $payment->tagihan - $payment->total_paid) }}" step="0.01"
                                    placeholder="Masukkan nominal pembayaran" required
                                    style="width: 100%; padding: 12px; border: 1px solid #d1c7bd; background: #fff; border-radius: 6px; font-family: inherit;"
                                    value="{{ $payment->status === 'Menunggu pembayaran DP' ? max($payment->tagihan / 2, $payment->tagihan - $payment->total_paid) : max(0, $payment->tagihan - $payment->total_paid) }}">
                            </div>

                            <div class="fld" id="receipt_container" style="margin-bottom: 20px;">
                                <label
                                    style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 12px; letter-spacing: 1px; color: #8c8275;">BUKTI
                                    TRANSFER / FOTO KWITANSI</label>
                                <label class="drop"
                                    style="display: block; border: 2px dashed #d1c7bd; padding: 20px; text-align: center; cursor: pointer; border-radius: 6px; background: #fff;">
                                    <span id="file-label-text" style="font-size: 14px; color: #444;">Ketuk untuk memilih
                                        foto bukti pembayaran</span><br />
                                    <span style="font-size: 11px; color: #777;">JPG / PNG · maks. 2 MB (foto struk untuk
                                        pembayaran tunai)</span>
                                    <input type="file" name="receipt_photo" id="receipt_photo"
                                        accept=".jpg,.jpeg,.png,image/jpeg,image/png" required style="display: none;"
                                        onchange="document.getElementById('file-label-text').innerText = this.files[0].name;" />
                                </label>
                            </div>

                            <div class="fld" style="margin-bottom: 20px;">
                                <label
                                    style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 12px; letter-spacing: 1px; color: #8c8275;">CATATAN
                                    (OPSIONAL)</label>
                                <textarea name="notes" rows="2" placeholder="Cth: Transfer atas nama Budi / Pembayaran Tunai di lokasi"
                                    style="width: 100%; padding: 12px; border: 1px solid #d1c7bd; background: #fff; border-radius: 6px; font-family: inherit;"></textarea>
                            </div>

                            <div class="acts" style="display: flex; gap: 12px; align-items: center; margin-top: 24px;">
                                <button type="submit" class="btn ink"
                                    style="background: #191512; color: #fff; padding: 12px 28px; border-radius: 30px; border: none; font-weight: 500; cursor: pointer;">
                                    Kirim Bukti Pembayaran
                                </button>
                                <a class="btn" href="{{ route('reservasi.result', ['token' => $payment->token]) }}"
                                    style="text-decoration: none; padding: 12px 20px; color: #333;">Kembali</a>
                            </div>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </main>

    <!-- JavaScript Logika Tampilan Dinamis -->
    <script>
        function togglePaymentDetails() {
            const methodElement = document.getElementById('payment_method');
            if (!methodElement) return;

            const method = methodElement.value;
            const bankSelectContainer = document.getElementById('bank_selection_container');
            const bankBox = document.getElementById('bank_details_container');
            const qrisBox = document.getElementById('qris_details_container');
            const cashBox = document.getElementById('cash_details_container');

            const rekeningSelect = document.getElementById('rekening_id');
            const receiptInput = document.getElementById('receipt_photo');

            // 1. Sembunyikan semua box detail dan pilihan bank terlebih dahulu
            if (bankBox) bankBox.style.display = 'none';
            if (qrisBox) qrisBox.style.display = 'none';
            if (cashBox) cashBox.style.display = 'none';
            if (bankSelectContainer) bankSelectContainer.style.display = 'none';

            // 2. Reset nilai pilihan rekening
            if (rekeningSelect) rekeningSelect.value = '';

            // 3. Tampilkan box sesuai metode yang dipilih
            if (method === 'Transfer Bank') {
                if (bankSelectContainer) bankSelectContainer.style.display = 'block';
                if (rekeningSelect) rekeningSelect.required = true;
                if (receiptInput) receiptInput.required = true;
            } else if (method === 'QRIS') {
                if (qrisBox) qrisBox.style.display = 'block'; // INI KUNCI UTAMA AGAR QRIS MUNCUL
                if (rekeningSelect) rekeningSelect.required = false;
                if (receiptInput) receiptInput.required = true;
            } else if (method === 'Cash') {
                if (cashBox) cashBox.style.display = 'block';
                if (rekeningSelect) rekeningSelect.required = false;
                if (receiptInput) receiptInput.required = false;
            }
        }

        function updateBankInfo() {
            const select = document.getElementById('rekening_id');
            if (!select) return;

            const selectedOption = select.options[select.selectedIndex];
            const bankBox = document.getElementById('bank_details_container');

            if (selectedOption && selectedOption.value) {
                const bankName = selectedOption.getAttribute('data-bank');
                const accNumber = selectedOption.getAttribute('data-acc');
                const accHolder = selectedOption.getAttribute('data-holder');

                const accNumEl = document.getElementById('display_acc_number');
                const accHoldEl = document.getElementById('display_acc_holder');

                if (accNumEl) accNumEl.innerText = accNumber;
                if (accHoldEl) accHoldEl.innerText = `a.n. ${accHolder} (${bankName})`;

                if (bankBox) bankBox.style.display = 'block';
            } else {
                if (bankBox) bankBox.style.display = 'none';
            }
        }
    </script>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('swal_payment_success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let paidAmount = "{{ number_format(session('paid_amount'), 0, ',', '.') }}";
                let token = "{{ session('token_reservasi') }}";

                Swal.fire({
                    icon: 'success',
                    title: 'Bukti Pembayaran Terkirim!',
                    text: 'Konfirmasi pembayaran sebesar Rp ' + paidAmount +
                        ' telah kami terima. Mohon tunggu verifikasi status dari admin.',
                    confirmButtonText: 'Cek Status Reservasi',
                    confirmButtonColor: '#191512'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('reservasi.result') }}?token=" + token;
                    }
                });
            });
        </script>
    @endif
@endsection
