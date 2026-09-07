@extends('layouts.admin')

@section('title', 'Manajemen Kupon & Promo - BiteRush Admin')

@section('admin_content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Voucher & Kupon Promo</h1>
            <p class="text-xs text-gray-500 mt-1">Buat kode voucher diskon baru dan pantau penggunaan promo pelanggan.</p>
        </div>
        <button onclick="document.getElementById('add-coupon-modal').classList.remove('hidden')" class="bg-bites-yellow hover:bg-bites-yellow-dark text-bites-dark font-extrabold text-xs px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-1.5 self-start">
            <span>+ Buat Voucher Baru</span>
        </button>
    </div>

    <!-- Coupons Table -->
    <div class="bg-white rounded-3xl border border-gray-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 text-gray-500 font-bold border-b border-gray-200 uppercase">
                    <tr>
                        <th class="p-4">Kode Kupon</th>
                        <th class="p-4">Tipe & Nilai Diskon</th>
                        <th class="p-4">Min. Belanja & Max Diskon</th>
                        <th class="p-4">Penggunaan</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($coupons as $coup)
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="p-4">
                                <span class="font-mono font-black text-sm bg-yellow-100 text-yellow-900 px-3 py-1 rounded-xl border border-yellow-200">
                                    {{ $coup->code }}
                                </span>
                            </td>

                            <td class="p-4 font-bold text-gray-900">
                                @if($coup->discount_type === 'percentage')
                                    Diskon {{ intval($coup->discount_value) }}%
                                @else
                                    Potongan Rp {{ number_format($coup->discount_value, 0, ',', '.') }}
                                @endif
                            </td>

                            <td class="p-4 text-gray-600">
                                <div>Min. Belanja: <strong>Rp {{ number_format($coup->min_spend, 0, ',', '.') }}</strong></div>
                                <div>Max. Potongan: <strong>{{ $coup->max_discount ? 'Rp ' . number_format($coup->max_discount, 0, ',', '.') : 'Tanpa Batas' }}</strong></div>
                            </td>

                            <td class="p-4">
                                <span class="font-black text-gray-900">{{ $coup->usage_count }}</span>
                                <span class="text-gray-400">/ {{ $coup->usage_limit ?: 'tak terbatas' }} kali</span>
                            </td>

                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase {{ $coup->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $coup->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <td class="p-4 text-right">
                                <form action="{{ route('admin.coupons.toggle', $coup->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-gray-100 hover:bg-gray-200 font-bold px-3 py-1.5 rounded-lg transition text-xs text-gray-800">
                                        {{ $coup->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ADD COUPON MODAL -->
<div id="add-coupon-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs hidden">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h3 class="font-black text-base text-gray-900">Buat Kupon Promo Baru</h3>
            <button onclick="document.getElementById('add-coupon-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700 text-lg font-bold">&times;</button>
        </div>

        <form action="{{ route('admin.coupons.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf

            <div>
                <label class="block font-bold text-gray-700 mb-1">Kode Voucher:</label>
                <input type="text" name="code" required placeholder="Contoh: RUSHDEAL20" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 font-mono uppercase font-bold focus:outline-none focus:border-bites-orange">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Tipe Diskon:</label>
                    <select name="discount_type" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 font-bold focus:outline-none">
                        <option value="fixed">Nominal Tetap (Rp)</option>
                        <option value="percentage">Persentase (%)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Nilai Diskon:</label>
                    <input type="number" name="discount_value" required placeholder="15000 / 20" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 font-bold focus:outline-none focus:border-bites-orange">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Min. Belanja (Rp):</label>
                    <input type="number" name="min_spend" value="0" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Maks. Potongan (Rp):</label>
                    <input type="number" name="max_discount" placeholder="Opsional" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Kuota Pemakaian:</label>
                <input type="number" name="usage_limit" placeholder="Contoh: 100" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 focus:outline-none">
            </div>

            <div class="pt-3">
                <button type="submit" class="w-full bg-bites-yellow hover:bg-bites-yellow-dark text-bites-dark font-black py-3 rounded-xl transition text-sm">
                    Simpan Kupon
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
