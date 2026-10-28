<?php

namespace App\Http\Controllers;

use App\Models\Galery;
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
    /**
     * Display GALERY.
     */
    public function galery()
    {
        return view('TemplateAdmin.galery');
    }
    /**
     * Display FOTOGRAFY.
     */
    public function fotografy()
    {
        return view('TemplateAdmin.fotografy');
    }
    /**
     * Display PROFILE.
     */
    public function profile()
    {
        return view('TemplateAdmin.profile');
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
