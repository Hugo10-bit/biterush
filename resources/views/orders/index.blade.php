@extends('layouts.app')

@section('title', 'Riwayat Pesanan Saya - BiteRush')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Riwayat Pesanan</h1>
            <p class="text-xs text-gray-500">Daftar semua transaksi dan pesanan yang pernah Anda buat di BiteRush.</p>
        </div>
        <a href="{{ route('menu') }}" class="bg-bites-yellow hover:bg-bites-yellow-dark text-bites-dark font-extrabold text-xs px-4 py-2.5 rounded-xl shadow-xs transition">
            + Pesan Baru
        </a>
    </div>

    @if($orders->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-gray-200 shadow-xs">
            <div class="text-sm font-bold text-gray-400 mb-1">[Belum Ada Data]</div>
            <h3 class="text-base font-bold text-gray-900">Belum ada riwayat pesanan</h3>
            <p class="text-xs text-gray-500 mt-1">Anda belum pernah melakukan pemesanan burger.</p>
            <a href="{{ route('menu') }}" class="inline-block mt-4 bg-bites-yellow text-bites-dark font-bold text-xs px-5 py-2.5 rounded-xl shadow-xs">
                Mulai Pesan Sekarang
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="bg-white rounded-3xl p-5 sm:p-6 border border-gray-200/80 shadow-xs hover:shadow-md transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-black text-xs text-gray-900 bg-gray-100 px-2.5 py-1 rounded-md">
                                {{ $order->order_number }}
                            </span>
                            <span class="text-xs text-gray-400">• {{ $order->created_at->format('d M Y, H:i') }}</span>
                            <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : ($order->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ $order->status }}
                            </span>
                        </div>

                        <div class="text-xs text-gray-700">
                            <strong>{{ $order->items->count() }} Menu:</strong>
                            <span class="text-gray-500">
                                {{ $order->items->pluck('product_name')->implode(', ') }}
                            </span>
                        </div>

                        <div class="text-xs font-bold text-gray-500">
                            Tipe: <span class="uppercase text-gray-900">{{ str_replace('_', ' ', $order->order_type) }}</span>
                            @if($order->table)
                                ({{ $order->table->table_number }})
                            @endif
                            • Pembayaran: <span class="uppercase {{ $order->payment_status === 'paid' ? 'text-green-600' : 'text-red-600' }}">{{ $order->payment_status }}</span>
                        </div>
                    </div>

                    <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center border-t sm:border-t-0 pt-3 sm:pt-0 border-gray-100 gap-2">
                        <div class="text-base font-black text-bites-red">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </div>
                        <a href="{{ route('orders.show', $order->order_number) }}" class="bg-gray-900 hover:bg-black text-white font-bold text-xs px-4 py-2 rounded-xl transition shadow-xs">
                            Lihat Detail &rarr;
                        </a>
                    </div>
                </div>
            @endforeach

            <div class="pt-4">
                {{ $orders->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
