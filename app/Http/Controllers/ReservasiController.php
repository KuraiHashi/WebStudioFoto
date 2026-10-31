<?php

namespace App\Http\Controllers;

use App\Models\Payment;
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
        // 1. Validasi Input Data
        $validated = $request->validate([
            'photography_package_id' => 'required|exists:photography_packages,id',
            'name'                     => 'required|string|max:255',
            'whatsapp'                 => 'required|string|max:20',
            'date'                     => 'required|date',
            'time'                     => 'required',
            'duration'                 => 'required|string',
            'location'                 => 'required|string',
            'request_consultation'     => 'nullable|boolean',
            'consultation_topic'       => 'nullable|string',
            'consultation_custom_topic' => 'nullable|string',
            'consultation_media'       => 'nullable|string',
            'consultation_offline_location' => 'nullable|string',
            'consultation_notes'       => 'nullable|string',
            'source_from'              => 'nullable|string',
        ]);

        // 2. Auto-generate Token Unik (Format: RSV-XXXXXX)
        $validated['token'] = 'RSV-' . strtoupper(Str::random(6));

        // 3. Simpan Data Reservasi ke Database
        $reservasi = Reservation::create($validated);
        $package = PhotographyPackage::find($request->photography_package_id);

        // 4. Cek Status Konsultasi
        $isConsultation = $request->has('request_consultation') && $request->request_consultation == 1;

        // 5. Simpan Data ke Tabel Pembayaran (Payments) secara Otomatis
        // Jika konsultasi dicentang, status pembayaran = 'KONSULTASI'. Jika tidak, default = 'DP'.
        Payment::create([
            'reservation_id' => $reservasi->id,
            'token'          => $reservasi->token,
            'status'         => $isConsultation ? 'KONSULTASI' : 'DP',
            'amount'         => null,
            'receipt_photo'  => null,
        ]);

        // 6. Ambil Nomor WhatsApp Studio (Dinamis / Fallback)
        $studio = StudioProfile::first();
        $rawPhone = $studio ? $studio->phone : '081234567890';

        $adminPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        if (str_starts_with($adminPhone, '0')) {
            $adminPhone = '62' . substr($adminPhone, 1);
        }

        // 7. Susun Draf Pesan WhatsApp Berdasarkan Pilihan (Reservasi / Reservasi & Konsultasi)
        if ($isConsultation) {
            $pesan = "Halo Admin, saya ingin mengkonfirmasi *RESERVASI & KONSULTASI* pemotretan:\n\n";
        } else {
            $pesan = "Halo Admin, saya ingin mengkonfirmasi *RESERVASI* pemotretan:\n\n";
        }

        // Detail Utama Reservasi
        $pesan .= "🔑 *Token Reservasi*: {$reservasi->token}\n" .
            "👤 *Nama*: {$reservasi->name}\n" .
            "📱 *No WhatsApp*: {$reservasi->whatsapp}\n" .
            "📦 *Paket*: {$package->name} (Rp " . number_format($package->price, 0, ',', '.') . ")\n" .
            "📅 *Tanggal*: {$reservasi->date}\n" .
            "⏰ *Jam*: {$reservasi->time}\n" .
            "⏱️ *Durasi*: {$reservasi->duration}\n" .
            "📍 *Lokasi*: {$reservasi->location}\n";

        // Detail Konsultasi (Hanya Muncul Jika Dicentang)
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

        // 8. Encode URL WhatsApp
        $encodedPesan = urlencode($pesan);
        $whatsappUrl = "https://wa.me/{$adminPhone}?text={$encodedPesan}";

        // 9. Redirect Kembali dengan Flash Session untuk SweetAlert2
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
