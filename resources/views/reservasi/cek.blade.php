@extends('template.main')

@section('konten_detail')
    <main class="pg" style="background: #F3EDE3;
    background: linear-gradient(180deg, rgba(37, 35, 31, 1) 80px, #F3EDE3 80px);">
        <div class="wrap" style="max-width: 880px">
            <div class="crumb">
                <a href="{{ route('reservasi.index') }}">Beranda</a> / Cek Reservasi
            </div>
            <h1>Cek <em>reservasi</em></h1>
            <p class="lead">
                Masukkan kode reservasi unik Anda untuk melihat status,
                membayar, dan membuka hasil foto.
            </p>
            
            @if(session('success_payment'))
                <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px; background: #d4edda; color: #155724; border-radius: 4px;">
                    {{ session('success_payment') }}
                </div>
            @endif

            <!-- Form Input Token -->
            <form action="{{ route('reservasi.result') }}" method="GET">
                <div class="fc" style="padding: 24px 30px; background: #fbf9f5; border: 1px solid #e6dec5; border-radius: 8px; margin-top: 20px;">
                    <div class="fld" style="margin: 0; position: relative;">
                        <label style="display: block; font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase; color: #8c8275; margin-bottom: 8px; font-weight: 600;">
                            Kode Reservasi
                        </label>
                        
                        <div class="row-in" style="display: flex; gap: 15px; align-items: flex-end;">
                            <input class="kc" id="kc" name="token" placeholder="LQ7K4M9RTX" maxlength="14"
                                autocomplete="off" value="{{ $token ?? '' }}" required 
                                style="flex: 1; border: none; border-bottom: 1px solid #c8bcab; background: transparent; font-family: inherit; font-size: 22px; color: #2c2825; padding: 4px 0; outline: none; text-transform: uppercase; letter-spacing: 1px;" />
                            
                            <button class="btn ink" type="submit" style="background: #191512; color: #fbf9f5; padding: 12px 28px; border-radius: 30px; border: none; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px; letter-spacing: 1px; transition: background 0.2s;">
                                CEK
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            @if(request('token') && !$payment)
                <div class="alert alert-danger" style="margin-top: 24px; padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px;">
                    Kode reservasi <strong>{{ $token }}</strong> tidak ditemukan. Silakan periksa kembali.
                </div>
            @endif

            @if(isset($payment) && $payment)
                <div id="kres" style="margin-top: 32px">
                    <div class="fc" style="padding: 30px; background: #fbf9f5; border: 1px solid #e6dec5; border-radius: 8px;">
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                            <h3 style="margin: 0;">Status reservasi</h3>
                            
                            <!-- ✨ TAMPILAN STATUS BARU YANG LEBIH ELEGAN DAN ESTETIK -->
                            <div style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 50px; font-size: 12px; font-weight: 600; letter-spacing: 1px;
                                @if($payment->status == 'LUNAS') background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7;
                                @elseif($payment->status == 'DP') background: #fff8e1; color: #f57f17; border: 1px solid #ffe082;
                                @else background: #e0f7fa; color: #00838f; border: 1px solid #80deea; @endif">
                                
                                <span style="width: 8px; height: 8px; border-radius: 50%; 
                                    @if($payment->status == 'LUNAS') background: #2e7d32;
                                    @elseif($payment->status == 'DP') background: #f57f17;
                                    @else background: #00838f; @endif">
                                </span>
                                
                                STATUS: {{ strtoupper($payment->status) }}
                            </div>
                        </div>

                        <div class="kv">
                            <i>Nama</i><span>{{ $payment->reservation->name }}</span>
                            <i>Paket</i><span>{{ $payment->reservation->package->name }}</span>
                            <i>Jadwal</i><span>{{ $payment->reservation->date }} ({{ $payment->reservation->time }})</span>
                            <i>Lokasi</i><span>{{ $payment->reservation->location }}</span>
                            <i>Total Tagihan</i><span>Rp {{ number_format($payment->tagihan, 0, ',', '.') }}</span>
                            <i>Sudah Dibayar</i><span>Rp {{ number_format($payment->total_paid, 0, ',', '.') }}</span>
                            <i>Sisa Kekurangan</i><span>Rp {{ number_format(max(0, $payment->tagihan - $payment->total_paid), 0, ',', '.') }}</span>
                            <i>Status Pembayaran</i><span>{{ $payment->status }}</span>
                        </div>
                        
                        <p class="note" id="r-msg" style="margin-top: 20px;">
                            @if($payment->status == 'KONSULTASI')
                                Reservasi Anda sedang dalam tahap konsultasi. Silakan tunggu arahan admin atau lakukan pembayaran DP jika sudah sepakat.
                            @elseif($payment->status == 'DP')
                                Silahkan Lakukan proses pembayaran & pelunasan sebelum jadwal pemotretan tiba.
                            @else
                                Pembayaran Anda telah lunas. Terima kasih!
                            @endif
                        </p>

                        <p class="note lock" id="r-lock" style="margin-top: 10px;">
                            @if($payment->status == 'LUNAS')
                                🎉 Hasil foto terbuka karena pelunasan terverifikasi.
                            @else
                                🔒 Hasil foto terbuka setelah status “LUNAS” (pelunasan terverifikasi).
                            @endif
                        </p>

                        <div class="acts" style="margin-top: 24px; display: flex; gap: 10px;">
                            @if($payment->status != 'LUNAS')
                                <a href="{{ route('reservasi.pembayaran', ['token' => $payment->token]) }}" class="btn ink" style="background: #191512; color: #fff; padding: 12px 24px; border-radius: 30px; text-decoration: none; font-size: 14px; font-weight: 500;">
                                    {{ $payment->status == 'KONSULTASI' ? 'Bayar DP / Lanjut →' : 'Bayar Pelunasan' }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </main>
@endsection