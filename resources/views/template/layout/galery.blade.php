@php
    $galleryPhotos = ($galeries ?? collect())->values();
    $galleryRows = $galleryPhotos->isNotEmpty()
        ? $galleryPhotos->chunk((int) ceil($galleryPhotos->count() / 2))
        : collect();
@endphp

<section class="gal gallery-showcase dark" id="kisah-terbingkai">
    <div class="wrap">
        <div class="gallery-heading rv">
            <div>
                <div class="eyebrow">Jurnal visual Liquif</div>
                <h2>Kisah yang <em>terbingkai.</em></h2>
                <p>Potret, suasana, dan momen kecil yang pantas tinggal lebih lama.</p>
            </div>
            @if ($galleryPhotos->isNotEmpty())
                <div class="gallery-controls" aria-label="Kontrol galeri">
                    <span class="gallery-autoplay-status"><i></i> BERGERAK OTOMATIS</span>
                    <button class="gallery-control" type="button" data-gallery-control="previous"
                        aria-label="Geser galeri ke kiri">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M19 12H5m0 0 6 6m-6-6 6-6" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                    <button class="gallery-control" type="button" data-gallery-control="next"
                        aria-label="Geser galeri ke kanan">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14m0 0-6-6m6 6-6 6" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                    <button class="gallery-control gallery-play-control" type="button" data-gallery-control="toggle"
                        aria-label="Jeda gerakan galeri otomatis" aria-pressed="false">
                        <svg class="gallery-pause-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M9 6v12M15 6v12" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" />
                        </svg>
                        <svg class="gallery-play-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m9 6 10 6-10 6V6Z" fill="currentColor" />
                        </svg>
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if ($galleryRows->isNotEmpty())
        <div class="gallery-marquee-stage rv" data-gallery-carousel role="region" aria-label="Galeri foto studio bergerak otomatis">
            @foreach ($galleryRows as $rowIndex => $row)
                <div class="gallery-marquee" data-gallery-marquee
                    data-direction="{{ $rowIndex % 2 === 0 ? 'forward' : 'reverse' }}">
                    <div class="gallery-track" data-gallery-track>
                        <div class="gallery-run" data-gallery-run>
                            @foreach ($row as $item)
                                @php
                                    $imageUrl = asset('storage/' . $item->filename);
                                @endphp
                                <a href="{{ $imageUrl }}" class="gallery-tile glightbox"
                                    data-gallery="gallery-portfolio"
                                    aria-label="Lihat foto karya studio dalam ukuran penuh">
                                    <img src="{{ $imageUrl }}" alt="Karya fotografi studio Liquif Picture"
                                        loading="lazy" decoding="async">
                                    <span class="gallery-tile-caption">LIQUIF PICTURE <i></i> MOMENT NO.
                                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="gallery-tile-open" aria-hidden="true">↗</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="wrap">
            <div class="gallery-empty">
                <span class="gallery-empty-index">LQ / 01</span>
                <p>Galeri sedang menyiapkan cerita pertamanya.</p>
                <span>Foto studio yang diunggah akan tampil bergerak di sini.</span>
            </div>
        </div>
    @endif

    <div class="wrap">
        <div class="gallery-footer-note">
            <span>FRAME-BY-FRAME, MADE WITH FEELING</span>
            <span>{{ str_pad((string) $galleryPhotos->count(), 2, '0', STR_PAD_LEFT) }} POTRET TERPILIH</span>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.GLightbox === 'function') {
            window.GLightbox({
                selector: '.glightbox',
                touchNavigation: true,
                loop: true,
                zoomable: true
            });
        }

        const carousel = document.querySelector('[data-gallery-carousel]');

        if (!carousel) {
            return;
        }

        const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
        const toggleButton = carousel.closest('.gallery-showcase').querySelector('[data-gallery-control="toggle"]');
        const previousButton = carousel.closest('.gallery-showcase').querySelector('[data-gallery-control="previous"]');
        const nextButton = carousel.closest('.gallery-showcase').querySelector('[data-gallery-control="next"]');
        const status = carousel.closest('.gallery-showcase').querySelector('.gallery-autoplay-status');

        carousel.querySelectorAll('[data-gallery-marquee]').forEach(function (marquee) {
            const track = marquee.querySelector('[data-gallery-track]');
            const run = marquee.querySelector('[data-gallery-run]');
            const originalTiles = Array.from(run.querySelectorAll('.gallery-tile'));

            if (originalTiles.length === 0) {
                return;
            }

            const viewportWidth = marquee.clientWidth;
            const tileWidth = originalTiles[0].getBoundingClientRect().width;

            while (run.scrollWidth < viewportWidth + tileWidth) {
                originalTiles.forEach(function (tile) {
                    const clone = tile.cloneNode(true);
                    clone.removeAttribute('href');
                    clone.removeAttribute('data-gallery');
                    clone.removeAttribute('aria-label');
                    clone.removeAttribute('class');
                    clone.className = 'gallery-tile gallery-tile-clone';
                    clone.setAttribute('aria-hidden', 'true');
                    clone.setAttribute('tabindex', '-1');
                    clone.querySelector('img')?.setAttribute('alt', '');
                    run.appendChild(clone);
                });
            }

            const repeatedRun = run.cloneNode(true);
            repeatedRun.classList.add('gallery-run-copy');
            repeatedRun.setAttribute('aria-hidden', 'true');
            repeatedRun.querySelectorAll('a').forEach(function (tile) {
                tile.removeAttribute('href');
                tile.removeAttribute('data-gallery');
                tile.removeAttribute('aria-label');
                tile.setAttribute('tabindex', '-1');
            });
            track.appendChild(repeatedRun);

            const duration = Math.max(32, Math.round(run.scrollWidth / 30));
            track.style.setProperty('--gallery-duration', duration + 's');
            marquee.classList.add('is-ready');
        });

        let isPaused = motionPreference.matches;

        function updatePlayback() {
            carousel.classList.toggle('is-paused', isPaused);
            toggleButton.setAttribute('aria-pressed', String(isPaused));
            toggleButton.setAttribute(
                'aria-label',
                isPaused ? 'Lanjutkan gerakan galeri otomatis' : 'Jeda gerakan galeri otomatis'
            );
            status.innerHTML = isPaused
                ? '<i></i> GERAKAN DIJEDA'
                : '<i></i> BERGERAK OTOMATIS';
        }

        function moveGallery(direction) {
            carousel.querySelectorAll('[data-gallery-track]').forEach(function (track) {
                const animation = track.getAnimations()[0];
                const run = track.querySelector('[data-gallery-run]');
                const firstTile = run?.querySelector('.gallery-tile');

                if (!animation || !run || !firstTile) {
                    return;
                }

                const duration = animation.effect.getComputedTiming().duration;
                const travelDistance = run.getBoundingClientRect().width;
                const step = firstTile.getBoundingClientRect().width + parseFloat(getComputedStyle(run).gap);
                const reversed = getComputedStyle(track).animationDirection === 'reverse';
                const timeDirection = reversed ? -direction : direction;
                const offset = (duration * step / travelDistance) * timeDirection;
                const currentTime = Number(animation.currentTime) || 0;

                animation.currentTime = ((currentTime + offset) % duration + duration) % duration;
            });
        }

        toggleButton.addEventListener('click', function () {
            isPaused = !isPaused;
            updatePlayback();
        });

        previousButton.addEventListener('click', function () {
            moveGallery(-1);
        });

        nextButton.addEventListener('click', function () {
            moveGallery(1);
        });

        motionPreference.addEventListener('change', function (event) {
            if (event.matches) {
                isPaused = true;
                updatePlayback();
            }
        });

        updatePlayback();
    });
</script>
