# WebStudioFoto

**WebStudioFoto** adalah proyek *mockup* antarmuka (UI/UX) untuk sistem manajemen studio & e-commerce fotografi yang dikembangkan menggunakan *framework* **Laravel**. Proyek ini dirancang untuk memberikan gambaran alur operasional studio fotografi mulai dari tampilan depan (*landing page*), katalog paket layanan, alur reservasi jadwal pemotretan, hingga *dashboard* admin.

### Latar Belakang Sistem
Sistem ini dibuat untuk menyelesaikan permasalahan manajemen operasional studio fotografi tradisional yang sering mengalami kendala penjadwalan ganda (*double booking*), transparansi paket harga, serta kemudahan akses status reservasi bagi pelanggan. Proyek ini hadir sebagai fondasi aplikasi web modern yang menghubungkan calon klien dengan pengelola studio secara terstruktur.

---

## 🛠️ Bahasa & Teknologi

- **PHP** (Framework Laravel)
- **HTML5 & CSS3** (Styling & Layout)
- **JavaScript** (Interaksi antarmuka)
- **Git & GitHub** (Kontrol versi & kolaborasi tim)

---

## 🗺️ Roadmap Proyek

- [x] **Fase 1 (Desain & Struktur):** Penyusunan struktur folder Laravel, desain *landing page*, dan *mockup dashboard* admin.
- [ ] **Fase 2 (Logika & Database):** Implementasi skema *database*, fitur autentikasi (Login/Register), dan alur reservasi real-time.
- [ ] **Fase 3 (Manajemen File & Laporan):** Integrasi sistem unggah galeri foto, transaksi pembayaran, serta laporan keuangan studio.

---

## 📁 Struktur Folder

```bash
WebStudioFoto/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── composer.json
├── artisan
├── README.md
└── .gitignore

## 🚀 Panduan Instalasi (Step-by-Step)
Pastikan lingkungan komputer Anda sudah terpasang PHP, Composer, dan Git.
- [x] **git clone [https://github.com/KuraiHashi/WebStudioFoto.git](https://github.com/KuraiHashi/WebStudioFoto.git)
cd WebStudioFoto
- [ ] **composer install
- [ ] **php artisan key:generate
- [ ] **php artisan storage:link

## 🤝 Aturan Kolaborasi Tim
Untuk menjaga riwayat kode tetap rapi dan menghindari kerosakan pada aplikasi utama, dilarang melakukan push langsung ke branch main.
1. Format Penamaan Branch
<img width="912" height="243" alt="image" src="https://github.com/user-attachments/assets/7841316c-6853-4986-aa32-ea84f8ec3c21" />

2. Format Commit Message
Gunakan pesan yang jelas pada setiap commit:
- feat: add reservation form UI
- information: update installation steps in README

## 💻 Panduan Git Step-by-Step
Sebelum mulai bekerja, selalu pastikan kode di komputer Anda adalah yang paling baru:
1. Langkah 1: Ambil pembaruan & buat branch baru
├── git checkout main
├── git pull origin main
├── git checkout -b feature/nama-fitur
(Gunakan git checkout -b information jika tugas Anda hanya memperbarui informasi/dokumentasi).

Langkah 2: Simpan hasil pekerjaan (Commit)
Setelah selesai mengubah kode, simpan perubahan Anda:
├── git add .
├── git commit -m "feat: deskripsi singkat pekerjaan Anda"

Langkah 3: Kirim branch ke GitHub (Push)
Unggah branch Anda ke repositori GitHub:
├── git push -u origin feature/nama-fitur

Langkah 4: Buat Pull Request (PR)
-Buka halaman repositori proyek di GitHub.
-Klik tombol Compare & pull request.                                                        
-Berikan judul dan penjelasan singkat mengenai perubahan yang Anda buat.
-Minta rekan tim untuk melakukan review sebelum di-merge ke branch main.
