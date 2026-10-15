// Liquif Picture — interaksi antarmuka (simulasi tampilan, bukan sistem sebenarnya)
const q = s => document.querySelector(s), qa = s => document.querySelectorAll(s);
const ADM = document.body.hasAttribute('data-adm');
const CH = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // tanpa 0/O/1/I agar tidak membingungkan

const PRICE = { Essence: 450000, Signature: 950000, Atelier: 1850000 };
const PK = {
  Essence:   { d: '1 jam', x: 'Sesi singkat dengan satu konsep.', f: '20 foto sunting · 1 lokasi · galeri privat', b: '—' },
  Signature: { d: '2 jam', x: 'Sesi dua konsep dengan arahan penuh.', f: '45 foto sunting · 2 lokasi · galeri privat', b: 'Cetak 4R' },
  Atelier:   { d: '4 jam', x: 'Konsep kustom dengan waktu longgar.', f: '90 foto sunting · bebas lokasi · galeri privat', b: 'Album cetak' }
};
const BOOK = ['2026-10-03', '2026-10-07', '2026-10-10', '2026-10-17', '2026-10-24']; // tanggal terisi

// Alur status nyata: DP → verifikasi admin → pelunasan → verifikasi → selesai (foto terbuka)
const LAB = ['Pembayaran DP', 'Konfirmasi', 'Pelunasan', 'Selesai'];
const ST = {
  bayar:  { step: 0, dp: ['Belum dibayar', 'wa'], pl: ['Setelah DP terverifikasi', ''], tag: ['Menunggu pembayaran DP', 'wa'], act: 'DP', msg: 'Segera bayar DP minimal 50% agar jadwal Anda dikunci.' },
  verif:  { step: 1, dp: ['Menunggu verifikasi', 'wa'], pl: ['Setelah DP terverifikasi', ''], tag: ['Konfirmasi pembayaran', 'wa'], msg: 'Bukti DP sedang diperiksa admin (maksimal 1×24 jam).' },
  lunasi: { step: 2, dp: ['Terverifikasi', 'ok'], pl: ['Belum dibayar', 'wa'], tag: ['Menunggu pelunasan', 'wa'], act: 'Pelunasan', msg: 'DP sudah diterima. Lakukan pelunasan setelah sesi pemotretan.' },
  verif2: { step: 2, dp: ['Terverifikasi', 'ok'], pl: ['Menunggu verifikasi', 'wa'], tag: ['Verifikasi pelunasan', 'wa'], msg: 'Bukti pelunasan sedang diperiksa admin.' },
  selesai:{ step: 3, dp: ['Terverifikasi', 'ok'], pl: ['Terverifikasi', 'ok'], tag: ['Selesai — lunas', 'ok'], foto: 1, msg: 'Pembayaran lunas. Hasil foto Anda sudah dapat dibuka dan diunduh.' },
  batal:  { step: -1, dp: ['—', ''], pl: ['—', ''], tag: ['Dibatalkan', 'rj'], msg: 'Reservasi ini dibatalkan. Hubungi studio bila ada pertanyaan.' }
};
// Contoh data (kode unik, tanpa tanda strip)
const DB = {
  LQ7K4M9RTX: { s: 'bayar',  n: 'Nama D', p: 'Essence',   j: '10 Okt 2026 · 09.00', l: 'Studio' },
  LQ3D8H2WNP: { s: 'verif',  n: 'Nama B', p: 'Essence',   j: '21 Okt 2026 · 13.00', l: 'Studio' },
  LQ9T5C6XAB: { s: 'lunasi', n: 'Nama A', p: 'Signature', j: '24 Okt 2026 · 10.00', l: 'Studio' },
  LQ4R2N7VKE: { s: 'verif2', n: 'Nama F', p: 'Essence',   j: '28 Okt 2026 · 15.00', l: 'Luar studio' },
  LQ6M8J3FYZ: { s: 'selesai',n: 'Nama C', p: 'Atelier',   j: '17 Okt 2026 · 08.00', l: 'Luar studio' },
  LQ5W9B4HQD: { s: 'batal',  n: 'Nama E', p: 'Signature', j: '03 Okt 2026 · 11.00', l: 'Studio' }
};
let cur = '', curPkg = 'Signature';
const rp = n => 'Rp ' + n.toLocaleString('id-ID');
const norm = v => (v || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
const pill = ([t, c]) => `<span class="pill ${c}">${t}</span>`;
function genCode() { let c; do { const a = new Uint32Array(8); crypto.getRandomValues(a); c = 'LQ' + [...a].map(x => CH[x % CH.length]).join(''); } while (DB[c]); return c; }

// ---------- Pop up ----------
function reset(md) {
  md.classList.remove('done'); md.querySelector('.okp')?.remove();
  md.querySelectorAll('.fld.bad').forEach(f => f.classList.remove('bad'));
  md.querySelectorAll('.er').forEach(e => e.remove());
  md.querySelector('#kres')?.classList.add('hide');
  if (md.hasAttribute('data-clear')) {
    md.querySelectorAll('input:not([type=file]),textarea').forEach(i => i.value = '');
    md.querySelectorAll('input[type=file]').forEach(i => { i.value = ''; const d = i.closest('label')?.querySelector('.dn'); if (d) d.textContent = d.dataset.o; });
    md.querySelectorAll('.stars').forEach(s => { s.dataset.v = ''; [...s.children].forEach(x => x.textContent = '☆'); });
  }
}
const closeM = () => qa('.ov.on:not(.top)').forEach(o => o.classList.remove('on'));
function openM(id) { closeM(); const o = document.getElementById(id); reset(o.querySelector('.md')); o.classList.add('on'); }
let tt;
function toast(m) { let t = q('.toast'); if (!t) { t = document.createElement('div'); t.className = 'toast'; document.body.appendChild(t); } t.textContent = m; t.classList.add('on'); clearTimeout(tt); tt = setTimeout(() => t.classList.remove('on'), 2800); }
function ask(msg, cb) {
  let o = q('#cf');
  if (!o) {
    o = document.createElement('div'); o.id = 'cf'; o.className = 'ov top';
    o.innerHTML = '<div class="md" style="max-width:420px"><div class="eyebrow">Konfirmasi</div><h3 style="font-size:30px">Apakah Anda yakin?</h3><p id="cfm" style="margin-bottom:6px"></p><div class="acts"><button class="btn sm" data-no>Batal</button><button class="btn sm ink" data-yes>Ya, lanjutkan</button></div></div>';
    document.body.appendChild(o);
    o.onclick = e => { if (e.target === o || e.target.closest('[data-no]')) o.classList.remove('on'); if (e.target.closest('[data-yes]')) { o.classList.remove('on'); o._cb(); } };
  }
  q('#cfm').textContent = msg; o._cb = cb; o.classList.add('on');
}

// Panel "telah selesai" — bukti nyata bagi pengguna bahwa aksi berhasil
function done(md, t, m, x = '') {
  md.querySelector('.okp')?.remove();
  const p = document.createElement('div'); p.className = 'okp';
  const when = new Date().toLocaleString('id-ID', { dateStyle: 'long', timeStyle: 'short' });
  p.innerHTML = `<div class="tick">✓</div><h3>${t}</h3><p>${m}</p>${x}<div class="rcpt">Tercatat pada ${when}${ADM ? '' : '<br>Simpan tangkapan layar ini sebagai bukti.'}</div><div class="acts" style="justify-content:center"><button class="btn ink" data-close>Tutup</button></div>`;
  md.appendChild(p); md.classList.add('done'); md.scrollTop = 0;
}

// ---------- Validasi ----------
function check(sc, md) {
  sc.querySelectorAll('.fld.bad').forEach(f => f.classList.remove('bad'));
  sc.querySelectorAll('.er').forEach(e => e.remove());
  let ok = true;
  const bad = (i, m) => { ok = false; const f = i.closest('.fld') || i.parentNode; f.classList.add('bad'); const s = document.createElement('small'); s.className = 'er'; s.textContent = m; f.appendChild(s); };
  sc.querySelectorAll('[required]').forEach(i => {
    if (i.closest('.hide')) return;
    const v = (i.value || '').trim(), t = i.dataset.vt;
    if (i.type === 'file') {
      const f = i.files[0];
      if (!f) bad(i, 'Pilih file terlebih dahulu');
      else if (f.size > 5 * 1048576) bad(i, 'Ukuran file maksimal 5 MB');
      else if (!/\.(jpe?g|png|pdf)$/i.test(f.name)) bad(i, 'Format harus JPG, PNG, atau PDF');
    }
    else if (!v) bad(i, 'Wajib diisi');
    else if (t === 'phone' && !/^(08|628)\d{8,11}$/.test(v.replace(/[\s-]/g, ''))) bad(i, 'Nomor tidak valid, contoh: 081234567890');
    else if (t === 'email' && !/^\S+@\S+\.\S+$/.test(v)) bad(i, 'Format email tidak valid');
    else if (t === 'min' && v.length < +i.dataset.min) bad(i, 'Minimal ' + i.dataset.min + ' karakter');
    else if (t === 'num' && !/^\d+$/.test(v.replace(/[.\s]/g, ''))) bad(i, 'Isi dengan angka saja');
    else if (t === 'date') {
      if (v < new Date().toISOString().slice(0, 10)) bad(i, 'Pilih tanggal hari ini atau setelahnya');
      else if (BOOK.includes(v)) bad(i, 'Jadwal tanggal ini sudah terisi, pilih tanggal lain');
    }
  });
  sc.querySelectorAll('.stars[data-req]').forEach(s => { if (!s.dataset.v) bad(s, 'Berikan rating bintang'); });
  if (!ok) { const m = md || sc; m.classList.remove('shake'); void m.offsetWidth; m.classList.add('shake'); }
  return ok;
}

// ---------- Cek reservasi ----------
function render(c) {
  const d = DB[c], S = ST[d.s], t = PRICE[d.p]; cur = c;
  q('#r-n').textContent = d.n; q('#r-p').textContent = d.p; q('#r-j').textContent = d.j; q('#r-l').textContent = d.l;
  q('#r-t').textContent = rp(t);
  q('#r-dp').innerHTML = pill(S.dp); q('#r-pl').innerHTML = pill(S.pl); q('#r-st').innerHTML = pill(S.tag);
  q('#r-steps').innerHTML = S.step < 0 ? '' : LAB.map((l, i) => `<span class="${i < S.step || (i === S.step && S.foto) ? 'd' : i === S.step ? 'c' : ''}">${l}</span>`).join('');
  q('#r-msg').textContent = S.msg;
  let a = '';
  if (S.act) a += `<button class="btn sm ink" data-act="paynow">Bayar ${S.act}</button>`;
  if (S.step >= 0) a += `<button class="btn sm" data-open="m-foto" ${S.foto ? '' : 'disabled'}>Lihat foto</button>`;
  if (S.foto) a += '<button class="btn sm" data-open="m-testi">Beri testimoni</button>';
  q('#r-acts').innerHTML = a;
  q('#r-lock').classList.toggle('hide', !!S.foto || S.step < 0);
}
function lookup() {
  const i = q('#kc'), k = q('#kres'), f = i.closest('.fld'), md = i.closest('.md');
  k.classList.add('hide'); f.classList.remove('bad'); f.querySelector('.er')?.remove();
  const c = norm(i.value); i.value = c;
  const bad = m => { f.classList.add('bad'); const s = document.createElement('small'); s.className = 'er'; s.textContent = m; f.appendChild(s); md.classList.remove('shake'); void md.offsetWidth; md.classList.add('shake'); };
  if (!c) return bad('Masukkan kode reservasi terlebih dahulu.');
  if (!DB[c]) return bad('Data Reservasi tidak ditemukan. Periksa kembali kode Anda (tanpa strip atau spasi).');
  render(c); k.classList.remove('hide');
}
function openCek(c) { openM('m-cek'); q('#kc').value = c; lookup(); }
function fillPkg() { const p = PK[curPkg]; q('#pk-n').textContent = curPkg; q('#pk-h').textContent = rp(PRICE[curPkg]); q('#pk-d').textContent = p.d; q('#pk-x').textContent = p.x; q('#pk-f').textContent = p.f; q('#pk-b').textContent = p.b; }

// ---------- Aksi ----------
const H = {
  quick() { const v = norm(q('#qc').value), e = q('#qe'); if (!v) { e.textContent = 'Masukkan kode reservasi Anda terlebih dahulu.'; return; } e.textContent = ''; openCek(v); },
  lookup,
  paynow() {
    const d = DB[cur], dp = d.s === 'bayar', t = PRICE[d.p];
    q('#b-code').textContent = cur; q('#b-jenis').textContent = dp ? 'DP (minimal 50%)' : 'Pelunasan';
    q('#b-total').textContent = rp(t); q('#b-nom').textContent = rp(t / 2);
    q('#b-batas').textContent = dp ? '1×24 jam setelah reservasi' : 'Setelah sesi pemotretan';
    openM('m-bayar');
  },
  pay(btn, md) {
    const d = DB[cur], dp = d.s === 'bayar'; d.s = dp ? 'verif' : 'verif2';
    done(md, 'Bukti pembayaran terkirim', `Bukti ${dp ? 'DP' : 'pelunasan'} untuk kode <b>${cur}</b> sudah kami terima. Admin akan memeriksanya maksimal 1×24 jam.`, `<button class="btn sm" data-check="${cur}" style="margin-bottom:6px">Lihat status terbaru</button>`);
  },
  reserve(btn, md) {
    const v = id => q('#' + id).value.trim(), c = genCode(), p = v('rp');
    const tgl = new Date(v('rt') + 'T00:00').toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    DB[c] = { s: 'bayar', n: v('rn'), p, j: tgl + ' · ' + v('rj'), l: v('rl') };
    done(md, 'Reservasi diterima', `Terima kasih, ${v('rn')}. Jadwal Anda telah tercatat. Catat kode reservasi unik Anda:`,
      `<div class="code-big">${c}</div><p class="note" style="margin-bottom:12px">Kode ini hanya untuk Anda dan berbeda pada setiap reservasi. Gunakan untuk cek status, bayar, dan membuka hasil foto.</p><button class="btn sm ink" data-check="${c}">Cek status reservasi</button>`);
  },
  login(btn) { btn.textContent = 'Memverifikasi…'; setTimeout(() => location.href = 'dashboard.html', 700); }
};
function submit(btn) {
  const md = btn.closest('.md'), sc = btn.dataset.scope ? q(btn.dataset.scope) : md;
  if (sc && !check(sc, md || sc)) return;
  const run = () => {
    if (btn.dataset.act && H[btn.dataset.act]) return H[btn.dataset.act](btn, md);
    if (btn.dataset.ok) { const [t, m] = btn.dataset.ok.split('|'); return done(md, t, m); }
    if (md) closeM(); toast(btn.dataset.toast || 'Tersimpan');
  };
  btn.dataset.confirm ? ask(btn.dataset.confirm, run) : run();
}

document.addEventListener('click', e => {
  const c = e.target.closest('[data-check]');
  if (c) { e.preventDefault(); openCek(c.dataset.check); return; }
  const o = e.target.closest('[data-open]');
  if (o) {
    e.preventDefault();
    if (o.dataset.pkg) { curPkg = o.dataset.pkg; fillPkg(); }
    openM(o.dataset.open);
    if (o.dataset.pre) q('#rp').value = curPkg;
    return;
  }
  const s = e.target.closest('[data-ok],[data-toast],[data-act],[data-confirm]');
  if (s) { e.preventDefault(); submit(s); return; }
  const t = e.target.closest('[data-toggle]');
  if (t) q(t.dataset.toggle).classList.toggle('hide');
  if (e.target.closest('[data-close]') || (e.target.classList.contains('ov') && !e.target.classList.contains('top'))) closeM();
  const st = e.target.closest('.stars span');
  if (st) { const p = st.parentNode, a = [...p.children], n = a.indexOf(st); p.dataset.v = n + 1; a.forEach((x, i) => x.textContent = i <= n ? '★' : '☆'); p.closest('.fld')?.classList.remove('bad'); p.closest('.fld')?.querySelector('.er')?.remove(); }
});
document.addEventListener('change', e => {
  if (e.target.type === 'file') { const d = e.target.closest('label').querySelector('.dn'), f = e.target.files; d.textContent = f.length ? (f.length > 1 ? f.length + ' file dipilih' : f[0].name) : d.dataset.o; }
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { const cf = q('#cf.on'); cf ? cf.classList.remove('on') : closeM(); }
  if (e.key === 'Enter') { if (e.target.id === 'kc') lookup(); if (e.target.id === 'qc') H.quick(); }
});
qa('.dn').forEach(d => d.dataset.o = d.textContent);
if (q('#pk-n')) fillPkg();
const rt = q('#rt'); if (rt) rt.min = new Date().toISOString().slice(0, 10);

// ---------- Tampilan ----------
const nav = q('nav'); if (nav) addEventListener('scroll', () => nav.classList.toggle('s', scrollY > 40));
const rv = qa('.rv');
if ('IntersectionObserver' in window) { const io = new IntersectionObserver(es => es.forEach(x => x.isIntersecting && x.target.classList.add('in')), { threshold: .12 }); rv.forEach(e => io.observe(e)); }
else rv.forEach(e => e.classList.add('in'));
const cal = q('#cal');
if (cal) {
  const b = BOOK.map(d => +d.slice(-2)), off = 3;
  let h = ['S', 'S', 'R', 'K', 'J', 'S', 'M'].map(d => `<span class="h">${d}</span>`).join('') + '<span></span>'.repeat(off);
  for (let d = 1; d <= 31; d++) h += `<span class="${b.includes(d) ? 'b' : ''}">${d}</span>`;
  cal.innerHTML = h;
}
