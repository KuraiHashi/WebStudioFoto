<section id="testimoni">
    <div class="wrap">
        <div class="rv">
            <div class="eyebrow">Testimoni</div>
        </div>
        <div class="tq rv">
            <div>
                @if($testimonials->isNotEmpty())
                    @php($featuredTestimonial = $testimonials->first())
                    <div class="st">{{ str_repeat('★', $featuredTestimonial->rating) }}{{ str_repeat('☆', 5 - $featuredTestimonial->rating) }}</div>
                    <blockquote>“{{ $featuredTestimonial->message }}”</blockquote>
                    <p class="note" style="margin-top:18px">— Pelanggan terverifikasi</p>
                @else
                    <blockquote>Belum ada testimoni. Jadilah pelanggan pertama yang berbagi pengalaman.</blockquote>
                @endif
            </div>
            <div>
                @foreach($testimonials->skip(1) as $testimonial)
                    <div class="sq">
                        <div class="st" style="margin:0 0 6px">{{ str_repeat('★', $testimonial->rating) }}{{ str_repeat('☆', 5 - $testimonial->rating) }}</div>
                        “{{ $testimonial->message }}”
                        <div class="note">— Pelanggan terverifikasi</div>
                    </div>
                @endforeach
                <a class="btn sm" style="margin-top:10px" href="{{ route('reservasi.cek') }}">Tulis testimoni</a>
                <p class="note" style="margin-top:8px">Masukkan kode reservasi di menu Cek Reservasi setelah status reservasi Selesai.</p>
            </div>
        </div>
    </div>
</section>