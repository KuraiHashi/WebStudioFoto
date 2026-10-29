<section class="gal" id="galeri">
    <div class="wrap">
        <div class="head rv">
            <div>
                <div class="eyebrow">Portofolio</div>
                <h2 style="margin-top:14px">Karya <em>terpilih</em></h2>
            </div>
            <span class="note">Klik foto untuk pratinjau</span>
        </div>

        <div class="masonry rv">
            @if(isset($galeries) && $galeries->count() > 0)
                @php
                    $classes = ['h3', 'b h1', 'c h2', 'd h3', 'e h2', 'h1', 'b h3', 'c h1'];
                @endphp

                @foreach($galeries as $index => $item)
                    @php
                        $sizeClass = $classes[$index % count($classes)];
                        $imageUrl = asset('storage/' . $item->filename);
                    @endphp

                    {{-- Bungkus dengan tag <a> dan beri attribute class="glightbox" --}}
                    <a href="{{ $imageUrl }}" class="glightbox ph {{ $sizeClass }}" data-gallery="gallery-portfolio" style="background-image: url('{{ $imageUrl }}');">
                        <!-- Opsional: Berikan efek hover atau biarkan kosong -->
                    </a>
                @endforeach
            @else
                <div class="ph h3" style="background: #222; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 12px;">Foto Belum Tersedia</div>
            @endif
        </div>
    </div>
</section>

<style>
/* Jaminan CSS agar kotak masonry tetap rapi dan bisa diklik */
.masonry a.ph {
    background-size: cover !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
    min-height: 220px;
    display: block;
    cursor: pointer;
    text-decoration: none;
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.masonry a.ph:hover {
    opacity: 0.9;
    transform: scale(1.01);
}
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Inisialisasi GLightbox secara otomatis
        const lightbox = GLightbox({
            selector: '.glightbox',
            touchNavigation: true,
            loop: true,
            zoomable: true
        });
    });
</script>