@extends('TemplateAdmin.main')
@section('galery', 'on')
@section('konten')
    <main class="main">
        <div class="top">
            <div>
                <div class="eyebrow">Galeri Studio</div>
                <h2 style="margin-top: 12px">Kelola <em>galeri</em></h2>
            </div>
        </div>

        {{-- Form Unggah Foto --}}
        <form action="{{ route('admin.galery.store') }}" method="POST" enctype="multipart/form-data" class="fc"
            id="gup" style="margin-bottom: 22px">
            @csrf
            <h3>Unggah foto</h3>
            <div class="fld">
                <label class="drop">
                    <span class="dn">Pilih atau seret foto portofolio</span><br />
                    <span class="note">JPG / PNG · maks. 5 MB per file</span>
                    <input type="file" name="photos[]" accept=".jpg,.jpeg,.png,image/*" multiple required />
                </label>
            </div>
            <div class="fld">
                <label for="upload-display-section">Tampilkan foto yang diunggah di</label>
                <select id="upload-display-section" name="display_section" required>
                    <option value="story" @selected(old('display_section', 'story') === 'story')>Kisah Terbingkai</option>
                    <option value="selected" @selected(old('display_section') === 'selected')>Karya Terpilih</option>
                </select>
            </div>
            <div class="acts">
                <button type="submit" class="btn ink">
                    Unggah
                </button>
            </div>
        </form>

        {{-- Daftar Foto Galeri --}}
        <div class="card">
            <form action="{{ route('admin.galery.display-sections') }}" method="POST">
                @csrf
                @method('PUT')
                <h3>Atur penempatan foto</h3>
                <p class="note" style="margin-bottom: 18px">Setiap foto dapat ditempatkan di Kisah Terbingkai atau Karya Terpilih.</p>
                <div class="mg big" id="gg">
                    @forelse($galeries as $item)
                        <div class="gph">
                            <input type="checkbox" class="chk" value="{{ $item->id }}" aria-label="Pilih foto untuk dihapus" />
                            <div class="ph"
                                style="background-image: url('{{ asset('storage/' . $item->filename) }}'); background-size: cover; background-position: center;">
                            </div>
                            <label class="gallery-placement" for="gallery-section-{{ $item->id }}">
                                Tampilkan di
                                <select id="gallery-section-{{ $item->id }}" name="display_sections[{{ $item->id }}]">
                                    <option value="story" @selected($item->display_section === 'story')>Kisah Terbingkai</option>
                                    <option value="selected" @selected($item->display_section === 'selected')>Karya Terpilih</option>
                                </select>
                            </label>
                        </div>
                    @empty
                        <p style="grid-column: 1 / -1; color: #777;">Belum ada foto di galeri.</p>
                    @endforelse
                </div>
                @if ($galeries->isNotEmpty())
                    <div class="acts">
                        <button type="submit" class="btn sm ink">Simpan penempatan</button>
                    </div>
                @endif
            </form>
            <div class="acts" style="margin-top: 16px">
                <button type="button" class="btn sm delete-btn" id="btn-delete-selected">Hapus terpilih</button>
            </div>
        </div>
    </main>

    {{-- Handler alert dan konfirmasi SweetAlert2 --}}
    <script>
        document.getElementById('btn-delete-selected').addEventListener('click', function() {
            let selectedIds = [];
            document.querySelectorAll('.chk:checked').forEach(function(checkbox) {
                selectedIds.push(checkbox.value); // Langsung ambil .value dari checkbox
            });

            // Validasi jika belum memilih foto
            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Pilih foto yang ingin dihapus terlebih dahulu!',
                });
                return;
            }

            // Konfirmasi Hapus
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Foto yang dipilih akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#a97f49',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ route('admin.galery.destroy') }}', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                ids: selectedIds
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: data.success || 'Foto terpilih berhasil dihapus.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Terjadi kesalahan pada server.',
                            });
                        });
                }
            });
        });
    </script>
@endsection
