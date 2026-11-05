@extends('template.main')

@section('konten_detail')
    <main class="pg reservation-page">
        <div class="wrap">
            <header class="reservation-heading">
                <div class="crumb">
                    <a href="/">Beranda</a> / Reservasi
                </div>
                <span class="reservation-kicker">MARI RENCANAKAN MOMEN ANDA</span>
                <h1>Reservasi & <em>Konsultasi</em></h1>
                <p class="lead">
                    Ceritakan sesi yang Anda impikan. Pilih paket, tentukan jadwal, dan kami akan membantu menyiapkan
                    setiap detailnya.
                </p>
            </header>

            <form action="{{ route('reservasi.store') }}" method="POST" class="reservation-form" id="rform">
                @csrf
                <input type="hidden" name="source_from" value="{{ isset($selectedPackageId) ? 'detail' : 'direct' }}">

                <section class="reservation-session-details">
                    <div class="reservation-form-heading">
                        <span>01 / DETAIL SESI</span>
                        <h3>Mulai dari cerita Anda</h3>
                        <p>Isi beberapa informasi agar kami dapat menyiapkan sesi yang paling sesuai.</p>
                    </div>
                    <div class="fld reservation-field">
                        <label>PAKET</label>
                        <select id="rp" name="photography_package_id" required>
                            <option value="" disabled
                                {{ !old('photography_package_id', $selectedPackageId ?? null) ? 'selected' : '' }}>Pilih paket
                                pemotretan</option>
                            @foreach ($packages as $pkg)
                                <option value="{{ $pkg->id }}" data-name="{{ $pkg->name }}"
                                    data-price="{{ number_format($pkg->price, 0, ',', '.') }}"
                                    data-duration="{{ $pkg->duration }}" data-facilities="{{ $pkg->facilities }}"
                                    data-wedding="{{ in_array($pkg->id, $weddingPackageIds, true) ? '1' : '0' }}"
                                    {{ old('photography_package_id', $selectedPackageId ?? null) == $pkg->id ? 'selected' : '' }}>
                                    {{ $pkg->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- KONSEP TAMBAHAN: Opsi Pengajuan Konsultasi -->
                    <div class="consultation-option">
                        <label for="toggle-konsultasi">
                            <input type="checkbox" id="toggle-konsultasi" name="request_consultation" value="1" />
                            <span class="consultation-option-copy">
                                <strong>Butuh sesi konsultasi?</strong>
                                <span>Diskusikan konsep, wardrobe, atau lokasi bersama fotografer kami.</span>
                            </span>
                        </label>
                    </div>

                    <!-- Box Form Konsultasi -->
                    <div id="konsultasi-section" class="consultation-details" style="display: none;">
                        <h4>Detail konsultasi</h4>
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
                        <div class="fld" id="topik-lainnya-container" style="display: none;">
                            <label>TULISKAN TOPIK LAINNYA</label>
                            <input type="text" id="konsultasi-custom-topic" name="consultation_custom_topic"
                                placeholder="Contoh: Properti tambahan, sesi khusus keluarga, dll." />
                        </div>

                        <!-- Media Offline -->
                        <div class="fld" id="media-offline-container" style="display: none;">
                            <label>LOKASI KONSULTASI OFFLINE</label>
                            <input type="text" id="konsultasi-offline-location" name="consultation_offline_location"
                                placeholder="Contoh: Studio utama / kafe" />
                        </div>

                        <div class="fld">
                            <label>CATATAN / IDE AWAL DARI ANDA</label>
                            <textarea id="konsultasi-catatan" name="consultation_notes"
                                placeholder="Tuliskan referensi gaya, warna tema, atau pertanyaan seputar paket..." rows="3"
                                ></textarea>
                        </div>
                    </div>
                </section>

                <input type="hidden" id="rt" name="date" value="{{ old('date') }}">
                <input type="hidden" id="rj" name="time" value="{{ old('time') }}">
                <input type="hidden" id="rd" name="duration" value="{{ old('duration') }}">

                <div class="reservation-booking-grid">
                    <section class="reservation-panel reservation-calendar">
                        <div class="reservation-calendar-heading">
                            <span>02 / PILIH TANGGAL</span>
                            <h3>Pilih Tanggal</h3>
                        </div>
                        <div class="cl reservation-calendar-nav">
                            <button id="cp" aria-label="Bulan sebelumnya" type="button">&lsaquo;</button>
                            <b id="cm">November 2026</b>
                            <button id="cn" aria-label="Bulan berikutnya" type="button">&rsaquo;</button>
                        </div>
                        <div class="reservation-weekdays" aria-hidden="true">
                            <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span>
                            <span>Kam</span><span>Jum</span><span>Sab</span>
                        </div>
                        <div class="cal" id="cal" tabindex="-1" aria-label="Pilih tanggal pemotretan"></div>
                        <div class="legend">
                            <span><i></i>Tidak tersedia</span>
                            <span>Pilih tanggal yang masih kosong</span>
                        </div>
                        <p class="reservation-date-status" id="date-selection-status" aria-live="polite">
                            Pilih tanggal yang masih tersedia.
                        </p>
                        <p class="reservation-date-error" id="date-selection-error" role="alert"
                            @if (!$errors->has('date')) hidden @endif>
                            {{ $errors->first('date') ?: 'Silakan pilih tanggal dari kalender sebelum mengirim reservasi.' }}
                        </p>
                    </section>

                    <section class="reservation-panel reservation-availability">
                        <div class="reservation-calendar-heading">
                            <span>03 / PILIH JAM</span>
                            <h3>Ketersediaan</h3>
                        </div>
                        <p class="reservation-availability-date" id="availability-date">
                            Pilih tanggal di kalender untuk melihat jam.
                        </p>
                        <p class="reservation-slot-note" id="reservation-slot-note">
                            Jam tersedia setiap 30 menit, pukul 06.00–18.00.
                        </p>
                        <div class="reservation-time-slots" id="available-time-slots" role="group"
                            aria-label="Pilihan jam pemotretan">
                            <p class="reservation-slot-placeholder">Pilih tanggal dan paket untuk melihat jam tersedia.</p>
                        </div>
                        <p class="reservation-time-error" id="time-selection-error" role="alert"
                            @if (!$errors->has('time')) hidden @endif>
                            {{ $errors->first('time') ?: 'Pilih jam yang tersedia sebelum mengirim reservasi.' }}
                        </p>
                    </section>

                    <aside class="reservation-panel reservation-service" id="rsum">
                        <div class="reservation-service-heading">
                            <span>04 / RINGKASAN</span>
                            <h3>Detail Layanan</h3>
                        </div>
                        <h2 id="sum-name">Pilih paket</h2>
                        <p id="sum-facilities">Detail paket akan tampil setelah paket dipilih.</p>
                        <div class="reservation-service-facts">
                            <div>
                                <span>HARGA PAKET</span>
                                <strong id="sum-price">Rp 0</strong>
                            </div>
                            <div>
                                <span>DURASI SESI</span>
                                <strong id="sum-duration">Pilih paket</strong>
                            </div>
                        </div>
                        <p class="reservation-service-datetime" id="service-date-time">
                            Tanggal dan jam pilihan Anda akan tampil di sini.
                        </p>
                        <div id="sum-consultation-badge">Sesi konsultasi konsep ditambahkan.</div>
                        <div class="fld reservation-field">
                            <label for="rl">LOKASI PEMOTRETAN / LINK GOOGLE MAPS</label>
                            <p class="reservation-map-hint">
                                Buka <a href="https://maps.google.com/" target="_blank" rel="noopener noreferrer">Google
                                    Maps</a>, cari lokasi pemotretan, pilih <strong>Bagikan</strong> lalu
                                <strong>Salin link</strong>. Tempel link-nya di bawah.
                            </p>
                            <input id="rl" name="location" type="url"
                                placeholder="Tempel link Google Maps di sini" required
                                value="{{ old('location') }}" />
                            <a class="reservation-map-link" id="reservation-map-link" href="#" target="_blank"
                                rel="noopener noreferrer" hidden>Buka lokasi terpilih di Google Maps</a>
                            @error('location')
                                <span class="reservation-map-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="fld reservation-field">
                            <label for="rn">NAMA</label>
                            <input id="rn" name="name" placeholder="Nama lengkap" required
                                value="{{ old('name') }}" />
                        </div>
                        <div class="fld reservation-field">
                            <label for="rk">KONTAK WHATSAPP</label>
                            <input id="rk" name="whatsapp" placeholder="081234567890" data-vt="phone" required
                                value="{{ old('whatsapp') }}" />
                        </div>
                        <p class="reservation-privacy">
                            Tanggal yang sudah terisi penuh tidak dapat dipilih. Kami tidak pernah meminta OTP, PIN,
                            atau password Anda.
                        </p>
                        <button type="submit" class="btn ink reservation-submit">
                            Kirim reservasi &amp; konsultasi
                        </button>
                    </aside>
                </div>
            </form>
        </div>
    </main>

    <!-- SCRIPT UTAMA, KALENDER, & VALIDASI BENTROK JAM -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectPaket = document.getElementById('rp');
            const sumName = document.getElementById('sum-name');
            const sumPrice = document.getElementById('sum-price');
            const sumFacilities = document.getElementById('sum-facilities');
            const sumDuration = document.getElementById('sum-duration');
            const inputDuration = document.getElementById('rd');

            const toggleKonsultasi = document.getElementById('toggle-konsultasi');
            const konsultasiSection = document.getElementById('konsultasi-section');
            const sumConsultationBadge = document.getElementById('sum-consultation-badge');

            const selectTopik = document.getElementById('konsultasi-topik');
            const topikLainnyaContainer = document.getElementById('topik-lainnya-container');

            const selectMedia = document.getElementById('konsultasi-media');
            const offlineContainer = document.getElementById('media-offline-container');

            const calContainer = document.getElementById('cal');
            const monthLabel = document.getElementById('cm');
            const prevBtn = document.getElementById('cp');
            const nextBtn = document.getElementById('cn');
            const inputDate = document.getElementById('rt');
            const inputTime = document.getElementById('rj');
            const dateSelectionStatus = document.getElementById('date-selection-status');
            const dateSelectionError = document.getElementById('date-selection-error');
            const timeSelectionError = document.getElementById('time-selection-error');
            const availableTimeSlots = document.getElementById('available-time-slots');
            const availabilityDate = document.getElementById('availability-date');
            const availabilityNote = document.getElementById('reservation-slot-note');
            const serviceDateTime = document.getElementById('service-date-time');
            const locationInput = document.getElementById('rl');
            const locationMapLink = document.getElementById('reservation-map-link');
            const reservationForm = document.getElementById('rform');
            const openingTimeMinutes = 6 * 60;
            const closingTimeMinutes = 18 * 60;
            const slotIntervalMinutes = 30;

            const previousDate = inputDate.value ? new Date(`${inputDate.value}T00:00:00`) : new Date();
            let currentDate = Number.isNaN(previousDate.getTime()) ? new Date() : previousDate;
            let bookedSlots = [];

            function updateLocationMapLink() {
                const isGoogleMapsLink =
                    /^https:\/\/(?:(?:www\.)?google\.com\/maps\/|maps\.google\.com\/|maps\.app\.goo\.gl\/|goo\.gl\/maps\/)/i
                    .test(locationInput.value);
                locationMapLink.hidden = !isGoogleMapsLink;
                if (isGoogleMapsLink) {
                    locationMapLink.href = locationInput.value;
                } else {
                    locationMapLink.removeAttribute('href');
                }
            }

            locationInput.addEventListener('input', updateLocationMapLink);
            updateLocationMapLink();

            function isWeddingPackageSelected() {
                const selectedOption = selectPaket.selectedOptions[0];
                return Boolean(selectedOption && selectedOption.getAttribute('data-wedding') === '1');
            }

            function updateSummary() {
                const selectedOption = selectPaket.options[selectPaket.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    sumName.textContent = selectedOption.getAttribute('data-name');
                    sumPrice.textContent = 'Rp ' + selectedOption.getAttribute('data-price');
                    sumFacilities.textContent = selectedOption.getAttribute('data-facilities');
                    sumDuration.textContent = selectedOption.getAttribute('data-duration');
                    inputDuration.value = selectedOption.getAttribute('data-duration');
                } else {
                    sumName.textContent = 'Pilih Paket';
                    sumPrice.textContent = 'Rp 0';
                    sumFacilities.textContent = 'Detail paket akan tampil setelah paket dipilih.';
                    sumDuration.textContent = 'Pilih paket';
                    inputDuration.value = '';
                }

                if (inputDate.value && !getAvailableTimes(inputDate.value).length) {
                    inputDate.value = '';
                    inputTime.value = '';
                    dateSelectionStatus.textContent = 'Pilih tanggal yang masih tersedia.';
                } else if (inputDate.value && isWeddingPackageSelected()) {
                    inputTime.value = '06:00';
                } else if (inputDate.value && !getAvailableTimes(inputDate.value).includes(inputTime.value)) {
                    inputTime.value = '';
                }

                renderCalendar();
                renderAvailableTimeSlots();
                updateSelectedScheduleSummary();
            }

            updateSummary();

            selectPaket.addEventListener('change', function() {
                inputTime.value = '';
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

            // Fetch Data Jadwal Terisi
            function fetchBookedSlots() {
                fetch("{{ route('reservasi.booked-slots') }}")
                    .then(response => response.json())
                    .then(data => {
                        bookedSlots = data;
                        if (inputDate.value && !getAvailableTimes(inputDate.value).length) {
                            inputDate.value = '';
                            inputTime.value = '';
                            dateSelectionStatus.textContent = 'Pilih tanggal yang masih tersedia.';
                        } else if (inputDate.value && !getAvailableTimes(inputDate.value).includes(inputTime.value)) {
                            inputTime.value = '';
                        }
                        renderCalendar();
                        renderAvailableTimeSlots();
                        updateSelectedScheduleSummary();
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
                if (inputDate.value) {
                    const selectedDate = new Date(`${inputDate.value}T00:00:00`);
                    if (!Number.isNaN(selectedDate.getTime())) {
                        dateSelectionStatus.textContent =
                            `Tanggal terpilih: ${selectedDate.getDate()} ${monthNames[selectedDate.getMonth()]} ${selectedDate.getFullYear()}`;
                    }
                }

                let firstDayIndex = new Date(year, month, 1).getDay();
                let totalDays = new Date(year, month + 1, 0).getDate();

                for (let i = 0; i < firstDayIndex; i++) {
                    let emptyCell = document.createElement('div');
                    emptyCell.className = 'cal-day empty';
                    calContainer.appendChild(emptyCell);
                }

                for (let day = 1; day <= totalDays; day++) {
                    let dayCell = document.createElement('button');
                    dayCell.type = 'button';
                    dayCell.className = 'cal-day';

                    let formattedMonth = String(month + 1).padStart(2, '0');
                    let formattedDay = String(day).padStart(2, '0');
                    let dateString = `${year}-${formattedMonth}-${formattedDay}`;

                    dayCell.textContent = day;
                    dayCell.setAttribute('aria-label', `${day} ${monthNames[month]} ${year}`);

                    const candidateDate = new Date(year, month, day);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const isUnavailable = candidateDate < today ||
                        (selectPaket.value !== '' && !getAvailableTimes(dateString).length);

                    if (isUnavailable) {
                        dayCell.classList.add('booked');
                        dayCell.disabled = true;
                        dayCell.title = candidateDate < today ? 'Tanggal sudah lewat' : 'Jadwal di tanggal ini penuh';
                    } else {
                        dayCell.classList.add('available');
                        if (inputDate.value === dateString) {
                            dayCell.classList.add('selected');
                            dayCell.setAttribute('aria-pressed', 'true');
                        } else {
                            dayCell.setAttribute('aria-pressed', 'false');
                        }

                        dayCell.addEventListener('click', function() {
                            document.querySelectorAll('.cal-day.selected').forEach(el => {
                                el.classList.remove('selected');
                                el.setAttribute('aria-pressed', 'false');
                            });
                            dayCell.classList.add('selected');
                            dayCell.setAttribute('aria-pressed', 'true');
                            inputDate.value = dateString;
                            inputTime.value = isWeddingPackageSelected() ? '06:00' : '';
                            dateSelectionStatus.textContent = `Tanggal terpilih: ${day} ${monthNames[month]} ${year}`;
                            dateSelectionError.hidden = true;
                            timeSelectionError.hidden = true;
                            availabilityDate.textContent = formatDate(dateString);
                            renderAvailableTimeSlots();
                            updateSelectedScheduleSummary();
                        });
                    }

                    calContainer.appendChild(dayCell);
                }
            }

            function formatDate(dateString) {
                const date = new Date(`${dateString}T00:00:00`);
                return new Intl.DateTimeFormat('id-ID', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                }).format(date);
            }

            function getDurationMinutes(durationText) {
                const match = durationText ? durationText.match(/\d+/) : null;
                return (match ? Number.parseInt(match[0], 10) : 2) * 60;
            }

            function isWeddingReservation(reservation) {
                return reservation.is_wedding === true || reservation.is_wedding === 1 ||
                    reservation.is_wedding === '1';
            }

            function getAvailableTimes(dateString) {
                const selectedOption = selectPaket.selectedOptions[0];
                if (!selectedOption || !selectedOption.value) {
                    return [];
                }

                const sessionDuration = getDurationMinutes(selectedOption.getAttribute('data-duration'));
                const existingReservations = bookedSlots.filter(reservation => reservation.date === dateString);
                const isWeddingPackage = selectedOption.getAttribute('data-wedding') === '1';
                const selectedDate = new Date(`${dateString}T00:00:00`);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (selectedDate < today) {
                    return [];
                }

                if (existingReservations.some(isWeddingReservation) ||
                    (isWeddingPackage && existingReservations.length > 0)) {
                    return [];
                }

                if (isWeddingPackage) {
                    return ['06:00'];
                }

                const availableTimes = [];
                for (let startMinutes = openingTimeMinutes; startMinutes <= closingTimeMinutes;
                    startMinutes += slotIntervalMinutes) {
                    const now = new Date();
                    const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
                    const currentMinutes = now.getHours() * 60 + now.getMinutes();
                    if (dateString === today && startMinutes <= currentMinutes) {
                        continue;
                    }

                    const overlapsReservation = existingReservations.some(reservation => {
                        const existingStart = reservation.time.split(':').slice(0, 2).reduce(
                            (total, part) => total * 60 + Number.parseInt(part, 10), 0);
                        const existingDuration = getDurationMinutes(reservation.duration);
                        return startMinutes <= existingStart + existingDuration &&
                            startMinutes + sessionDuration > existingStart;
                    });

                    if (!overlapsReservation) {
                        availableTimes.push(
                            `${String(Math.floor(startMinutes / 60)).padStart(2, '0')}:${String(startMinutes % 60).padStart(2, '0')}`
                        );
                    }
                }

                return availableTimes;
            }

            function renderAvailableTimeSlots() {
                availableTimeSlots.innerHTML = '';
                availabilityNote.textContent = isWeddingPackageSelected()
                    ? 'Paket Wedding mencakup satu hari penuh. Jam tidak perlu dipilih.'
                    : 'Jam tersedia setiap 30 menit, pukul 06.00–18.00.';

                if (!inputDate.value) {
                    availabilityDate.textContent = 'Pilih tanggal di kalender untuk melihat jam.';
                    appendSlotMessage(isWeddingPackageSelected()
                        ? 'Pilih tanggal untuk melanjutkan reservasi Wedding.'
                        : 'Pilih tanggal dan paket untuk melihat jam tersedia.');
                    return;
                }

                availabilityDate.textContent = formatDate(inputDate.value);
                if (!selectPaket.value) {
                    appendSlotMessage('Pilih paket terlebih dahulu agar durasi sesi dapat diperhitungkan.');
                    return;
                }

                if (isWeddingPackageSelected()) {
                    inputTime.value = '06:00';
                    timeSelectionError.hidden = true;
                    appendSlotMessage('Paket Wedding menggunakan jadwal seharian penuh tanpa pilihan jam.');
                    return;
                }

                const timeOptions = [];
                const sessionDuration = getDurationMinutes(selectPaket.selectedOptions[0].getAttribute('data-duration'));
                for (let minutes = openingTimeMinutes; minutes <= closingTimeMinutes; minutes += slotIntervalMinutes) {
                    timeOptions.push(
                        `${String(Math.floor(minutes / 60)).padStart(2, '0')}:${String(minutes % 60).padStart(2, '0')}`
                    );
                }

                const availableTimes = getAvailableTimes(inputDate.value);
                if (!timeOptions.length) {
                    appendSlotMessage('Durasi paket tidak sesuai dengan jam operasional studio.');
                    return;
                }

                timeOptions.forEach(time => {
                    const timeButton = document.createElement('button');
                    timeButton.type = 'button';
                    timeButton.className = 'reservation-time-slot';
                    timeButton.textContent = time;
                    timeButton.disabled = !availableTimes.includes(time);
                    timeButton.setAttribute('aria-pressed', inputTime.value === time ? 'true' : 'false');
                    if (inputTime.value === time) {
                        timeButton.classList.add('selected');
                    }

                    timeButton.addEventListener('click', function() {
                        inputTime.value = time;
                        timeSelectionError.hidden = true;
                        renderAvailableTimeSlots();
                        updateSelectedScheduleSummary();
                    });

                    availableTimeSlots.appendChild(timeButton);
                });

                if (!availableTimes.length) {
                    appendSlotMessage('Tidak ada jam yang tersedia untuk paket ini pada tanggal tersebut.');
                }
            }

            function appendSlotMessage(message) {
                const messageElement = document.createElement('p');
                messageElement.className = 'reservation-slot-placeholder';
                messageElement.textContent = message;
                availableTimeSlots.appendChild(messageElement);
            }

            function updateSelectedScheduleSummary() {
                if (!inputDate.value) {
                    serviceDateTime.textContent = 'Tanggal dan jam pilihan Anda akan tampil di sini.';
                    return;
                }

                if (isWeddingPackageSelected()) {
                    serviceDateTime.textContent = `${formatDate(inputDate.value)} · Sesi seharian`;
                    return;
                }

                if (!inputTime.value) {
                    serviceDateTime.textContent = 'Tanggal dan jam pilihan Anda akan tampil di sini.';
                    return;
                }

                serviceDateTime.textContent = `${formatDate(inputDate.value)} pukul ${inputTime.value}`;
            }

            reservationForm.addEventListener('submit', function(event) {
                if (!inputDate.value || (!isWeddingPackageSelected() && !inputTime.value)) {
                    event.preventDefault();
                    dateSelectionError.hidden = Boolean(inputDate.value);
                    timeSelectionError.hidden = isWeddingPackageSelected() || Boolean(inputTime.value);
                    const target = inputDate.value ? availableTimeSlots : calContainer;
                    target.focus();
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });

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

            if (inputDate.value) {
                availabilityDate.textContent = formatDate(inputDate.value);
            }
            updateSelectedScheduleSummary();
            fetchBookedSlots();

            // SweetAlert Sukses Reservasi dengan Tombol Tutup / Batal
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
                    showCancelButton: true,
                    confirmButtonText: isConsultationChecked ? 'Konfirmasi ke Admin (WhatsApp)' :
                        'Cek Detail Reservasi & Bayar',
                    cancelButtonText: 'Tutup',
                    confirmButtonColor: isConsultationChecked ? '#25D366' : '#191512',
                    cancelButtonColor: '#6c757d',
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
@endsection