<footer id="kontak">
    <div class="wrap">
        <div class="fg">
            <div><a class="logo" href="index.html" style="margin-bottom:18px"><i></i>liquif
                    <small>Picture</small></a>
                <p>{{ $studio->description ?? '-'}}</p>
            </div>
            <div>
                <h4>Info Studio</h4>
                <p>
                    {{ $studio->address ?? 'Alamat' }}<br>
                    {{ $studio->phone ?? 'Contact' }}<br>
                    {{ $studio->operational_hours ?? 'Jam Operational' }}<br>
                    {{ $studio->social_media ?? 'Media Sosial' }}
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
        <div class="copy"><span>© Liquif Picture</span><a href="login.html">Login Admin</a></div>
    </div>
</footer>
