<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\PhotographyPackage;
use App\Models\Rekening;
use App\Models\Reservation;
use App\Models\StudioProfile;
use App\Support\SessionDuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ReservasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $packages = PhotographyPackage::all();
        $weddingPackageIds = $packages
            ->filter(fn (PhotographyPackage $package): bool => $this->isWeddingPackage($package))
            ->modelKeys();
        $selectedPackageId = $request->query('package_id');

        return view('reservasi.reservasi', compact('packages', 'selectedPackageId', 'weddingPackageIds'));
    }

    /**
     * Mengambil daftar jadwal yang sudah terisi untuk keperluan kalender (API JSON).
     */
    public function getBookedSchedules()
    {
        $reservations = Reservation::with(['payment', 'package'])
            ->whereHas('payment', function ($q) {
                $q->where('status', '!=', 'Dibatalkan');
            })
            ->get()
            ->map(function ($res) {
                return [
                    'date' => $res->date,
                    'time' => $res->time,
                    'duration' => $res->package?->duration ?? $res->duration,
                    'duration_seconds' => $res->sessionDurationInSeconds(),
                    'is_wedding' => $this->isWeddingPackage($res->package),
                ];
            });

        return response()->json($reservations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'photography_package_id' => 'required|exists:photography_packages,id',
            'name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'duration' => 'required|string',
            'location' => [
                'required',
                'string',
                'max:255',
                'url',
                'regex:/^https:\/\/(?:(?:www\.)?google\.com\/maps\/|maps\.google\.com\/|maps\.app\.goo\.gl\/|goo\.gl\/maps\/)/i',
            ],
            'request_consultation' => 'nullable|boolean',
            'consultation_topic' => 'nullable|string',
            'consultation_custom_topic' => 'nullable|string',
            'consultation_media' => 'nullable|string',
            'consultation_offline_location' => 'nullable|string',
            'consultation_notes' => 'nullable|string',
            'source_from' => 'nullable|string',
        ]);

        $package = PhotographyPackage::findOrFail($validated['photography_package_id']);
        $validated['duration'] = $package->duration;
        $validated['token'] = 'RSV-'.strtoupper(Str::random(6));
        $isWeddingPackage = $this->isWeddingPackage($package);

        $newStart = strtotime($validated['date'].' '.$validated['time']);
        $newEnd = $newStart + SessionDuration::inSeconds($package->duration);
        $startMinute = ((int) date('G', $newStart) * 60) + (int) date('i', $newStart);
        $now = now();
        $isPastTimeToday = $validated['date'] === $now->toDateString() && $newStart <= $now->timestamp;

        if ($startMinute < 360 || $startMinute > 1080 || $startMinute % 30 !== 0 ||
            ($isPastTimeToday && ! $isWeddingPackage)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['time' => 'Pilih waktu mulai setiap 30 menit antara pukul 06.00–18.00.']);
        }

        $existingReservations = Reservation::where('date', $validated['date'])
            ->whereHas('payment', function ($q) {
                $q->where('status', '!=', 'Dibatalkan');
            })
            ->with('package')
            ->get();

        foreach ($existingReservations as $existing) {
            if ($this->isWeddingPackage($package) || $this->isWeddingPackage($existing->package)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['date' => 'Maaf, tanggal tersebut sudah memiliki jadwal pemotretan lain. Paket Wedding memerlukan tanggal yang sepenuhnya kosong.']);
            }

            $existStart = strtotime($existing->date.' '.$existing->time);
            $existEnd = $existStart + $existing->sessionDurationInSeconds();

            if ($newStart <= $existEnd && $newEnd > $existStart) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['time' => 'Maaf, jam pemotretan tersebut bentrok dengan jadwal lain yang sudah ada. Silakan pilih jam lain.']);
            }
        }

        // Simpan reservasi jika lolos validasi
        $reservasi = Reservation::create($validated);

        $isConsultation = $request->has('request_consultation') && $request->request_consultation == 1;
        $initialStatus = $isConsultation ? 'Masa konsultasi' : 'Menunggu pembayaran DP';

        // Inisialisasi data pembayaran
        Payment::create([
            'reservation_id' => $reservasi->id,
            'token' => $reservasi->token,
            'status' => $initialStatus,
            'tagihan' => $package->price,
            'total_paid' => 0,
            'description' => $isConsultation
                ? "Konsultasi & Paket: {$package->name} (Rp ".number_format($package->price, 0, ',', '.').')'
                : "Harga Paket Dasar: {$package->name} (Rp ".number_format($package->price, 0, ',', '.').')',
        ]);

        // Format pesan WhatsApp
        $studio = StudioProfile::first();
        $rawPhone = $studio ? $studio->phone : '081234567890';
        $adminPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        if (str_starts_with($adminPhone, '0')) {
            $adminPhone = '62'.substr($adminPhone, 1);
        }

        $pesan = $isConsultation
            ? "Halo Admin, saya ingin mengkonfirmasi *RESERVASI & KONSULTASI* pemotretan:\n\n"
            : "Halo Admin, saya ingin mengkonfirmasi *RESERVASI* pemotretan:\n\n";

        $pesan .= "🔑 *Token Reservasi*: {$reservasi->token}\n".
            "👤 *Nama*: {$reservasi->name}\n".
            "📱 *No WhatsApp*: {$reservasi->whatsapp}\n".
            "📦 *Paket*: {$package->name} (Rp ".number_format($package->price, 0, ',', '.').")\n".
            "📅 *Tanggal*: {$reservasi->date}\n".
            "⏰ *Jam*: {$reservasi->time}\n".
            "⏱️ *Durasi*: {$reservasi->duration}\n".
            "📍 *Lokasi*: {$reservasi->location}\n";

        if ($isConsultation) {
            $topicText = $request->consultation_topic;
            if ($topicText === 'Lainnya') {
                $topicText = 'Lainnya ('.($request->consultation_custom_topic ?? '-').')';
            }
            $mediaText = $request->consultation_media;
            if ($mediaText === 'Offline') {
                $mediaText = 'Offline (Lokasi: '.($request->consultation_offline_location ?? '-').')';
            }

            $pesan .= "\n💬 *DETAIL KONSULTASI*:\n".
                '- Topik Utama: '.$topicText."\n".
                '- Media Konsultasi: '.$mediaText."\n".
                '- Catatan / Ide: '.($request->consultation_notes ?? '-')."\n";
        }

        $pesan .= "\nMohon informasi selanjutnya untuk proses pembayaran. Terima kasih!";

        $encodedPesan = urlencode($pesan);
        $whatsappUrl = "https://wa.me/{$adminPhone}?text={$encodedPesan}";

        return redirect()->back()->with([
            'swal_success' => true,
            'token' => $reservasi->token,
            'whatsapp_url' => $whatsappUrl,
            'is_consultation' => $isConsultation,
        ]);
    }

    /**
     * Menampilkan halaman awal input cek status.
     */
    public function checkStatusForm()
    {
        return view('reservasi.cek');
    }

    /**
     * Memproses pencarian data berdasarkan token dan menampilkan hasilnya.
     */
    public function checkStatus(Request $request)
    {
        $token = $request->query('token');
        $payment = null;

        if ($token) {
            $payment = Payment::with(['reservation.package', 'reservation.testimonial', 'histories'])
                ->where('token', strtoupper(trim($token)))
                ->first();
        }

        return view('reservasi.cek', compact('payment', 'token'));
    }

    public function requestPhotoEdit(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'exists:payments,token'],
            'edit_request_message' => ['required', 'string', 'max:500'],
        ]);

        $payment = Payment::with('reservation')->where('token', strtoupper(trim($validated['token'])))->firstOrFail();
        $reservation = $payment->reservation;

        if ($payment->status !== 'Selesai') {
            throw ValidationException::withMessages([
                'edit_request_message' => 'Permintaan edit hanya dapat dikirim setelah pembayaran lunas dan hasil foto sudah dibagikan.',
            ]);
        }

        $reservation->edit_request_message = trim($validated['edit_request_message']);
        $reservation->save();

        return redirect()->route('reservasi.result', ['token' => $payment->token])
            ->with('success', 'Permintaan edit foto berhasil dikirim ke admin.');
    }

    /**
     * Menampilkan halaman upload pembayaran.
     */
    public function paymentPage(Request $request)
    {
        $token = $request->query('token');
        $payment = null;
        $studio = StudioProfile::first();

        $rekenings = Rekening::all();
        $qrisRekening = $rekenings->first(
            fn (Rekening $rekening): bool => filled($rekening->foto_qris)
        );

        if ($token) {
            $payment = Payment::with(['reservation.package', 'reservation'])
                ->where('token', strtoupper(trim($token)))
                ->first();
        }

        // Kirim $rekenings ke view detailPembayaran
        return view('reservasi.detailPembayaran', compact('payment', 'token', 'studio', 'rekenings', 'qrisRekening'));
    }

    /**
     * Menangani Upload Bukti Pembayaran Lanjutan (DP / Pelunasan) oleh Klien.
     */
    public function storePaymentHistory(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'exists:payments,token'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', Rule::in(['Transfer Bank', 'QRIS', 'Cash'])],
            'receipt_photo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        $file = $request->file('receipt_photo');
        $paymentToken = $validated['token'];
        $receiptPath = null;
        $amount = (float) $validated['amount'];
        $isLunas = false;

        DB::transaction(function () use ($validated, $file, $paymentToken, $amount, &$isLunas, &$receiptPath): void {
            $payment = Payment::where('token', $paymentToken)->lockForUpdate()->firstOrFail();
            $reservation = $payment->reservation;

            if ($payment->histories()->where('status', 'menunggu_verifikasi')->exists()) {
                throw ValidationException::withMessages([
                    'receipt_photo' => 'Bukti pembayaran sebelumnya masih menunggu pemeriksaan admin.',
                ]);
            }

            $isDeposit = $payment->status === 'Menunggu pembayaran DP';
            $isBalancePayment = $payment->status === 'Menunggu pelunasan'
                && $reservation->canSubmitBalancePayment();

            if (! $isDeposit && ! $isBalancePayment) {
                throw ValidationException::withMessages([
                    'token' => 'Pembayaran belum dapat dilakukan pada tahap reservasi saat ini.',
                ]);
            }

            $remaining = max(0, (float) $payment->tagihan - (float) $payment->total_paid);

            if ($amount > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => 'Nominal pembayaran melebihi sisa tagihan.',
                ]);
            }

            if ($isDeposit && $amount < (float) $payment->tagihan / 2) {
                throw ValidationException::withMessages([
                    'amount' => 'Pembayaran DP minimal 50% dari total tagihan.',
                ]);
            }

            if ($isBalancePayment && $amount < $remaining) {
                throw ValidationException::withMessages([
                    'amount' => 'Pelunasan harus menutup seluruh sisa tagihan.',
                ]);
            }

            $receiptPath = $file->storePublicly('receipts', 'public');
            if ($receiptPath === false) {
                throw new RuntimeException('Bukti pembayaran gagal disimpan.');
            }

            PaymentHistory::create([
                'payment_id' => $payment->id,
                'amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'receipt_photo' => $receiptPath,
                'notes' => $validated['notes'] ?? null,
                'status' => 'menunggu_verifikasi',
            ]);

            $payment->status = $isBalancePayment
                ? 'Verifikasi pelunasan'
                : 'Konfirmasi pembayaran';
            $payment->save();
            $isLunas = $payment->total_paid + $amount >= $payment->tagihan;
        });

        return redirect()->route('reservasi.pembayaran', ['token' => $paymentToken])->with([
            'swal_payment_success' => true,
            'paid_amount' => $amount,
            'is_lunas' => $isLunas,
            'token_reservasi' => $paymentToken,
        ]);
    }

    private function isWeddingPackage(?PhotographyPackage $package): bool
    {
        if ($package === null) {
            return false;
        }

        $normalizedName = preg_replace('/[\s_-]+/', ' ', strtolower($package->name));

        if (preg_match('/\bpre\s*wedding\b/', $normalizedName) === 1) {
            return false;
        }

        return (bool) $package->is_wedding || preg_match('/\bwedding\b/', $normalizedName) === 1;
    }
}
