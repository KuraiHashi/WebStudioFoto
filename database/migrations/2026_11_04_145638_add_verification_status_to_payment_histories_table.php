<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_histories', function (Blueprint $table): void {
            $table->string('status')->default('diterima');
        });

        $pendingPaymentStatuses = ['Konfirmasi pembayaran', 'Verifikasi pelunasan'];
        $pendingPayments = DB::table('payments')
            ->whereIn('status', $pendingPaymentStatuses)
            ->get(['id', 'total_paid']);

        foreach ($pendingPayments as $payment) {
            $latestHistory = DB::table('payment_histories')
                ->where('payment_id', $payment->id)
                ->latest('id')
                ->first(['id', 'amount']);

            if ($latestHistory === null) {
                continue;
            }

            DB::table('payment_histories')
                ->where('id', $latestHistory->id)
                ->update(['status' => 'menunggu_verifikasi']);

            DB::table('payments')
                ->where('id', $payment->id)
                ->update([
                    'total_paid' => max(0, (float) $payment->total_paid - (float) $latestHistory->amount),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $pendingAmounts = DB::table('payment_histories')
            ->where('status', 'menunggu_verifikasi')
            ->select('payment_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('payment_id')
            ->get();

        foreach ($pendingAmounts as $pendingAmount) {
            DB::table('payments')
                ->where('id', $pendingAmount->payment_id)
                ->increment('total_paid', $pendingAmount->total_amount);
        }

        Schema::table('payment_histories', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
