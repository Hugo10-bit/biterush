@extends('layouts.admin')

@section('title', 'Manajemen Menu & Produk - BiteRush Admin')

@section('admin_content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Manajemen Menu Makanan</h1>
            <p class="text-xs text-gray-500 mt-1">Tambah menu baru, edit rincian/harga, dan pantau ketersediaan stok.</p>
        </div>
        <button onclick="document.getElementById('add-product-modal').classList.remove('hidden')" class="bg-bites-yellow hover:bg-bites-yellow-dark text-bites-dark font-extrabold text-xs px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-1.5 self-start">
            <span>+ Tambah Menu Baru</span>
        </button>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-3xl border border-gray-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 text-gray-500 font-bold border-b border-gray-200 uppercase">
                    <tr>
                        <th class="p-4">Menu</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4">Harga Jual</th>
                        <th class="p-4">Kalori & Waktu</th>
                        <th class="p-4">Status Stok</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $prod)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="p-4 flex items-center gap-3">
                                <img src="{{ asset($prod->image ?: 'images/burger-bg.jpg') }}" alt="{{ $prod->name }}" class="w-12 h-12 rounded-xl object-cover border border-gray-200 flex-shrink-0">
                                <div>
                                    <div class="font-bold text-gray-900 text-sm flex items-center gap-1.5">
                                        <span>{{ $prod->name }}</span>
                                        @if($prod->is_featured)
                                            <span class="bg-red-100 text-red-700 text-[10px] font-extrabold px-1.5 py-0.2 rounded">Favorit</span>
                                        @endif
                                    </div>
                                    <p class="text-gray-400 line-clamp-1 max-w-xs">{{ $prod->description }}</p>
                                </div>
                            </td>

                            <td class="p-4 font-semibold text-gray-700">
                                <span class="bg-gray-100 px-2.5 py-1 rounded-lg text-gray-800 font-bold text-[11px]">
                                    {{ $prod->category ? $prod->category->name : '-' }}
                                </span>
                            </td>

                            <td class="p-4">
                                <div class="font-black text-gray-900 text-sm">Rp {{ number_format($prod->price, 0, ',', '.') }}</div>
                                @if($prod->original_price)
                                    <div class="text-[11px] text-gray-400 line-through">Rp {{ number_format($prod->original_price, 0, ',', '.') }}</div>
                                @endif
                            </td>

                            <td class="p-4 text-gray-500 font-medium">
                                <div>{{ $prod->calories ?: '-' }} kcal</div>
                                <div>{{ $prod->prep_time_minutes ?: '-' }} mnt</div>
                            </td>

                            <td class="p-4">
                                <form action="{{ route('admin.products.toggle', $prod->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 rounded-full text-[11px] font-extrabold transition {{ $prod->is_available ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                        {{ $prod->is_available ? 'Tersedia' : 'Habis' }}
                                    </button>
                                </form>
                            </td>

                            <td class="p-4 text-right space-x-1 whitespace-nowrap">
                                <button type="button" onclick="openEditProductModal({{ json_encode($prod) }})" class="bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold px-3 py-1.5 rounded-lg transition text-xs">
                                    Edit
                                </button>

                                <form action="{{ route('admin.products.delete', $prod->id) }}" method="POST" onsubmit="return confirm('Hapus menu {{ $prod->name }}?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 font-bold px-3 py-1.5 rounded-lg transition text-xs">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-gray-400">
                                <div class="font-bold text-sm mb-1">[Belum Ada Menu]</div>
                                <p class="text-xs">Daftar produk/menu belum tersedia di sistem database saat ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ═══ 1. ADD PRODUCT MODAL ═══ -->
<div id="add-product-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h3 class="font-black text-base text-gray-900">Tambah Menu Makanan Baru</h3>
            <button onclick="document.getElementById('add-product-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700 text-lg font-bold">&times;</button>
        </div>

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3 text-xs">
            @csrf

            <div>
                <label class="block font-bold text-gray-700 mb-1">Nama Menu:</label>
                <input type="text" name="name" required placeholder="Contoh: Triple Bacon Cheeseburger" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 font-medium focus:outline-none focus:border-bites-orange">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Kategori:</label>
                    <select name="category_id" required class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 font-semibold focus:outline-none">
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Harga Jual (Rp):</label>
                    <input type="number" name="price" required placeholder="55000" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 font-medium focus:outline-none focus:border-bites-orange">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Harga Coret / Promo (Opsional):</label>
                    <input type="number" name="original_price" placeholder="65000" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Upload File Foto / Gambar:</label>
                    <input type="file" name="image_file" accept="image/*" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-1.5 focus:outline-none text-[11px]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Estimasi Kalori (kcal):</label>
                    <input type="number" name="calories" placeholder="650" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Waktu Masak (Menit):</label>
                    <input type="number" name="prep_time_minutes" placeholder="10" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Deskripsi Menu:</label>
                <textarea name="description" rows="2" placeholder="Jelaskan bahan dan keunggulan rasa burger..." class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 focus:outline-none"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_featured" id="add_is_featured" value="1" class="rounded text-bites-orange focus:ring-bites-orange">
                <label for="add_is_featured" class="font-bold text-gray-800 cursor-pointer">Tandai sebagai Menu Rekomendasi / Favorit</label>
            </div>

            <div class="pt-3">
                <button type="submit" class="w-full bg-bites-yellow hover:bg-bites-yellow-dark text-bites-dark font-black py-3 rounded-xl transition text-sm">
                    Simpan Menu Baru
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══ 2. EDIT PRODUCT MODAL ═══ -->
<div id="edit-product-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h3 class="font-black text-base text-gray-900">Edit Menu Makanan</h3>
            <button onclick="document.getElementById('edit-product-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700 text-lg font-bold">&times;</button>
        </div>

        <form id="edit-product-form" method="POST" enctype="multipart/form-data" class="space-y-3 text-xs">
            @csrf

            <div>
                <label class="block font-bold text-gray-700 mb-1">Nama Menu:</label>
                <input type="text" name="name" id="edit_name" required class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 font-medium focus:outline-none focus:border-bites-orange">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Kategori:</label>
                    <select name="category_id" id="edit_category_id" required class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 font-semibold focus:outline-none">
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Harga Jual (Rp):</label>
                    <input type="number" name="price" id="edit_price" required class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 font-medium focus:outline-none focus:border-bites-orange">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Harga Coret / Promo (Opsional):</label>
                    <input type="number" name="original_price" id="edit_original_price" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Ganti Foto Menu (Opsional):</label>
                    <input type="file" name="image_file" accept="image/*" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-1.5 focus:outline-none text-[11px]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Estimasi Kalori (kcal):</label>
                    <input type="number" name="calories" id="edit_calories" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Waktu Masak (Menit):</label>
                    <input type="number" name="prep_time_minutes" id="edit_prep_time" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Deskripsi Menu:</label>
                <textarea name="description" id="edit_description" rows="2" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-2.5 focus:outline-none"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_featured" id="edit_is_featured" value="1" class="rounded text-bites-orange focus:ring-bites-orange">
                <label for="edit_is_featured" class="font-bold text-gray-800 cursor-pointer">Tandai sebagai Menu Rekomendasi / Favorit</label>
            </div>

            <div class="pt-3">
                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-black py-3 rounded-xl transition text-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditProductModal(product) {
        document.getElementById('edit-product-form').action = `/admin/products/${product.id}/update`;
        document.getElementById('edit_name').value = product.name;
        document.getElementById('edit_category_id').value = product.category_id;
        document.getElementById('edit_price').value = parseInt(product.price);
        document.getElementById('edit_original_price').value = product.original_price ? parseInt(product.original_price) : '';
        document.getElementById('edit_calories').value = product.calories || '';
        document.getElementById('edit_prep_time').value = product.prep_time_minutes || '';
        document.getElementById('edit_description').value = product.description || '';
        document.getElementById('edit_is_featured').checked = !!product.is_featured;

        document.getElementById('edit-product-modal').classList.remove('hidden');
    }
</script>
@endsection
