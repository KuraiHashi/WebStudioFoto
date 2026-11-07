@extends('TemplateAdmin.main')
@section('profile', 'on')
@section('konten')
    <main class="main profile-page">
        <div class="top profile-top">
            <div>
                <div class="eyebrow">Ruang studio / profil</div>
                <h2>Kenali <em>studio</em> Anda</h2>
                <p class="profile-intro">Perbarui informasi yang membantu klien menemukan dan mengenal studio Anda.</p>
            </div>
            <div class="profile-top-note">
                <span>01</span>
                <div>
                    <strong>Pengaturan profil</strong>
                    <small>Identitas studio Anda</small>
                </div>
            </div>
        </div>

        <div class="profile-layout">
            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data"
                class="fc profile-form" id="pform">
                @csrf

                <div class="profile-form-heading">
                    <span class="profile-form-number">01</span>
                    <div>
                        <h3>Informasi studio</h3>
                        <p>Lengkapi detail utama agar klien tahu cara menghubungi dan mengunjungi Anda.</p>
                    </div>
                    <span class="profile-required"><i></i> Wajib diisi</span>
                </div>

                <div class="profile-fields">
                    <div class="fld profile-field-wide">
                        <label for="profile-name">Nama studio</label>
                        <input id="profile-name" type="text" name="name"
                            value="{{ old('name', $profile->name ?? 'Liquif Picture') }}" autocomplete="organization"
                            @error('name') aria-describedby="profile-name-error" @enderror required />
                        @error('name')
                            <span class="er" id="profile-name-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="fld profile-field-wide">
                        <label for="profile-photo">Foto studio <span>(opsional)</span></label>
                        <input id="profile-photo" type="file" name="foto_studio"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            aria-describedby="profile-photo-help @error('foto_studio') profile-photo-error @enderror" />
                        <small class="profile-field-hint" id="profile-photo-help">JPG, PNG, atau WEBP · maks. 2 MB. Foto ini akan tampil di navbar dan footer.</small>
                        @if ($profile?->foto_studio)
                            <img class="profile-photo-preview" src="{{ asset('storage/' . $profile->foto_studio) }}"
                                alt="Foto studio saat ini" loading="lazy">
                        @endif
                        @error('foto_studio')
                            <span class="er" id="profile-photo-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="fld profile-field-wide">
                        <label for="profile-address">Alamat studio</label>
                        <input id="profile-address" type="text" name="address"
                            value="{{ old('address', $profile->address ?? '') }}" autocomplete="street-address"
                            placeholder="Jalan, kota, dan patokan lokasi"
                            @error('address') aria-describedby="profile-address-error" @enderror required />
                        @error('address')
                            <span class="er" id="profile-address-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="fld">
                        <label for="profile-phone">Kontak</label>
                        <input id="profile-phone" type="text" name="phone"
                            value="{{ old('phone', $profile->phone ?? '') }}" data-vt="phone" autocomplete="tel"
                            placeholder="Nomor telepon atau WhatsApp"
                            @error('phone') aria-describedby="profile-phone-error" @enderror required />
                        @error('phone')
                            <span class="er" id="profile-phone-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="fld">
                        <label for="profile-hours">Jam operasional</label>
                        <input id="profile-hours" type="text" name="operational_hours"
                            value="{{ old('operational_hours', $profile->operational_hours ?? '') }}"
                            placeholder="Contoh: Senin–Sabtu, 09.00–18.00"
                            @error('operational_hours') aria-describedby="profile-hours-error" @enderror required />
                        @error('operational_hours')
                            <span class="er" id="profile-hours-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="fld profile-field-wide">
                        <label for="profile-social">Media sosial <span>(opsional)</span></label>
                        <input id="profile-social" type="text" name="social_media"
                            value="{{ old('social_media', $profile->social_media ?? '') }}"
                            placeholder="Contoh: @liquifpicture atau tautan profil lengkap"
                            @error('social_media') aria-describedby="profile-social-error" @enderror />
                        <small class="profile-field-hint">Gunakan @username untuk Instagram atau tempel tautan lengkap profil media sosial Anda.</small>
                        @error('social_media')
                            <span class="er" id="profile-social-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="fld profile-field-wide">
                        <label for="profile-maps">Sematkan Google Maps <span>(opsional)</span></label>
                        <textarea id="profile-maps" name="maps_link" rows="3"
                            placeholder="Tempel kode iframe Google Maps di sini"
                            aria-describedby="profile-maps-help @error('maps_link') profile-maps-error @enderror">{{ old('maps_link', $profile->maps_link ?? '') }}</textarea>
                        <small class="profile-field-hint" id="profile-maps-help">Salin kode sematkan dari Google Maps agar klien lebih mudah menemukan studio.</small>
                        @error('maps_link')
                            <span class="er" id="profile-maps-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="fld profile-field-wide">
                        <label for="profile-description">Cerita singkat tentang studio</label>
                        <textarea id="profile-description" name="description" rows="4"
                            placeholder="Ceritakan pengalaman atau layanan khas studio Anda"
                            @error('description') aria-describedby="profile-description-error" @enderror
                            required>{{ old('description', $profile->description ?? 'Studio fotografi untuk momen yang ingin Anda simpan selamanya.') }}</textarea>
                        @error('description')
                            <span class="er" id="profile-description-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="profile-form-footer">
                    <span>Pastikan informasi studio selalu terbaru.</span>
                    <button type="submit" class="btn ink profile-submit">Simpan perubahan</button>
                </div>
            </form>

            <aside class="profile-aside">
                <div class="profile-showcase">
                    <div class="profile-showcase-top">
                        <span>LIQUIF <i></i> STUDIO</span>
                        <span class="profile-showcase-label">PROFIL</span>
                    </div>
                    <div class="profile-monogram" aria-hidden="true">
                        {{ mb_strtoupper(mb_substr(old('name', $profile->name ?? 'Liquif Picture'), 0, 1)) }}
                    </div>
                    <h3>{{ old('name', $profile->name ?? 'Liquif Picture') }}</h3>
                    <p class="profile-showcase-description">
                        {{ old('description', $profile->description ?? 'Studio fotografi untuk momen yang ingin Anda simpan selamanya.') }}
                    </p>
                    <div class="profile-showcase-details">
                        <div>
                            <span>LOKASI</span>
                            <p>{{ old('address', $profile->address ?? '') ?: 'Alamat studio belum ditambahkan' }}</p>
                        </div>
                        <div>
                            <span>KONTAK</span>
                            <p>{{ old('phone', $profile->phone ?? '') ?: 'Tambahkan kontak studio' }}</p>
                        </div>
                        <div>
                            <span>JAM OPERASIONAL</span>
                            <p>{{ old('operational_hours', $profile->operational_hours ?? '') ?: 'Atur jam operasional studio' }}</p>
                        </div>
                    </div>
                    <div class="profile-showcase-bottom">MENGABADIKAN CERITA, SELAMANYA</div>
                </div>

                <div class="profile-aside-note">
                    <span class="profile-note-mark" aria-hidden="true">✳</span>
                    <div>
                        <h4>Profil yang lengkap terasa lebih dekat.</h4>
                        <p>Tambahkan alamat yang jelas, jam operasional, dan kontak aktif agar klien lebih mudah membuat janji.</p>
                    </div>
                </div>
            </aside>
        </div>
    </main>
@endsection
