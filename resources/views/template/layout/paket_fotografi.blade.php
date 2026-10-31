<section id="paket" style="background:var(--sand)">
    <div class="wrap">
        <div class="rv">
            <div class="eyebrow">Paket Layanan</div>
            <h2 style="margin-top:14px">Pilih sesi yang <em>paling sesuai</em></h2>
        </div>
        
        <div class="pk rv">
            @forelse($packages as $index => $package)
                @php
                    $isFeatured = ($index === 1); 
                    $romans = ['I', 'II', 'III', 'IV', 'V', 'VI'];
                    $rn = $romans[$index] ?? ($index + 1);
                    
                    // Memecah fasilitas berdasarkan titik tengah (·) ATAU koma (,)
                    // agar teks yang dipisah koma otomatis turun ke bawah menjadi list baru
                    $facilities = preg_split('/[·,]/', $package->facilities);
                @endphp

                <div class="pc {{ $isFeatured ? 'f' : '' }}">
                    <div class="rn">{{ $rn }}</div>
                    <h3>{{ $package->name }}</h3>
                    <div class="pr">Rp {{ number_format($package->price, 0, ',', '.') }}</div>
                    <span class="note" {{ $isFeatured ? 'style=color:#bfb4a2' : '' }}>{{ $package->duration }}</span>
                    
                    <ul>
                        @foreach($facilities as $facility)
                            @if(trim($facility) !== '')
                                <li>{{ trim($facility) }}</li>
                            @endif
                        @endforeach
                        
                        @if($package->bonus && $package->bonus !== '—')
                            <li>Bonus {{ $package->bonus }}</li>
                        @endif
                    </ul>

                    <a class="btn sm {{ $isFeatured ? 'gold' : '' }}" href="paket.html?p={{ urlencode($package->name) }}">Lihat detail</a>
                </div>
            @empty
                <p class="note">Belum ada paket tersedia.</p>
            @endforelse
        </div>
    </div>
</section>