@extends('layouts.admin')

@section('title', 'Kitchen & Orders Management - BiteRush Admin')

@section('admin_content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Kitchen & Order Board</h1>
            <p class="text-xs text-gray-500 mt-1">Kelola dan update proses memasak pesanan dapur secara langsung.</p>
        </div>
        <button onclick="window.location.reload()" class="bg-white hover:bg-gray-100 border border-gray-300 text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-1.5 self-start">
            Refresh Antrean
        </button>
    </div>

    <!-- STATUS TABS -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 text-xs font-bold">
        <a href="{{ route('admin.orders', ['status' => 'all']) }}" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'all' ? 'bg-gray-900 text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
            Semua Antrean <span class="bg-white/20 px-1.5 py-0.2 rounded-full text-[10px]">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ route('admin.orders', ['status' => 'pending']) }}" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'pending' ? 'bg-yellow-500 text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-yellow-50 border border-gray-200' }}">
            Menunggu <span class="bg-yellow-100 text-yellow-900 px-1.5 py-0.2 rounded-full text-[10px]">{{ $counts['pending'] }}</span>
        </a>
        <a href="{{ route('admin.orders', ['status' => 'confirmed']) }}" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'confirmed' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-blue-50 border border-gray-200' }}">
            Dikonfirmasi <span class="bg-blue-100 text-blue-900 px-1.5 py-0.2 rounded-full text-[10px]">{{ $counts['confirmed'] }}</span>
        </a>
        <a href="{{ route('admin.orders', ['status' => 'preparing']) }}" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'preparing' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-amber-50 border border-gray-200' }}">
            Sedang Dimasak <span class="bg-amber-100 text-amber-900 px-1.5 py-0.2 rounded-full text-[10px]">{{ $counts['preparing'] }}</span>
        </a>
        <a href="{{ route('admin.orders', ['status' => 'ready']) }}" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'ready' ? 'bg-green-600 text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-green-50 border border-gray-200' }}">
            Siap Saji <span class="bg-green-100 text-green-900 px-1.5 py-0.2 rounded-full text-[10px]">{{ $counts['ready'] }}</span>
        </a>
        <a href="{{ route('admin.orders', ['status' => 'completed']) }}" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'completed' ? 'bg-emerald-700 text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-emerald-50 border border-gray-200' }}">
            Selesai <span class="bg-gray-100 text-gray-700 px-1.5 py-0.2 rounded-full text-[10px]">{{ $counts['completed'] }}</span>
        </a>
    </div>

    <!-- ORDERS GRID -->
    @if($orders->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-gray-200 shadow-xs">
            <div class="text-sm font-bold text-gray-400 mb-1">[Kosong]</div>
            <h3 class="text-sm font-bold text-gray-800">Tidak ada pesanan dalam status ini</h3>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($orders as $order)
                <div class="bg-white rounded-3xl border-2 {{ in_array($order->status, ['pending', 'confirmed', 'preparing']) ? 'border-amber-300' : 'border-gray-200' }} shadow-sm p-5 flex flex-col justify-between space-y-4">
                    
                    <!-- Header -->
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div>
                                <span class="font-mono font-black text-xs text-gray-900 bg-gray-100 px-2 py-0.5 rounded">
                                    {{ $order->order_number }}
                                </span>
                                <div class="text-[11px] text-gray-400 mt-0.5">{{ $order->created_at->diffForHumans() }}</div>
                            </div>

                            <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : ($order->status === 'preparing' ? 'bg-amber-100 text-amber-800' : ($order->status === 'ready' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                {{ $order->status }}
                            </span>
                        </div>

                        <!-- Customer & Table -->
                        <div class="py-2.5 text-xs text-gray-700 flex items-center justify-between">
                            <div>
                                <strong class="text-gray-900">{{ $order->customer_name }}</strong>
                                <span class="text-gray-400 block text-[11px]">{{ $order->customer_phone }}</span>
                            </div>
                            <span class="font-black text-xs uppercase bg-amber-50 text-amber-800 px-2.5 py-1 rounded-lg border border-amber-200">
                                {{ str_replace('_', ' ', $order->order_type) }}
                                @if($order->table) &bull; {{ $order->table->table_number }} @endif
                            </span>
                        </div>

                        <!-- Items List -->
                        <div class="bg-gray-50 rounded-2xl p-3 space-y-2 text-xs">
                            @foreach($order->items as $item)
                                <div class="flex items-start justify-between">
                                    <div>
                                        <span class="font-bold text-gray-900">{{ $item->quantity }}x {{ $item->product_name }}</span>
                                        @if($item->options->isNotEmpty())
                                            <div class="text-[10px] text-gray-500">
                                                {{ $item->options->pluck('option_value_name')->implode(', ') }}
                                            </div>
                                        @endif
                                        @if($item->notes)
                                            <div class="text-[10px] text-red-600 font-medium italic">[Catatan] "{{ $item->notes }}"</div>
                                        @endif
                                    </div>
                                    <span class="font-semibold text-gray-700">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions & Status Update Buttons -->
                    <div class="pt-3 border-t border-gray-100 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500 font-semibold">Total Tagihan:</span>
                            <span class="font-black text-sm text-bites-red">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>

                        <!-- 1-Click Status Advance Form -->
                        <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="grid grid-cols-2 gap-2 text-xs">
                            @csrf
                            
                            @if($order->status === 'pending')
                                <button type="submit" name="status" value="confirmed" class="col-span-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-xl transition">
                                    Konfirmasi Pesanan
                                </button>
                            @elseif($order->status === 'confirmed')
                                <button type="submit" name="status" value="preparing" class="col-span-2 bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 rounded-xl transition">
                                    Mulai Memasak
                                </button>
                            @elseif($order->status === 'preparing')
                                <button type="submit" name="status" value="ready" class="col-span-2 bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded-xl transition">
                                    Pesanan Siap Saji
                                </button>
                            @elseif($order->status === 'ready')
                                <button type="submit" name="status" value="completed" class="col-span-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold py-2 rounded-xl transition">
                                    Selesaikan Pesanan
                                </button>
                            @endif

                            @if($order->payment_status !== 'paid')
                                <input type="hidden" name="payment_status" value="paid">
                                <button type="submit" name="status" value="{{ $order->status }}" class="col-span-2 bg-green-50 text-green-800 border border-green-200 font-bold py-1.5 rounded-lg text-[11px] hover:bg-green-100">
                                    Tandai Lunas (Kasir)
                                </button>
                            @endif
                        </form>

                        <div class="flex justify-between items-center text-[11px] pt-1">
                            <a href="{{ route('orders.show', $order->order_number) }}" target="_blank" class="text-blue-600 hover:underline font-bold">
                                Buka Struk / Invoice &rarr;
                            </a>
                            
                            @if(!in_array($order->status, ['completed', 'cancelled']))
                                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" onsubmit="return confirm('Batalkan pesanan ini?')">
                                    @csrf
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="text-red-500 hover:underline font-bold">
                                        Batalkan
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <div class="pt-4">
            {{ $orders->links() }}
        </div>
    @endif

</div>
@endsection
