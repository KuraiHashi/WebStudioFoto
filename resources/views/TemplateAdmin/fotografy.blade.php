@extends('TemplateAdmin.main')
@section('fotografy', 'on')
@section('konten')

<style>
    /* Menghilangkan scrollbar pada tabel */
    .tw::-webkit-scrollbar {
        display: none;
    }
    .tw {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    /* Menghilangkan scrollbar pada Modal Edit & Tambah */
    .md::-webkit-scrollbar {
        display: none;
    }
    .md {
        scrollbar-width: none;
        -ms-overflow-style: none;
        max-height: 90vh;
        overflow-y: auto; 
    }
</style>

    <main class="main">
        <div class="top">
            <div>
                <div class="eyebrow">Paket Fotografi</div>
                <h2 style="margin-top: 12px">Kelola <em>paket</em></h2>
            </div>
            <button class="btn gold" type="button" onclick="openModal('m-tambah')">
                + Tambah paket
            </button>
        </div>

        <div class="card bare">
            <div class="tw rt" style="overflow-x: auto;">
                <!-- Tabel Data -->
                <table class="table" id="ptab-manual" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="padding: 12px; text-align: left;">Nama Paket</th>
                            <th style="padding: 12px; text-align: left;">Kategori</th>
                            <th style="padding: 12px; text-align: left;">Harga</th>
                            <th style="padding: 12px; text-align: left;">Durasi</th>
                            <th style="padding: 12px; text-align: left;">Deskripsi</th>
                            <th style="padding: 12px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pakets as $item)
                        <tr>
                            <td style="padding: 12px;">
                                <strong>{{ $item->name }}</strong>
                                @if($item->is_wedding)
                                    <br><span style="background: #d4af37; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 3px;">Wedding / Full Day</span>
                                @endif
                                @if($item->bonus)
                                    <br><small style="color: #b8860b;">Bonus: {{ $item->bonus }}</small>
                                @endif
                            </td>
                            <td style="padding: 12px;">{{ $item->category }}</td>
                            <td style="padding: 12px;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td style="padding: 12px;">{{ $item->duration }}</td>
                            <td style="padding: 12px;">{{ $item->description ?? '-' }}</td>
                            <td style="padding: 12px; text-align: center;">
                                <div style="display: flex; justify-content: center; gap: 4px;">
                                    <!-- Tombol Edit -->
                                    <button type="button" class="btn sm" onclick="openModal('m-edit-{{ $item->id }}')">Edit</button>

                                    <!-- Tombol Hapus -->
                                    <button type="button" class="btn sm" style="background: black; color: white;" onclick="confirmDelete('{{ $item->id }}')">Hapus</button>
                                    
                                    <form id="delete-form-{{ $item->id }}" action="{{ route('admin.fotografi.destroy', $item->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 20px; text-align: center; color: #777;">Belum ada paket fotografi tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Edit Ditempatkan di Luar Tabel agar Struktur HTML Bersih -->
    @foreach ($pakets as $item)
    <div class="ov" id="m-edit-{{ $item->id }}">
        <div class="md w">
            <button type="button" class="x" onclick="closeModal('m-edit-{{ $item->id }}')">×</button>
            <div class="eyebrow">Paket fotografi</div>
            <h3>Edit paket</h3>
            
            <form method="POST" action="{{ route('admin.fotografi.update', $item->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="r2">
                    <div class="fld">
                        <label>Nama paket</label>
                        <input name="name" value="{{ $item->name }}" required />
                    </div>
                    <div class="fld">
                        <label>Kategori filter</label>
                        <input name="category" value="{{ $item->category }}" list="package-category-options" maxlength="80" placeholder="mis. Wedding atau Pas Foto" required />
                        <small style="color: #666;">Pilih kategori yang ada atau ketik kategori baru.</small>
                    </div>
                </div>
                <div class="r2">
                    <div class="fld">
                        <label>Harga (Rp)</label>
                        <input name="price" value="{{ $item->price }}" inputmode="numeric" required />
                    </div>
                </div>
                <div class="r2">
                    <div class="fld">
                        <label>Durasi</label>
                        <input name="duration" value="{{ $item->duration }}" placeholder="mis. 4 jam, 10 menit, 30 detik, atau 1 hari" required />
                        <small style="color: #666;">Gunakan satuan jam, menit, detik, atau hari.</small>
                    </div>
                    <div class="fld">
                        <label>Bonus (opsional)</label>
                        <input name="bonus" value="{{ $item->bonus }}" />
                    </div>
                </div>
                <div class="fld">
                    <label>Deskripsi</label>
                    <input name="description" value="{{ $item->description }}" />
                </div>
                <div class="fld">
                    <label>Fasilitas (pisahkan dengan koma)</label>
                    <input name="facilities" value="{{ $item->facilities }}" required />
                </div>
                
                <!-- Checkbox Opsi Paket Wedding / Full Day -->
                <div class="fld" style="margin-top: 15px; background: #faf6f0; padding: 12px; border-radius: 6px; border: 1px dashed #d4af37;">
                    <label style="display: flex; align-items: center; cursor: pointer; font-weight: 600; margin-bottom: 0;">
                        <input type="checkbox" name="is_wedding" value="1" {{ $item->is_wedding ? 'checked' : '' }} 
                            style="width: 18px; height: 18px; accent-color: #1a1814; margin-right: 10px;" />
                        Jadikan sebagai Paket Wedding / Full Day (Mengunci Tanggal Total)
                    </label>
                    <small style="color: #666; font-size: 11px; display: block; margin-top: 4px; margin-left: 28px;">Jika dicentang, pemesanan paket ini akan menutup total tanggal tersebut dari pesanan lain.</small>
                </div>

                {{-- Input File Galeri --}}
                <div class="fld" style="margin-top: 15px;">
                    <label>Tambah Foto Paket (Opsional)</label>
                    <label class="drop">
                        <span class="dn">Pilih atau seret foto tambahan paket</span><br />
                        <span class="note">JPG / PNG / WEBP · maks. 4 MB per file</span>
                        <input type="file" name="photos[]" accept=".jpg,.jpeg,.png,.webp,image/*" multiple />
                    </label>
                </div>

                {{-- Preview Foto Saat Ini --}}
                @if($item->photos)
                    <div class="fld">
                        <label>Foto Saat Ini:</label>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 5px;">
                            @foreach($item->photos as $photo)
                                <img src="{{ asset('storage/' . $photo) }}" alt="Foto" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" />
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="acts">
                    <button type="button" class="btn sm" onclick="closeModal('m-edit-{{ $item->id }}')">Batal</button>
                    <button type="submit" class="btn sm ink">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach

    <!-- Modal Tambah Paket -->
    <div class="ov" id="m-tambah">
        <div class="md w">
            <button type="button" class="x" onclick="closeModal('m-tambah')">×</button>
            <div class="eyebrow">Paket fotografi</div>
            <h3>Tambah paket</h3>
            
            <form method="POST" action="{{ route('admin.fotografi.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="r2">
                    <div class="fld">
                        <label>Nama paket</label>
                        <input name="name" required />
                    </div>
                    <div class="fld">
                        <label>Kategori filter</label>
                        <input name="category" list="package-category-options" maxlength="80" placeholder="mis. Wedding, Prewedding, Wisuda, Pas Foto" required />
                        <small style="color: #666;">Pilih kategori yang ada atau ketik kategori baru.</small>
                    </div>
                </div>
                <div class="r2">
                    <div class="fld">
                        <label>Harga (Rp)</label>
                        <input name="price" inputmode="numeric" required />
                    </div>
                </div>
                <div class="r2">
                    <div class="fld">
                        <label>Durasi</label>
                        <input name="duration" placeholder="mis. 4 jam, 10 menit, 30 detik, atau 1 hari" required />
                        <small style="color: #666;">Gunakan satuan jam, menit, detik, atau hari.</small>
                    </div>
                    <div class="fld">
                        <label>Bonus (opsional)</label>
                        <input name="bonus" />
                    </div>
                </div>
                <div class="fld">
                    <label>Deskripsi</label>
                    <input name="description" placeholder="Ringkasan singkat paket" />
                </div>
                <div class="fld">
                    <label>Fasilitas (pisahkan dengan koma)</label>
                    <input name="facilities" placeholder="20 foto sunting, 1 lokasi, galeri privat" required />
                </div>

                <!-- Checkbox Opsi Paket Wedding / Full Day untuk Tambah -->
                <div class="fld" style="margin-top: 15px; background: #faf6f0; padding: 12px; border-radius: 6px; border: 1px dashed #d4af37;">
                    <label style="display: flex; align-items: center; cursor: pointer; font-weight: 600; margin-bottom: 0;">
                        <input type="checkbox" name="is_wedding" value="1" style="width: 18px; height: 18px; accent-color: #1a1814; margin-right: 10px;" />
                        Jadikan sebagai Paket Wedding / Full Day (Mengunci Tanggal Total)
                    </label>
                    <small style="color: #666; font-size: 11px; display: block; margin-top: 4px; margin-left: 28px;">Jika dicentang, pemesanan paket ini akan menutup total tanggal tersebut dari pesanan lain.</small>
                </div>

                {{-- Input File Galeri --}}
                <div class="fld" style="margin-top: 15px;">
                    <label>Foto Paket (Bisa pilih banyak sekaligus)</label>
                    <label class="drop">
                        <span class="dn">Pilih atau seret foto portofolio paket</span><br />
                        <span class="note">JPG / PNG / WEBP · maks. 4 MB per file</span>
                        <input type="file" name="photos[]" accept=".jpg,.jpeg,.png,.webp,image/*" multiple />
                    </label>
                </div>

                <div class="acts">
                    <button type="button" class="btn sm" onclick="closeModal('m-tambah')">Batal</button>
                    <button type="submit" class="btn sm ink">Simpan paket</button>
                </div>
            </form>
        </div>
    </div>

    <datalist id="package-category-options">
        @foreach ($packageCategories as $category)
            <option value="{{ $category }}">
        @endforeach
    </datalist>

    {{-- Script SweetAlert2 & Modal Handler --}}
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2500
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan!',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonText: 'OK'
            });
        </script>
    @endif

    <script>
        function openModal(modalId) {
            let modal = document.getElementById(modalId);
            if (modal) modal.classList.add('on');
        }

        function closeModal(modalId) {
            let modal = document.getElementById(modalId);
            if (modal) modal.classList.remove('on');
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Paket fotografi yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
@endsection