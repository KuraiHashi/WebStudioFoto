@extends('TemplateAdmin.main')
@section('laporan', 'on')
@section('konten')
    <style>
        #report-print-meta {
            display: none;
        }
    </style>
    <style>
        @page {
            size: landscape;
            margin: 12mm 10mm;
        }

        @media print {
            body {
                background: #fff;
                color: #14110e;
                font: 9pt/1.4 Arial, sans-serif;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .mbar,
            .sbk,
            .sb,
            #lform,
            #download-report,
            .screen-only {
                display: none !important;
            }

            .dsh {
                display: block !important;
                min-height: 0;
            }

            .main {
                padding: 0 !important;
            }

            .top {
                align-items: flex-start;
                margin-bottom: 12px;
                padding-bottom: 12px;
                border-bottom: 2px solid #14110e;
            }

            .top h2 {
                margin-top: 4px !important;
                font-size: 25pt;
            }

            .eyebrow {
                font-size: 8pt;
            }

            #report-print-meta {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 12px;
                margin-bottom: 14px;
                padding: 10px 12px;
                border: 1px solid #ded8ce;
                background: #faf8f4;
            }

            #report-print-meta div {
                min-width: 0;
            }

            #report-print-meta span {
                display: block;
                margin-bottom: 3px;
                color: #756b60;
                font-size: 7pt;
                letter-spacing: .12em;
                text-transform: uppercase;
            }

            #report-print-meta strong {
                font-size: 9pt;
                font-weight: 600;
            }

            .sum {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 8px;
                margin: 0 0 14px;
            }

            .sum div {
                padding: 10px 12px;
                border: 1px solid #ded8ce;
                border-top: 3px solid #a97f49;
                border-radius: 0;
                background: #fff;
                break-inside: avoid;
            }

            .sum span {
                color: #756b60;
                font-size: 7pt;
            }

            .sum b {
                margin-top: 4px;
                font: 600 14pt/1.2 Arial, sans-serif;
            }

            #report-table-card {
                padding: 0;
                border: 0;
                border-radius: 0;
                background: #fff;
                break-inside: auto !important;
                page-break-inside: auto !important;
            }

            .tw {
                overflow: visible !important;
            }

            #report-table {
                width: 100%;
                min-width: 0;
                table-layout: auto;
                font-size: 8pt;
            }

            #report-table thead {
                display: table-header-group;
            }

            #report-table tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            #report-table th {
                padding: 8px 7px;
                background: #211c17;
                color: #fff;
                font-size: 7pt;
            }

            #report-table td {
                padding: 7px;
                border-bottom: 1px solid #e5e0d8;
                vertical-align: top;
            }

            #report-table tbody tr:nth-child(even) {
                background: #faf8f4;
            }

            #report-table td:nth-child(1),
            #report-table td:nth-child(4),
            #report-table td:nth-child(5),
            #report-table td:nth-child(6) {
                white-space: nowrap;
            }

            #report-table .pill {
                padding: 0;
                background: transparent;
                color: #14110e;
                font-size: 8pt;
                letter-spacing: 0;
            }
        }
    </style>
    <main class="main">
        <div class="top">
            <div>
                <div class="eyebrow">Laporan</div>
                <h2 style="margin-top: 12px">
                    Reservasi &amp; <em>pembayaran</em>
                </h2>
            </div>
            <button class="btn ink" id="download-report" type="button">Unduh PDF</button>
        </div>
        <div id="report-print-meta">
            <div>
                <span>Periode jadwal</span>
                <strong>
                    {{ !empty($filters['from']) ? \Illuminate\Support\Carbon::parse($filters['from'])->format('d/m/Y') : 'Semua tanggal' }}
                    &ndash;
                    {{ !empty($filters['to']) ? \Illuminate\Support\Carbon::parse($filters['to'])->format('d/m/Y') : 'Sekarang' }}
                </strong>
            </div>
            <div>
                <span>Filter laporan</span>
                <strong>
                    {{ ($filters['status'] ?? '') === 'tanpa_pembayaran' ? 'Belum ada pembayaran' : ($filters['status'] ?? 'Semua status') }}
                    @if (!empty($filters['q']))
                        &middot; {{ $filters['q'] }}
                    @endif
                </strong>
            </div>
            <div>
                <span>Dicetak pada</span>
                <strong>{{ now()->format('d/m/Y H:i') }}</strong>
            </div>
        </div>
        <p class="note screen-only" style="margin: -18px 0 24px">Pilih “Simpan sebagai PDF” pada dialog cetak yang terbuka.</p>
        <form class="fc" id="lform" method="GET" action="{{ route('admin.laporan') }}">
            <div class="flt">
                <div class="fld">
                    <label for="ld">Dari</label>
                    <input id="ld" name="from" type="date" value="{{ $filters['from'] ?? '' }}" />
                </div>
                <div class="fld">
                    <label for="lt">Sampai</label>
                    <input id="lt" name="to" type="date" value="{{ $filters['to'] ?? '' }}" />
                    @error('to')
                        <span class="note">{{ $message }}</span>
                    @enderror
                </div>
                <div class="fld">
                    <label for="lq">Nama / kode</label>
                    <input id="lq" name="q" value="{{ $filters['q'] ?? '' }}" />
                </div>
                <div class="fld">
                    <label for="ls">Status</label>
                    <select id="ls" name="status">
                        <option value="">Semua</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                {{ $status }}
                            </option>
                        @endforeach
                        <option value="tanpa_pembayaran" @selected(($filters['status'] ?? '') === 'tanpa_pembayaran')}>
                            Belum ada pembayaran
                        </option>
                    </select>
                </div>
            </div>
            <div class="acts" style="margin-top: 18px">
                <button class="btn ink" type="submit">Cari</button>
                <a class="btn" href="{{ route('admin.laporan') }}">Reset</a>
            </div>
        </form>

        <div class="sum">
            <div><span>Jumlah reservasi</span><b>{{ $reservations->count() }}</b></div>
            <div><span>Total tagihan</span><b>Rp {{ number_format((float) $totalTagihan, 0, ',', '.') }}</b></div>
            <div><span>Pembayaran diterima</span><b>Rp {{ number_format((float) $totalDiterima, 0, ',', '.') }}</b></div>
        </div>

        <div class="card" id="report-table-card">
            <div class="tw rt">
                <table class="table" id="report-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Pelanggan</th>
                            <th>Paket</th>
                            <th>Jadwal</th>
                            <th>Total</th>
                            <th>Diterima</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reservations as $reservation)
                            <tr>
                                <td>{{ $reservation->token }}</td>
                                <td>{{ $reservation->name }}</td>
                                <td>{{ $reservation->package?->name ?? 'Paket tidak tersedia' }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($reservation->date)->format('d M Y') }}</td>
                                <td>Rp {{ number_format((float) ($reservation->payment?->tagihan ?? 0), 0, ',', '.') }}</td>
                                <td>Rp {{ number_format((float) ($reservation->payment?->total_paid ?? 0), 0, ',', '.') }}</td>
                                <td><span class="pill">{{ $reservation->payment?->status ?? 'Belum ada pembayaran' }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="note">Data laporan tidak ditemukan. Ubah filter lalu cari kembali.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        document.getElementById('download-report')?.addEventListener('click', () => window.print());
    </script>
@endpush
