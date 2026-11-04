<?php

namespace App\Http\Controllers;

use App\Models\Galery;
use App\Models\PhotographyPackage;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class GaleriController extends Controller
{
    public function index()
    {
        // Mengambil maksimal 8 foto terbaru (atau gunakan inRandomOrder() jika ingin acak)
        $galeries = Galery::latest()->take(8)->get();

        // Mengambil semua data paket fotografi
    $packages = PhotographyPackage::all();

        $testimonials = Testimonial::latest()->limit(3)->get();

        // dd($packages->toArray());

        return view('template.main', compact('galeries', 'packages', 'testimonials'));
    }
}
