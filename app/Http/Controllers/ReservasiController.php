<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\PhotographyPackage;
use App\Models\Reservation;
use App\Models\StudioProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReservasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil semua data paket dari database
        $packages = PhotographyPackage::all();

        // Tangkap parameter package_id jika ada dari URL
        $selectedPackageId = $request->query('package_id');

        return view('reservasi.reservasi', compact('packages', 'selectedPackageId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'photography_package_id'        => 'required|exists:photography_packages,id',
            'name'                          => 'required|string|max:255',
            'whatsapp'                      => 'required|string|max:20',
            'date'                          => 'required|date',
            'time'                          => 'required',
            'duration'                      => 'required|string',
            'location'                      => 'required|string',
            'request_consultation'          => 'nullable|boolean',
            'consultation_topic'            => 'nullable|string',
            'consultation_custom_topic'     => 'nullable|string',
            'consultation_media'            => 'nullable|string',
            'consultation_offline_location' => 'nullable|string',
            'consultation_notes'            => 'nullable|string',
            'source_from'                   => 'nullable|string',
        ]);

        // Auto-generate Token
        $validated['token'] = 'RSV-' . strtoupper(Str::random(6));

        $reservasi = Reservation::create($validated);
        $package = PhotographyPackage::find($request->photography_package_id);

        $isConsultation = $request->has('request_consultation') && $request->request_consultation == 1;

        // Inisialisasi ke tabel payments
        Payment::create([
            'reservation_id' => $reservasi->id,
            'token'          => $reservasi->token,
            'status'         => $isConsultation ? 'KONSULTASI' : 'DP',
            'tagihan'        => $package->price,
            'total_paid'     => 0,
            'description'    => "Harga Paket Dasar: {$package->name} (Rp " . number_format($package->price, 0, ',', '.') . ")",
        ]);

        // WhatsApp formatting
        $studio = StudioProfile::first();
        $rawPhone = $studio ? $studio->phone : '081234567890';
        $adminPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        if (str_starts_with($adminPhone, '0')) {
            $adminPhone = '62' . substr($adminPhone, 1);
        }

        if ($isConsultation) {
            $pesan = "Halo Admin, saya ingin mengkonfirmasi *RESERVASI & KONSULTASI* pemotretan:\n\n";
        } else {
            $pesan = "Halo Admin, saya ingin mengkonfirmasi *RESERVASI* pemotretan:\n\n";
        }

        $pesan .= "🔑 *Token Reservasi*: {$reservasi->token}\n" .
            "👤 *Nama*: {$reservasi->name}\n" .
            "📱 *No WhatsApp*: {$reservasi->whatsapp}\n" .
            "📦 *Paket*: {$package->name} (Rp " . number_format($package->price, 0, ',', '.') . ")\n" .
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
            'token'        => $reservasi->token,
            'whatsapp_url' => $whatsappUrl
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Menampilkan halaman awal input cek status (Referensi 1).
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
     * Menampilkan halaman upload pembayaran (Referensi 2).
     */
    public function paymentPage(Request $request)
    {
        $token = $request->query('token');
        $payment = null;

        if ($token) {
            $payment = Payment::with(['reservation.package'])->where('token', strtoupper(trim($token)))->first();
        }

        return view('reservasi.detailPembayaran', compact('payment', 'token'));
    }


    /**
     * Menangani Upload Bukti Pembayaran Lanjutan (DP / Pelunasan) oleh Klien.
     */
    public function storePaymentHistory(Request $request)
    {
        $request->validate([
            'token'          => 'required|exists:payments,token',
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'receipt_photo'  => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'notes'          => 'nullable|string',
        ]);

        $payment = Payment::where('token', $request->token)->first();

        if ($request->hasFile('receipt_photo')) {
            $file = $request->file('receipt_photo');
            $filename = time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/receipts'), $filename);
            $receiptPath = 'uploads/receipts/' . $filename;

            PaymentHistory::create([
                'payment_id'     => $payment->id,
                'amount'         => $request->amount,
                'payment_method' => $request->payment_method,
                'receipt_photo'  => $receiptPath,
                'notes'          => $request->notes,
            ]);

            $newTotalPaid = $payment->total_paid + $request->amount;
            $isLunas = $newTotalPaid >= $payment->tagihan;
            $newStatus = $isLunas ? 'LUNAS' : 'DP';

            $payment->update([
                'total_paid' => $newTotalPaid,
                'status'     => $newStatus,
            ]);

            // Redirect kembali ke halaman pembayaran dengan membawa flash session SweetAlert
            return redirect()->route('reservasi.pembayaran', ['token' => $payment->token])->with([
                'swal_payment_success' => true,
                'paid_amount'          => $request->amount,
                'is_lunas'             => $isLunas,
                'token_reservasi'      => $payment->token,
            ]);
        }

        return redirect()->back()->withErrors('Gagal mengunggah bukti pembayaran.');
    }
}
