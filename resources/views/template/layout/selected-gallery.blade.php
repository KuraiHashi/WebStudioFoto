@php
    $selectedPhotos = ($selectedPhotos ?? collect())->values();
@endphp

<section class="selected-gallery" id="galeri">
    <div class="wrap">
        <div class="selected-gallery-heading rv">
            <div>
                <div class="eyebrow">Pilihan dari Liquif Picture</div>
                <h2>Karya <em>terpilih.</em></h2>
                <p>Setiap bingkai menyimpan cerita yang berbeda. Pilih foto untuk melihatnya lebih dekat.</p>
            </div>
            <span class="selected-gallery-count">
                {{ str_pad((string) $selectedPhotos->count(), 2, '0', STR_PAD_LEFT) }} FOTO
            </span>
        </div>

        @if ($selectedPhotos->isNotEmpty())
            <div class="selected-gallery-grid">
                @foreach ($selectedPhotos as $item)
                    @php
                        $imageUrl = asset('storage/' . $item->filename);
                    @endphp
                    <a href="{{ $imageUrl }}" class="selected-gallery-card glightbox"
                        data-gallery="gallery-selected"
                        aria-label="Buka foto karya terpilih nomor {{ $loop->iteration }}">
                        <img src="{{ $imageUrl }}"
                            alt="Karya fotografi terpilih Liquif Picture nomor {{ $loop->iteration }}"
                            loading="lazy" decoding="async">
                        <span class="selected-gallery-caption">
                            <span>LIQUIF PICTURE</span>
                            <span>MOMENT NO. {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        </span>
                        <span class="selected-gallery-open" aria-hidden="true">↗</span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="selected-gallery-empty">
                <p>Karya pilihan sedang disiapkan.</p>
                <span>Foto yang diunggah akan tampil di sini dan dapat dibuka satu per satu.</span>
            </div>
        @endif
    </div>
</section>
