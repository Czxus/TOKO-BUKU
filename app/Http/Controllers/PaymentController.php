<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Modules\Ordering\Services\CartService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function show(Payment $payment)
    {
        $payment->load('order');
        return view('payments.show', compact('payment'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:qris,bank_transfer',
        ]);

        $user = Auth::user();
        $cartService = app(CartService::class);
        $cartView = $cartService->view($user);

        if ($cartView->isEmpty() || $cartView->hasBlockingIssues) {
            return redirect()->route('keranjang')->with('error', 'Keranjang kosong atau memiliki item bermasalah.');
        }

        $now = Carbon::now();
        $batasPembayaran = $now->copy()->addMinutes(30);
        
        // 1. Buat record Order sesuai kolom yang ada di database Anda
        $order = Order::create([
            'user_id' => $user->id,
            'nomor_nota' => 'NOTA-' . strtoupper(uniqid()),
            'checkout_key' => bin2hex(random_bytes(16)),
            'biaya_layanan' => $cartView->serviceFeeAmount ?? 0,
            'total_tagihan' => $cartView->grandTotal(),
            'status_pembayaran' => 'menunggu',
            'batas_pembayaran' => $batasPembayaran,
        ]);

        // 2. Petakan pilihan metode pembayaran form ('qris' / 'bank_transfer') 
        // ke nilai ENUM di database payments ('qris' atau 'cash')
        $dbPaymentMethod = ($request->payment_method === 'qris') ? 'qris' : 'cash';

        // 3. Buat record Payment sesuai kolom wajib di database Anda
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => $dbPaymentMethod,
            'jumlah_bayar' => $cartView->grandTotal(),
            'idempotency_key' => bin2hex(random_bytes(16)),
            'status' => 'pending',
            'expires_at' => $batasPembayaran,
        ]);

        // Kosongkan keranjang setelah pesanan dibuat
        $cartService->clear($user);

        // Arahkan ke halaman instruksi pembayaran
        return redirect()->route('payment.show', $payment->id);
    }
}