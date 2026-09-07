@extends('layouts.admin')

@section('title', 'Manajemen Meja Restoran - BiteRush Admin')

@section('admin_content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Manajemen Meja Restoran</h1>
            <p class="text-xs text-gray-500 mt-1">Pantau ketersediaan meja, status terisi (occupied), dan kode QR pemesanan pelanggan.</p>
        </div>
    </div>

    <!-- Tables Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5">
        @foreach($tables as $table)
            <div class="bg-white rounded-3xl p-5 border-2 {{ $table->status === 'occupied' ? 'border-red-400 bg-red-50/20' : ($table->status === 'reserved' ? 'border-yellow-400 bg-yellow-50/20' : 'border-green-400 bg-green-50/20') }} shadow-xs flex flex-col justify-between space-y-4">
                
                <div>
                    <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                        <h3 class="font-black text-base text-gray-900">{{ $table->table_number }}</h3>
                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full {{ $table->status === 'occupied' ? 'bg-red-100 text-red-800' : ($table->status === 'reserved' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                            {{ $table->status }}
                        </span>
                    </div>

                    <div class="py-3 text-xs text-gray-600 space-y-1">
                        <div>Kapasitas: <strong>{{ $table->capacity }} Orang</strong></div>
                        <div>Pesanan Aktif: <strong>{{ $table->orders_count }} Transaksi</strong></div>
                    </div>

                    <!-- QR Link -->
                    <div class="bg-gray-50 p-2 rounded-xl text-center">
                        <a href="{{ route('menu', ['table' => $table->table_number]) }}" target="_blank" class="text-[11px] font-bold text-blue-600 hover:underline">
                            Buka Link Menu QR
                        </a>
                    </div>
                </div>

                <!-- Update Status Form -->
                <form action="{{ route('admin.tables.status', $table->id) }}" method="POST" class="pt-2 border-t border-gray-100">
                    @csrf
                    <div class="flex items-center gap-1 text-[11px]">
                        <select name="status" class="w-full bg-gray-100 border-none rounded-lg p-1.5 font-bold text-gray-800 focus:ring-1 focus:ring-bites-orange">
                            <option value="available" {{ $table->status === 'available' ? 'selected' : '' }}>Available (Kosong)</option>
                            <option value="occupied" {{ $table->status === 'occupied' ? 'selected' : '' }}>Occupied (Terisi)</option>
                            <option value="reserved" {{ $table->status === 'reserved' ? 'selected' : '' }}>Reserved (Dipesan)</option>
                        </select>
                        <button type="submit" class="bg-gray-900 hover:bg-black text-white font-bold px-2.5 py-1.5 rounded-lg transition">
                            Ubah
                        </button>
                    </div>
                </form>

            </div>
        @endforeach
    </div>

</div>
@endsection
