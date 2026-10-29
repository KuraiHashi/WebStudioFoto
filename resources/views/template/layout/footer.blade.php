<footer id="kontak">
    <div class="wrap">
        <div class="fg">
            <div><a class="logo" href="index.html" style="margin-bottom:18px"><i></i>liquif
                    <small>Picture</small></a>
                <p>Studio fotografi untuk momen yang ingin Anda simpan selamanya.</p>
            </div>
            <div>
                <h4>Info Studio</h4>
                <p>Jl. Contoh Alamat No. 00<br>0812-0000-0000<br>Senin–Sabtu · 09.00–17.00<br>@liquifpicture</p>
            </div>
            <div>
                <h4>Lokasi</h4>
                <div class="map ph">
                    @if(!empty($studio->maps_link))
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