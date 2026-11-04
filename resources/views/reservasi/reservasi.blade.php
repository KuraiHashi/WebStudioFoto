@extends('template.main')

@section('konten_detail')
    <main class="pg"
        style="background: #F3EDE3; background: linear-gradient(180deg, rgba(37, 35, 31, 1) 80px, #F3EDE3 80px);">
        <div class="wrap">
            <div class="crumb">
                <a href="index.html">Beranda</a> / Reservasi
            </div>
            <h1>Reservasi & <em>Konsultasi</em></h1>
            <p class="lead">
                Lengkapi data berikut. Pilih paket pemotretan dan jadwalkan sesi konsultasi jika Anda memerlukan diskusi
                konsep terlebih dahulu.
            </p>
            <div class="seq">
                <span>1 Pilih paket</span>
                <span class="on">2 Isi reservasi</span>
                <span>3 Cek status &amp; bayar</span>
            </div>

            <div class="two">
                <form action="{{ route('reservasi.store') }}" method="POST" class="fc" id="rform">
                    @csrf
                    <input type="hidden" name="source_from" value="{{ isset($selectedPackageId) ? 'detail' : 'direct' }}">

                    <h3>Form reservasi</h3>
                    <div class="fld">
                        <label>PAKET</label>
                        <select id="rp" name="photography_package_id" required>
                            <option value="" disabled {{ !isset($selectedPackageId) ? 'selected' : '' }}>Pilih paket
                                pemotretan</option>
                            @foreach ($packages as $pkg)
                                <option value="{{ $pkg->id }}" data-name="{{ $pkg->name }}"
                                    data-price="{{ number_format($pkg->price, 0, ',', '.') }}"
                                    data-duration="{{ $pkg->duration }}" data-facilities="{{ $pkg->facilities }}"
                                    {{ isset($selectedPackageId) && $selectedPackageId == $pkg->id ? 'selected' : '' }}>
                                    {{ $pkg->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- KONSEP TAMBAHAN: Opsi Pengajuan Konsultasi -->
                    <div class="fld"
                        style="margin-top: 20px; background: #faf6f0; padding: 16px; border-radius: 8px; border: 1px dashed #d4af37;">
                        <label
                            style="display: flex; align-items: center; cursor: pointer; font-weight: 600; color: #1a1814; margin-bottom: 0;">
                            <input type="checkbox" id="toggle-konsultasi" name="request_consultation" value="1"
                                style="width: 18px; height: 18px; accent-color: #1a1814; margin-right: 10px;" />
                            Ajukan Sesi Konsultasi Konsep / Jadwal
                        </label>
                        <p style="font-size: 12px; color: #666; margin: 6px 0 0 28px;">
                            Diskusikan pakaian (wardrobe), moodboard, atau detail lokasi langsung dengan fotografer kami.
                        </p>
                    </div>

                    <!-- Box Form Konsultasi -->
                    <div id="konsultasi-section"
                        style="display: none; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0d6cc;">
                        <h4
                            style="font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; color: #1a1814;">
                            Detail Konsultasi</h4>
                        <div class="r2">
                            <div class="fld">
                                <label>TOPIK UTAMA</label>
                                <select id="konsultasi-topik" name="consultation_topic">
                                    <option value="Konsep & Wardrobe">Konsep &amp; Wardrobe (Busana)</option>
                                    <option value="Pemilihan Lokasi">Pemilihan Lokasi Pemotretan</option>
                                    <option value="Custom Moodboard">Custom Moodboard / Gaya Foto</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="fld">
                                <label>MEDIA KONSULTASI</label>
                                <select id="konsultasi-media" name="consultation_media">
                                    <option value="WhatsApp Chat">WhatsApp Chat</option>
                                    <option value="Offline">Offline (Bertatap Muka)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Topik Lainnya -->
                        <div class="fld" id="topik-lainnya-container" style="display: none; margin-top: 15px;">
                            <label>TULISKAN TOPIK LAINNYA</label>
                            <input type="text" id="konsultasi-custom-topic" name="consultation_custom_topic"
                                placeholder="Contoh: Properti tambahan, Sesi khusus keluarga, dll."
                                style="width: 100%; padding: 12px; border-radius: 6px; border: 1px solid #ccc; background: #fff; font-family: inherit;" />
                        </div>

                        <!-- Media Offline -->
                        <div class="fld" id="media-offline-container" style="display: none; margin-top: 15px;">
                            <label>LOKASI KONSULTASI OFFLINE</label>
                            <input type="text" id="konsultasi-offline-location" name="consultation_offline_location"
                                placeholder="Contoh: Studio Utama / Kafe A"
                                style="width: 100%; padding: 12px; border-radius: 6px; border: 1px solid #ccc; background: #fff; font-family: inherit;" />
                        </div>

                        <div class="fld" style="margin-top: 15px;">
                            <label>CATATAN / IDE AWAL DARI ANDA</label>
                            <textarea id="konsultasi-catatan" name="consultation_notes"
                                placeholder="Tuliskan referensi gaya, warna tema, atau pertanyaan seputar paket..." rows="3"
                                style="width: 100%; padding: 12px; border-radius: 6px; border: 1px solid #ccc; background: #fff; font-family: inherit;"></textarea>
                        </div>
                    </div>

                    <div class="r2" style="margin-top: 20px;">
                        <div class="fld">
                            <label>NAMA</label>
                            <input id="rn" name="name" placeholder="Nama lengkap" required />
                        </div>
                        <div class="fld">
                            <label>KONTAK WHATSAPP</label>
                            <input id="rk" name="whatsapp" placeholder="081234567890" data-vt="phone" required />
                        </div>
                    </div>
                    <div class="r2">
                        <div class="fld">
                            <label>TANGGAL</label>
                            <input id="rt" name="date" type="date" placeholder="dd/mm/yyyy" data-vt="date"
                                required style="cursor: pointer;" />
                            <small style="color: #8c8275; font-size: 11px;">Pilih tanggal dari kalender di samping.</small>
                        </div>
                        <div class="fld">
                            <label>JAM PEMOTRETAN</label>
                            <!-- Input jam dibuat bebas diisi/diketik oleh user -->
                            <input id="rj" name="time" type="time" placeholder="--:--" required />

                            <!-- Container Informasi Jadwal Terisi -->
                            <div id="booked-info-container"
                                style="display: none; margin-top: 8px; background: #faf6f0; padding: 10px; border-radius: 6px; border: 1px solid #e0d6cc;">
                                <span
                                    style="font-size: 11px; font-weight: 600; color: #b8860b; display: block; margin-bottom: 4px;">⚠️
                                    Jadwal yang sudah terisi di tanggal ini:</span>
                                <div id="booked-list-text" style="font-size: 12px; color: #555;"></div>
                            </div>
                            <small id="time-helper" style="color: #8c8275; font-size: 11px;">Bebas memilih jam, pastikan
                                tidak bentrok.</small>
                        </div>
                    </div>
                    <div class="r2">
                        <div class="fld">
                            <label>DURASI</label>
                            <input type="text" id="rd" name="duration" placeholder="Otomatis dari paket"
                                readonly value="" />
                        </div>
                        <div class="fld">
                            <label>LOKASI PEMOTRETAN</label>
                            <input id="rl" name="location" placeholder="Studio / nama tempat" required />
                        </div>
                    </div>
                    <p class="note">
                        Tanggal yang sudah terisi penuh tidak dapat dipilih. Kami tidak pernah meminta OTP, PIN, atau
                        password Anda.
                    </p>
                    <div class="acts">
                        <button type="submit" class="btn ink"
                            style="background: #1a1814; color: #fff; border-radius: 30px; padding: 14px 28px; font-weight: 600; letter-spacing: 0.5px; cursor: pointer;">
                            KIRIM RESERVASI &amp; KONSULTASI
                        </button>
                    </div>
                </form>

                <div>
                    <div class="fc dk" id="rsum"
                        style="margin-bottom: 20px; background: #1a1814; color: #f3ede3; border-radius: 8px 8px 36px 8px; padding: 24px;">
                        <div
                            style="font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase; color: #c4b5a5; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <span style="display: inline-block; width: 24px; height: 1px; background: #c4b5a5;"></span>
                            RINGKASAN PAKET
                        </div>
                        <h2 id="sum-name"
                            style="font-family: serif; font-size: 28px; margin-bottom: 8px; font-weight: normal; color: #fff;">
                            Pilih Paket</h2>
                        <div id="sum-price"
                            style="font-family: serif; font-size: 24px; color: #d4af37; margin-bottom: 12px;">Rp 0</div>
                        <p id="sum-facilities" style="font-size: 13px; color: #c4b5a5; margin: 0; margin-bottom: 12px;">
                            Silakan pilih paket di samping untuk melihat detail.</p>

                        <div id="sum-consultation-badge"
                            style="display: none; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px; font-size: 12px; color: #d4af37;">
                            ✨ Termasuk Sesi Konsultasi Konsep
                        </div>
                    </div>

                    <div class="fc">
                        <h3>Ketersediaan jadwal</h3>
                        <div class="cl">
                            <button id="cp" aria-label="Bulan sebelumnya">&lsaquo;</button>
                            <b id="cm">Oktober 2026</b>
                            <button id="cn" aria-label="Bulan berikutnya">&rsaquo;</button>
                        </div>
                        <div class="cal" id="cal"></div>
                        <div class="legend">
                            <span><i></i>Terisi</span><span>Ketuk tanggal kosong untuk memilih</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- SCRIPT UTAMA, FORM, & SWEETALERT -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectPaket = document.getElementById('rp');
            const sumName = document.getElementById('sum-name');
            const sumPrice = document.getElementById('sum-price');
            const sumFacilities = document.getElementById('sum-facilities');
            const inputDuration = document.getElementById('rd');

            const toggleKonsultasi = document.getElementById('toggle-konsultasi');
            const konsultasiSection = document.getElementById('konsultasi-section');
            const sumConsultationBadge = document.getElementById('sum-consultation-badge');

            const selectTopik = document.getElementById('konsultasi-topik');
            const topikLainnyaContainer = document.getElementById('topik-lainnya-container');

            const selectMedia = document.getElementById('konsultasi-media');
            const offlineContainer = document.getElementById('media-offline-container');

            function updateSummary() {
                const selectedOption = selectPaket.options[selectPaket.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    sumName.textContent = selectedOption.getAttribute('data-name');
                    sumPrice.textContent = 'Rp ' + selectedOption.getAttribute('data-price');
                    sumFacilities.textContent = selectedOption.getAttribute('data-duration') + ' - ' +
                        selectedOption.getAttribute('data-facilities');
                    inputDuration.value = selectedOption.getAttribute('data-duration');
                } else {
                    sumName.textContent = 'Pilih Paket';
                    sumPrice.textContent = 'Rp 0';
                    sumFacilities.textContent = 'Silakan pilih paket di samping untuk melihat detail.';
                    inputDuration.value = '';
                }
            }

            updateSummary();

            selectPaket.addEventListener('change', function() {
                updateSummary();
            });

            toggleKonsultasi.addEventListener('change', function() {
                if (this.checked) {
                    konsultasiSection.style.display = 'block';
                    sumConsultationBadge.style.display = 'block';
                } else {
                    konsultasiSection.style.display = 'none';
                    sumConsultationBadge.style.display = 'none';
                }
            });

            selectTopik.addEventListener('change', function() {
                if (this.value === 'Lainnya') {
                    topikLainnyaContainer.style.display = 'block';
                } else {
                    topikLainnyaContainer.style.display = 'none';
                }
            });

            selectMedia.addEventListener('change', function() {
                if (this.value === 'Offline') {
                    offlineContainer.style.display = 'block';
                } else {
                    offlineContainer.style.display = 'none';
                }
            });

            // SweetAlert Sukses Reservasi
            @if (session('swal_success'))
                let isConsultationChecked = {{ session('is_consultation') ? 'true' : 'false' }};
                let token = "{{ session('token') }}";

                Swal.fire({
                    icon: 'success',
                    title: isConsultationChecked ? 'Reservasi & Konsultasi Berhasil!' :
                        'Reservasi Berhasil!',
                    html: `
                        <p style="font-size: 14px; color: #555; margin-bottom: 10px;">
                            ${isConsultationChecked 
                                ? 'Terima kasih, data reservasi dan pengajuan konsultasi Anda telah kami simpan.' 
                                : 'Terima kasih, data reservasi Anda telah kami simpan. Silakan lakukan pembayaran DP.'}
                        </p>
                        <div style="background: #f8f9fa; border: 1px dashed #d4af37; padding: 12px; border-radius: 8px; margin-top: 10px;">
                            <span style="font-size: 11px; text-transform: uppercase; color: #888; display: block;">Token Reservasi Anda (Wajib Disimpan):</span>
                            <strong style="font-size: 20px; color: #1a1814; letter-spacing: 1.5px;">${token}</strong>
                        </div>
                    `,
                    confirmButtonText: isConsultationChecked ? 'Konfirmasi ke Admin (WhatsApp)' :
                        'Cek Detail Reservasi & Bayar',
                    confirmButtonColor: isConsultationChecked ? '#25D366' : '#191512',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (isConsultationChecked) {
                            window.open("{{ session('whatsapp_url') }}", '_blank');
                            window.location.href = "{{ route('reservasi.index') }}";
                        } else {
                            window.location.href = "{{ route('reservasi.result') }}?token=" + token;
                        }
                    }
                });
            @endif
        });
    </script>

    {{-- SCRIPT KALENDER & INFORMASI JAM TERISI --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calContainer = document.getElementById('cal');
            const monthLabel = document.getElementById('cm');
            const prevBtn = document.getElementById('cp');
            const nextBtn = document.getElementById('cn');

            const inputDate = document.getElementById('rt');

            let currentDate = new Date();
            let bookedSlots = [];

            function fetchBookedSlots() {
                fetch("{{ route('reservasi.booked-slots') }}")
                    .then(response => response.json())
                    .then(data => {
                        bookedSlots = data;
                        renderCalendar();
                    })
                    .catch(error => console.error('Gagal memuat jadwal:', error));
            }

            function renderCalendar() {
                if (!calContainer) return;
                calContainer.innerHTML = '';

                let year = currentDate.getFullYear();
                let month = currentDate.getMonth();

                const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus",
                    "September", "Oktober", "November", "Desember"
                ];
                monthLabel.textContent = `${monthNames[month]} ${year}`;

                let firstDayIndex = new Date(year, month, 1).getDay();
                let totalDays = new Date(year, month + 1, 0).getDate();

                for (let i = 0; i < firstDayIndex; i++) {
                    let emptyCell = document.createElement('div');
                    emptyCell.className = 'cal-day empty';
                    calContainer.appendChild(emptyCell);
                }

                for (let day = 1; day <= totalDays; day++) {
                    let dayCell = document.createElement('div');
                    dayCell.className = 'cal-day';

                    let formattedMonth = String(month + 1).padStart(2, '0');
                    let formattedDay = String(day).padStart(2, '0');
                    let dateString = `${year}-${formattedMonth}-${formattedDay}`;

                    dayCell.textContent = day;

                    let isFullyBooked = checkIfDateIsFullyBooked(dateString);

                    if (isFullyBooked) {
                        dayCell.classList.add('booked');
                        dayCell.title = "Tanggal ini sudah penuh";
                    } else {
                        dayCell.classList.add('available');
                        dayCell.addEventListener('click', function() {
                            document.querySelectorAll('.cal-day').forEach(el => el.classList.remove(
                                'selected'));
                            dayCell.classList.add('selected');
                            inputDate.value = dateString;
                            showBookedTimesForDate(dateString);
                        });
                    }

                    calContainer.appendChild(dayCell);
                }
            }

            if (inputDate) {
                inputDate.addEventListener('change', function() {
                    if (this.value) {
                        showBookedTimesForDate(this.value);
                    }
                });
            }

            function checkIfDateIsFullyBooked(dateStr) {
                const existing = bookedSlots.filter(item => item.date === dateStr);
                return existing.length >= 4;
            }

            function showBookedTimesForDate(selectedDate) {
                const infoContainer = document.getElementById('booked-info-container');
                const listText = document.getElementById('booked-list-text');

                const existingReservations = bookedSlots.filter(item => item.date === selectedDate);

                if (existingReservations.length > 0) {
                    infoContainer.style.display = 'block';
                    let schedulesHtml = '<ul style="margin: 0; padding-left: 15px;">';
                    existingReservations.forEach(res => {
                        schedulesHtml +=
                            `<li>Jam <strong>${res.time}</strong> (Durasi: ${res.duration || '2 Jam'})</li>`;
                    });
                    schedulesHtml += '</ul>';
                    listText.innerHTML = schedulesHtml;
                } else {
                    infoContainer.style.display = 'block';
                    listText.innerHTML = '<em>Belum ada jadwal pemotretan di tanggal ini (Masih kosong).</em>';
                }
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentDate.setMonth(currentDate.getMonth() - 1);
                    renderCalendar();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentDate.setMonth(currentDate.getMonth() + 1);
                    renderCalendar();
                });
            }

            fetchBookedSlots();
        });
    </script>

    <style>
        .cal {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
            margin-top: 15px;
            text-align: center;
        }

        .cal-day {
            padding: 10px 0;
            font-size: 13px;
            border-radius: 6px;
            cursor: pointer;
            background: #f4efe6;
            color: #2c2825;
            transition: all 0.2s;
        }

        .cal-day.empty {
            background: transparent;
            cursor: default;
        }

        .cal-day.available:hover {
            background: #e6dec5;
        }

        .cal-day.selected {
            background: #191512 !important;
            color: #fff !important;
            font-weight: bold;
        }

        .cal-day.booked {
            background: #e0d0c0;
            color: #999;
            text-decoration: line-through;
            cursor: not-allowed;
        }
    </style>
@endsection
