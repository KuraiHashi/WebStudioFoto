<?php

namespace App\Http\Controllers;

use App\Models\Galery;
use App\Models\PhotographyPackage;
use App\Models\StudioProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminsController extends Controller
{
    /**
     * Display Template.
     */
    public function TemplateAdmin()
    {
        return view('TemplateAdmin.main');
    }
    /**
     * Display DASHBOARD.
     */
    public function Dashboard()
    {
        return view('TemplateAdmin.dashboard');
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







    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 
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
        //
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
