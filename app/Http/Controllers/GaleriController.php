<?php

namespace App\Http\Controllers;

use App\Models\Galery;
use App\Models\PhotographyPackage;
use App\Models\Testimonial;

class GaleriController extends Controller
{
    public function index()
    {
        $galeries = Galery::where('display_section', 'story')->latest()->take(8)->get();
        $selectedPhotos = Galery::where('display_section', 'selected')->latest()->take(8)->get();

        // Mengambil semua data paket fotografi
        $packages = PhotographyPackage::all();

        $testimonials = Testimonial::latest()->limit(3)->get();

        // dd($packages->toArray());

        return view('template.main', compact('galeries', 'selectedPhotos', 'packages', 'testimonials'));
    }
}
