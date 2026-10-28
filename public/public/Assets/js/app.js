// Liquif Picture — interaksi antarmuka (simulasi tampilan; data contoh disimpan di localStorage)
const q = s => document.querySelector(s), qa = s => document.querySelectorAll(s);
const PG = document.body.dataset.pg || '', ADM = document.body.hasAttribute('data-adm');
const qs = k => new URLSearchParams(location.search).get(k);
const CH = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
const load = (k, d) => { try { return JSON.parse(localStorage.getItem(k)) || d; } catch (e) { return d; } };
const save = (k, v) => { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} };

// ---------- Data contoh ----------
const PK0 = [
  { n: 'Essence', h: 450000, d: '1 jam', x: 'Sesi singkat dengan satu konsep.', f: '20 foto sunting · 1 lokasi · galeri privat', b: '—' },
  { n: 'Signature', h: 950000, d: '2 jam', x: 'Sesi dua konsep dengan arahan penuh.', f: '45 foto sunting · 2 lokasi · galeri privat', b: 'Cetak 4R' },
  { n: 'Atelier', h: 1850000, d: '4 jam', x: 'Konsep kustom dengan waktu longgar.', f: '90 foto sunting · bebas lokasi · galeri privat', b: 'Album cetak' }
];
const DB0 = {
  LQ7K4M9RTX: { s: 'bayar',  n: 'Nama D', hp: '081200000004', p: 'Essence',   t: '2026-10-30', jm: '09.00', d: '1 jam', l: 'Studio', f: 0 },
  LQ3D8H2WNP: { s: 'verif',  n: 'Nama B', hp: '081200000002', p: 'Essence',   t: '2026-10-21', jm: '13.00', d: '1 jam', l: 'Studio', f: 0 },
  LQ9T5C6XAB: { s: 'lunasi', n: 'Nama A', hp: '081200000001', p: 'Signature', t: '2026-10-24', jm: '10.00', d: '2 jam', l: 'Studio', f: 0 },
  LQ4R2N7VKE: { s: 'verif2', n: 'Nama F', hp: '081200000006', p: 'Essence',   t: '2026-10-16', jm: '15.00', d: '1 jam', l: 'Luar studio', f: 0 },
  LQ6M8J3FYZ: { s: 'selesai',n: 'Nama C', hp: '081200000003', p: 'Atelier',   t: '2026-10-12', jm: '08.00', d: '4 jam', l: 'Luar studio', f: 1 },
  LQ5W9B4HQD: { s: 'batal',  n: 'Nama E', hp: '081200000005', p: 'Signature', t: '2026-10-03', jm: '11.00', d: '2 jam', l: 'Studio', f: 0, alasan: 'Jadwal bentrok dengan acara studio.' }
};
let PK = load('lq_pk2', PK0), DB = load('lq_db2', DB0);
const sv = () => { save('lq_pk2', PK); save('lq_db2', DB); };
const pkOf = n => PK.find(p => p.n === n);
const price = n => (pkOf(n) || { h: 0 }).h;
const booked = () => Object.values(DB).filter(d => d.s !== 'batal').map(d => d.t);

// Alur status: DP → verifikasi → pelunasan → verifikasi → selesai (foto terbuka)
const LAB = ['Pembayaran DP', 'Konfirmasi', 'Pelunasan', 'Selesai'];
const ST = {
  bayar:  { step: 0, dp: ['Belum dibayar', 'wa'], pl: ['Setelah DP terverifikasi', ''], tag: ['Menunggu pembayaran DP', 'wa'], act: 'DP', msg: 'Segera bayar DP minimal 50% agar jadwal Anda dikunci.' },
  verif:  { step: 1, dp: ['Menunggu verifikasi', 'wa'], pl: ['Setelah DP terverifikasi', ''], tag: ['Konfirmasi pembayaran', 'wa'], msg: 'Bukti DP sedang diperiksa admin (maksimal 1×24 jam).' },
  lunasi: { step: 2, dp: ['Terverifikasi', 'ok'], pl: ['Belum dibayar', 'wa'], tag: ['Menunggu pelunasan', 'wa'], act: 'Pelunasan', msg: 'DP sudah diterima. Lakukan pelunasan setelah sesi pemotretan.' },
  verif2: { step: 2, dp: ['Terverifikasi', 'ok'], pl: ['Menunggu verifikasi', 'wa'], tag: ['Verifikasi pelunasan', 'wa'], msg: 'Bukti pelunasan sedang diperiksa admin.' },
  selesai:{ step: 3, dp: ['Terverifikasi', 'ok'], pl: ['Terverifikasi', 'ok'], tag: ['Selesai — lunas', 'ok'], foto: 1, msg: 'Pembayaran lunas. Hasil foto Anda dapat dibuka dan diunduh.' },
  batal:  { step: -1, dp: ['—', ''], pl: ['—', ''], tag: ['Dibatalkan', 'rj'], msg: 'Reservasi ini dibatalkan.' }
};
const PAID = { bayar: 0, verif: 0, lunasi: .5, verif2: .5, selesai: 1, batal: 0 };
let cur = '', eidx = -1;
const rp = n => 'Rp ' + n.toLocaleString('id-ID');
const fd = t => new Date(t + 'T00:00').toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
const norm = v => (v || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
const pill = ([t, c]) => `<span class="pill ${c}">${t}</span>`;
const drop = (t, s, multi) => `<div class="fld"><label class="drop"><span class="dn">${t}</span><br><span class="note">${s}</span><input type="file" accept=".jpg,.jpeg,.png,.pdf,image/*" ${multi ? 'multiple' : ''} required></label></div>`;
function genCode() { let c; do { const a = new Uint32Array(8); crypto.getRandomValues(a); c = 'LQ' + [...a].map(x => CH[x % CH.length]).join(''); } while (DB[c]); return c; }

// ---------- Pop up, notifikasi, konfirmasi ----------
function reset(md) {
  md.classList.remove('done'); md.querySelector('.okp')?.remove();
  md.querySelectorAll('.fld.bad').forEach(f => f.classList.remove('bad'));
  md.querySelectorAll('.er').forEach(e => e.remove());
  if (md.hasAttribute('data-clear')) {
    md.querySelectorAll('input:not([type=file]),textarea').forEach(i => i.value = '');
    md.querySelectorAll('input[type=file]').forEach(i => { i.value = ''; const d = i.closest('label')?.querySelector('.dn'); if (d && d.dataset.o) d.textContent = d.dataset.o; });
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
function done(md, t, m, x = '') {
  md.querySelector('.okp')?.remove();
  const p = document.createElement('div'); p.className = 'okp';
  const when = new Date().toLocaleString('id-ID', { dateStyle: 'long', timeStyle: 'short' });
  p.innerHTML = `<div class="tick">✓</div><h3>${t}</h3><p>${m}</p>${x}<div class="rcpt">Tercatat pada ${when}${ADM ? '' : '<br>Simpan tangkapan layar ini sebagai bukti.'}</div>${md.classList.contains('md') ? '<div class="acts" style="justify-content:center"><button class="btn ink" data-close>Tutup</button></div>' : ''}`;
  md.appendChild(p); md.classList.add('done'); if (md.scrollTo) md.scrollTop = 0;
}

// ---------- Validasi ----------
function err(i, m) {
  const f = i.closest('.fld') || i.parentNode; f.classList.add('bad'); f.querySelector('.er')?.remove();
  const s = document.createElement('small'); s.className = 'er'; s.textContent = m; f.appendChild(s);
}
function shake(m) { m.classList.remove('shake'); void m.offsetWidth; m.classList.add('shake'); }
function check(sc, md) {
  sc.querySelectorAll('.fld.bad').forEach(f => f.classList.remove('bad'));
  sc.querySelectorAll('.er').forEach(e => e.remove());
  let ok = true; const bad = (i, m) => { ok = false; err(i, m); };
  sc.querySelectorAll('[required]').forEach(i => {
    if (i.closest('.hide')) return;
    const v = (i.value || '').trim(), t = i.dataset.vt;
    if (i.type === 'file') {
      const fs = [...i.files];
      if (!fs.length) bad(i, 'Pilih file terlebih dahulu');
      else if (fs.some(f => f.size > 5 * 1048576)) bad(i, 'Ukuran file maksimal 5 MB');
      else if (fs.some(f => !/\.(jpe?g|png|pdf)$/i.test(f.name))) bad(i, 'Format harus JPG, PNG, atau PDF');
    }
    else if (!v) bad(i, 'Wajib diisi');
    else if (t === 'phone' && !/^(08|628)\d{8,11}$/.test(v.replace(/[\s-]/g, ''))) bad(i, 'Nomor tidak valid, contoh: 081234567890');
    else if (t === 'email' && !/^\S+@\S+\.\S+$/.test(v)) bad(i, 'Format email tidak valid');
    else if (t === 'min' && v.length < +i.dataset.min) bad(i, 'Minimal ' + i.dataset.min + ' karakter');
    else if (t === 'num' && !/^\d+$/.test(v.replace(/[.\s]/g, ''))) bad(i, 'Isi dengan angka saja');
    else if (t === 'date') {
      if (v < new Date().toISOString().slice(0, 10)) bad(i, 'Pilih tanggal hari ini atau setelahnya');
      else if (booked().includes(v)) bad(i, 'Jadwal tanggal ini sudah terisi, pilih tanggal lain');
    }
  });
  sc.querySelectorAll('.stars[data-req]').forEach(s => { if (!s.dataset.v) bad(s, 'Berikan rating bintang'); });
  if (!ok) shake(md || sc);
  return ok;
}

// ---------- Kalender ----------
const BLN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
function buildCal(el, y, m, pick) {
  const first = (new Date(y, m, 1).getDay() + 6) % 7, n = new Date(y, m + 1, 0).getDate(), today = new Date().toISOString().slice(0, 10), bk = booked();
  let h = ['S', 'S', 'R', 'K', 'J', 'S', 'M'].map(d => `<span class="h">${d}</span>`).join('') + '<span></span>'.repeat(first);
  for (let d = 1; d <= n; d++) {
    const t = `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    h += `<span class="${bk.includes(t) ? 'b' : t < today ? 'p' : pick ? 'o' : ''}" data-t="${t}">${d}</span>`;
  }
  el.innerHTML = h;
}
function calInit(pick) {
  const el = q('#cal'); if (!el) return;
  const now = new Date(); let y = now.getFullYear(), m = now.getMonth();
  const draw = () => { q('#cm').textContent = BLN[m] + ' ' + y; buildCal(el, y, m, pick); };
  q('#cp').onclick = () => { m--; if (m < 0) { m = 11; y--; } draw(); };
  q('#cn').onclick = () => { m++; if (m > 11) { m = 0; y++; } draw(); };
  if (pick) el.onclick = e => { const s = e.target.closest('span.o'); if (s) { const r = q('#rt'); r.value = s.dataset.t; const f = r.closest('.fld'); f.classList.remove('bad'); f.querySelector('.er')?.remove(); } };
  draw();
}

// ---------- Pelanggan ----------
function pkCards() {
  const g = q('#pkgrid'); if (!g) return;
  g.innerHTML = PK.map((p, i) => {
    const f = i === 1, rn = ['I', 'II', 'III', 'IV', 'V', 'VI'][i] || i + 1;
    return `<div class="pc${f ? ' f' : ''}"><div class="rn">${rn}</div><h3>${p.n}</h3><div class="pr">${rp(p.h)}</div><span class="note"${f ? ' style="color:#bfb4a2"' : ''}>${p.d}</span><ul>${p.f.split(' · ').map(x => `<li>${x}</li>`).join('')}${p.b && p.b !== '—' ? `<li>Bonus ${p.b}</li>` : ''}</ul><a class="btn sm${f ? ' gold' : ''}" href="paket.html?p=${encodeURIComponent(p.n)}">Lihat detail</a></div>`;
  }).join('') || '<p class="note">Belum ada paket tersedia.</p>';
}
function sum() {
  const p = pkOf(q('#rp').value);
  q('#rsum').innerHTML = p ? `<div class="eyebrow" style="color:var(--gold)">Ringkasan paket</div><h3 style="margin:12px 0 4px">${p.n}</h3><div style="font:500 28px var(--serif);color:var(--gold)">${rp(p.h)}</div><p style="opacity:.75;margin-top:8px;font-size:14px">${p.d} · ${p.f}</p>` : '';
}
function render(c) {
  const d = DB[c], S = ST[d.s], t = price(d.p); cur = c;
  q('#r-n').textContent = d.n; q('#r-p').textContent = d.p; q('#r-j').textContent = fd(d.t) + ' · ' + d.jm; q('#r-l').textContent = d.l;
  q('#r-t').textContent = rp(t);
  q('#r-dp').innerHTML = pill(S.dp); q('#r-pl').innerHTML = pill(S.pl); q('#r-st').innerHTML = pill(S.tag);
  q('#r-steps').innerHTML = S.step < 0 ? '' : LAB.map((l, i) => `<span class="${i < S.step || (i === S.step && S.foto) ? 'd' : i === S.step ? 'c' : ''}">${l}</span>`).join('');
  q('#r-msg').textContent = d.alasan && d.s === 'batal' ? 'Reservasi dibatalkan. Alasan: ' + d.alasan : S.msg;
  let a = '';
  if (S.act) a += `<a class="btn sm ink" href="bayar.html?kode=${c}">Bayar ${S.act}</a>`;
  if (S.step >= 0) a += S.foto ? `<a class="btn sm" href="hasil.html?kode=${c}">Lihat foto</a>` : '<button class="btn sm" disabled>Lihat foto</button>';
  q('#r-acts').innerHTML = a;
  q('#r-lock').classList.toggle('hide', !!S.foto || S.step < 0);
  const tc = q('#tcard'); reset(tc); tc.classList.toggle('hide', !(S.foto && d.f && !d.ts));
}
function lookup() {
  const i = q('#kc'), k = q('#kres'), f = i.closest('.fld'), box = i.closest('.fc');
  k.classList.add('hide'); f.classList.remove('bad'); f.querySelector('.er')?.remove();
  const c = norm(i.value); i.value = c;
  if (!c) { err(i, 'Masukkan kode reservasi terlebih dahulu.'); return shake(box); }
  if (!DB[c]) { err(i, 'Data Reservasi tidak ditemukan. Periksa kembali kode Anda (tanpa strip atau spasi).'); return shake(box); }
  render(c); k.classList.remove('hide');
}

// ---------- Admin ----------
function rows() {
  const f = (q('#fq').value || '').toUpperCase().trim(), s = q('#fs').value;
  const list = Object.entries(DB).filter(([c, d]) => (!f || c.includes(norm(f)) || d.n.toUpperCase().includes(f)) && (!s || d.s === s)).sort((a, b) => a[1].t < b[1].t ? 1 : -1);
  q('#rtab').innerHTML = '<tr class="th"><th>Kode</th><th>Pelanggan</th><th>Paket</th><th>Jadwal</th><th>Status</th><th>Aksi</th></tr>' +
    (list.map(([c, d]) => { const v = d.s === 'verif' || d.s === 'verif2'; return `<tr><td data-l="Kode">${c}</td><td data-l="Pelanggan">${d.n}</td><td data-l="Paket">${d.p}</td><td data-l="Jadwal">${fd(d.t)}</td><td data-l="Status">${pill(ST[d.s].tag)}</td><td data-l="Aksi"><a class="btn sm${v ? ' ink' : ''}" href="admin-reservasi.html?kode=${c}">${v ? 'Verifikasi' : 'Detail'}</a></td></tr>`; }).join('') || '<tr><td colspan="6" class="note">Data reservasi tidak ditemukan.</td></tr>');
}
function kpi() {
  const ds = Object.values(DB), td = new Date().toISOString().slice(0, 10), z = n => String(n).padStart(2, '0');
  q('#kp').innerHTML = [['Reservasi baru', ds.filter(d => d.s === 'bayar').length], ['Menunggu verifikasi', ds.filter(d => d.s === 'verif' || d.s === 'verif2').length], ['Sesi mendatang', ds.filter(d => d.s !== 'batal' && d.t >= td).length], ['Menunggu hasil foto', ds.filter(d => d.s === 'selesai' && !d.f).length]]
    .map(([l, n]) => `<div><span>${l}</span><b>${z(n)}</b></div>`).join('');
}
function aRender() {
  const c = cur, d = DB[c], S = ST[d.s], t = price(d.p), v = d.s === 'verif' || d.s === 'verif2';
  const clash = Object.entries(DB).some(([k, o]) => k !== c && o.s !== 'batal' && d.s !== 'batal' && o.t === d.t && o.jm === d.jm);
  q('#a-code').textContent = c; q('#a-tag').innerHTML = pill(S.tag);
  q('#a-kv').innerHTML = `<i>Nama</i><span>${d.n}</span><i>Kontak</i><span>${d.hp || '—'}</span><i>Paket</i><span>${d.p}</span><i>Jadwal</i><span>${fd(d.t)} · ${d.jm} · ${d.d || '—'}</span><i>Lokasi</i><span>${d.l}</span><i>Bentrok</i><span>${clash ? pill(['Bentrok dengan reservasi lain', 'rj']) : pill(['Tidak ada bentrok', 'ok'])}</span>${d.alasan ? `<i>Alasan tolak</i><span>${d.alasan}</span>` : ''}`;
  q('#a-pkv').innerHTML = `<i>Total</i><span>${rp(t)}</span><i>DP</i><span>${pill(S.dp)}</span><i>Pelunasan</i><span>${pill(S.pl)}</span>`;
  q('#a-bukti').innerHTML = v ? `<div class="ph c" style="height:160px;border-radius:14px;margin:14px 0 6px"></div><p class="note">Bukti ${d.s === 'verif' ? 'DP' : 'pelunasan'} dari pelanggan — periksa nominal dan nama pengirim.</p>` : d.s === 'bayar' ? '<p class="note">Pembayaran belum dilakukan / bukti pembayaran belum tersedia.</p>' : '';
  q('#a-pay').innerHTML = v ? `<button class="btn sm ink" data-act="verify" data-confirm="Pastikan nominal dan pengirim sesuai. Verifikasi pembayaran ini?">Verifikasi ${d.s === 'verif' ? 'DP' : 'pelunasan'}</button>` : '';
  const up = q('#a-up'); reset(up);
  q('#a-upb').innerHTML = d.s === 'selesai' ? (d.f ? '<p class="note" style="margin-bottom:14px">Hasil foto sudah diunggah. Anda dapat menambah file baru.</p>' : '') + drop('Pilih atau seret file hasil foto', 'JPG / PNG · maks. 5 MB per file', 1) + `<div class="kv"><i>Kode akses</i><span>${c}</span></div><div class="acts"><button class="btn sm ink" data-act="upfoto" data-scope="#a-up">Unggah hasil foto</button></div>` : '<p class="note">Unggah hasil foto tersedia setelah pembayaran dinyatakan lunas (status Selesai).</p>';
  q('#ns').value = d.s;
  q('#a-act').innerHTML = (d.s === 'selesai' || d.s === 'batal' ? '' : '<button class="btn sm no" data-open="m-tolak">Tolak</button>') + '<button class="btn sm ink" data-act="okres">Konfirmasi</button>';
}
function pRows() {
  q('#ptab').innerHTML = '<tr class="th"><th>Paket</th><th>Harga</th><th>Durasi</th><th>Fasilitas</th><th>Aksi</th></tr>' +
    (PK.map((p, i) => `<tr><td data-l="Paket"><b>${p.n}</b></td><td data-l="Harga">${rp(p.h)}</td><td data-l="Durasi">${p.d}</td><td data-l="Fasilitas">${p.f}</td><td data-l="Aksi"><div class="ab"><button class="btn sm" data-act="editpk" data-i="${i}">Edit</button><button class="btn sm no" data-act="delpk" data-i="${i}" data-confirm="Hapus paket ${p.n}? Paket tidak akan tampil lagi di website.">Hapus</button></div></td></tr>`).join('') || '<tr><td colspan="5" class="note">Belum ada paket. Tekan “Tambah paket”.</td></tr>');
}
function lap() {
  const f = norm(q('#lq').value), s = q('#ls').value, a = q('#ld').value, b = q('#lt').value;
  const list = Object.entries(DB).filter(([c, d]) => (!f || c.includes(f) || norm(d.n).includes(f)) && (!s || d.s === s) && (!a || d.t >= a) && (!b || d.t <= b)).sort((x, y) => x[1].t < y[1].t ? 1 : -1);
  const tot = list.reduce((n, [, d]) => n + PAID[d.s] * price(d.p), 0);
  q('#lsum').innerHTML = `<div><span>Jumlah reservasi</span><b>${list.length}</b></div><div><span>Pembayaran diterima</span><b style="font-size:30px">${rp(tot)}</b></div>`;
  q('#ltab').innerHTML = '<tr class="th"><th>Kode</th><th>Pelanggan</th><th>Paket</th><th>Jadwal</th><th>Total</th><th>Diterima</th><th>Status</th></tr>' +
    (list.map(([c, d]) => `<tr><td data-l="Kode">${c}</td><td data-l="Pelanggan">${d.n}</td><td data-l="Paket">${d.p}</td><td data-l="Jadwal">${fd(d.t)}</td><td data-l="Total">${rp(price(d.p))}</td><td data-l="Diterima">${rp(PAID[d.s] * price(d.p))}</td><td data-l="Status">${pill(ST[d.s].tag)}</td></tr>`).join('') || '<tr><td colspan="7" class="note">Data laporan tidak ditemukan. Ubah filter lalu cari kembali.</td></tr>');
}

// ---------- Aksi ----------
const H = {
  quick() { const v = norm(q('#qc').value), e = q('#qe'); if (!v) { e.textContent = 'Masukkan kode reservasi Anda terlebih dahulu.'; return; } e.textContent = ''; location.href = 'cek.html?kode=' + v; },
  lookup,
  reserve(btn) {
    const v = id => q('#' + id).value.trim(), c = genCode();
    DB[c] = { s: 'bayar', n: v('rn'), hp: v('rk'), p: v('rp'), t: v('rt'), jm: v('rj'), d: v('rd'), l: v('rl'), f: 0 }; sv();
    btn.textContent = 'Mengirim…'; location.href = 'cek.html?kode=' + c + '&baru=1';
  },
  pay(btn, md) {
    const d = DB[cur], dp = d.s === 'bayar'; d.s = dp ? 'verif' : 'verif2'; sv();
    done(md, 'Bukti pembayaran terkirim', `Bukti ${dp ? 'DP' : 'pelunasan'} untuk kode <b>${cur}</b> sudah kami terima. Status: menunggu verifikasi admin (maksimal 1×24 jam).`, `<a class="btn sm ink" href="cek.html?kode=${cur}" style="margin-bottom:6px">Lihat status terbaru</a>`);
  },
  testi(btn, md) { DB[cur].ts = 1; sv(); done(md, 'Terima kasih atas testimoni Anda', 'Testimoni sudah kami terima dan akan membantu calon pelanggan lain.'); },
  dlsel() { const n = qa('.chk:checked').length; toast(n ? `Mengunduh ${n} foto…` : 'Pilih foto terlebih dahulu'); },
  dlall() { toast('Mengunduh semua foto…'); },
  login(btn) { btn.textContent = 'Memverifikasi…'; setTimeout(() => location.href = 'dashboard.html', 700); },
  okres() { toast('Reservasi dikonfirmasi. Jadwal dikunci untuk pelanggan.'); },
  setst() { DB[cur].s = q('#ns').value; sv(); aRender(); toast('Status reservasi diperbarui'); },
  verify() { const d = DB[cur]; d.s = d.s === 'verif' ? 'lunasi' : 'selesai'; sv(); aRender(); toast('Pembayaran terverifikasi'); },
  reject(btn, md) { const d = DB[cur]; d.s = 'batal'; d.alasan = md.querySelector('textarea').value.trim(); sv(); closeM(); aRender(); toast('Reservasi ditolak'); },
  upfoto(btn, md) { DB[cur].f = 1; sv(); done(md, 'Hasil foto terunggah', 'Foto tersimpan di galeri privat dan dapat dibuka pelanggan dengan kode reservasinya.'); },
  lap() {
    const a = q('#ld').value, b = q('#lt').value;
    if (a && b && a > b) { err(q('#lt'), 'Tanggal akhir harus setelah tanggal awal'); return shake(q('#lform')); }
    lap(); toast('Laporan diperbarui');
  },
  newpk() { eidx = -1; q('#pkt').textContent = 'Tambah paket'; openM('m-pk'); },
  editpk(btn) {
    eidx = +btn.dataset.i; const p = PK[eidx]; q('#pkt').textContent = 'Edit paket'; openM('m-pk');
    q('#pn').value = p.n; q('#ph').value = p.h; q('#pd').value = p.d; q('#px').value = p.x === '—' ? '' : p.x; q('#pf').value = p.f.replace(/ · /g, ', '); q('#pb').value = p.b === '—' ? '' : p.b;
  },
  savepk() {
    const g = id => q('#' + id).value.trim(), n = g('pn');
    if (PK.some((p, i) => i !== eidx && p.n.toLowerCase() === n.toLowerCase())) { err(q('#pn'), 'Nama paket sudah dipakai'); return shake(q('#m-pk .md')); }
    const o = { n, h: +g('ph').replace(/[.\s]/g, ''), d: g('pd'), x: g('px') || '—', f: g('pf').split(/\s*,\s*/).filter(Boolean).join(' · '), b: g('pb') || '—' };
    if (eidx >= 0) { const old = PK[eidx].n; Object.values(DB).forEach(d => { if (d.p === old) d.p = n; }); PK[eidx] = o; } else PK.push(o);
    sv(); closeM(); pRows(); toast(eidx >= 0 ? 'Paket diperbarui' : 'Paket baru ditambahkan');
  },
  delpk(btn) { PK.splice(+btn.dataset.i, 1); sv(); pRows(); toast('Paket dihapus'); },
  upgal() {
    const n = q('#gup input[type=file]').files.length, g = q('#gg'), v = ['', 'b', 'c', 'd', 'e'];
    for (let i = 0; i < n; i++) g.insertAdjacentHTML('afterbegin', `<div class="gph"><input type="checkbox" class="chk" aria-label="Pilih foto"><div class="ph ${v[i % 5]}"></div></div>`);
    reset(q('#gup')); toast(n + ' foto ditambahkan ke galeri');
  },
  delgal() {
    const s = qa('#gg .chk:checked'); if (!s.length) return toast('Pilih foto yang akan dihapus terlebih dahulu');
    ask(`Hapus ${s.length} foto terpilih dari galeri studio?`, () => { s.forEach(c => c.closest('.gph').remove()); toast('Foto dihapus'); });
  },
  saveprof() { toast('Profil studio disimpan'); }
};
function submit(btn) {
  const md = btn.closest('.md,.fc'), sc = btn.dataset.scope ? q(btn.dataset.scope) : md;
  if (sc && !check(sc, md || sc)) return;
  const run = () => { if (btn.dataset.act && H[btn.dataset.act]) return H[btn.dataset.act](btn, md); if (btn.dataset.ok) { const [t, m] = btn.dataset.ok.split('|'); return done(md, t, m); } if (md && md.classList.contains('md')) closeM(); toast(btn.dataset.toast || 'Tersimpan'); };
  btn.dataset.confirm ? ask(btn.dataset.confirm, run) : run();
}

document.addEventListener('click', e => {
  const bg = e.target.closest('.bg');
  if (bg) {
    const n = bg.closest('nav');
    if (n) n.classList.toggle('open');
    else { bg.closest('.mbar').classList.toggle('open'); q('.sb').classList.toggle('open'); q('.sbk').classList.toggle('on'); }
    return;
  }
  if (e.target.closest('nav .menu a') || e.target.closest('.sb a') || e.target.classList.contains('sbk')) {
    q('nav')?.classList.remove('open'); q('.sb')?.classList.remove('open'); q('.sbk')?.classList.remove('on'); q('.mbar')?.classList.remove('open');
  }
  const o = e.target.closest('[data-open]');
  if (o) { e.preventDefault(); openM(o.dataset.open); return; }
  const s = e.target.closest('[data-act],[data-toast],[data-confirm],[data-ok]');
  if (s) { e.preventDefault(); submit(s); return; }
  if (e.target.closest('[data-close]') || (e.target.classList.contains('ov') && !e.target.classList.contains('top'))) closeM();
  const st = e.target.closest('.stars span');
  if (st) { const p = st.parentNode, a = [...p.children], n = a.indexOf(st); p.dataset.v = n + 1; a.forEach((x, i) => x.textContent = i <= n ? '★' : '☆'); const f = p.closest('.fld'); f?.classList.remove('bad'); f?.querySelector('.er')?.remove(); }
});
document.addEventListener('change', e => {
  if (e.target.type === 'file') { const d = e.target.closest('label').querySelector('.dn'), f = e.target.files; if (!d.dataset.o) d.dataset.o = d.textContent; d.textContent = f.length ? (f.length > 1 ? f.length + ' file dipilih' : f[0].name) : d.dataset.o; }
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { const cf = q('#cf.on'); cf ? cf.classList.remove('on') : closeM(); }
  if (e.key === 'Enter') { if (e.target.id === 'kc') lookup(); if (e.target.id === 'qc') H.quick(); }
});

// ---------- Inisialisasi per halaman ----------
const nav = q('nav');
if (nav) { const f = () => nav.classList.toggle('s', nav.classList.contains('solid') || scrollY > 40); f(); addEventListener('scroll', f); }
pkCards();
if (PG === 'paket') {
  let n = qs('p'); if (!pkOf(n)) n = (PK[0] || {}).n; const p = pkOf(n);
  if (!p) q('#pk-n').textContent = 'Paket tidak tersedia';
  else { q('#pk-n').textContent = p.n; q('#pk-h').textContent = rp(p.h); q('#pk-x').textContent = p.x; q('#pk-d').textContent = p.d; q('#pk-f').textContent = p.f; q('#pk-b').textContent = p.b; q('#pk-go').href = 'reservasi.html?p=' + encodeURIComponent(n); }
  q('#pk-ch').innerHTML = PK.map(x => `<a class="chip${x.n === n ? ' on' : ''}" href="paket.html?p=${encodeURIComponent(x.n)}">${x.n}</a>`).join('');
}
if (PG === 'reservasi') {
  const s = q('#rp'); s.innerHTML = PK.map(p => `<option>${p.n}</option>`).join('');
  const p0 = qs('p'); if (p0 && pkOf(p0)) s.value = p0;
  sum(); s.onchange = sum; q('#rt').min = new Date().toISOString().slice(0, 10); calInit(true);
}
if (PG === 'cek') {
  const k = norm(qs('kode'));
  if (k) { q('#kc').value = k; lookup(); if (qs('baru') && DB[k]) { const b = q('#bnr'); b.classList.remove('hide'); b.innerHTML = `Reservasi berhasil dibuat. Catat kode unik Anda: <b>${k}</b><br><span class="note">Gunakan kode ini untuk cek status, bayar, dan membuka hasil foto.</span>`; } }
}
if (PG === 'bayar') {
  const c = norm(qs('kode')), d = DB[c], miss = m => { q('#bmsg').textContent = m; q('#bmiss').classList.remove('hide'); };
  if (!d) miss('Kode reservasi tidak ditemukan. Mulai dari menu Cek Reservasi.');
  else if (!['bayar', 'lunasi'].includes(d.s)) miss(d.s === 'verif' || d.s === 'verif2' ? 'Bukti pembayaran Anda sedang diverifikasi admin.' : d.s === 'selesai' ? 'Pembayaran Anda sudah lunas.' : 'Reservasi ini tidak memerlukan pembayaran.');
  else {
    cur = c; const dp = d.s === 'bayar', t = price(d.p);
    q('#bt').textContent = dp ? 'Pembayaran DP' : 'Pelunasan';
    q('#b-code').textContent = c; q('#b-jenis').textContent = dp ? 'DP (minimal 50%)' : 'Pelunasan'; q('#b-total').textContent = rp(t); q('#b-nom').textContent = rp(t / 2);
    q('#b-batas').textContent = dp ? '1×24 jam setelah reservasi' : 'Setelah sesi pemotretan'; q('#bform').classList.remove('hide');
  }
  qa('#bmiss a,#bback').forEach(a => a.href = 'cek.html' + (d ? '?kode=' + c : ''));
}
if (PG === 'hasil') {
  const c = norm(qs('kode')), d = DB[c], lock = (t, m) => { q('#hlt').textContent = t; q('#hmsg').textContent = m; q('#hlock').classList.remove('hide'); };
  if (!d) lock('Kode tidak ditemukan', 'Buka galeri privat dari menu Cek Reservasi dengan kode Anda.');
  else if (d.s !== 'selesai') lock('Galeri belum dapat dibuka', 'Hasil foto terbuka setelah pembayaran lunas dan terverifikasi (status Selesai).');
  else if (!d.f) lock('Hasil foto belum tersedia', 'Studio sedang menyiapkan hasil foto Anda. Silakan cek kembali nanti.');
  else { q('#hgal').classList.remove('hide'); q('#hl').textContent = 'Kode ' + c + ' · paket ' + d.p; q('#hg').innerHTML = ['', 'b', 'c', 'd', 'e', 'b', 'c', ''].map(v => `<div class="gph"><input type="checkbox" class="chk" aria-label="Pilih foto"><div class="ph ${v}"></div></div>`).join(''); }
  qa('#hlock a,#hb2').forEach(a => a.href = 'cek.html' + (d ? '?kode=' + c : ''));
}
if (PG === 'dashboard') { kpi(); rows(); calInit(false); q('#fq').oninput = rows; q('#fs').onchange = rows; }
if (PG === 'areservasi') {
  cur = norm(qs('kode')); if (!DB[cur]) cur = Object.keys(DB)[0];
  q('#ns').innerHTML = Object.keys(ST).map(k => `<option value="${k}">${ST[k].tag[0]}</option>`).join(''); aRender();
}
if (PG === 'paketadm') pRows();
if (PG === 'laporan') lap();

const rv = qa('.rv');
if ('IntersectionObserver' in window) { const io = new IntersectionObserver(es => es.forEach(x => x.isIntersecting && x.target.classList.add('in')), { threshold: .12 }); rv.forEach(e => io.observe(e)); }
else rv.forEach(e => e.classList.add('in'));
