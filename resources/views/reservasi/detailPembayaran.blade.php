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

        @if(!$payment)
            <div class="fc" id="bmiss">
                <h3>Pembayaran tidak tersedia</h3>
                <p id="bmsg" style="margin-bottom: 18px">Kode reservasi tidak valid atau tidak ditemukan.</p>
                <a class="btn ink" href="{{ route('reservasi.cek') }}">Ke Cek Reservasi</a>
            </div>
        @else
            <div class="fc" id="bform">
                <h3 id="bt">Pembayaran untuk Token: {{ $payment->token }}</h3>
                
                @if ($errors->any())
                    <div class="alert alert-danger" style="margin-bottom: 15px; color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px;">
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
                        <i>Status Saat Ini</i><span>{{ $payment->status }}</span>
                        <i>Total Tagihan</i><span>Rp {{ number_format($payment->tagihan, 0, ',', '.') }}</span>
                        <i>Sudah Dibayar</i><span>Rp {{ number_format($payment->total_paid, 0, ',', '.') }}</span>
                        <i>Sisa Kekurangan</i><span style="font-weight: bold; color: #d9534f;">Rp {{ number_format(max(0, $payment->tagihan - $payment->total_paid), 0, ',', '.') }}</span>
                        <i>Rekening Resmi</i><span>BANK BCA 123-456-7890<br />a.n. Liquif Picture</span>
                    </div>

                    <div class="fld" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Nominal yang Dibayarkan (Rp)</label>
                        <input type="number" name="amount" placeholder="Cth: 500000" required style="width: 100%; padding: 12px; border: 1px solid #d1c7bd; background: transparent; border-radius: 4px;" value="{{ max(0, $payment->tagihan - $payment->total_paid) }}">
                    </div>

                    <div class="fld" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Metode Pembayaran</label>
                        <select name="payment_method" required style="width: 100%; padding: 12px; border: 1px solid #d1c7bd; background: transparent; border-radius: 4px;">
                            <option value="QRIS">QRIS</option>
                            <option value="Transfer BCA">Transfer BCA</option>
                            <option value="Transfer Mandiri">Transfer Mandiri</option>
                            <option value="Cash">Cash / Tunai di Studio</option>
                        </select>
                    </div>

                    <div class="fld" style="margin-bottom: 20px;">
                        <label class="drop" style="display: block; border: 2px dashed #d1c7bd; padding: 24px; text-align: center; cursor: pointer; border-radius: 6px; background: rgba(0,0,0,0.01);">
                            <span class="dn" id="file-label-text">Ketuk untuk memilih bukti pembayaran</span><br />
                            <span class="note" style="font-size: 12px; color: #777;">JPG / PNG / PDF · maks. 2 MB</span>
                            <input type="file" name="receipt_photo" accept=".jpg,.jpeg,.png,.pdf,image/*" required style="display: none;" onchange="document.getElementById('file-label-text').innerText = this.files[0].name;" />
                        </label>
                    </div>

                    <div class="fld" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Catatan (Opsional)</label>
                        <textarea name="notes" rows="2" placeholder="Cth: Transfer atas nama Budi" style="width: 100%; padding: 12px; border: 1px solid #d1c7bd; background: transparent; border-radius: 4px;"></textarea>
                    </div>

                    <p class="note" style="font-size: 13px; color: #666; margin-bottom: 24px;">
                        Transfer hanya ke rekening resmi di atas. Kami tidak pernah meminta OTP atau PIN.
                    </p>

                    <div class="acts" style="display: flex; gap: 12px; align-items: center;">
                        <button type="submit" class="btn ink">
                            Kirim bukti
                        </button>
                        <a class="btn" href="{{ route('reservasi.result', ['token' => $payment->token]) }}" style="text-decoration: none; padding: 12px 24px; display: inline-block;">Kembali</a>
                    </div>
                </form>
            </div>
        @endif
    </div>
</main>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('swal_payment_success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let paidAmount = "{{ number_format(session('paid_amount'), 0, ',', '.') }}";
            let isLunas = {{ session('is_lunas') ? 'true' : 'false' }};
            let token = "{{ session('token_reservasi') }}";

            if (isLunas) {
                Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Lunas!',
                    text: 'Berhasil melakukan pembayaran sebesar Rp ' + paidAmount + '. Terima kasih, tagihan Anda sudah LUNAS!',
                    confirmButtonText: 'Cek Status Reservasi',
                    confirmButtonColor: '#191512'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('reservasi.result') }}?token=" + token;
                    }
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Bukti Berhasil Dikirim!',
                    text: 'Berhasil melakukan pembayaran sebesar Rp ' + paidAmount + '. Silakan lakukan pelunasan sebelum hari pemotretan.',
                    confirmButtonText: 'Cek Status Reservasi',
                    confirmButtonColor: '#191512'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('reservasi.result') }}?token=" + token;
                    }
                });
            }
        });
    </script>
@endif
@endsection