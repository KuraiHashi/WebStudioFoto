<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\PhotographyPackage;
use App\Models\Reservation;
use App\Models\StudioProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Rekening; // Jangan lupa tambahkan use model Rekening di bagian atas

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

        $validated['token'] = 'RSV-' . strtoupper(Str::random(6));

        // Ambil data paket yang ingin dibooking user saat ini
        $newPackage = PhotographyPackage::findOrFail($request->photography_package_id);

        // Ambil semua reservasi aktif di tanggal yang sama
        $existingReservations = Reservation::where('date', $request->date)
            ->whereHas('payment', function ($q) {
                $q->where('status', '!=', 'Dibatalkan');
            })
            ->with('package')
            ->get();

        foreach ($existingReservations as $exist) {
            $existIsWedding = $exist->package->is_wedding ?? false;

            // ATURAN 1: Jika di tanggal tersebut SUDAH ADA Paket Wedding, 
            // maka tutup total untuk SEMUA jenis paket.
            if ($existIsWedding) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['date' => 'Maaf, tanggal tersebut sudah dibooking penuh untuk acara Wedding. Tanggal tertutup untuk semua reservasi.']);
            }

            // ATURAN 2: Jika user saat ini ingin membooking Paket Wedding, 
            // tapi di tanggal tersebut sudah ada reservasi paket reguler lain, tolak.
            if ($newPackage->is_wedding) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['date' => 'Maaf, tanggal tersebut sudah memiliki jadwal pemotretan lain. Paket Wedding memerlukan tanggal yang sepenuhnya kosong.']);
            }
        }

        // ATURAN 3: Jika sama-sama paket reguler (non-wedding), cek bentrok jam (overlapping) berdasarkan durasi
        if (!$newPackage->is_wedding) {
            preg_match('/\d+/', $newPackage->duration, $matchNew);
            $newHours = isset($matchNew[0]) ? (int)$matchNew[0] : 1;
            
            // Konversi waktu baru (pastikan format jam digabung tanggal dengan benar)
            $newStart = strtotime($request->date . ' ' . $request->time);
            $newEnd = $newStart + ($newHours * 3600);

            // LOGGING untuk debugging di laravel.log
            \Illuminate\Support\Facades\Log::info("Mencoba Booking: {$request->date} jam {$request->time} (Start: $newStart, End: $newEnd)");

            foreach ($existingReservations as $exist) {
                if ($exist->package->is_wedding ?? false) continue;

                preg_match('/\d+/', $exist->package->duration ?? '1', $matchExist);
                $existHours = isset($matchExist[0]) ? (int)$matchExist[0] : 1;

                $existStart = strtotime($exist->date . ' ' . $exist->time);
                $existEnd = $existStart + ($existHours * 3600);

                \Illuminate\Support\Facades\Log::info("Membandingkan dengan Eksisting: {$exist->date} jam {$exist->time} (Start: $existStart, End: $existEnd)");

                // Rumus irisan waktu (Overlapping check)
                if ($newStart < $existEnd && $newEnd > $existStart) {
                    \Illuminate\Support\Facades\Log::warning("BENTROK TERDETEKSI! Menolak reservasi.");
                    
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

        // Inisialisasi data pembayaran
        Payment::create([
            'reservation_id' => $reservasi->id,
            'token' => $reservasi->token,
            'status' => $initialStatus,
            'tagihan' => $newPackage->price,
            'total_paid' => 0,
            'description' => $isConsultation
                ? "Konsultasi & Paket: {$newPackage->name} (Rp " . number_format($newPackage->price, 0, ',', '.') . ")"
                : "Harga Paket Dasar: {$newPackage->name} (Rp " . number_format($newPackage->price, 0, ',', '.') . ")",
        ]);

        // Format pesan WhatsApp
        $studio = StudioProfile::first();
        $rawPhone = $studio ? $studio->phone : '081234567890';
        $adminPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        if (str_starts_with($adminPhone, '0')) {
            $adminPhone = '62' . substr($adminPhone, 1);
        }

        $pesan = $isConsultation
            ? "Halo Admin, saya ingin mengkonfirmasi *RESERVASI & KONSULTASI* pemotretan:\n\n"
            : "Halo Admin, saya ingin mengkonfirmasi *RESERVASI* pemotretan:\n\n";

        $pesan .= "🔑 *Token Reservasi*: {$reservasi->token}\n" .
            "👤 *Nama*: {$reservasi->name}\n" .
            "📱 *No WhatsApp*: {$reservasi->whatsapp}\n" .
            "📦 *Paket*: {$newPackage->name} (Rp " . number_format($newPackage->price, 0, ',', '.') . ")\n" .
            "📅 *Tanggal*: {$reservasi->date}\n" .
            "⏰ *Jam*: {$reservasi->time}\n" .
            "⏱️ *Durasi*: {$reservasi->duration}\n" .
            "📍 *Lokasi*: {$reservasi->location}\n";

        if ($isConsultation) {
            $topicText = $request->consultation_topic;
            if ($topicText === 'Lainnya') {
                $topicText = "Lainnya (" . ($request->consultation_custom_topic ?? '-') . ")";
            }
            $mediaText = $request->consultation_media;
            if ($mediaText === 'Offline') {
                $mediaText = "Offline (Lokasi: " . ($request->consultation_offline_location ?? '-') . ")";
            }

            $pesan .= "\n💬 *DETAIL KONSULTASI*:\n" .
                "- Topik Utama: " . $topicText . "\n" .
                "- Media Konsultasi: " . $mediaText . "\n" .
                "- Catatan / Ide: " . ($request->consultation_notes ?? '-') . "\n";
        }

        $pesan .= "\nMohon informasi selanjutnya untuk proses pembayaran. Terima kasih!";

        $encodedPesan = urlencode($pesan);
        $whatsappUrl = "https://wa.me/{$adminPhone}?text={$encodedPesan}";

        return redirect()->back()->with([
            'swal_success' => true,
            'token' => $reservasi->token,
            'whatsapp_url' => $whatsappUrl,
            'is_consultation' => $isConsultation
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
            $payment = Payment::with(['reservation.package', 'histories'])
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
        
        // Ambil daftar rekening aktif dari database
        $rekenings = Rekening::all(); // Atau Rekening::where('is_active', true)->get() jika ada kolom status aktif

        if ($token) {
            $payment = Payment::with(['reservation.package'])->where('token', strtoupper(trim($token)))->first();
        }

        // Kirim $rekenings ke view detailPembayaran
        return view('reservasi.detailPembayaran', compact('payment', 'token', 'studio', 'rekenings'));
    }

    /**
     * Menangani Upload Bukti Pembayaran Lanjutan (DP / Pelunasan) oleh Klien.
     */
    public function storePaymentHistory(Request $request)
    {
        $request->validate([
            'token' => 'required|exists:payments,token',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'rekening_id' => 'required_if:payment_method,Transfer Bank|nullable|exists:rekening,id', // Validasi rekening tujuan transfer
            'receipt_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'notes' => 'nullable|string',
        ]);

        $payment = Payment::where('token', $request->token)->first();

        if ($request->hasFile('receipt_photo')) {
            $file = $request->file('receipt_photo');
            $filename = time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/receipts'), $filename);
            $receiptPath = 'uploads/receipts/' . $filename;

            PaymentHistory::create([
                'payment_id' => $payment->id,
                'rekening_id' => $request->payment_method === 'Transfer Bank' ? $request->rekening_id : null, // Simpan ID rekening jika transfer bank
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'receipt_photo' => $receiptPath,
                'notes' => $request->notes,
            ]);

            $newTotalPaid = $payment->total_paid + $request->amount;
            $isLunas = $newTotalPaid >= $payment->tagihan;

            if ($payment->status === 'Menunggu pelunasan') {
                $newStatus = 'Verifikasi pelunasan';
            } else {
                $newStatus = 'Konfirmasi pembayaran';
            }

            $payment->update([
                'total_paid' => $newTotalPaid,
                'status' => $newStatus,
            ]);

            return redirect()->route('reservasi.pembayaran', ['token' => $payment->token])->with([
                'swal_payment_success' => true,
                'paid_amount' => $request->amount,
                'is_lunas' => $isLunas,
                'token_reservasi' => $payment->token,
            ]);
        }

        return redirect()->back()->withErrors('Gagal mengunggah bukti pembayaran.');
    }
}
