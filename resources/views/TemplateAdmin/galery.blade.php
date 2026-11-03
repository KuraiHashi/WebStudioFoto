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
            <div class="acts">
                <button type="submit" class="btn ink">
                    Unggah
                </button>
            </div>
        </form>

        {{-- Daftar Foto Galeri --}}
        <div class="card">
            <h3>Foto galeri</h3>
            <div class="mg big" id="gg">
                @forelse($galeries as $item)
                    <div class="gph">
                        <input type="checkbox" class="chk" name="selected_photos[]" value="{{ $item->id }}"
                            aria-label="Pilih foto" />
                        <div class="ph"
                            style="background-image: url('{{ asset('storage/' . $item->filename) }}'); background-size: cover; background-position: center;">
                        </div>
                    </div>
                @empty
                    <p style="grid-column: 1 / -1; color: #777;">Belum ada foto di galeri.</p>
                @endforelse
            </div>
            <div class="acts">
                <button type="button" class="btn sm no" id="btn-delete-selected">
                    Hapus terpilih
                </button>
            </div>
        </div>
    </main>

    {{-- Script SweetAlert2 --}}
    <script>
        // 1. Notifikasi Sukses saat Upload / Session Laravel
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        // 2. Notifikasi Error Validasi Laravel
        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal Mengunggah!',
                text: 'Cek kembali foto anda, pastikan foto tidak lebih dari 5mb',
                confirmButtonText: 'OK'
            });
        @endif

        // 2. Handler Tombol Hapus Terpilih dengan SweetAlert2
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
                confirmButtonColor: '#d33',
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
