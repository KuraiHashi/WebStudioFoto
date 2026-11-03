<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status & Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <!-- Kotak Pencarian Token -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="card-title mb-3 text-center fw-bold">Cek Status Reservasi & Pembayaran</h3>
                        <p class="text-muted text-center mb-4">Masukkan Token Reservasi Anda (Cth: RSV-XXXXXX)</p>

                        <form action="{{ route('reservasi.status') }}" method="GET" class="input-group mb-3">
                            <input type="text" name="token" class="form-control form-control-lg text-uppercase" placeholder="Masukkan Token" value="{{ $token ?? '' }}" required>
                            <button class="btn btn-primary px-4" type="submit">Cari</button>
                        </form>

                        @if(request('token') && !$payment)
                            <div class="alert alert-danger mt-3 mb-0">Token <strong>{{ $token }}</strong> tidak ditemukan.</div>
                        @endif
                    </div>
                </div>

                @if($payment)
                    <!-- Detail Informasi Reservasi -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                            <h5 class="mb-0">Token: {{ $payment->token }}</h5>
                            <span class="badge 
                                @if($payment->status == 'LUNAS') bg-success 
                                @elseif($payment->status == 'DP') bg-warning text-dark 
                                @else bg-info text-dark @endif px-3 py-2">
                                Status: {{ $payment->status }}
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <span class="text-muted small">Nama Pemesan:</span>
                                    <h6 class="fw-bold">{{ $payment->reservation->name }}</h6>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted small">Paket:</span>
                                    <h6 class="fw-bold">{{ $payment->reservation->package->name }}</h6>
                                </div>
                            </div>

                            <hr>

                            <!-- Ringkasan Keuangan -->
                            <div class="row text-center bg-light p-3 rounded mb-4">
                                <div class="col-sm-4">
                                    <span class="text-muted small d-block">Total Tagihan</span>
                                    <h5 class="fw-bold text-dark">Rp {{ number_format($payment->tagihan, 0, ',', '.') }}</h5>
                                </div>
                                <div class="col-sm-4">
                                    <span class="text-muted small d-block">Sudah Dibayar</span>
                                    <h5 class="fw-bold text-success">Rp {{ number_format($payment->total_paid, 0, ',', '.') }}</h5>
                                </div>
                                <div class="col-sm-4">
                                    <span class="text-muted small d-block">Sisa Kekurangan</span>
                                    <h5 class="fw-bold text-danger">Rp {{ number_format(max(0, $payment->tagihan - $payment->total_paid), 0, ',', '.') }}</h5>
                                </div>
                            </div>

                            @if($payment->description)
                                <div class="mb-3">
                                    <span class="text-muted small">Rincian Tagihan:</span>
                                    <div class="p-2 bg-white border rounded text-secondary small">{{ $payment->description }}</div>
                                </div>
                            @endif

                            <!-- Tabel Riwayat Detail Pembayaran (Payment Histories) -->
                            <h5 class="fw-bold mt-4 mb-3">Riwayat Bukti Pembayaran</h5>
                            @if($payment->histories->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Nominal</th>
                                                <th>Metode</th>
                                                <th>Bukti</th>
                                                <th>Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($payment->histories as $history)
                                                <tr>
                                                    <td>{{ $history->created_at->format('d/m/Y H:i') }}</td>
                                                    <td class="fw-bold text-success">Rp {{ number_format($history->amount, 0, ',', '.') }}</td>
                                                    <td>{{ $history->payment_method }}</td>
                                                    <td>
                                                        <a href="{{ asset($history->receipt_photo) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Foto</a>
                                                    </td>
                                                    <td>{{ $history->notes ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted italic">Belum ada riwayat pembayaran yang diunggah.</p>
                            @endif

                            <!-- Form Upload Bukti Bayar Lanjutan -->
                            @if($payment->status != 'LUNAS')
                                <div class="card border-primary mt-4">
                                    <div class="card-header bg-primary text-white fw-bold">Upload Bukti Pembayaran / Pelunasan</div>
                                    <div class="card-body">
                                        @if(session('success_payment'))
                                            <div class="alert alert-success">{{ session('success_payment') }}</div>
                                        @endif

                                        <form action="{{ route('reservasi.payment.store') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="token" value="{{ $payment->token }}">

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Nominal yang Dibayar (Rp)</label>
                                                <input type="number" name="amount" class="form-control" placeholder="Cth: 500000" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Metode Pembayaran</label>
                                                <select name="payment_method" class="form-select" required>
                                                    <option value="QRIS">QRIS</option>
                                                    <option value="Transfer BCA">Transfer BCA</option>
                                                    <option value="Transfer Mandiri">Transfer Mandiri</option>
                                                    <option value="Cash">Cash / Tunai di Studio</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Foto Bukti Transfer (Maks 2MB)</label>
                                                <input type="file" name="receipt_photo" class="form-control" accept="image/png, image/jpeg, image/jpg" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Catatan (Opsional)</label>
                                                <textarea name="notes" class="form-control" rows="2" placeholder="Cth: DP ditransfer atas nama Budi"></textarea>
                                            </div>

                                            <button type="submit" class="btn btn-success w-100 fw-bold py-2">Kirim Bukti Pembayaran</button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-success mt-4 text-center fw-bold">
                                    🎉 Tagihan ini sudah <strong>LUNAS</strong>!
                                </div>
                            @endif

                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>