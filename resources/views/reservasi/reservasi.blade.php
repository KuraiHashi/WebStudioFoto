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
                <!-- FORM KIRI: MENCAKUP INPUT PAKET, RINGKASAN PAKET, & DATA DIRI -->
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

                    <!-- RINGKASAN PAKET DIPINDAHKAN KE SINI (PAS DI BAWAH INPUT PAKET) -->
                    <div class="fc dk" id="rsum"
                        style="margin-top: 16px; margin-bottom: 20px; background: #1a1814; color: #f3ede3; border-radius: 8px 8px 24px 8px; padding: 20px;">
                        <div
                            style="font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; color: #c4b5a5; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                            <span style="display: inline-block; width: 16px; height: 1px; background: #c4b5a5;"></span>
                            RINGKASAN PAKET
                        </div>
                        <h2 id="sum-name"
                            style="font-family: serif; font-size: 22px; margin-bottom: 6px; font-weight: normal; color: #fff;">
                            Pilih Paket</h2>
                        <div id="sum-price"
                            style="font-family: serif; font-size: 20px; color: #d4af37; margin-bottom: 8px;">Rp 0</div>
                        <p id="sum-facilities" style="font-size: 12px; color: #c4b5a5; margin: 0; line-height: 1.4;">
                            Silakan pilih paket di atas untuk melihat detail.</p>

                        <div id="sum-consultation-badge"
                            style="display: none; border-top: 1px solid rgba(255,255,255,0.1); margin-top: 10px; padding-top: 8px; font-size: 11px; color: #d4af37;">
                            ✨ Termasuk Sesi Konsultasi Konsep
                        </div>
                    </div>

                    <!-- KONSEP TAMBAHAN: Opsi Pengajuan Konsultasi -->
                    <div class="fld"
                        style="margin-top: 15px; background: #faf6f0; padding: 16px; border-radius: 8px; border: 1px dashed #d4af37;">
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

                    <!-- Hidden Input Tanggal & Jam untuk Backend -->
                    <input type="hidden" id="rt" name="date" required />
                    <input type="hidden" id="rj" name="time" required />

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

                <!-- KOLOM KANAN: KHUSUS TANGGAL & WAKTU SESI SECARA VERTIKAL -->
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    
                    <!-- 1. Pilih Tanggal -->
                    <div class="fc" style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                        <h3 style="font-size: 16px; margin-bottom: 14px; font-weight: 600; color: #1a1814;">Pilih Tanggal</h3>
                        <div class="cl" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <button id="cp" aria-label="Bulan sebelumnya" style="background:none; border:none; cursor:pointer; font-size:16px;">&lsaquo;</button>
                            <b id="cm" style="font-size: 13px; color: #333;">November 2026</b>
                            <button id="cn" aria-label="Bulan berikutnya" style="background:none; border:none; cursor:pointer; font-size:16px;">&rsaquo;</button>
                        </div>
                        <!-- Header Hari -->
                        <div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; font-size: 11px; color: #888; margin-bottom: 6px;">
                            <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span>
                        </div>
                        <div class="cal" id="cal" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center;"></div>
                        
                        <!-- Legend / Indikator -->
                        <div style="display: flex; gap: 16px; align-items: center; margin-top: 16px; font-size: 11px; color: #666;">
                            <span style="display: flex; align-items: center; gap: 4px;"><i style="width: 8px; height: 8px; background: #d4af37; border-radius: 50%; display: inline-block;"></i> Tersedia[cite: 2]</span>
                            <span style="display: flex; align-items: center; gap: 4px;"><i style="width: 8px; height: 8px; background: #ccc; border-radius: 50%; display: inline-block;"></i> Terisi[cite: 2]</span>
                        </div>
                    </div>

                    <!-- 2. Waktu Sesi (Desain Tema Gelap Elegan) -->
                    <div class="fc dk" id="ketersediaan-card" style="background: #1a1814; color: #f3ede3; padding: 24px; border-radius: 8px 8px 36px 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: flex-start;">
                        <div style="font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase; color: #c4b5a5; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <span style="display: inline-block; width: 20px; height: 1px; background: #c4b5a5;"></span>
                            WAKTU SESI
                        </div>
                        <div id="selected-date-label" style="font-size: 13px; font-weight: 500; color: #d4af37; margin-bottom: 16px;">Pilih tanggal terlebih dahulu</div>
                        
                        <div id="ketersediaan-content" style="font-size: 12px; color: #c4b5a5; line-height: 1.5;">
                            <p style="color: #888; font-style: italic;">Pilih tanggal di kalender di atas untuk melihat jadwal terisi.</p>
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
                    sumFacilities.textContent = 'Silakan pilih paket di atas untuk melihat detail.';
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

    {{-- SCRIPT KALENDER & WAKTU SESUAI TEMA GELAP ELEGAN --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calContainer = document.getElementById('cal');
            const monthLabel = document.getElementById('cm');
            const prevBtn = document.getElementById('cp');
            const nextBtn = document.getElementById('cn');

            const inputDate = document.getElementById('rt');
            const inputTime = document.getElementById('rj');
            const dateLabel = document.getElementById('selected-date-label');
            const ketersediaanContent = document.getElementById('ketersediaan-content');

            let currentDate = new Date();
            let bookedSlots = [];
            let selectedDateStr = null;

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
                    calContainer.appendChild(emptyCell);
                }

                for (let day = 1; day <= totalDays; day++) {
                    let dayCell = document.createElement('div');
                    dayCell.className = 'cal-day';

                    let formattedMonth = String(month + 1).padStart(2, '0');
                    let formattedDay = String(day).padStart(2, '0');
                    let dateString = `${year}-${formattedMonth}-${formattedDay}`;

                    dayCell.textContent = day;
                    dayCell.style.cssText = "padding: 8px 0; font-size: 12px; border-radius: 6px; cursor: pointer; transition: all 0.2s; background: #faf6f0; color: #2c2825;";

                    let hasBookings = bookedSlots.some(item => item.date === dateString);
                    if (hasBookings) {
                        dayCell.style.border = "1px solid #d4af37";
                    }

                    if (dateString === selectedDateStr) {
                        dayCell.style.background = "#1a1814";
                        dayCell.style.color = "#fff";
                        dayCell.style.fontWeight = "bold";
                    }

                    dayCell.addEventListener('click', function() {
                        document.querySelectorAll('.cal-day').forEach(el => {
                            el.style.background = "#faf6f0";
                            el.style.color = "#2c2825";
                            el.style.fontWeight = "normal";
                        });
                        dayCell.style.background = "#1a1814";
                        dayCell.style.color = "#fff";
                        dayCell.style.fontWeight = "bold";

                        selectedDateStr = dateString;
                        inputDate.value = dateString;
                        
                        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                        dateLabel.textContent = new Date(dateString).toLocaleDateString('id-ID', options);

                        showBookingsAndFreeTimeInput(dateString);
                    });

                    calContainer.appendChild(dayCell);
                }
            }

            function showBookingsAndFreeTimeInput(dateStr) {
                const bookingsOnDate = bookedSlots.filter(item => item.date === dateStr);
                
                let html = `<div style="margin-bottom: 12px;">`;
                html += `<div style="font-size: 10px; text-transform: uppercase; font-weight: 700; color: #c4b5a5; margin-bottom: 6px; letter-spacing: 0.5px;">JADWAL TERISI DI TANGGAL INI:</div>`;
                
                if (bookingsOnDate.length > 0) {
                    html += `<div style="display: flex; flex-direction: column; gap: 6px; max-height: 90px; overflow-y: auto;">`;
                    bookingsOnDate.forEach(b => {
                        let match = (b.package && b.package.duration) ? b.package.duration.match(/\d+/) : null;
                        let dur = match ? parseInt(match[0]) : 2;
                        
                        let startTime = b.time.substring(0, 5);
                        let [sh, sm] = startTime.split(':').map(Number);
                        let endHour = sh + dur;
                        let endTime = String(endHour).padStart(2, '0') + ':' + String(sm).padStart(2, '0');

                        html += `<div style="padding: 8px 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(212,175,55,0.3); border-radius: 6px; font-size: 11px; color: #d4af37; font-weight: 600;">• ${startTime} - ${endTime} <span style="font-weight: normal; color: #a3988c; font-size: 10px;">(${dur} Jam)</span></div>`;
                    });
                    html += `</div>`;
                } else {
                    html += `<div style="padding: 8px 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; font-size: 11px; color: #a3988c; font-style: italic;">Belum ada jadwal terisi.</div>`;
                }
                html += `</div>`;

                // Bagian Pilih Jam Mulai
                html += `<div>`;
                html += `<div style="font-size: 10px; text-transform: uppercase; font-weight: 700; color: #c4b5a5; margin-bottom: 6px; letter-spacing: 0.5px;">PILIH JAM MULAI PEMOTRETAN</div>`;
                html += `<div style="position: relative;">`;
                html += `<input type="time" id="rj" name="time" required style="width: 100%; padding: 10px 12px; border-radius: 6px; border: 1px solid rgba(212,175,55,0.4); background: rgba(255,255,255,0.05); font-size: 13px; font-weight: 600; color: #f3ede3; box-sizing: border-box;" />`;
                html += `</div>`;
                html += `<div id="conflict-warning" style="display: none; margin-top: 6px; padding: 6px 8px; background: rgba(244,67,54,0.2); color: #ff8a80; border-radius: 6px; font-size: 10px; font-weight: 500;"></div>`;
                html += `</div>`;

                ketersediaanContent.innerHTML = html;

                const timeInput = document.getElementById('rj');
                timeInput.addEventListener('input', function() {
                    validateTimeConflict(dateStr, this.value);
                });
            }

            function validateTimeConflict(dateStr, selectedTime) {
                const warningBox = document.getElementById('conflict-warning');
                if (!selectedTime) {
                    warningBox.style.display = 'none';
                    return;
                }

                const selectPaket = document.getElementById('rp');
                const selectedOption = selectPaket.options[selectPaket.selectedIndex];
                let userDuration = 2; 
                if (selectedOption && selectedOption.value) {
                    let durStr = selectedOption.getAttribute('data-duration');
                    let match = durStr ? durStr.match(/\d+/) : null;
                    if (match) userDuration = parseInt(match[0]);
                }

                let newStart = new Date(`${dateStr} ${selectedTime}:00`).getTime();
                let newEnd = newStart + (userDuration * 3600 * 1000);

                let isConflict = false;
                const bookingsOnDate = bookedSlots.filter(item => item.date === dateStr);

                for (let b of bookingsOnDate) {
                    let match = (b.package && b.package.duration) ? b.package.duration.match(/\d+/) : null;
                    let existDuration = match ? parseInt(match[0]) : 2;

                    let existStart = new Date(`${b.date} ${b.time}`).getTime();
                    let existEnd = existStart + (existDuration * 3600 * 1000);

                    if (newStart < existEnd && newEnd > existStart) {
                        isConflict = true;
                        break;
                    }
                }

                if (isConflict) {
                    warningBox.textContent = "⚠️ Bentrok dengan jadwal lain! Durasi paket bersinggungan dengan sesi yang sudah ada.";
                    warningBox.style.display = 'block';
                } else {
                    warningBox.style.display = 'none';
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
@endsection