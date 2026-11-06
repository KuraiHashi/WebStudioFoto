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

            @if(session('testimonial_success'))
                <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px; background: #d4edda; color: #155724; border-radius: 4px;">
                    {{ session('testimonial_success') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px; background: #d4edda; color: #155724; border-radius: 4px;">
                    {{ session('success') }}
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
                @if($errors->any())
                    <div class="alert alert-danger" style="margin-top: 24px; padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px;">
                        <ul style="margin: 0; padding-left: 18px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @php
                    $reservation = $payment->reservation;
                    $canPay = $payment->status === 'Menunggu pembayaran DP'
                        || $payment->status === 'Menunggu pelunasan' && $reservation->canSubmitBalancePayment();
                    $statusMessage = match ($payment->status) {
                        'Masa konsultasi' => 'Reservasi menunggu konsultasi selesai. Admin akan mengaktifkan pembayaran DP setelah kesepakatan.',
                        'Menunggu pembayaran DP' => 'Silakan bayar DP minimal 50% dan kirim bukti. Jadwal dikunci setelah DP diverifikasi admin.',
                        'Konfirmasi pembayaran' => 'Bukti DP sudah diterima dan sedang diperiksa admin. Anda belum perlu mengirim bukti lagi.',
                        'Menunggu pelunasan' => $reservation->canSubmitBalancePayment()
                            ? 'Jadwal sesi pemotretan telah berakhir. Silakan lunasi sisa tagihan dan kirim bukti pelunasan.'
                            : 'DP sudah disetujui dan jadwal Anda tercatat. Form pelunasan tersedia setelah waktu sesi pemotretan berakhir.',
                        'Verifikasi pelunasan' => 'Bukti pelunasan sudah diterima dan sedang diperiksa admin.',
                        'Selesai' => match ($reservation->photo_status) {
                            'diproses' => 'Pembayaran lunas. Hasil foto sedang diproses oleh studio.',
                            'siap' => 'Hasil foto sudah siap. Gunakan tautan di bawah untuk membukanya.',
                            default => 'Pembayaran lunas. Reservasi masuk ke antrean hasil foto studio.',
                        },
                        'Dibatalkan' => 'Reservasi ini telah dibatalkan. Hubungi studio jika Anda memerlukan bantuan.',
                        default => 'Hubungi studio untuk informasi lebih lanjut mengenai reservasi Anda.',
                    };
                @endphp
                <div id="kres" style="margin-top: 32px">
                    <div class="fc" style="padding: 30px; background: #fbf9f5; border: 1px solid #e6dec5; border-radius: 8px;">
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                            <h3 style="margin: 0;">Status reservasi</h3>
                            <div style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 50px; font-size: 12px; font-weight: 600; letter-spacing: 1px;
                                @if($payment->status == 'Selesai') background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7;
                                @elseif($payment->status == 'Dibatalkan') background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;
                                @else background: #e0f7fa; color: #00838f; border: 1px solid #80deea; @endif">
                                <span style="width: 8px; height: 8px; border-radius: 50%;
                                    @if($payment->status == 'Selesai') background: #2e7d32;
                                    @elseif($payment->status == 'Dibatalkan') background: #721c24;
                                    @else background: #00838f; @endif">
                                </span>
                                {{ $payment->status }}
                            </div>
                        </div>

                        <div class="kv">
                            <i>Nama</i><span>{{ $reservation->name }}</span>
                            <i>Paket</i><span>{{ $reservation->package->name }}</span>
                            <i>Jadwal</i><span>{{ $reservation->date }} ({{ $reservation->time }})</span>
                            <i>Lokasi</i>
                            <span>
                                @if (\Illuminate\Support\Str::startsWith($reservation->location, [
                                        'https://www.google.com/maps/',
                                        'https://maps.google.com/maps/',
                                        'https://maps.app.goo.gl/',
                                        'https://goo.gl/maps/',
                                    ]))
                                    <a href="{{ $reservation->location }}" target="_blank" rel="noopener noreferrer">
                                        Buka lokasi di Google Maps
                                    </a>
                                @else
                                    {{ $reservation->location }}
                                @endif
                            </span>
                            <i>Total Tagihan</i><span>Rp {{ number_format($payment->tagihan, 0, ',', '.') }}</span>
                            <i>Sudah Dibayar</i><span>Rp {{ number_format($payment->total_paid, 0, ',', '.') }}</span>
                            <i>Sisa Kekurangan</i><span>Rp {{ number_format(max(0, $payment->tagihan - $payment->total_paid), 0, ',', '.') }}</span>
                            @if($reservation->session_completed_at)
                                <i>Sesi</i><span>Selesai</span>
                            @endif
                            @if($payment->status === 'Selesai')
                                <i>Hasil foto</i><span>{{ ['belum_diproses' => 'Menunggu diproses', 'diproses' => 'Sedang diproses', 'siap' => 'Siap'][$reservation->photo_status] ?? 'Menunggu diproses' }}</span>
                            @endif
                        </div>

                        <p class="note" id="r-msg" style="margin-top: 20px;">{{ $statusMessage }}</p>

                        <div class="acts" style="margin-top: 24px; display: flex; gap: 10px; flex-wrap: wrap;">
                            @if($canPay)
                                <a href="{{ route('reservasi.pembayaran', ['token' => $payment->token]) }}" class="btn ink" style="background: #191512; color: #fff; padding: 12px 24px; border-radius: 30px; text-decoration: none; font-size: 14px; font-weight: 500;">
                                    {{ $payment->status === 'Menunggu pembayaran DP' ? 'Bayar DP' : 'Bayar pelunasan' }}
                                </a>
                            @endif
                            @if($payment->status === 'Selesai' && $reservation->photo_delivery_url)
                                <a href="{{ $reservation->photo_delivery_url }}" class="btn ink" target="_blank" rel="noopener noreferrer">Buka hasil foto</a>
                            @endif
                            @if($payment->status === 'Selesai' && $reservation->edited_photo_url)
                                <a href="{{ $reservation->edited_photo_url }}" class="btn ink" target="_blank" rel="noopener noreferrer">Lihat hasil edit</a>
                            @endif
                        </div>

                        @if($payment->status === 'Selesai' && $reservation->photo_delivery_url)
                            <div class="fc" style="margin-top: 20px; padding: 18px 20px; background: #fff; border: 1px solid #e6dec5; border-radius: 8px;">
                                <h4 style="margin-top: 0; margin-bottom: 10px;">Permintaan edit foto</h4>
                                @if($reservation->edit_request_message)
                                    <p class="note" style="margin-bottom: 12px;">Permintaan Anda: <strong>{{ $reservation->edit_request_message }}</strong></p>
                                @endif
                                <form action="{{ route('reservasi.request-edit') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ $payment->token }}">
                                    <div class="fld" style="margin-bottom: 12px;">
                                        <label for="edit-request-message">Tuliskan foto mana yang ingin diedit</label>
                                        <textarea id="edit-request-message" name="edit_request_message" rows="3" maxlength="500" required placeholder="Contoh: Saya ingin edit foto dengan nama file nikah agar lebih natural dan crop lebih rapih." style="width: 100%; padding: 12px; border: 1px solid #d1c7bd; border-radius: 6px; font-family: inherit;">{{ old('edit_request_message', $reservation->edit_request_message ?? '') }}</textarea>
                                    </div>
                                    <button type="submit" class="btn ink" style="background: #191512; color: #fff; padding: 12px 24px; border-radius: 30px; border: none; font-weight: 500; cursor: pointer;">
                                        Kirim permintaan edit
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    @if($reservation->testimonial)
                        <div class="fc" style="margin-top: 20px; padding: 24px 30px; background: #fbf9f5; border: 1px solid #e6dec5; border-radius: 8px;">
                            <h3 style="margin-top: 0;">Testimoni Anda sudah diterima</h3>
                            <div class="st">{{ str_repeat('★', $reservation->testimonial->rating) }}{{ str_repeat('☆', 5 - $reservation->testimonial->rating) }}</div>
                            <p style="margin-bottom: 0;">{{ $reservation->testimonial->message }}</p>
                        </div>
                    @elseif($payment->canSubmitTestimonial())
                        <div class="fc" style="margin-top: 20px; padding: 24px 30px; background: #fbf9f5; border: 1px solid #e6dec5; border-radius: 8px;">
                            <h3 style="margin-top: 0;">Bagikan pengalaman Anda</h3>
                            <p class="note">Ceritakan pengalaman Anda kepada calon pelanggan lain.</p>
                            <form action="{{ route('reservasi.testimoni.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="token" value="{{ $payment->token }}">

                                <fieldset class="fld testimonial-rating">
                                    <legend class="testimonial-rating__legend">Rating</legend>
                                    <div class="testimonial-rating__stars" role="radiogroup" aria-label="Pilih rating">
                                        @for($rating = 5; $rating >= 1; $rating--)
                                            <input class="testimonial-rating__input" type="radio" id="rating-{{ $rating }}" name="rating" value="{{ $rating }}" required @checked((int) old('rating') === $rating)>
                                            <label class="testimonial-rating__star" for="rating-{{ $rating }}" aria-label="{{ $rating }} bintang">
                                                <span aria-hidden="true">★</span>
                                            </label>
                                        @endfor
                                    </div>
                                    <p class="testimonial-rating__hint">Pilih 1 sampai 5 bintang</p>
                                </fieldset>

                                <div class="fld" style="margin-bottom: 16px;">
                                    <label for="testimonial-message" style="display: block; margin-bottom: 8px;">Testimoni</label>
                                    <textarea id="testimonial-message" name="message" rows="4" minlength="10" maxlength="1000" required placeholder="Ceritakan pengalaman Anda (minimal 10 karakter)" style="width: 100%; padding: 12px; border: 1px solid #d1c7bd; background: #fff; border-radius: 6px; font-family: inherit;">{{ old('message') }}</textarea>
                                </div>

                                <button type="submit" class="btn ink" style="background: #191512; color: #fff; padding: 12px 24px; border-radius: 30px; border: none; font-weight: 500; cursor: pointer;">
                                    Kirim testimoni
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </main>
@endsection