<section id="paket" style="background: var(--sand); padding: 80px 0;">
    <!-- CSS Internal untuk Carousel & Sembunyikan Scrollbar -->
    <style>
        #packageContainer::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }
        #packageContainer {
            -ms-overflow-style: none;  /* IE & Edge lama */
            scrollbar-width: none;  /* Firefox */
        }
    </style>

    <div class="wrap">
        <!-- Header & Live Search Bar -->
        <div class="rv" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px; margin-bottom: 32px;">
            <div>
                <div class="eyebrow">Paket Layanan</div>
                <h2 style="margin-top: 14px;">Pilih sesi yang <em>paling sesuai</em></h2>
            </div>
            
            <!-- Input Live Search Bertema Studio Foto -->
            <div style="min-width: 280px; flex: 0 1 320px; position: relative;">
                <input type="text" id="packageSearch" placeholder="Cari nama paket atau fasilitas..." 
                       style="width: 100%; padding: 12px 18px 12px 42px; border: 1px solid rgba(0,0,0,0.1); border-radius: 6px; background: rgba(255,255,255,0.7); font-size: 14px; color: inherit; outline: none; transition: all 0.2s; backdrop-filter: blur(4px);"
                       onfocus="this.style.background='#fff'; this.style.borderColor='var(--gold, #bfa15f)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.05)';"
                       onblur="this.style.background='rgba(255,255,255,0.7)'; this.style.borderColor='rgba(0,0,0,0.1)'; this.style.boxShadow='none';">
                
                <!-- Ikon Kaca Pembesar Minimalis (SVG) -->
                <svg style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; opacity: 0.4; pointer-events: none;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
        </div>

        <!-- Container Paket (Horizontal Carousel) -->
        <div class="pk rv" id="packageContainer" style="display: flex; flex-wrap: nowrap; overflow-x: auto; gap: 24px; padding-bottom: 20px; scroll-snap-type: x mandatory;">
            @forelse($packages as $index => $package)
                @php
                    $isFeatured = $index === 1;
                    $romans = ['I', 'II', 'III', 'IV', 'V', 'VI'];
                    $rn = $romans[$index] ?? $index + 1;

                    // Memecah fasilitas berdasarkan titik tengah (·) ATAU koma (,)
                    $facilities = preg_split('/[·,]/', $package->facilities);
                    
                    // Gabungkan teks untuk kebutuhan atribut pencarian (Nama + Fasilitas + Bonus)
                    $searchableText = strtolower($package->name . ' ' . $package->facilities . ' ' . ($package->bonus ?? ''));
                @endphp

                <div class="pc {{ $isFeatured ? 'f' : '' }}" 
                     data-search="{{ $searchableText }}" 
                     style="flex: 0 0 320px; scroll-snap-align: start;">
                    <div class="rn">{{ $rn }}</div>
                    <h3>{{ $package->name }}</h3>
                    <div class="pr">Rp {{ number_format($package->price, 0, ',', '.') }}</div>
                    <span class="note" {{ $isFeatured ? 'style=color:#bfb4a2' : '' }}>{{ $package->duration }}</span>

                    <ul>
                        @foreach ($facilities as $facility)
                            @if (trim($facility) !== '')
                                <li>{{ trim($facility) }}</li>
                            @endif
                        @endforeach

                        @if ($package->bonus && $package->bonus !== '—')
                            <li>Bonus {{ $package->bonus }}</li>
                        @endif
                    </ul>

                    <a class="btn sm {{ $isFeatured ? 'gold' : '' }}"
                        href="{{ route('paket.detail', $package->id) }}">Lihat detail</a>
                </div>
            @empty
                <p class="note">Belum ada paket tersedia.</p>
            @endforelse

            <!-- Pesan Kosong Jika Hasil Pencarian Tidak Ditemukan -->
            <div id="noResultState" style="display: none; width: 100%; text-align: center; padding: 40px 0; color: #666;">
                <p class="note">Tidak ada paket yang sesuai dengan kata kunci pencarian Anda.</p>
            </div>
        </div>
    </div>
</section>

<!-- Skrip JavaScript untuk Live Search -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('packageSearch');
    const container = document.getElementById('packageContainer');
    const cards = container.querySelectorAll('.pc');
    const noResultState = document.getElementById('noResultState');

    searchInput.addEventListener('input', function(e) {
        const keyword = e.target.value.toLowerCase().trim();
        let visibleCount = 0;

        cards.forEach(card => {
            const searchData = card.getAttribute('data-search');
            if (searchData.includes(keyword)) {
                card.style.display = ''; // Tampilkan kembali
                visibleCount++;
            } else {
                card.style.display = 'none'; // Sembunyikan jika tidak cocok
            }
        });

        // Kontrol tampilan pesan kosong
        if (visibleCount === 0) {
            noResultState.style.display = 'block';
        } else {
            noResultState.style.display = 'none';
        }
    });
});
</script>