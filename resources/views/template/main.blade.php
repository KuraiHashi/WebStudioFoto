<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Studio Fotografi — Liquif Picture</title>
    <link rel="preconnect" href="https:fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Jost:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('Assets/css/style.css') }}">
</head>

<body data-pg="index">
    <nav>
        <div class="wrap">
            <a class="logo" href="index.html"><i></i>liquif <small>Picture</small></a>
            <div class="menu"><a class="l" href="#profil">Profil</a><a class="l" href="#galeri">Galeri</a><a
                    class="l" href="#paket">Paket</a><a class="l" href="#testimoni">Testimoni</a><a
                    class="l" href="#kontak">Kontak</a>
                <a class="btn sm" href="/reservasi/cek">Cek Reservasi</a><a class="btn sm gold"
                    href="/reservasi">Reservasi</a>
            </div>
            <button class="bg" aria-label="Menu"><i></i><i></i><i></i></button>
        </div>
    </nav>

    {{-- KONTEN DETAIL --}}
    @yield('konten_detail')

    @sectionMissing('konten_detail')


    @php
        $heroGalleryPhoto = $galeries->first();
        $heroImage = $heroGalleryPhoto
            ? asset('storage/' . $heroGalleryPhoto->filename)
            : 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1400&q=85';
    @endphp

    <header class="hero hero-editorial">
        <img class="hero-background" src="{{ $heroImage }}"
            alt="{{ $heroGalleryPhoto ? 'Potret pilihan dari galeri studio Liquif Picture' : 'Pasangan pengantin di momen istimewa mereka' }}"
            fetchpriority="high">
        <div class="hero-backdrop" aria-hidden="true"></div>
        <div class="wrap">
            <div class="hero-content">
                <span class="hero-edition">STUDIO FOTOGRAFI <i></i> POTRET DENGAN PERASAAN</span>
                <h1>Cerita Anda,<br>dibingkai dengan <em>rasa.</em></h1>
                <p>
                    Ada momen yang layak dikenang lebih lama. Kami mengabadikannya lewat cahaya, suasana,
                    dan cerita yang terasa seperti Anda.
                </p>
                <div class="acts">
                    <a class="btn gold" href="/reservasi">Rencanakan sesi</a>
                    <a class="btn hero-outline" href="#galeri">Jelajahi karya <span aria-hidden="true">↗</span></a>
                </div>
            </div>
        </div>
        <div class="hero-bottom">
            <span class="hero-bottom-note">LIQUIF PICTURE <i></i> MOMENTS, HELD FOREVER</span>
            <a class="hero-scroll" href="#kisah-terbingkai"><span></span> GULIR UNTUK MENJELAJAH</a>
        </div>
    </header>

    {{-- GALERI OTOMATIS --}}
    @include('template/layout/galery')

    @php
        $storyPhotos = $galeries->take(2)->values();
        $storyPrimaryImage = $storyPhotos->isNotEmpty()
            ? asset('storage/' . $storyPhotos->first()->filename)
            : $heroImage;
        $storySecondaryImage = $storyPhotos->count() > 1
            ? asset('storage/' . $storyPhotos->get(1)->filename)
            : 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=900&q=85';
    @endphp

    <section class="profil story-section" id="profil">
        <div class="wrap">
            <div class="story-visual rv">
                <div class="story-photo story-photo-primary">
                    <img src="{{ $storyPrimaryImage }}" alt="Momen hangat yang diabadikan Liquif Picture"
                        loading="lazy" decoding="async">
                    <span class="story-photo-label">STORIES, HELD GENTLY</span>
                </div>
                <div class="story-photo story-photo-secondary">
                    <img src="{{ $storySecondaryImage }}" alt="Cerita cinta dalam bingkai fotografi"
                        loading="lazy" decoding="async">
                </div>
                <span class="story-photo-script" aria-hidden="true">our story</span>
                <span class="story-photo-index" aria-hidden="true">LQ / 01</span>
            </div>
            <div class="story-content rv">
                <div class="eyebrow">Mengenal Liquif Picture</div>
                <span class="story-overline">FOTOGRAFI DENGAN PERASAAN</span>
                <h2>Fotografi sebagai cara <em>merawat</em> ingatan.</h2>
                <p class="story-lead">Hai, kami Liquif Picture. Kami percaya foto terbaik bukan cuma tentang bagaimana sesuatu terlihat—tetapi bagaimana momen itu terasa.</p>
                <p class="story-description">Dengan suasana yang nyaman dan arahan yang personal, kami mengabadikan cerita Anda lewat cahaya dan ekspresi yang jujur. Setiap potret kami siapkan dengan teliti, supaya momen istimewa itu tetap terasa dekat, bahkan bertahun-tahun kemudian.</p>
                <div class="pillars">
                    <div><span class="story-pillar-index">01</span><b>Natural</b><span>Cahaya &amp; ekspresi apa adanya</span></div>
                    <div><span class="story-pillar-index">02</span><b>Personal</b><span>Konsep sesuai karakter Anda</span></div>
                    <div><span class="story-pillar-index">03</span><b>Penuh rasa</b><span>Detail kecil, kenangan besar</span></div>
                </div>
                <a class="story-link" href="#galeri">Lihat cerita yang pernah kami bingkai <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    </section>




        {{-- KARYA TERPILIH --}}
        @include('template/layout/selected-gallery')

        {{-- PAKET FOTOGRAFI --}}
        @include('paketFotografi/paket_fotografi')

        {{-- ALUR RESERVASI --}}
        @include('template/layout/reservasi')

        {{-- TESTIMONI --}}
        @include('template/layout/testimoni')
    @endif
    
    {{-- FOOTER --}}
    @include('template/layout/footer')

    <script src="{{ asset('Assets/js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js"></script>
</body>

</html>
