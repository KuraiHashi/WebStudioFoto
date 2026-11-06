@php
    $socialMedia = trim((string) ($studio->social_media ?? ''));
    $socialMediaUrl = null;
    $socialMediaLabel = $socialMedia;
    $isInstagram = false;

    if (preg_match('/^@([A-Za-z0-9._]{1,30})$/', $socialMedia, $matches) === 1) {
        $socialMediaUrl = 'https://www.instagram.com/' . $matches[1] . '/';
        $socialMediaLabel = '@' . $matches[1];
        $isInstagram = true;
    } elseif (filter_var($socialMedia, FILTER_VALIDATE_URL) !== false) {
        $scheme = strtolower((string) parse_url($socialMedia, PHP_URL_SCHEME));

        if (in_array($scheme, ['http', 'https'], true)) {
            $socialMediaUrl = $socialMedia;
            $socialMediaHost = strtolower((string) parse_url($socialMedia, PHP_URL_HOST));
            $isInstagram = in_array($socialMediaHost, ['instagram.com', 'www.instagram.com'], true);

            if ($isInstagram) {
                $instagramPath = trim((string) parse_url($socialMedia, PHP_URL_PATH), '/');
                $instagramUsername = explode('/', $instagramPath, 2)[0];
                $reservedInstagramPaths = ['accounts', 'direct', 'explore', 'p', 'reel', 'reels', 'stories', 'tv'];

                $socialMediaLabel = preg_match('/^[A-Za-z0-9._]{1,30}$/', $instagramUsername) === 1
                    && !in_array(strtolower($instagramUsername), $reservedInstagramPaths, true)
                    ? '@' . $instagramUsername
                    : 'Instagram';
            }
        }
    }
@endphp

<footer id="kontak">
    <div class="wrap">
        <div class="fg">
            <div><a class="logo" href="{{ url('/') }}" style="margin-bottom:18px"
                    aria-label="Halaman utama Liquif Picture">
                    @if ($studio?->foto_studio)
                        <img src="{{ asset('storage/' . $studio->foto_studio) }}" alt=""
                            class="studio-photo-icon">
                    @else
                        <i></i>
                    @endif
                    liquif <small>Picture</small>
                </a>
                <p>{{ $studio->description ?? '-'}}</p>
            </div>
            <div>
                <h4>Info Studio</h4>
                <p>
                    {{ $studio->address ?? 'Alamat' }}<br>
                    {{ $studio->phone ?? 'Contact' }}<br>
                    {{ $studio->operational_hours ?? 'Jam Operational' }}<br>
                    @if ($socialMediaUrl)
                        <a class="social-link" href="{{ $socialMediaUrl }}" target="_blank" rel="noopener noreferrer"
                            aria-label="{{ $isInstagram ? 'Kunjungi Instagram studio' : 'Kunjungi media sosial studio' }} (terbuka di tab baru)">
                            @if ($isInstagram)
                                <svg class="social-link-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"
                                    focusable="false">
                                    <rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor"
                                        stroke-width="1.8" />
                                    <circle cx="12" cy="12" r="4.2" stroke="currentColor" stroke-width="1.8" />
                                    <circle cx="17.7" cy="6.5" r="1.1" fill="currentColor" />
                                </svg>
                            @endif
                            {{ $socialMediaLabel }}
                        </a>
                    @else
                        {{ $socialMedia ?: 'Media Sosial' }}
                    @endif
                </p>
            </div>
            <div>
                <h4>Lokasi</h4>
                <div class="map ph">
                    @if (!empty($studio->maps_link))
                        {!! $studio->maps_link !!}
                    @else
                        <span>Google Maps</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="copy"><span>© Liquif Picture</span><a href="{{ route('login') }}">Login Admin</a></div>
    </div>
</footer>
