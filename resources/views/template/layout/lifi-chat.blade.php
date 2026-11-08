<section class="lifi-chat" aria-label="Asisten Lifi">
    <div class="lifi-panel" id="lifi-panel" aria-labelledby="lifi-title" hidden>
        <header class="lifi-header">
            <div class="lifi-avatar" aria-hidden="true">L</div>
            <div class="lifi-heading">
                <h2 id="lifi-title">Lifi</h2>
                <p>Teman kecil Liquif Picture</p>
            </div>
            <button class="lifi-close" type="button" aria-label="Tutup chat Lifi" data-lifi-close>&times;</button>
        </header>
        <div class="lifi-messages" id="lifi-messages" role="log" aria-live="polite" aria-relevant="additions">
            <div class="lifi-message lifi-message-assistant">
                Hai! Aku Lifi. Senang kamu mampir 😊 Mau tanya soal paket, reservasi, atau cek jadwal?
            </div>
        </div>
        <div class="lifi-suggestions" aria-label="Pertanyaan cepat">
            <button type="button" data-lifi-prompt="Ada paket dan harga apa saja?">Paket &amp; harga</button>
            <button type="button" data-lifi-prompt="Bagaimana cara reservasi?">Cara reservasi</button>
            <button type="button" data-lifi-prompt="Saya mau cek jadwal">Cek jadwal</button>
        </div>
        <form class="lifi-form" id="lifi-form" data-endpoint="{{ route('lifi.chat') }}">
            @csrf
            <label class="sr-only" for="lifi-input">Tulis pesan untuk Lifi</label>
            <input id="lifi-input" name="lifi_message" type="text" maxlength="500" autocomplete="off"
                placeholder="Tulis pesanmu..." required>
            <button type="submit" aria-label="Kirim pesan">&rarr;</button>
        </form>
        <p class="lifi-footnote">Jangan kirim data pribadi selain token reservasi.</p>
    </div>
    <button class="lifi-launcher" type="button" aria-label="Buka chat Lifi" aria-expanded="false"
        aria-controls="lifi-panel" data-lifi-toggle>
        <span class="lifi-launcher-mark" aria-hidden="true">L</span>
        <span class="lifi-launcher-label">Tanya Lifi</span>
    </button>
</section>
