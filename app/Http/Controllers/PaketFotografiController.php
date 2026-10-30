<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PhotographyPackage;

class PaketFotografiController extends Controller
{

    // DETAIL FOTO GRAPHI
    public function show($id)
    {
        $package = PhotographyPackage::findOrFail($id);
        $otherPackages = PhotographyPackage::where('id', '!=', $id)->get();

        return view('paketFotografi.detail', compact('package', 'otherPackages'));
    }
}
