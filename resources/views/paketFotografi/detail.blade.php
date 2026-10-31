@extends('template.main')

@section('konten_detail')
    <main class="pg" style="background: #F3EDE3; background: linear-gradient(180deg, rgba(37, 35, 31, 1) 80px, #F3EDE3 80px);">
        <div class="wrap">
            <div class="crumb">
                <a href="{{ url('/#paket') }}">Paket</a> / {{ $package->nama_paket ?? $package->name }}
            </div>
            <div class="det">
                <!-- Bagian Foto Utama -->
                <div class="ph-container">
                    @php
                        // Cek apakah kolom photos ada isinya dan berupa array
                        $photos = $package->photos ?? [];
                        $mainPhoto = count($photos) > 0 ? asset('storage/' . $photos[0]) : '';
                    @endphp

                    <!-- Foto Utama -->
                    <div class="ph b" id="mainPhoto"
                        style="
                        aspect-ratio: 4/5;
                        border-radius: 4px 4px 4px 110px;
                        max-height: 560px;
                        width: 100%;
                        background-image: url('{{ $mainPhoto }}');
                        background-size: cover;
                        background-position: center;
                        transition: background-image 0.3s ease;
                        ">
                    </div>

                    <!-- List Thumbnail Foto di Bawah -->
                    @if(is_array($photos) && count($photos) > 1)
                        <div class="photo-thumbnails" style="display: flex; gap: 10px; margin-top: 15px; overflow-x: auto; padding-bottom: 5px;">
                            @foreach($photos as $index => $photo)
                                <div class="thumb-item {{ $index === 0 ? 'active' : '' }}" 
                                   onclick="changeMainPhoto('{{ asset('storage/' . $photo) }}', this)"
                                   style="
                                   width: 70px; 
                                   height: 70px; 
                                   border-radius: 8px; 
                                   background-image: url('{{ asset('storage/' . $photo) }}'); 
                                   background-size: cover; 
                                   background-position: center; 
                                   cursor: pointer; 
                                   flex-shrink: 0;
                                   border: 2px solid {{ $index === 0 ? 'var(--bronze, #8C7A6B)' : 'transparent' }};
                                   transition: all 0.2s ease;">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div>
                    <div class="eyebrow">Detail Paket</div>
                    <h1 style="margin: 14px 0 8px">{{ $package->nama_paket ?? $package->name }}</h1>
                    <div style="font: 500 36px var(--serif); color: var(--bronze);">
                        Rp {{ number_format($package->harga ?? $package->price, 0, ',', '.') }}
                    </div>
                    <p class="lead" style="margin: 14px 0 24px">
                        {{ $package->deskripsi ?? $package->description }}
                    </p>
                    <div class="kv">
                        <i>Durasi</i><span>{{ $package->durasi ?? $package->duration }}</span>
                        <i>Fasilitas</i><span>{{ $package->fasilitas ?? $package->facilities }}</span>
                        <i>Bonus</i><span>{{ $package->bonus ?? '-' }}</span>
                    </div>
                    <div class="acts">
                        <a class="btn ink" href="{{ route('reservasi.index') }}?package_id={{ $package->id }}">Reservasi & Konsul paket ini</a>
                        <a class="btn" href="/">Kembali</a>
                    </div>
                </div>
            </div>

            <div class="eyebrow" style="margin: 56px 0 16px">
                Paket lainnya
            </div>
            <div class="chips">
                @foreach ($otherPackages as $other)
                    <a href="{{ route('paket.detail', $other->id) }}" class="chip">
                        {{ $other->nama_paket ?? $other->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </main>

    <!-- Script untuk mengganti foto utama saat thumbnail diklik -->
    <script>
        function changeMainPhoto(imageSrc, element) {
            // Ubah background foto utama
            document.getElementById('mainPhoto').style.backgroundImage = `url('${imageSrc}')`;
            
            // Atur efek aktif (border) pada thumbnail
            const thumbnails = document.querySelectorAll('.thumb-item');
            thumbnails.forEach(thumb => {
                thumb.style.border = '2px solid transparent';
            });
            element.style.border = '2px solid var(--bronze, #8C7A6B)';
        }
    </script>
@endsection