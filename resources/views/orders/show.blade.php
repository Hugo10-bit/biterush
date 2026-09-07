@extends('layouts.app')

@section('title', 'Detail Pesanan #' . $order->order_number . ' - BiteRush')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="bg-gray-900 text-white font-mono text-xs font-bold px-3 py-1 rounded-lg">
                    {{ $order->order_number }}
                </span>
                <span class="text-xs text-gray-500">{{ $order->created_at->format('d M Y, H:i') }} WIB</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 mt-1">Status & Bukti Pesanan</h1>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="bg-white hover:bg-gray-100 text-gray-700 border border-gray-300 font-bold text-xs px-4 py-2.5 rounded-xl transition shadow-xs flex items-center gap-1.5">
                Cetak Struk
            </button>
            <a href="{{ route('menu') }}" class="bg-bites-yellow hover:bg-bites-yellow-dark text-bites-dark font-extrabold text-xs px-4 py-2.5 rounded-xl transition shadow-xs">
                + Pesan Lagi
            </a>
        </div>
    </div>

    <!-- LIVE KITCHEN STATUS STEPPER -->
    @php
        $statuses = ['pending' => 'Menunggu', 'confirmed' => 'Dikonfirmasi', 'preparing' => 'Dimasak', 'ready' => 'Siap Saji', 'completed' => 'Selesai'];
        $statusKeys = array_keys($statuses);
        $currentIndex = array_search($order->status, $statusKeys);
        if ($currentIndex === false && $order->status === 'cancelled') $currentIndex = -1;
    @endphp

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-xs mb-8">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
            <div>
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Status Dapur</span>
                <div class="text-lg font-black text-gray-900 flex items-center gap-2">
                    @if($order->status === 'pending')
                        <span class="w-3 h-3 rounded-full bg-yellow-400 animate-ping"></span> Menunggu Konfirmasi Dapur
                    @elseif($order->status === 'confirmed')
                        <span class="w-3 h-3 rounded-full bg-blue-500 animate-pulse"></span> Pesanan Telah Dikonfirmasi
                    @elseif($order->status === 'preparing')
                        <span class="w-3 h-3 rounded-full bg-amber-500 animate-pulse"></span> Koki Sedang Memasak Pesanan Anda
                    @elseif($order->status === 'ready')
                        <span class="w-3 h-3 rounded-full bg-green-500"></span> Pesanan Siap Diambil / Diantar
                    @elseif($order->status === 'completed')
                        <span class="w-3 h-3 rounded-full bg-green-600"></span> Pesanan Selesai
                    @elseif($order->status === 'cancelled')
                        <span class="w-3 h-3 rounded-full bg-red-600"></span> Pesanan Dibatalkan
                    @endif
                </div>
            </div>

            <!-- Payment Status Pill -->
            <div class="text-right">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Status Pembayaran</span>
                @if($order->payment_status === 'paid')
                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 font-extrabold text-xs px-3 py-1 rounded-full">
                        LUNAS ({{ strtoupper($order->payment_method) }})
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 bg-red-100 text-red-800 font-extrabold text-xs px-3 py-1 rounded-full">
                        BELUM DIBAYAR
                    </span>
                @endif
            </div>
        </div>

        <!-- Progress Bar Stepper -->
        <div class="relative flex items-center justify-between">
            <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1.5 bg-gray-200 w-full z-0 rounded-full"></div>
            <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1.5 bg-bites-yellow z-0 rounded-full transition-all duration-500" 
                 style="width: {{ $currentIndex >= 0 ? ($currentIndex / (count($statusKeys) - 1)) * 100 : 0 }}%;"></div>

            @foreach($statuses as $key => $label)
                @php
                    $stepIdx = array_search($key, $statusKeys);
                    $isDone = $currentIndex >= $stepIdx;
                    $isCurrent = $currentIndex === $stepIdx;
                @endphp
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-full flex items-center justify-center font-black text-xs transition {{ $isDone ? 'bg-bites-yellow text-bites-dark shadow-md ring-4 ring-yellow-100' : 'bg-gray-200 text-gray-500' }}">
                        {{ $stepIdx + 1 }}
                    </div>
                    <span class="text-[10px] sm:text-xs font-bold mt-2 text-center {{ $isCurrent ? 'text-bites-red font-black' : ($isDone ? 'text-gray-900' : 'text-gray-400') }}">
                        {{ $label }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- PAYMENT PROMPT IF UNPAID (QRIS / CASH) -->
    @if($order->payment_status !== 'paid')
        <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-3xl p-6 text-white shadow-lg mb-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="space-y-2">
                <span class="bg-black/20 text-yellow-200 text-[10px] font-extrabold px-2.5 py-1 rounded-full uppercase">
                    Pembayaran Diperlukan
                </span>
                <h3 class="text-xl font-black">Total Tagihan: Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h3>
                <p class="text-xs text-white/90">
                    Metode: <strong class="uppercase text-yellow-200">{{ $order->payment_method }}</strong>. Selesaikan pembayaran untuk memproses pesanan Anda.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3">
                @if($order->payment_method === 'qris')
                    <button onclick="document.getElementById('qris-modal').classList.remove('hidden')" class="bg-black text-white hover:bg-gray-900 font-bold text-xs px-5 py-3 rounded-xl transition shadow-md whitespace-nowrap">
                        Tampilkan Kode QRIS
                    </button>
                @endif

                <form action="{{ route('orders.pay', $order->order_number) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-white text-gray-900 hover:bg-yellow-100 font-black text-xs px-6 py-3 rounded-xl transition shadow-md whitespace-nowrap">
                        Bayar Sekarang (Simulasi Lunas)
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- INVOICE RECEIPT DETAILS -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-xs space-y-6">
        
        <!-- Header Info -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pb-6 border-b border-gray-100 text-xs">
            <div>
                <span class="text-gray-400 font-medium block">Tipe Pesanan</span>
                <span class="font-extrabold text-gray-900 uppercase">
                    {{ str_replace('_', ' ', $order->order_type) }}
                    @if($order->table)
                        ({{ $order->table->table_number }})
                    @endif
                </span>
            </div>
            <div>
                <span class="text-gray-400 font-medium block">Nama Pemesan</span>
                <span class="font-extrabold text-gray-900">{{ $order->customer_name }}</span>
            </div>
            <div>
                <span class="text-gray-400 font-medium block">No. Telepon</span>
                <span class="font-extrabold text-gray-900">{{ $order->customer_phone }}</span>
            </div>
            <div>
                <span class="text-gray-400 font-medium block">Metode Pembayaran</span>
                <span class="font-extrabold text-gray-900 uppercase">{{ $order->payment_method }}</span>
            </div>
        </div>

        @if($order->delivery_address)
            <div class="bg-gray-50 p-3 rounded-xl text-xs">
                <span class="font-bold text-gray-600 block">Alamat Pengiriman:</span>
                <p class="text-gray-800 mt-0.5">{{ $order->delivery_address }}</p>
            </div>
        @endif

        <!-- Items Table -->
        <div>
            <h4 class="font-black text-xs text-gray-400 uppercase tracking-wider mb-3">Rincian Menu yang Dipesan</h4>
            <div class="space-y-3">
                @foreach($order->items as $item)
                    <div class="flex items-start justify-between gap-4 p-3.5 rounded-2xl bg-gray-50/70 border border-gray-100 text-xs">
                        <div class="flex-1">
                            <h5 class="font-bold text-gray-900 text-sm">{{ $item->product_name }}</h5>
                            <span class="text-gray-500 font-semibold">{{ $item->quantity }}x @ Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                            
                            @if($item->options->isNotEmpty())
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach($item->options as $opt)
                                        <span class="bg-white px-2 py-0.5 rounded border border-gray-200 text-[10px] text-gray-600">
                                            + {{ $opt->option_value_name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            @if($item->notes)
                                <div class="text-[11px] text-amber-700 italic mt-1">Catatan: "{{ $item->notes }}"</div>
                            @endif

                            <!-- Review Button if completed and user logged in -->
                            @if($order->status === 'completed' && $item->product_id)
                                <div class="mt-2">
                                    <button onclick="openReviewModal({{ $item->product_id }}, '{{ addslashes($item->product_name) }}')" class="text-[11px] font-bold text-bites-red hover:underline inline-flex items-center gap-1">
                                        Beri Ulasan Menu Ini
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="font-black text-gray-900 text-sm whitespace-nowrap">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Total Calculation -->
        <div class="pt-4 border-t border-gray-100 space-y-2 text-xs text-gray-600">
            <div class="flex justify-between">
                <span>Subtotal Menu:</span>
                <span class="font-semibold text-gray-900">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($order->discount_amount > 0)
                <div class="flex justify-between text-green-600 font-bold">
                    <span>Diskon Promo / Kupon:</span>
                    <span>- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex justify-between">
                <span>Pajak Restoran PB1 (10%):</span>
                <span class="font-semibold text-gray-900">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-base font-black text-gray-900 pt-3 border-t border-gray-200">
                <span>Total Pembayaran:</span>
                <span class="text-bites-red font-black text-xl">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

    </div>

</div>

<!-- QRIS PAYMENT MODAL -->
<div id="qris-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-xs hidden">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 text-center shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
            <div class="text-left">
                <span class="font-black text-sm text-gray-900 block">Pembayaran QRIS</span>
                <span class="text-[11px] text-gray-500 font-medium">Merchant: Hugo (NMID: ID1026551937497)</span>
            </div>
            <button onclick="document.getElementById('qris-modal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center font-bold text-lg transition">&times;</button>
        </div>
        
        <!-- 5 MINUTE COUNTDOWN TIMER BADGE -->
        <div class="inline-flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 px-4 py-1.5 rounded-full text-xs font-bold shadow-xs">
            <svg class="w-4 h-4 text-red-600 animate-spin" style="animation-duration: 3s;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke-width="2" stroke-dasharray="32" stroke-dashoffset="10"></circle>
            </svg>
            <span>Sisa Waktu Pembayaran: <strong id="qris-timer-display" class="font-mono text-red-800 text-sm">05:00</strong></span>
        </div>

        <!-- QRIS IMAGE BOX -->
        <div class="relative bg-white p-2 rounded-2xl border-2 border-gray-200 inline-block shadow-sm w-full max-w-xs mx-auto">
            <img id="qris-barcode-img" src="{{ asset('images/qris.png') }}" alt="QRIS Hugo" class="w-full h-auto max-h-[380px] mx-auto object-contain rounded-xl">
            <div id="qris-expired-overlay" class="absolute inset-0 bg-white/90 backdrop-blur-xs rounded-2xl flex-col items-center justify-center p-4 hidden">
                <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-bold text-xl mb-2">!</div>
                <h4 class="font-bold text-sm text-gray-900 mb-1">Waktu Pembayaran Habis</h4>
                <p class="text-xs text-gray-500 mb-3">Sesi pembayaran QRIS 5 menit telah berakhir.</p>
                <button type="button" onclick="restartQrisTimer()" class="bg-gray-900 text-white font-bold text-xs px-4 py-2 rounded-xl">Muat Ulang Timer</button>
            </div>
        </div>

        <div class="bg-amber-50 rounded-2xl p-3 border border-amber-200/70 text-left space-y-1">
            <div class="flex justify-between items-center text-xs">
                <span class="text-gray-600 font-medium">Total yang harus dibayar:</span>
                <span class="text-bites-red font-black text-base">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-gray-500">
                Buka aplikasi m-Banking (BCA, Mandiri, BRI, BNI) atau E-Wallet (GoPay, OVO, Dana, ShopeePay), lalu scan barcode QRIS di atas.
            </p>
        </div>

        <form action="{{ route('orders.pay', $order->order_number) }}" method="POST">
            @csrf
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold text-xs py-3.5 rounded-xl shadow-md transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Saya Sudah Membayar</span>
            </button>
        </form>
    </div>
</div>

<!-- REVIEW MODAL -->
<div id="review-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs hidden">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-black text-sm text-gray-900">Beri Ulasan Menu</h3>
            <button onclick="document.getElementById('review-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700 text-lg font-bold">&times;</button>
        </div>

        <form action="{{ route('orders.review', $order->order_number) }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="product_id" id="review-product-id">

            <div>
                <h4 id="review-product-name" class="font-bold text-xs text-gray-800 mb-2">Nama Menu</h4>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Rating Penilaian (1 - 5):</label>
                <select name="rating" required class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 text-xs font-bold text-amber-600 focus:outline-none">
                    <option value="5">Bintang 5 (Sangat Enak & Puas)</option>
                    <option value="4">Bintang 4 (Enak & Sesuai)</option>
                    <option value="3">Bintang 3 (Cukup Baik)</option>
                    <option value="2">Bintang 2 (Kurang Sesuai)</option>
                    <option value="1">Bintang 1 (Mengecewakan)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Komentar & Saran:</label>
                <textarea name="comment" rows="3" placeholder="Ceritakan rasa burger atau kemasan BiteRush..." class="w-full bg-gray-50 border border-gray-300 rounded-xl p-3 text-xs focus:outline-none"></textarea>
            </div>

            <button type="submit" class="w-full bg-bites-yellow hover:bg-bites-yellow-dark text-bites-dark font-black text-xs py-3 rounded-xl transition">
                Kirim Ulasan
            </button>
        </form>
    </div>
</div>

<script>
    function openReviewModal(productId, productName) {
        document.getElementById('review-product-id').value = productId;
        document.getElementById('review-product-name').innerText = productName;
        document.getElementById('review-modal').classList.remove('hidden');
    }

    let qrisTimerSeconds = 5 * 60; // 5 menit
    let qrisTimerInterval = null;

    function startQrisCountdown() {
        if (qrisTimerInterval) clearInterval(qrisTimerInterval);
        const overlay = document.getElementById('qris-expired-overlay');
        if (overlay) {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }

        function tick() {
            const min = Math.floor(qrisTimerSeconds / 60);
            const sec = qrisTimerSeconds % 60;
            const display = String(min).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
            const el = document.getElementById('qris-timer-display');
            if (el) el.innerText = display;

            if (qrisTimerSeconds <= 0) {
                clearInterval(qrisTimerInterval);
                if (overlay) {
                    overlay.classList.remove('hidden');
                    overlay.classList.add('flex');
                }
            } else {
                qrisTimerSeconds--;
            }
        }

        tick();
        qrisTimerInterval = setInterval(tick, 1000);
    }

    function restartQrisTimer() {
        qrisTimerSeconds = 5 * 60;
        startQrisCountdown();
    }

    @if($order->payment_method === 'qris' && $order->payment_status !== 'paid')
        window.addEventListener('DOMContentLoaded', () => {
            const qrisModal = document.getElementById('qris-modal');
            if (qrisModal) {
                qrisModal.classList.remove('hidden');
                startQrisCountdown();
            }
        });
    @endif
</script>
@endsection
