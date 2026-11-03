@extends('template.main')

@section('konten_detail')
    <main class="pg">
        <div class="wrap" style="max-width: 760px">
            <div class="crumb">
                <a href="{{ route('reservasi.cek') }}">Cek Reservasi</a> / Pembayaran
            </div>
            <h1>Pem<em>bayaran</em></h1>
            <p class="lead">
                Unggah bukti transfer. Admin akan memverifikasinya dan
                status Anda akan diperbarui.
            </p>

            @if (!$payment)
                <div class="fc" id="bmiss">
                    <h3>Pembayaran tidak tersedia</h3>
                    <p id="bmsg" style="margin-bottom: 18px">Kode reservasi tidak valid atau tidak ditemukan.</p>
                    <a class="btn ink" href="{{ route('reservasi.cek') }}">Ke Cek Reservasi</a>
                </div>
            @else
                <div class="fc" id="bform">
                    <h3 id="bt">Pembayaran untuk Token: {{ $payment->token }}</h3>

                    @if ($errors->any())
                        <div class="alert alert-danger"
                            style="margin-bottom: 15px; color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px;">
                            <ul style="margin: 0; padding-left: 15px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('reservasi.payment.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="token" value="{{ $payment->token }}">

                        <div class="kv" style="margin-bottom: 24px;">
                            <i>Kode</i><span>{{ $payment->token }}</span>
                            <i>Status Saat Ini</i><span><strong>{{ $payment->status }}</strong></span>
                            <i>Total Tagihan</i><span>Rp {{ number_format($payment->tagihan, 0, ',', '.') }}</span>
                            <i>Sudah Dibayar</i><span>Rp {{ number_format($payment->total_paid, 0, ',', '.') }}</span>
                            <i>Sisa Kekurangan</i><span style="font-weight: bold; color: #d9534f;">Rp
                                {{ number_format(max(0, $payment->tagihan - $payment->total_paid), 0, ',', '.') }}</span>
                        </div>

                        <div class="fld" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Metode Pembayaran</label>
                            <select name="payment_method" id="payment_method" required onchange="togglePaymentDetails()"
                                style="width: 100%; padding: 12px; border: 1px solid #d1c7bd; background: transparent; border-radius: 4px;">
                                <option value="" selected disabled>-- Pilih Metode Pembayaran --</option>
                                <option value="Transfer Bank">Transfer Bank (Rekening Studio)</option>
                                <option value="QRIS">QRIS (GoPay, OVO, Dana, M-Banking)</option>
                                <option value="Cash">Cash / Tunai di Studio</option>
                            </select>
                        </div>

                        <!-- Detail Rekening Bank Dinamis -->
                        <div id="bank_details_container"
                            style="display: none; margin-bottom: 20px; padding: 16px; background: #f9f9f9; border: 1px solid #d1c7bd; border-radius: 6px;">
                            <h4 style="margin-top: 0; font-size: 16px;">Informasi Rekening Bank</h4>
                            <p style="margin-bottom: 4px; color: #555;">Silakan transfer ke rekening resmi berikut:</p>
                            <strong
                                style="font-size: 18px; display: block; color: #191512;">{{ $studio->no_rekening ?? '123-456-7890' }}</strong>
                            <span style="font-size: 13px; color: #666;">a.n.
                                {{ $studio->nama_pemilik ?? 'Liquif Picture' }} ({{ $studio->nama_bank ?? 'BCA' }})</span>
                        </div>

                        <!-- Detail Gambar QRIS Dinamis -->
                        <div id="qris_details_container"
                            style="display: none; margin-bottom: 20px; padding: 16px; background: #f9f9f9; border: 1px solid #d1c7bd; border-radius: 6px; text-align: center;">
                            <h4 style="margin-top: 0; font-size: 16px;">Scan QRIS Liquif Picture</h4>
                            <div style="margin: 12px 0;">
                                @if (!empty($studio->foto_qris))
                                    <img src="{{ asset('storage/' . $studio->foto_qris) }}" alt="QRIS"
                                        style="max-width: 200px; height: auto; border-radius: 6px;">
                                @else
                                    <img src="{{ asset('Assets/img/qris-default.png') }}" alt="QRIS"
                                        style="max-width: 200px; height: auto; border-radius: 6px;">
                                @endif
                            </div>
                            <span style="font-size: 12px; color: #666;">Dapat di-scan dengan seluruh aplikasi e-Wallet &
                                Mobile Banking</span>
                        </div>

                        <div class="fld" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Nominal yang Dibayarkan
                                (Rp)</label>
                            <input type="number" name="amount" placeholder="Cth: 500000" required
                                style="width: 100%; padding: 12px; border: 1px solid #d1c7bd; background: transparent; border-radius: 4px;"
                                value="{{ max(0, $payment->tagihan - $payment->total_paid) }}">
                        </div>

                        <!-- Box Upload Bukti (Dinamis: Opsional jika Cash) -->
                        <div class="fld" id="receipt_container" style="margin-bottom: 20px;">
                            <label class="drop"
                                style="display: block; border: 2px dashed #d1c7bd; padding: 24px; text-align: center; cursor: pointer; border-radius: 6px; background: rgba(0,0,0,0.01);">
                                <span class="dn" id="file-label-text">Ketuk untuk memilih bukti pembayaran</span><br />
                                <span class="note" style="font-size: 12px; color: #777;">JPG / PNG / PDF · maks. 2
                                    MB</span>
                                <input type="file" name="receipt_photo" id="receipt_photo"
                                    accept=".jpg,.jpeg,.png,.pdf,image/*" required style="display: none;"
                                    onchange="document.getElementById('file-label-text').innerText = this.files[0].name;" />
                            </label>
                        </div>

                        <div class="fld" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Catatan (Opsional)</label>
                            <textarea name="notes" rows="2" placeholder="Cth: Transfer atas nama Budi / Pembayaran Tunai di lokasi"
                                style="width: 100%; padding: 12px; border: 1px solid #d1c7bd; background: transparent; border-radius: 4px;"></textarea>
                        </div>

                        <p class="note" style="font-size: 13px; color: #666; margin-bottom: 24px;">
                            Transfer hanya ke rekening resmi di atas. Kami tidak pernah meminta OTP atau PIN.
                        </p>

                        <div class="acts" style="display: flex; gap: 12px; align-items: center;">
                            <button type="submit" class="btn ink">
                                Kirim bukti
                            </button>
                            <a class="btn" href="{{ route('reservasi.result', ['token' => $payment->token]) }}"
                                style="text-decoration: none; padding: 12px 24px; display: inline-block;">Kembali</a>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </main>

    <script>
        function togglePaymentDetails() {
            const method = document.getElementById('payment_method').value;
            const bankBox = document.getElementById('bank_details_container');
            const qrisBox = document.getElementById('qris_details_container');
            const receiptInput = document.getElementById('receipt_photo');

            bankBox.style.display = 'none';
            qrisBox.style.display = 'none';

            if (method === 'Transfer Bank') {
                bankBox.style.display = 'block';
                receiptInput.required = true;
            } else if (method === 'QRIS') {
                qrisBox.style.display = 'block';
                receiptInput.required = true;
            } else if (method === 'Cash') {
                // Untuk pembayaran Cash, bukti foto tidak diwajibkan
                receiptInput.required = false;
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
