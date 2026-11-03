@extends('TemplateAdmin.main')
@section('rekening', 'on')
@section('konten')
    <main class="main">
        <div class="top">
            <div>
                <div class="eyebrow">Metode Pembayaran</div>
                <h2 style="margin-top: 12px">Kelola <em>rekening</em></h2>
            </div>
            <button class="btn gold" type="button" onclick="openModal('m-tambah')">
                + Tambah rekening
            </button>
        </div>

        <div class="card bare">
            <div class="tw rt" style="overflow-x: auto;">
                <!-- Tabel Data -->
                <table class="table" id="ptab-manual" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="padding: 12px; text-align: left;">Nama Bank</th>
                            <th style="padding: 12px; text-align: left;">No. Rekening</th>
                            <th style="padding: 12px; text-align: left;">Nama Pemilik</th>
                            <th style="padding: 12px; text-align: center;">QRIS</th>
                            <th style="padding: 12px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rekenings as $item)
                            <tr>
                                <td style="padding: 12px;">
                                    <strong>{{ $item->nama_bank }}</strong>
                                </td>
                                <td style="padding: 12px; font-family: monospace; font-size: 1.05rem;">
                                    {{ $item->no_rekening }}
                                </td>
                                <td style="padding: 12px;">{{ $item->nama_pemilik }}</td>
                                <td style="padding: 12px; text-align: center;">
                                    @if ($item->foto_qris)
                                        <img src="{{ asset('storage/' . $item->foto_qris) }}" alt="QRIS"
                                            style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;" />
                                    @else
                                        <span style="color: #999; font-size: 0.9rem;">-</span>
                                    @endif
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <div style="display: flex; justify-content: center; gap: 4px;">
                                        <!-- Tombol Edit -->
                                        <button type="button" class="btn sm"
                                            onclick="openModal('m-edit-{{ $item->id }}')">Edit</button>

                                        <!-- Tombol Hapus dengan SweetAlert2 -->
                                        <form id="delete-form-{{ $item->id }}"
                                            action="{{ route('rekening.destroy', $item->id) }}" method="POST"
                                            style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn sm ink"
                                                style="background-color: #dc3545; color: white; border: none;"
                                                onclick="confirmDelete({{ $item->id }})">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Edit untuk setiap item -->
                            <div class="ov" id="m-edit-{{ $item->id }}">
                                <div class="md w">
                                    <button type="button" class="x"
                                        onclick="closeModal('m-edit-{{ $item->id }}')">×</button>
                                    <div class="eyebrow">Metode Pembayaran</div>
                                    <h3>Edit rekening</h3>

                                    <form method="POST" action="{{ route('rekening.update', $item->id) }}"
                                        enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')

                                        <div class="r2">
                                            <div class="fld">
                                                <label>Nama Bank</label>
                                                <input name="nama_bank" value="{{ $item->nama_bank }}" required />
                                            </div>
                                            <div class="fld">
                                                <label>Nomor Rekening</label>
                                                <input name="no_rekening" value="{{ $item->no_rekening }}" inputmode="numeric"
                                                    required />
                                            </div>
                                        </div>

                                        <div class="fld">
                                            <label>Nama Pemilik Rekening</label>
                                            <input name="nama_pemilik" value="{{ $item->nama_pemilik }}" required />
                                        </div>

                                        {{-- Input File QRIS --}}
                                        <div class="fld">
                                            <label>Ganti Foto QRIS (Opsional)</label>
                                            <label class="drop">
                                                <span class="dn">Pilih atau seret foto QRIS baru</span><br />
                                                <span class="note">JPG / PNG / WEBP · maks. 2 MB</span>
                                                <input type="file" name="foto_qris" accept=".jpg,.jpeg,.png,.webp,image/*" />
                                            </label>
                                        </div>

                                        {{-- Preview Foto QRIS Saat Ini --}}
                                        @if ($item->foto_qris)
                                            <div class="fld">
                                                <label>QRIS Saat Ini:</label>
                                                <div style="margin-top: 5px;">
                                                    <img src="{{ asset('storage/' . $item->foto_qris) }}" alt="QRIS"
                                                        style="width: 70px; height: 70px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;" />
                                                </div>
                                            </div>
                                        @endif

                                        <div class="acts">
                                            <button type="button" class="btn sm"
                                                onclick="closeModal('m-edit-{{ $item->id }}')">Batal</button>
                                            <button type="submit" class="btn sm ink">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 20px; text-align: center; color: #777;">Belum ada data rekening tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Tambah Rekening -->
    <div class="ov" id="m-tambah">
        <div class="md w">
            <button type="button" class="x" onclick="closeModal('m-tambah')">×</button>
            <div class="eyebrow">Metode Pembayaran</div>
            <h3>Tambah rekening</h3>

            <form method="POST" action="{{ route('rekening.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="r2">
                    <div class="fld">
                        <label>Nama Bank</label>
                        <input name="nama_bank" placeholder="mis. BCA / Mandiri / QRIS" required />
                    </div>
                    <div class="fld">
                        <label>Nomor Rekening</label>
                        <input name="no_rekening" placeholder="mis. 1234567890" inputmode="numeric" required />
                    </div>
                </div>

                <div class="fld">
                    <label>Nama Pemilik Rekening</label>
                    <input name="nama_pemilik" placeholder="Atas Nama Sesuai Buku Rekening" required />
                </div>

                {{-- Input File QRIS --}}
                <div class="fld">
                    <label>Foto QRIS (Opsional)</label>
                    <label class="drop">
                        <span class="dn">Pilih atau seret file QRIS</span><br />
                        <span class="note">JPG / PNG / WEBP · maks. 2 MB</span>
                        <input type="file" name="foto_qris" accept=".jpg,.jpeg,.png,.webp,image/*" />
                    </label>
                </div>

                <div class="acts">
                    <button type="button" class="btn sm" onclick="closeModal('m-tambah')">Batal</button>
                    <button type="submit" class="btn sm ink">Simpan rekening</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Script SweetAlert2 Otomatis untuk Flash Message Laravel --}}
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

        // Fungsi konfirmasi hapus menggunakan SweetAlert2
        function confirmDelete(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data rekening yang dihapus tidak dapat dikembalikan!",
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