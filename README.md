# WebStudioFoto

WebStudioFoto adalah proyek UI/UX mockup untuk studio fotografi dengan fokus pada pengalaman brand, paket layanan, alur reservasi, dan dashboard admin.

Saat ini, proyek ini berstatus prototype front-end. Artinya fokus utama masih pada tampilan, struktur halaman, dan pengalaman pengguna (UX/UI), bukan fitur backend atau sistem produksi yang lengkap.

## Tujuan proyek
- Menampilkan brand studio fotografi dengan gaya premium dan elegan
- Menyediakan halaman landing page yang informatif
- Menjelaskan paket layanan dan alur reservasi
- Menyediakan simulasi status reservasi untuk pengguna
- Menyediakan dashboard admin sebagai mockup operasional

## Struktur folder
```bash
WebStudioFoto/
├── css/
│   └── style.css
├── js/
│   └── app.js
├── index.html
├── login.html
├── dashboard.html
├── Test.docx
├── README.md
└── .gitignore
```

## Teknologi yang digunakan
- HTML
- CSS
- JavaScript
- Git
- GitHub
- Browser (buka file langsung atau gunakan Live Server)

## Status project
Saat ini repo ini masih fokus pada:
- desain halaman landing page
- interaksi UI/UX
- flow reservasi mockup
- dashboard admin mockup

Belum ada implementasi backend yang nyata seperti:
- database
- autentikasi user/admin
- API
- upload file real
- data persisten

## Cara clone repo
Pastikan Git sudah terinstal pada komputer Anda.

```bash
git clone https://github.com/KuraiHashi/WebStudioFoto.git
cd WebStudioFoto
```

## Cara membuka project di browser
### Opsi 1: Buka file langsung
```bash
open index.html
```
Atau cukup buka file `index.html` dari file manager / explorer.

### Opsi 2: Pakai Live Server (direkomendasikan untuk developer)
Jika menggunakan VS Code:
1. Install ekstensi "Live Server"
2. Buka folder project
3. Klik kanan pada `index.html`
4. Pilih `Open with Live Server`

## Git dan GitHub untuk tim
GitHub adalah platform untuk kolaborasi tim. Setiap anggota tim tidak boleh bekerja langsung di branch utama tanpa review. Gunanya agar:
- perubahan terdokumentasi
- mudah menjaga kode tetap rapi
- mudah melihat history perubahan
- mudah review sebelum merge
- mengurangi konflik dan error

## Aturan dasar kolaborasi
- Jangan push langsung ke `main`
- Buat branch baru untuk setiap tugas
- Gunakan commit message yang jelas
- Buka Pull Request sebelum merge ke `main`
- Review dan diskusikan perubahan sebelum merge
- Jika ada konflik, selesaikan dulu sebelum lanjut

## Branch workflow yang disarankan
Gunakan format berikut:
```bash
feature/nama-fitur
fix/perbaikan-ui
docs/readme-github
chore/setup-project
```

Contoh branch yang aman:
```bash
feature/hero-section
feature/reservasi-form
fix/mobile-navigation
docs/github-collaboration
```

## Cara membuat branch baru
Pastikan Anda berada di branch utama terlebih dahulu:

```bash
git checkout main
git pull origin main
git checkout -b feature/nama-fitur
```

Contoh:
```bash
git checkout main
git pull origin main
git checkout -b feature/reservasi-form
```

## Cara melihat status repo
```bash
git status
```

## Cara menambahkan file yang diubah
```bash
git add .
```

Atau hanya file tertentu:
```bash
git add index.html css/style.css js/app.js
```

## Cara commit
Gunakan pesan commit yang jelas dan ringkas.

```bash
git commit -m "feat: add reservation form UI"
```

Contoh pesan commit yang baik:
- `feat: add hero section redesign`
- `fix: improve mobile navigation`
- `docs: add GitHub collaboration guide`
- `ui: update admin dashboard layout`
- `refactor: clean up JS interactions`

## Cara push branch ke GitHub
```bash
git push -u origin feature/nama-fitur
```

Contoh:
```bash
git push -u origin feature/reservasi-form
```

## Cara membuat Pull Request (PR)
Setelah push branch, buka repository di GitHub:
1. Klik `Compare & pull request`
2. Pilih branch sumber
3. Pilih branch tujuan (`main`)
4. Isi judul PR
5. Tulis deskripsi perubahan
6. Klik `Create pull request`

## Pull Request Draft
Jika perubahan masih dalam tahap review atau belum siap untuk merge, gunakan `Draft Pull Request`.

Langkahnya:
1. Klik `Create draft pull request`
2. Isi ringkasan perubahan
3. Lampirkan checklist jika diperlukan
4. Setelah siap, ubah menjadi `Ready for review`

Draft PR berguna untuk:
- menampilkan progress pekerjaan
- meminta review sejak awal
- memberi waktu tim memberi feedback sebelum merge

## Cara mengambil perubahan terbaru dari main
Jika branch Anda sudah lama dibuat, update terlebih dahulu agar tidak ketinggalan perubahan dari main.

```bash
git checkout main
git pull origin main
git checkout feature/nama-fitur
git merge main
```

Atau dengan rebase:
```bash
git checkout feature/nama-fitur
git rebase main
```

## Cara mengambil perubahan terbaru dari remote
```bash
git fetch origin
git pull origin main
```

## Cara mengatasi conflict saat merge
Jika saat merge atau rebase muncul conflict, lakukan langkah berikut:
1. Buka file yang konflik
2. Selesaikan isi file sesuai kebutuhan
3. Simpan file
4. Jalankan:

```bash
git add .
git commit -m "resolve: fix merge conflict"
```

## Workflow kolaborasi tim yang disarankan
Berikut alur kerja yang paling umum dipakai:

```bash
git checkout main
git pull origin main
git checkout -b feature/nama-fitur
# lakukan pekerjaan
# tambah perubahan
# commit
# push branch
# buka PR
# review
# merge ke main
```

## Contoh flow kerja tim
Misalnya tim Anda terdiri dari 3 orang:
- A: mengerjakan landing page hero
- B: memperbaiki dashboard admin
- C: membuat dokumentasi GitHub

Maka masing-masing akan membuat branch sendiri:
```bash
git checkout -b feature/hero-section
git checkout -b fix/admin-dashboard
git checkout -b docs/github-guide
```

Setelah selesai, masing-masing push branch dan membuka PR. Setelah review dan persetujuan, barulah merge ke `main`.

## Template PR yang baik
Judul PR contoh:
- `feat: add hero section redesign`
- `fix: improve mobile navigation`
- `docs: add GitHub collaboration guide`

Deskripsi PR contoh:
```markdown
## Summary
Menambahkan revisi desain hero untuk meningkatkan fokus CTA utama di landing page.

## Changes
- memperbarui struktur navigasi
- memperbesar tombol utama
- menata whitespace dan spacing
- memperbaiki responsiveness di mobile

## Notes
- masih prototype UI/UX
- belum termasuk backend
```

## Tips supaya kolaborasi lebih rapi
- Buat branch sesuai tugas
- Gunakan commit message yang konsisten
- Jangan menunggu terlalu lama untuk buka PR
- Tulis ringkasan pekerjaan dengan jelas
- Gunakan review untuk mencegah bug dan konflik
- Kalau tugas besar, pecah menjadi beberapa PR kecil

## Troubleshooting umum
### 1. Error branch tidak ditemukan
```bash
git fetch origin
git checkout -b feature/nama-fitur origin/feature/nama-fitur
```

### 2. Error saat pull
```bash
git pull --rebase origin main
```

### 3. Error conflict saat merge
```bash
git status
git add .
git commit -m "resolve: fix conflict"
```

## Referensi cepat
```bash
git clone <url-repo>
git checkout main
git pull origin main
git checkout -b feature/nama-fitur
git status
git add .
git commit -m "feat: ..."
git push -u origin feature/nama-fitur
```

## Kesimpulan
GitHub akan berjalan dengan baik jika tim mengikuti pola berikut:
- branch per tugas
- commit yang jelas
- push ke branch sendiri
- buka PR
- review
- merge ke main

Dengan cara ini, tim baru akan lebih mudah belajar kolaborasi menggunakan GitHub secara rapi dan terstruktur.

## Catatan final
Proyek ini adalah contoh yang bagus untuk belajar:
- Git dan GitHub
- workflow branch
- commit dan push
- PR review
- desain UI/UX dan kolaborasi tim

Semua proses di atas sangat cocok untuk latihan tim baru yang ingin belajar bekerja secara profesional di GitHub.
