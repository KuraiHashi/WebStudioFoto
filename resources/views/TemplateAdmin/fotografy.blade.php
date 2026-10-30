@extends('TemplateAdmin.main')
@section('fotografy', 'on')
@section('konten')
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

        {{-- Notifikasi Sukses --}}
        @if(session('success'))
            <div style="background: #dfe8d5; color: #3f5a35; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        {{-- Notifikasi Error Validasi --}}
        @if($errors->any())
            <div style="background: #f0d6cd; color: #8a3d2d; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px;">
                <ul style="margin-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card bare">
            <div class="tw rt" style="overflow-x: auto;">
                <!-- Tabel Data -->
                <table class="table" id="ptab-manual" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="padding: 12px; text-align: left;">Nama Paket</th>
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
                                @if($item->bonus)
                                    <br><small style="color: #b8860b;">Bonus: {{ $item->bonus }}</small>
                                @endif
                            </td>
                            <td style="padding: 12px;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td style="padding: 12px;">{{ $item->duration }}</td>
                            <td style="padding: 12px;">{{ $item->description ?? '-' }}</td>
                            <td style="padding: 12px; text-align: center;">
                                <div style="display: flex; justify-content: center; gap: 4px;">
                                    <!-- Tombol Edit -->
                                    <button type="button" class="btn sm" onclick="openModal('m-edit-{{ $item->id }}')">Edit</button>

                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('admin.fotografi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini?');" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn sm ink" style="background-color: #dc3545; color: white; border: none;">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit untuk setiap item -->
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
                                            <label>Harga (Rp)</label>
                                            <input name="price" value="{{ $item->price }}" inputmode="numeric" required />
                                        </div>
                                    </div>
                                    <div class="r2">
                                        <div class="fld">
                                            <label>Durasi</label>
                                            <input name="duration" value="{{ $item->duration }}" required />
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

                                    {{-- Input File Gaya Dropzone Galeri --}}
                                    <div class="fld">
                                        <label>Tambah Foto Paket (Bisa pilih banyak sekaligus)</label>
                                        <label class="drop">
                                            <span class="dn">Pilih atau seret foto tambahan paket</span><br />
                                            <span class="note">JPG / PNG / WEBP · maks. 2 MB per file</span>
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
                        @empty
                        <tr>
                            <td colspan="5" style="padding: 20px; text-align: center; color: #777;">Belum ada paket fotografi tersedia.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

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
                        <label>Harga (Rp)</label>
                        <input name="price" inputmode="numeric" required />
                    </div>
                </div>
                <div class="r2">
                    <div class="fld">
                        <label>Durasi</label>
                        <input name="duration" placeholder="mis. 2 jam" required />
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

                {{-- Input File Gaya Dropzone Galeri --}}
                <div class="fld">
                    <label>Foto Paket (Bisa pilih banyak sekaligus)</label>
                    <label class="drop">
                        <span class="dn">Pilih atau seret foto portofolio paket</span><br />
                        <span class="note">JPG / PNG / WEBP · maks. 4 MB per file</span>
                        <input type="file" name="photos[]" accept=".jpg,.jpeg,.png,.webp,image/*" multiple required />
                    </label>
                </div>

                <div class="acts">
                    <button type="button" class="btn sm" onclick="closeModal('m-tambah')">Batal</button>
                    <button type="submit" class="btn sm ink">Simpan paket</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            let modal = document.getElementById(modalId);
            if (modal) modal.classList.add('on');
        }

        function closeModal(modalId) {
            let modal = document.getElementById(modalId);
            if (modal) modal.classList.remove('on');
        }
    </script>
@endsection