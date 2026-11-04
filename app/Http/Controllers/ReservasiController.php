<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\PhotographyPackage;
use App\Models\Reservation;
use App\Models\StudioProfile;
use App\Models\Rekening;
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
        $selectedPackageId = $request->query('package_id');

        return view('reservasi.reservasi', compact('packages', 'selectedPackageId'));
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
                    'duration' => $res->duration,
                    'is_wedding' => $res->package->is_wedding ?? false,
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
            'date' => 'required|date',
            'time' => 'required',
            'duration' => 'required|string',
            'location' => 'required|string',
            'request_consultation' => 'nullable|boolean',
            'consultation_topic' => 'nullable|string',
            'consultation_custom_topic' => 'nullable|string',
            'consultation_media' => 'nullable|string',
            'consultation_offline_location' => 'nullable|string',
            'consultation_notes' => 'nullable|string',
            'source_from' => 'nullable|string',
        ]);

        // Auto-generate Token
        $validated['token'] = 'RSV-'.strtoupper(Str::random(6));

        // 1. Ambil data paket yang dipilih (menggunakan variabel $package)
        $package = PhotographyPackage::findOrFail($request->photography_package_id);
        
        preg_match('/\d+/', $package->duration, $matches);
        $durationHours = isset($matches[0]) ? (int) $matches[0] : 2; // Default 2 jam jika tidak ketemu

        // Hitung waktu mulai dan waktu selesai reservasi baru
        $newStart = strtotime($request->date.' '.$request->time);
        $newEnd = $newStart + ($durationHours * 3600); // 3600 detik = 1 jam

        // 2. Cek apakah ada reservasi aktif pada tanggal yang sama yang jamnya beririsan
        $existingReservations = Reservation::where('date', $request->date)
            ->whereHas('payment', function ($q) {
                $q->where('status', '!=', 'Dibatalkan');
            })
            ->with('package')
            ->get();

        foreach ($existingReservations as $existing) {
            // Hitung durasi reservasi yang sudah ada di database
            preg_match('/\d+/', $existing->package->duration ?? '2', $matchExist);
            $existDuration = isset($matchExist[0]) ? (int) $matchExist[0] : 2;

            $existStart = strtotime($existing->date.' '.$existing->time);
            $existEnd = $existStart + ($existDuration * 3600);

            // Jika di tanggal tersebut sudah ada Paket Wedding, tolak semua reservasi lain
            if ($existing->package->is_wedding ?? false) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['date' => 'Maaf, tanggal tersebut sudah dibooking penuh untuk acara Wedding. Tanggal tertutup untuk semua reservasi.']);
            }

            // ATURAN 2: Jika user saat ini ingin membooking Paket Wedding, 
            // tapi di tanggal tersebut sudah ada reservasi paket reguler lain, tolak.
            if ($package->is_wedding) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['date' => 'Maaf, tanggal tersebut sudah memiliki jadwal pemotretan lain. Paket Wedding memerlukan tanggal yang sepenuhnya kosong.']);
            }
        }

        // ATURAN 3: Jika sama-sama paket reguler (non-wedding), cek bentrok jam (overlapping) berdasarkan durasi
        if (!$package->is_wedding) {
            preg_match('/\d+/', $package->duration, $matchNew);
            $newHours = isset($matchNew[0]) ? (int)$matchNew[0] : 1;
            
            $newStart = strtotime($request->date . ' ' . $request->time);
            $newEnd = $newStart + ($newHours * 3600);

            foreach ($existingReservations as $exist) {
                if ($exist->package->is_wedding ?? false) continue;

                preg_match('/\d+/', $exist->package->duration ?? '1', $matchExist);
                $existHours = isset($matchExist[0]) ? (int)$matchExist[0] : 1;

                $existStart = strtotime($exist->date . ' ' . $exist->time);
                $existEnd = $existStart + ($existHours * 3600);

                // Rumus irisan waktu (Overlapping check)
                if ($newStart < $existEnd && $newEnd > $existStart) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['time' => 'Maaf, jam pemotretan (' . $request->time . ') bentrok dengan jadwal lain yang sudah ada di hari ini (' . $exist->time . ' - durasi ' . $exist->package->duration . '). Silakan pilih jam lain.']);
                }
            }
        }

        // Simpan reservasi jika lolos validasi
        $reservasi = Reservation::create($validated);

        $isConsultation = $request->has('request_consultation') && $request->request_consultation == 1;
        $initialStatus = $isConsultation ? 'Masa konsultasi' : 'Menunggu pembayaran DP';

        // Inisialisasi data pembayaran (menggunakan $package->price)
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

    /**
     * Menampilkan halaman upload pembayaran.
     */
    public function paymentPage(Request $request)
    {
        $token = $request->query('token');
        $payment = null;
        $studio = StudioProfile::first();
        
        $rekenings = Rekening::all();
        
        // Ambil data rekening yang nama bank-nya mengandung kata 'QRIS'
        $qrisData = Rekening::where('nama_bank', 'LIKE', '%QRIS%')->first();

        if ($token) {
            $payment = Payment::with(['reservation.package', 'reservation'])
                ->where('token', strtoupper(trim($token)))
                ->first();
        }

        return view('reservasi.detailPembayaran', compact('payment', 'token', 'studio', 'rekenings', 'qrisData'));
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
            'rekening_id' => ['nullable', 'exists:rekening,id'],
            'receipt_photo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        $file = $request->file('receipt_photo');
        $paymentToken = $validated['token'];
        $receiptPath = null;
        $amount = (float) $validated['amount'];
        $isLunas = false;

        $receiptPath = $file->storePublicly('receipts', 'public');
        if ($receiptPath === false) {
            throw new RuntimeException('Bukti pembayaran gagal disimpan.');
        }

        try {
            DB::transaction(function () use ($validated, $paymentToken, $amount, $receiptPath, &$isLunas): void {
                $payment = Payment::where('token', $paymentToken)->lockForUpdate()->firstOrFail();
                $reservation = $payment->reservation;

                if ($payment->histories()->where('status', 'menunggu_verifikasi')->exists()) {
                    throw ValidationException::withMessages([
                        'receipt_photo' => 'Bukti pembayaran sebelumnya masih menunggu pemeriksaan admin.',
                    ]);
                }

                $isDeposit = $payment->status === 'Menunggu pembayaran DP';
                $isBalancePayment = $payment->status === 'Menunggu pelunasan'
                    && $reservation->session_completed_at !== null;

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

                PaymentHistory::create([
                    'payment_id' => $payment->id,
                    'rekening_id' => $validated['rekening_id'] ?? null,
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
            });
        } catch (\Throwable $e) {
            if ($receiptPath && Storage::disk('public')->exists($receiptPath)) {
                Storage::disk('public')->delete($receiptPath);
            }
            throw $e;
        }

        return redirect()->route('reservasi.pembayaran', ['token' => $paymentToken])->with([
            'swal_payment_success' => true,
            'paid_amount' => $amount,
            'token_reservasi' => $paymentToken,
        ]);
    }
}