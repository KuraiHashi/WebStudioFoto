<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TestimonialController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:14'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'message' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'token.required' => 'Kode reservasi wajib diisi.',
            'token.string' => 'Kode reservasi tidak valid.',
            'token.max' => 'Kode reservasi tidak valid.',
            'rating.required' => 'Silakan pilih rating.',
            'rating.integer' => 'Rating harus berupa angka.',
            'rating.between' => 'Rating harus antara 1 sampai 5 bintang.',
            'message.required' => 'Silakan tulis testimoni Anda.',
            'message.string' => 'Testimoni harus berupa teks.',
            'message.min' => 'Testimoni minimal 10 karakter.',
            'message.max' => 'Testimoni maksimal 1.000 karakter.',
        ]);

        $payment = Payment::with('reservation.testimonial')
            ->where('token', strtoupper(trim($validated['token'])))
            ->firstOrFail();

        if (! $payment->canSubmitTestimonial()) {
            throw ValidationException::withMessages([
                'token' => 'Testimoni hanya dapat dikirim untuk reservasi berstatus Selesai.',
            ]);
        }

        $reservation = $payment->reservation;

        if ($reservation->testimonial) {
            throw ValidationException::withMessages([
                'testimonial' => 'Anda sudah mengirim testimoni untuk reservasi ini.',
            ]);
        }

        $reservation->testimonial()->create([
            'rating' => (int) $validated['rating'],
            'message' => $validated['message'],
        ]);

        return redirect()
            ->route('reservasi.result', ['token' => $payment->token])
            ->with('testimonial_success', 'Terima kasih, testimoni Anda berhasil dikirim.');
    }
}
