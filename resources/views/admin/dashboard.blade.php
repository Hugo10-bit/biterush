@extends('layouts.admin')

@section('title', 'Dashboard Analytics - BiteRush Admin')

@section('admin_content')
<div class="max-w-7xl mx-auto space-y-8">
    
    <!-- Top Welcome -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Overview Dashboard</h1>
            <p class="text-xs text-gray-500 mt-1">Pantau performa penjualan, pesanan aktif, dan meja restoran BiteRush hari ini.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.pos') }}" class="bg-bites-yellow hover:bg-bites-yellow-dark text-bites-dark font-extrabold text-xs px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-1.5">
                <span>Buka POS Kasir</span>
            </a>
            <a href="{{ route('admin.orders') }}" class="bg-gray-900 hover:bg-black text-white font-extrabold text-xs px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-1.5">
                <span>Kitchen Board</span>
            </a>
        </div>
    </div>

    <!-- METRICS GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center font-bold text-xs">
                Rp
            </div>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Pendapatan Hari Ini</span>
                <span class="text-xl font-black text-gray-900">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs">
                TRX
            </div>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Pesanan Hari Ini</span>
                <span class="text-xl font-black text-gray-900">{{ $totalOrdersToday }} Transaksi</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-700 flex items-center justify-center font-bold text-xs">
                PROS
            </div>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Pesanan Dalam Proses</span>
                <span class="text-xl font-black text-red-600">{{ $pendingOrders }} Antrean</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs">
                MEJA
            </div>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Okupansi Meja</span>
                <span class="text-xl font-black text-purple-900">{{ $occupiedTables }} / {{ $totalTables }} Terisi</span>
            </div>
        </div>

    </div>

    <!-- RECENT ORDERS & TOP PRODUCTS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Recent Orders Table (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 border border-gray-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="font-black text-sm text-gray-900 uppercase tracking-wider">Pesanan Terbaru</h3>
                <a href="{{ route('admin.orders') }}" class="text-xs font-bold text-bites-red hover:underline">Lihat Semua &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-gray-400 font-bold border-b border-gray-100">
                            <th class="pb-3">No. Order</th>
                            <th class="pb-3">Pemesan</th>
                            <th class="pb-3">Tipe / Meja</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3">Total</th>
                            <th class="pb-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentOrders as $ro)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="py-3 font-mono font-bold text-gray-900">{{ $ro->order_number }}</td>
                                <td class="py-3 font-semibold text-gray-700">{{ $ro->customer_name }}</td>
                                <td class="py-3 uppercase font-bold text-gray-500">
                                    {{ str_replace('_', ' ', $ro->order_type) }}
                                    @if($ro->table) <span class="text-gray-900">({{ $ro->table->table_number }})</span> @endif
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase {{ $ro->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $ro->status }}
                                    </span>
                                </td>
                                <td class="py-3 font-black text-gray-900">Rp {{ number_format($ro->total_amount, 0, ',', '.') }}</td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('orders.show', $ro->order_number) }}" class="bg-gray-100 hover:bg-gray-200 font-bold px-2.5 py-1 rounded-lg text-gray-800">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Selling Products (4 cols) -->
        <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-gray-200 shadow-xs space-y-4">
            <h3 class="font-black text-sm text-gray-900 uppercase tracking-wider pb-3 border-b border-gray-100">
                Menu Terlaris
            </h3>

            <div class="space-y-3">
                @foreach($topProducts as $idx => $tp)
                    <div class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 text-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-bites-yellow text-bites-dark flex items-center justify-center font-black text-[11px]">
                                {{ $idx + 1 }}
                            </span>
                            <div>
                                <h4 class="font-bold text-gray-900">{{ $tp->product_name }}</h4>
                                <span class="text-gray-400 font-semibold">{{ $tp->total_qty }} porsi terjual</span>
                            </div>
                        </div>
                        <div class="font-black text-gray-800 text-right">
                            Rp {{ number_format($tp->total_sales, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
@endsection
