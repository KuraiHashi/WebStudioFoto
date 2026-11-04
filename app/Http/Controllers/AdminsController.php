<?php

namespace App\Http\Controllers;

use App\Models\Galery;
use App\Models\PhotographyPackage;
use App\Models\Reservation;
use App\Models\StudioProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminsController extends Controller
{
/**
     * Tampilan utama Dashboard Admin.
     */
    public function Dashboard(Request $request)
    {
        $query = Reservation::with(['package', 'payment']);

        // Filter Pencarian Nama atau Token
        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('token', 'like', "%{$keyword}%");
            });
        }

        // Filter Status Pembayaran
        if ($request->filled('status')) {
            $status = $request->status;
            $query->where(function($q) use ($status) {
                $q->whereHas('payment', function($subQ) use ($status) {
                    $subQ->where('status', $status);
                });
                if ($status === 'Masa konsultasi') {
                    $q->orDoesntHave('payment');
                }
            });
        }

        $reservations = $query->orderBy('date', 'desc')->get();

        // Hitung Statistik (KP) dengan aman menggunakan collection filtering
        $allReservations = Reservation::with('payment')->get();
        $total = $allReservations->count();
        
        $reservasiBaru = $allReservations->filter(function($item) {
            $status = optional($item->payment)->status;
            return !$status || $status === 'Masa konsultasi' || $status === 'Menunggu pembayaran DP';
        })->count();

        $menungguVerifikasi = $allReservations->filter(function($item) {
            $status = optional($item->payment)->status;
            return $status === 'Konfirmasi pembayaran' || $status === 'Verifikasi pelunasan';
        })->count();

        $lunasSelesai = $allReservations->filter(function($item) {
            $status = optional($item->payment)->status;
            return $status === 'Selesai' || $status === 'Lunas';
        })->count();

        return view('TemplateAdmin.dashboard', compact('reservations', 'total', 'reservasiBaru', 'menungguVerifikasi', 'lunasSelesai'));
    }

    /**
     * API untuk mengambil jadwal yang sudah terisi (Kalender Admin)
     */
    public function getSchedule(Request $request)
    {
        $bookedDates = Reservation::whereHas('payment', function($q) {
            $q->where('status', '!=', 'Dibatalkan');
        })->orDoesntHave('payment')
        ->select('date', 'time', 'name', 'token')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $bookedDates
        ]);
    }

    /**
     * Display LAPORAN.
     */
    public function laporan()
    {
        return view('TemplateAdmin.laporan');
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
                    'filename' => $path
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

        $studio = StudioProfile::first() ?? new StudioProfile();

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
            'price' => 'required|numeric|min:0',
            'duration' => 'required|string|max:50',
            'bonus' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'facilities' => 'required|string',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:4048', // Validasi tiap file foto (maks 2MB)
        ]);

        $data = $request->except('photos');
        $photoPaths = [];

        // Proses upload banyak foto
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                // Simpan ke folder public/storage/packages
                $path = $photo->store('packages', 'public');
                $photoPaths[] = $path;
            }
            $data['photos'] = $photoPaths;
        }

        PhotographyPackage::create($data);

        return redirect()->route('admin.fotografi')->with('success', 'Paket fotografi berhasil ditambahkan!');
    }

    public function updateFotografy(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|string|max:50',
            'bonus' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'facilities' => 'required|string',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:4048',
        ]);

        $paket = PhotographyPackage::findOrFail($id);
        $data = $request->except('photos');

        $photoPaths = $paket->photos ?? []; // Ambil foto lama jika ada

        // Jika user mengunggah foto baru
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('packages', 'public');
                $photoPaths[] = $path; // Gabungkan dengan foto lama
            }
            $data['photos'] = $photoPaths;
        }

        $paket->update($data);

        return redirect()->route('admin.fotografi')->with('success', 'Paket fotografi berhasil diperbarui!');
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
