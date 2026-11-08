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
            <div class="package-section-header rv">
                <div>
                    <div class="eyebrow">Paket Layanan</div>
                    <h2 style="margin-top: 14px;">Pilih sesi yang <em>paling sesuai</em></h2>
                </div>
                <div class="package-section-actions">
                    <div class="package-category-filters" role="group" aria-label="Filter kategori paket">
                        <button class="package-category-button is-active" type="button" data-category-filter=""
                            aria-pressed="true">Semua</button>
                        @foreach ($packageCategories as $category)
                            <button class="package-category-button" type="button"
                                data-category-filter="{{ $category }}" aria-pressed="false">
                                {{ $category }}
                            </button>
                        @endforeach
                    </div>
                    <div class="package-carousel-controls" role="group" aria-label="Navigasi daftar paket">
                        <button class="package-carousel-button" type="button" data-package-scroll="left"
                            aria-label="Geser daftar paket ke kiri" aria-controls="packageContainer" disabled>
                            <span aria-hidden="true">&larr;</span>
                        </button>
                        <button class="package-carousel-button" type="button" data-package-scroll="right"
                            aria-label="Geser daftar paket ke kanan" aria-controls="packageContainer" disabled>
                            <span aria-hidden="true">&rarr;</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Container Paket (Horizontal Carousel) -->
            <div class="pk rv" id="packageContainer" style="display: flex; flex-wrap: nowrap; overflow-x: auto; gap: 24px; padding-bottom: 20px; scroll-snap-type: x mandatory;">
                @forelse($packages as $index => $package)
                    @php
                        $isFeatured = false;
                        $rn = $index + 1;

                        // Memecah fasilitas berdasarkan titik tengah (·) ATAU koma (,)
                        $facilities = preg_split('/[·,]/', $package->facilities);
                        
                    @endphp

                    <div class="pc {{ $isFeatured ? 'f' : '' }}" 
                        data-category="{{ $package->category }}"
                        style="flex: 0 0 min(320px, 100%); scroll-snap-align: start;">
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
                    <p class="note">Belum ada paket untuk kategori ini.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Filter kategori paket -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const categoryButtons = document.querySelectorAll('.package-category-button');
        const container = document.getElementById('packageContainer');
        if (!container) {
            return;
        }

        const cards = container.querySelectorAll('.pc');
        const noResultState = document.getElementById('noResultState');
        const scrollButtons = document.querySelectorAll('[data-package-scroll]');

        function updateScrollButtons() {
            const hasVisibleCards = Array.from(cards).some(card => card.style.display !== 'none');
            const maxScrollLeft = container.scrollWidth - container.clientWidth;

            scrollButtons.forEach(button => {
                const isLeftButton = button.dataset.packageScroll === 'left';
                const hasOverflow = hasVisibleCards && maxScrollLeft > 1;
                button.disabled = !hasOverflow || (isLeftButton
                    ? container.scrollLeft <= 1
                    : container.scrollLeft >= maxScrollLeft - 1);
            });
        }

        scrollButtons.forEach(button => {
            button.addEventListener('click', function() {
                const direction = button.dataset.packageScroll === 'left' ? -1 : 1;
                container.scrollBy({
                    left: direction * container.clientWidth * 0.85,
                    behavior: 'smooth'
                });
            });
        });

        container.addEventListener('scroll', updateScrollButtons, { passive: true });
        window.addEventListener('resize', updateScrollButtons);

        function filterPackages(selectedCategory) {
            let visibleCount = 0;

            cards.forEach(card => {
                if (!selectedCategory || card.dataset.category === selectedCategory) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            noResultState.style.display = visibleCount === 0 ? 'block' : 'none';
            container.scrollLeft = 0;
            updateScrollButtons();
        }

        categoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                const selectedCategory = button.dataset.categoryFilter;

                categoryButtons.forEach(categoryButton => {
                    const isActive = categoryButton === button;
                    categoryButton.classList.toggle('is-active', isActive);
                    categoryButton.setAttribute('aria-pressed', String(isActive));
                });

                filterPackages(selectedCategory);
            });
        });

        updateScrollButtons();
    });
    </script>