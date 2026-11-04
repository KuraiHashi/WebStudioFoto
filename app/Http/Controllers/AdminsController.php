<?php

namespace App\Http\Controllers;

use App\Models\Galery;
use App\Models\Payment;
use App\Models\PhotographyPackage;
use App\Models\Reservation;
use App\Models\StudioProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminsController extends Controller
{
    /**
     * Tampilan Utama Dashboard Admin
     */
    public function Dashboard(Request $request)
    {
        $query = Reservation::with([
            'package',
            'payment.histories' => fn ($historyQuery) => $historyQuery->latest(),
        ]);
        $filters = $request->validate([
            'filter' => ['nullable', Rule::in(['new', 'verification', 'completed'])],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        // 1. Filter Pencarian Nama atau Token
        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('token', 'like', "%{$keyword}%");
            });
        }

        // 2. Filter cepat dari kartu statistik
        if (($filters['filter'] ?? null) === 'new') {
            $query->where(function ($reservationQuery) {
                $reservationQuery->whereDoesntHave('payment')
                    ->orWhereHas('payment', function ($paymentQuery) {
                        $paymentQuery->whereIn('status', ['Masa konsultasi', 'Menunggu pembayaran DP']);
                    });
            });
        } elseif (($filters['filter'] ?? null) === 'verification') {
            $query->whereHas('payment', function ($paymentQuery) {
                $paymentQuery->whereIn('status', ['Konfirmasi pembayaran', 'Verifikasi pelunasan']);
            });
        } elseif (($filters['filter'] ?? null) === 'completed') {
            $query->whereHas('payment', function ($paymentQuery) {
                $paymentQuery->where('status', 'Selesai');
            });
        }

        // 3. Filter Status Pembayaran / Reservasi
        if ($request->filled('status')) {
            $status = $request->status;
            $query->where(function ($q) use ($status) {
                $q->whereHas('payment', function ($subQ) use ($status) {
                    $subQ->where('status', $status);
                });

                // Menampung reservasi baru yang belum memiliki record payment
                if ($status === 'Masa konsultasi') {
                    $q->orDoesntHave('payment');
                }
            });
        }

        // 4. Filter Tanggal dari Kalender
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $reservations = $query->orderByDesc('date')->orderByDesc('time')->get();

        // 5. Perhitungan antrean kerja
        $reservasiBaru = Reservation::where(function ($reservationQuery) {
            $reservationQuery->whereDoesntHave('payment')
                ->orWhereHas('payment', function ($paymentQuery) {
                    $paymentQuery->whereIn('status', ['Masa konsultasi', 'Menunggu pembayaran DP']);
                });
        })->count();

        $menungguVerifikasi = Reservation::whereHas('payment', function ($paymentQuery) {
            $paymentQuery->whereIn('status', ['Konfirmasi pembayaran', 'Verifikasi pelunasan']);
        })->count();

        $reservasiSelesai = Reservation::whereHas('payment', function ($paymentQuery) {
            $paymentQuery->where('status', 'Selesai');
        })->count();

        return view('TemplateAdmin.dashboard', compact(
            'reservations',
            'reservasiBaru',
            'menungguVerifikasi',
            'reservasiSelesai'
        ));
    }

    /**
     * Advance a payment only after an administrator accepts or rejects its latest proof.
     */
    public function updateVerification(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                'Menunggu pembayaran DP',
                'Menunggu pelunasan',
                'Selesai',
                'Dibatalkan',
            ])],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($id, $validated): void {
            $reservation = Reservation::whereKey($id)->lockForUpdate()->firstOrFail();
            $payment = $reservation->payment()->lockForUpdate()->first();

            if ($payment === null) {
                if ($validated['status'] !== 'Menunggu pembayaran DP') {
                    throw ValidationException::withMessages([
                        'status' => 'Pembayaran DP harus diaktifkan sebelum bukti dapat diverifikasi.',
                    ]);
                }

                $payment = $reservation->payment()->make([
                    'token' => $reservation->token,
                    'status' => 'Masa konsultasi',
                    'tagihan' => $reservation->package()->value('price'),
                    'total_paid' => 0,
                ]);
            }

            if ($validated['status'] === 'Dibatalkan') {
                if (in_array($payment->status, ['Selesai', 'Dibatalkan'], true)) {
                    throw ValidationException::withMessages([
                        'status' => 'Reservasi yang sudah selesai atau dibatalkan tidak dapat dibatalkan lagi.',
                    ]);
                }

                $payment->histories()
                    ->where('status', 'menunggu_verifikasi')
                    ->update(['status' => 'ditolak']);
                $payment->status = 'Dibatalkan';
                $payment->notes = $validated['notes'] ?? null;
                $payment->save();

                return;
            }

            if ($payment->status === 'Masa konsultasi' && $validated['status'] === 'Menunggu pembayaran DP') {
                $payment->status = 'Menunggu pembayaran DP';
                $payment->notes = $validated['notes'] ?? null;
                $payment->save();

                return;
            }

            $expectedPaymentStatus = match ($payment->status) {
                'Konfirmasi pembayaran' => 'Menunggu pelunasan',
                'Verifikasi pelunasan' => 'Selesai',
                default => null,
            };
            $isRejected = $validated['status'] === 'Menunggu pembayaran DP'
                && $payment->status === 'Konfirmasi pembayaran'
                || $validated['status'] === 'Menunggu pelunasan'
                && $payment->status === 'Verifikasi pelunasan';

            if ($expectedPaymentStatus === null
                || $validated['status'] !== $expectedPaymentStatus && ! $isRejected) {
                throw ValidationException::withMessages([
                    'status' => 'Langkah ini tidak sesuai dengan tahap pembayaran reservasi saat ini.',
                ]);
            }

            $pendingHistory = $payment->histories()
                ->where('status', 'menunggu_verifikasi')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($pendingHistory === null) {
                throw ValidationException::withMessages([
                    'status' => 'Belum ada bukti pembayaran yang menunggu verifikasi.',
                ]);
            }

            if ($payment->status === 'Verifikasi pelunasan' && $reservation->session_completed_at === null) {
                throw ValidationException::withMessages([
                    'status' => 'Tandai sesi pemotretan selesai sebelum memverifikasi pelunasan.',
                ]);
            }

            if ($payment->status === 'Konfirmasi pembayaran'
                && $pendingHistory->amount < $payment->tagihan / 2) {
                throw ValidationException::withMessages([
                    'status' => 'Nominal DP yang disetujui harus minimal 50% dari total tagihan.',
                ]);
            }

            if ($isRejected) {
                if (blank($validated['notes'] ?? null)) {
                    throw ValidationException::withMessages([
                        'notes' => 'Jelaskan alasan penolakan agar pelanggan tahu apa yang perlu diperbaiki.',
                    ]);
                }

                $pendingHistory->status = 'ditolak';
                $pendingHistory->save();
                $payment->status = $payment->status === 'Konfirmasi pembayaran'
                    ? 'Menunggu pembayaran DP'
                    : 'Menunggu pelunasan';
            } else {
                $newTotalPaid = $payment->total_paid + $pendingHistory->amount;
                if ($newTotalPaid > $payment->tagihan) {
                    throw ValidationException::withMessages([
                        'status' => 'Total pembayaran melebihi tagihan. Periksa kembali nominal bukti.',
                    ]);
                }

                if ($payment->status === 'Verifikasi pelunasan' && $newTotalPaid < $payment->tagihan) {
                    throw ValidationException::withMessages([
                        'status' => 'Total pembayaran yang disetujui belum menutup seluruh tagihan.',
                    ]);
                }

                $pendingHistory->status = 'diterima';
                $pendingHistory->save();
                $payment->total_paid = $newTotalPaid;
                $payment->status = $payment->total_paid >= $payment->tagihan
                    ? 'Selesai'
                    : 'Menunggu pelunasan';
            }

            $payment->notes = $validated['notes'] ?? null;
            $payment->save();
        });

        return redirect()->route('admin.dashboard')->with('success', 'Status verifikasi pembayaran berhasil diperbarui.');
    }

    public function completeSession(Reservation $reservation): RedirectResponse
    {
        $payment = $reservation->payment;

        if ($payment === null || ! in_array($payment->status, ['Menunggu pelunasan', 'Verifikasi pelunasan', 'Selesai'], true)) {
            throw ValidationException::withMessages([
                'session' => 'Sesi baru dapat ditandai selesai setelah pembayaran DP diverifikasi.',
            ]);
        }

        if ($reservation->session_completed_at !== null) {
            throw ValidationException::withMessages([
                'session' => 'Sesi pemotretan ini sudah ditandai selesai.',
            ]);
        }

        if (! $reservation->canBeMarkedComplete()) {
            throw ValidationException::withMessages([
                'session' => 'Sesi hanya dapat ditandai selesai setelah jadwal pemotretan berakhir.',
            ]);
        }

        $reservation->session_completed_at = now();
        $reservation->save();

        return redirect()->route('admin.dashboard')->with('success', 'Sesi selesai. Reservasi masuk ke antrean pelunasan atau hasil foto.');
    }

    public function updatePhotoWorkflow(Request $request, Reservation $reservation): RedirectResponse
    {
        $validated = $request->validate([
            'photo_delivery_url' => ['required', 'url', 'max:2048'],
        ]);

        if ($reservation->payment?->status !== 'Selesai'
            || $reservation->payment->total_paid < $reservation->payment->tagihan) {
            throw ValidationException::withMessages([
                'photo_delivery_url' => 'Hasil foto dapat dikirim setelah pembayaran lunas diverifikasi.',
            ]);
        }

        if (! in_array($reservation->photo_status, ['belum_diproses', 'diproses'], true)) {
            throw ValidationException::withMessages([
                'photo_delivery_url' => 'Hasil foto untuk reservasi ini sudah dikirim.',
            ]);
        }

        $reservation->photo_status = 'siap';
        $reservation->photo_delivery_url = $validated['photo_delivery_url'];
        $reservation->save();

        return redirect()->route('admin.dashboard')->with('success', 'Tautan hasil foto berhasil dikirim kepada pelanggan.');
    }

    // 2. Update Tagihan
    public function updateTagihan(Request $request, $id)
    {
        $validated = $request->validate([
            'tagihan' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($id, $validated): void {
            $reservation = Reservation::whereKey($id)->lockForUpdate()->firstOrFail();
            $payment = $reservation->payment()->lockForUpdate()->first();

            if ($payment === null) {
                $payment = $reservation->payment()->make([
                    'token' => $reservation->token,
                    'total_paid' => 0,
                ]);
            }

            $pendingAmount = $payment->histories()
                ->where('status', 'menunggu_verifikasi')
                ->sum('amount');
            $minimumBill = (float) $payment->total_paid + (float) $pendingAmount;

            if ((float) $validated['tagihan'] < $minimumBill) {
                throw ValidationException::withMessages([
                    'tagihan' => 'Tagihan tidak boleh lebih kecil dari pembayaran yang sudah diterima dan bukti yang menunggu verifikasi.',
                ]);
            }

            $payment->token = $reservation->token;
            $payment->tagihan = $validated['tagihan'];
            $payment->save();
        });

        return redirect()->route('admin.dashboard')->with('success', 'Data tagihan berhasil diperbarui.');
    }

    // 3. Update Detail Reservasi (Nama, WhatsApp, Tanggal, Jam)
    public function updateReservasiDetail(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'date' => 'required|date',
            'time' => 'required',
        ]);

        $reservation = Reservation::findOrFail($id);
        $reservation->name = $request->name;
        $reservation->whatsapp = $request->whatsapp;
        $reservation->date = $request->date;
        $reservation->time = $request->time;
        $reservation->save();

        return redirect()->route('admin.dashboard')->with('success', 'Detail data reservasi berhasil diperbarui.');
    }

    /**
     * API Endpoint Jadwal Terisi untuk Kalender Admin
     */
    public function getSchedule()
    {
        $bookedDates = Reservation::whereHas('payment', function ($q) {
            $q->where('status', '!=', 'Dibatalkan');
        })
            ->orDoesntHave('payment')
            ->select('date', 'time', 'name', 'token')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookedDates,
        ]);
    }

    public function laporan(Request $request)
    {
        $statuses = [
            'Masa konsultasi',
            'Menunggu pembayaran DP',
            'Konfirmasi pembayaran',
            'Menunggu pelunasan',
            'Verifikasi pelunasan',
            'Selesai',
            'Dibatalkan',
        ];
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([...$statuses, 'tanpa_pembayaran'])],
        ]);

        $query = Reservation::with(['package', 'payment']);

        if (! empty($filters['from'])) {
            $query->whereDate('date', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('date', '<=', $filters['to']);
        }

        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            if ($keyword !== '') {
                $query->where(function ($reservationQuery) use ($keyword) {
                    $reservationQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('token', 'like', "%{$keyword}%");
                });
            }
        }

        if (($filters['status'] ?? null) === 'tanpa_pembayaran') {
            $query->doesntHave('payment');
        } elseif (! empty($filters['status'])) {
            $query->whereHas('payment', function ($paymentQuery) use ($filters) {
                $paymentQuery->where('status', $filters['status']);
            });
        }

        $reservations = $query->orderByDesc('date')->get();
        $totalTagihan = $reservations->sum(fn(Reservation $reservation) => $reservation->payment?->tagihan ?? 0);
        $totalDiterima = $reservations->sum(fn(Reservation $reservation) => $reservation->payment?->total_paid ?? 0);

        return view('TemplateAdmin.laporan', compact(
            'reservations',
            'filters',
            'statuses',
            'totalTagihan',
            'totalDiterima'
        ));
    }

    // GALERY CONTROLLER //

    public function galeryindex()
    {
        $galeries = Galery::latest()->get();

        return view('TemplateAdmin.galery', compact('galeries'));
    }

    /**
     * Store Uploaded Gallery Photos.
     */
    public function storeGalery(Request $request)
    {
        $request->validate([
            'photos.*' => 'required|image|mimes:jpg,jpeg,png|max:5120', // Maks 5MB per file
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                // Simpan ke storage/app/public/galeri
                $path = $file->store('galeri', 'public');

                // Simpan path ke database
                Galery::create([
                    'filename' => $path,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Foto berhasil diunggah!');
    }

    /**
     * Delete Selected Gallery Photos.
     */
    public function destroyGalery(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:galeries,id',
        ]);

        $galeries = Galery::whereIn('id', $request->ids)->get();

        foreach ($galeries as $item) {
            // Hapus file fisik dari storage
            if (Storage::disk('public')->exists($item->filename)) {
                Storage::disk('public')->delete($item->filename);
            }
            // Hapus data dari database
            $item->delete();
        }

        return response()->json(['success' => 'Foto terpilih berhasil dihapus.']);
    }

    // PROFILE STUDIO //

    /**
     * Display PROFILE.
     */
    public function profileIndex()
    {
        // Ambil data profil pertama (atau buat kosong jika belum ada)
        $profile = StudioProfile::first();

        return view('TemplateAdmin.profile', compact('profile'));
    }

    /**
     * Update PROFILE STUDIO.
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'operational_hours' => 'required|string|max:100',
            'social_media' => 'nullable|string|max:100',
            'maps_link' => 'nullable|string', // Diubah dari 'url' menjadi 'string' agar bisa menerima tag iframe
            'description' => 'required|string',
        ]);

        StudioProfile::updateOrCreate(
            ['id' => 1],
            [
                'name' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'operational_hours' => $request->operational_hours,
                'social_media' => $request->social_media,
                'maps_link' => $request->maps_link, // Menyimpan string iframe secara langsung
                'description' => $request->description,
            ]
        );

        $studio = StudioProfile::first() ?? new StudioProfile;

        $data = $request->only([
            'nama_studio' => $request->input('name'),
            'alamat' => $request->input('address'),
            'kontak' => $request->input('phone'),
            'jam_operasional' => $request->input('operational_hours'),
            'media_sosial' => $request->input('social_media'),
            'google_maps' => $request->input('maps_link'),
            'deskripsi' => $request->input('description'),
            'nama_bank' => $request->input('nama_bank'),
            'no_rekening' => $request->input('no_rekening'),
            'nama_pemilik' => $request->input('nama_pemilik'),
        ]);

        // Handle Upload Gambar QRIS
        if ($request->hasFile('foto_qris')) {
            $path = $request->file('foto_qris')->store('qris', 'public');
            $data['foto_qris'] = $path;
        }

        $studio->fill($data)->save();

        return redirect()->back()->with('success', 'Profil studio berhasil diperbarui!');
    }

    // PAKET FOTOGRAPHY //

    public function fotografy()
    {
        $pakets = PhotographyPackage::latest()->get();

        return view('TemplateAdmin.fotografy', compact('pakets'));
    }

    public function storeFotografy(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'duration' => 'required|string',
            'facilities' => 'required|string',
        ]);

        // Mengambil nilai is_wedding (jika dicentang bernilai 1, jika tidak bernilai 0)
        $isWedding = $request->has('is_wedding') ? 1 : 0;

        PhotographyPackage::create([
            'name' => $request->name,
            'is_wedding' => $isWedding, // <-- Simpan ke database
            'price' => $request->price,
            'duration' => $request->duration,
            'facilities' => $request->facilities,
            'description' => $request->description,
            'bonus' => $request->bonus,
        ]);

        return redirect()->back()->with('success', 'Paket fotografi berhasil ditambahkan.');
    }

    public function updateFotografy(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'duration' => 'required|string',
            'facilities' => 'required|string',
        ]);

        $paket = PhotographyPackage::findOrFail($id);

        // Cek apakah checkbox di-centang saat update
        $isWedding = $request->has('is_wedding') ? 1 : 0;

        $paket->update([
            'name' => $request->name,
            'is_wedding' => $isWedding, // <-- Perbarui data di database
            'price' => $request->price,
            'duration' => $request->duration,
            'facilities' => $request->facilities,
            'description' => $request->description,
            'bonus' => $request->bonus,
        ]);

        return redirect()->back()->with('success', 'Paket fotografi berhasil diperbarui.');
    }

    /**
     * Remove the specified photography package.
     */
    public function destroyFotografy($id)
    {
        $paket = PhotographyPackage::findOrFail($id);
        $paket->delete();

        return redirect()->route('admin.fotografi')->with('success', 'Paket fotografi berhasil dihapus!');
    }
}
