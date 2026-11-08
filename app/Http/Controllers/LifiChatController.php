<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PhotographyPackage;
use App\Models\Reservation;
use App\Models\StudioProfile;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LifiChatController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'token' => ['nullable', 'string', 'max:32'],
        ]);

        $message = mb_strtolower(trim($validated['message']));
        $token = strtoupper(trim($validated['token'] ?? ''));

        if ($token !== '') {
            $payment = Payment::query()
                ->with('reservation.package')
                ->where('token', $token)
                ->first();

            return response()->json([
                'reply' => $payment
                    ? $this->reservationReply($payment)
                    : 'Aku belum menemukan reservasi dengan token itu. Coba cek lagi penulisan tokennya, ya.',
            ]);
        }

        if ($this->isScheduleQuestion($message)) {
            $date = $this->dateFromMessage($message);

            if ($date === null) {
                return response()->json([
                    'reply' => 'Boleh kirim tanggal yang ingin dicek, ya? Contohnya 2026-12-20 atau 20/12/2026. Kalau ingin cek jadwal reservasi sendiri, kirim token reservasinya.',
                ]);
            }

            $bookedTimes = Reservation::query()
                ->whereDate('date', $date)
                ->whereHas('payment', fn ($query) => $query->where('status', '!=', 'Dibatalkan'))
                ->orderBy('time')
                ->pluck('time')
                ->map(fn (string $time): string => substr($time, 0, 5))
                ->unique()
                ->values();

            $reply = $bookedTimes->isEmpty()
                ? 'Sejauh ini belum ada jadwal reservasi pada '.Carbon::parse($date)->format('d-m-Y').'. Tanggalnya terlihat masih terbuka, tetapi konfirmasi akhir tetap mengikuti admin, ya.'
                : 'Pada '.Carbon::parse($date)->format('d-m-Y').', sudah ada sesi pada pukul '.$bookedTimes->join(', ').
                    '. Aku belum bisa memastikan jam lain tanpa konfirmasi admin, ya.';

            return response()->json(['reply' => $reply]);
        }

        if ($this->isPackageQuestion($message)) {
            $packages = PhotographyPackage::query()
                ->orderBy('category')
                ->orderBy('name')
                ->get(['name', 'category', 'price', 'duration']);

            if ($packages->isEmpty()) {
                return response()->json([
                    'reply' => 'Daftar paket sedang disiapkan. Kamu bisa hubungi admin studio untuk informasi terbaru, ya.',
                ]);
            }

            $packageList = $packages
                ->map(function (PhotographyPackage $package): string {
                    $category = filled($package->category) ? " ({$package->category})" : '';

                    return "{$package->name}{$category} — Rp ".number_format((float) $package->price, 0, ',', '.').
                        " · {$package->duration}";
                })
                ->implode("\n");

            return response()->json([
                'reply' => "Ini daftar paket yang tersedia saat ini:\n{$packageList}\n\nKalau ada yang menarik, aku bisa bantu arahkan ke halaman reservasi.",
            ]);
        }

        if ($this->isContactQuestion($message)) {
            $studio = StudioProfile::query()->first(['phone', 'operational_hours', 'address']);
            $contactDetails = collect([
                filled($studio?->phone) ? "WhatsApp studio: {$studio->phone}" : null,
                filled($studio?->operational_hours) ? "Jam operasional: {$studio->operational_hours}" : null,
                filled($studio?->address) ? "Alamat: {$studio->address}" : null,
            ])->filter()->implode("\n");

            return response()->json([
                'reply' => $contactDetails !== ''
                    ? "Ini info studio yang tersedia:\n{$contactDetails}"
                    : 'Info kontak studio belum tersedia di website. Kamu bisa cek bagian Kontak di halaman ini, ya.',
            ]);
        }

        if ($this->isBookingQuestion($message)) {
            return response()->json([
                'reply' => 'Untuk reservasi, pilih paket yang kamu suka, lalu isi tanggal, waktu, dan detail sesi di halaman Reservasi. Setelah terkirim, simpan token yang diberikan karena token itu bisa dipakai untuk cek jadwal dan status reservasi.',
            ]);
        }

        if (preg_match('/\b(halo|hai|hi|pagi|siang|sore|malam)\b/u', $message) === 1) {
            return response()->json([
                'reply' => 'Hai juga! Aku Lifi, teman kecilmu di Liquif Picture. Mau tanya paket, cara reservasi, jadwal, atau cek status dengan token?',
            ]);
        }

        return response()->json([
            'reply' => 'Aku siap bantu soal paket foto, harga, cara reservasi, info studio, dan jadwal. Untuk cek reservasi sendiri, kirim token reservasinya, ya.',
        ]);
    }

    private function reservationReply(Payment $payment): string
    {
        $reservation = $payment->reservation;

        if ($reservation === null) {
            return 'Data reservasi untuk token ini belum tersedia. Silakan hubungi admin studio agar bisa dibantu, ya.';
        }

        $packageName = $reservation->package?->name ?? 'Paket foto';
        $date = Carbon::parse($reservation->date)->format('d-m-Y');
        $time = substr($reservation->time, 0, 5);
        $remaining = max(0, (float) $payment->tagihan - (float) $payment->total_paid);
        $reply = "Aku menemukan reservasi paket {$packageName} pada {$date}, pukul {$time} ({$reservation->duration}).\n".
            "Status pembayaran: {$payment->status}.\n".
            'Sisa tagihan: Rp '.number_format($remaining, 0, ',', '.').'.';

        if (filled($reservation->photo_status)) {
            $reply .= "\nStatus foto: {$reservation->photo_status}.";
        }

        if (filled($reservation->photo_delivery_url)) {
            $reply .= "\nHasil foto sudah dibagikan. Kamu bisa membukanya melalui halaman Cek Reservasi.";
        }

        return $reply;
    }

    private function isScheduleQuestion(string $message): bool
    {
        return preg_match('/\b(jadwal|tanggal|tersedia|kosong)\b/u', $message) === 1;
    }

    private function isPackageQuestion(string $message): bool
    {
        return preg_match('/\b(paket|harga|biaya|tarif|price)\b/u', $message) === 1;
    }

    private function isContactQuestion(string $message): bool
    {
        return preg_match('/\b(kontak|alamat|lokasi|whatsapp|wa|telepon|nomor|operasional|buka)\b/u', $message) === 1;
    }

    private function isBookingQuestion(string $message): bool
    {
        return preg_match('/\b(reservasi|booking|pesan|cara daftar|cara memesan)\b/u', $message) === 1;
    }

    private function dateFromMessage(string $message): ?string
    {
        if (preg_match('/\b(\d{4})-(\d{1,2})-(\d{1,2})\b/', $message, $matches) === 1) {
            [$year, $month, $day] = [(int) $matches[1], (int) $matches[2], (int) $matches[3]];
        } elseif (preg_match('/\b(\d{1,2})[\/.-](\d{1,2})[\/.-](\d{4})\b/', $message, $matches) === 1) {
            [$day, $month, $year] = [(int) $matches[1], (int) $matches[2], (int) $matches[3]];
        } else {
            return null;
        }

        if (! checkdate($month, $day, $year)) {
            return null;
        }

        return Carbon::createFromDate($year, $month, $day)->toDateString();
    }
}
