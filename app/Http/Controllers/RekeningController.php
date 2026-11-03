<?php

namespace App\Http\Controllers;

use App\Models\Rekening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RekeningController extends Controller
{
    public function index()
    {
        $rekenings = Rekening::latest()->get();
        return view('TemplateAdmin.rekening', compact('rekenings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_bank'    => 'required|string|max:255',
            'no_rekening'  => 'required|string|max:255',
            'nama_pemilik' => 'required|string|max:255',
            'foto_qris'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->only(['nama_bank', 'no_rekening', 'nama_pemilik']);

        if ($request->hasFile('foto_qris')) {
            $data['foto_qris'] = $request->file('foto_qris')->store('qris', 'public');
        }

        Rekening::create($data);

        return redirect()->route('rekening.index')->with('success', 'Rekening baru berhasil ditambahkan.');
    }

    public function update(Request $request, Rekening $rekening)
    {
        $request->validate([
            'nama_bank'    => 'required|string|max:255',
            'no_rekening'  => 'required|string|max:255',
            'nama_pemilik' => 'required|string|max:255',
            'foto_qris'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->only(['nama_bank', 'no_rekening', 'nama_pemilik']);

        if ($request->hasFile('foto_qris')) {
            if ($rekening->foto_qris && Storage::disk('public')->exists($rekening->foto_qris)) {
                Storage::disk('public')->delete($rekening->foto_qris);
            }
            $data['foto_qris'] = $request->file('foto_qris')->store('qris', 'public');
        }

        $rekening->update($data);

        return redirect()->route('rekening.index')->with('success', 'Data rekening berhasil diperbarui.');
    }

    public function destroy(Rekening $rekening)
    {
        if ($rekening->foto_qris && Storage::disk('public')->exists($rekening->foto_qris)) {
            Storage::disk('public')->delete($rekening->foto_qris);
        }

        $rekening->delete();

        return redirect()->route('rekening.index')->with('success', 'Rekening berhasil dihapus.');
    }
}