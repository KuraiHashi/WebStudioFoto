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

        {{-- Notifikasi Sukses --}}
        @if(session('success'))
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
        @endif

        {{-- Form Unggah Foto --}}
        <form action="{{ route('admin.galery.store') }}" method="POST" enctype="multipart/form-data" class="fc" id="gup" style="margin-bottom: 22px">
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
                        <input type="checkbox" class="chk" name="selected_photos[]" value="{{ $item->id }}" aria-label="Pilih foto" />
                        <div class="ph" style="background-image: url('{{ asset('storage/' . $item->filename) }}'); background-size: cover; background-position: center;"></div>
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

    {{-- Script AJAX Sederhana untuk Hapus Terpilih --}}
    <script>
        document.getElementById('btn-delete-selected').addEventListener('click', function() {
            let selectedIds = [];
            document.querySelectorAll('.chk:checked').forEach(function(checkbox) {
                selectedIds.push(checkbox.closest('.gph').querySelector('.chk').value);
            });

            if (selectedIds.length === 0) {
                alert('Pilih foto yang ingin dihapus terlebih dahulu!');
                return;
            }

            if (confirm('Yakin ingin menghapus foto terpilih?')) {
                fetch('{{ route('admin.galery.destroy') }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ids: selectedIds })
                })
                .then(response => response.json())
                .then(data => {
                    location.reload();
                })
                .catch(error => console.error('Error:', error));
            }
        });
    </script>
@endsection