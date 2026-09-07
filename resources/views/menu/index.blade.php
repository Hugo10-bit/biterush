@extends('layouts.app')

@section('title', 'BiteRush - Menu Makanan & Burger Premium')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- HERO BANNER -->
    <div class="relative bg-gradient-to-r from-amber-500 via-bites-orange to-red-600 rounded-3xl overflow-hidden shadow-xl mb-8 p-6 sm:p-10 text-white flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-3 z-10 max-w-xl">
            <div class="inline-flex items-center gap-2 bg-black/20 backdrop-blur-md px-3.5 py-1 rounded-full text-xs font-bold text-yellow-200">
                <span>PROMO HARI INI</span> • <span>Diskon s.d 50%</span>
            </div>
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                Crave the Ultimate <span class="text-yellow-300">Bite Rush!</span>
            </h1>
            <p class="text-xs sm:text-sm text-white/90 leading-relaxed">
                100% Australian Beef Patty dengan keju cheddar meleleh dan brioche bun empuk panggang. Pesan sekarang untuk Dine-in, Takeaway, atau Delivery!
            </p>
            <div class="flex flex-wrap items-center gap-3 pt-2">
                <a href="#menu-catalog" class="bg-black text-white hover:bg-gray-900 font-bold text-xs px-5 py-3 rounded-xl transition shadow-md">
                    Lihat Semua Menu
                </a>
                <span class="text-xs bg-white/20 px-3 py-2 rounded-xl font-medium">
                    Gunakan Kupon: <strong class="text-yellow-300 font-mono">BITERUSH50</strong>
                </span>
            </div>
        </div>

        <div class="w-full md:w-1/3 flex justify-center z-10">
            <img src="{{ asset('images/burger-bg.png') }}" alt="Burger Hero" class="max-h-48 sm:max-h-56 object-contain drop-shadow-2xl hover:scale-105 transition duration-300">
        </div>

        <!-- Pattern Deco -->
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
    </div>

    <!-- DINE-IN TABLE ALERT IF SCANNED -->
    @if(!empty($tableNum))
        <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-4 mb-6 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-xs">
                    Meja
                </div>
                <div>
                    <h4 class="text-xs font-bold text-amber-900">Pemesanan Meja: {{ $tableNum }}</h4>
                    <p class="text-[11px] text-amber-700">Pesanan Anda akan langsung diantar oleh pelayan ke meja ini.</p>
                </div>
            </div>
            <span class="bg-amber-200/60 text-amber-900 text-xs font-bold px-3 py-1 rounded-lg">Dine In</span>
        </div>
    @endif

    <!-- CATEGORIES FILTER PILLS -->
    <div id="menu-catalog" class="sticky top-18 z-30 bg-bites-bg/95 backdrop-blur-md py-3 -mx-4 px-4 sm:mx-0 sm:px-0 mb-6">
        <div class="flex items-center gap-2.5 overflow-x-auto hide-scrollbar pb-1">
            <a href="{{ route('menu', ['category' => 'all', 'q' => request('q')]) }}" 
               class="px-4 py-2.5 rounded-2xl text-xs font-extrabold flex items-center gap-1.5 whitespace-nowrap transition shadow-xs {{ $selectedCategory === 'all' ? 'bg-bites-yellow text-bites-dark shadow-md scale-102' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                Semua Menu
            </a>

            @foreach($categories as $cat)
                <a href="{{ route('menu', ['category' => $cat->slug, 'q' => request('q')]) }}" 
                   class="px-4 py-2.5 rounded-2xl text-xs font-extrabold flex items-center gap-1.5 whitespace-nowrap transition shadow-xs {{ $selectedCategory === $cat->slug ? 'bg-bites-yellow text-bites-dark shadow-md scale-102' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- PRODUCTS GRID -->
    @if($products->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-gray-200 shadow-xs">
            <div class="text-sm font-bold text-gray-400 mb-1">[Pencarian]</div>
            <h3 class="text-base font-bold text-gray-900">Menu tidak ditemukan</h3>
            <p class="text-xs text-gray-500 mt-1">Coba cari dengan kata kunci lain atau pilih kategori yang berbeda.</p>
            <a href="{{ route('menu') }}" class="inline-block mt-4 bg-bites-yellow text-bites-dark font-bold text-xs px-4 py-2 rounded-xl">
                Reset Pencarian
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                <div class="bg-white rounded-3xl border border-gray-200/80 hover:border-bites-orange/60 overflow-hidden shadow-xs hover:shadow-xl transition-all duration-200 flex flex-col group">
                    
                    <!-- Product Image & Badges -->
                    <div class="relative bg-gray-100 aspect-[4/3] overflow-hidden">
                        <img src="{{ asset($product->image ?: 'images/burger-bg.jpg') }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        
                        @if($product->is_featured)
                            <span class="absolute top-3 left-3 bg-bites-red text-white text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider shadow-sm">
                                Rekomendasi
                            </span>
                        @endif

                        @if($product->original_price && $product->original_price > $product->price)
                            @php
                                $disc = round((($product->original_price - $product->price) / $product->original_price) * 100);
                            @endphp
                            <span class="absolute top-3 right-3 bg-yellow-400 text-yellow-950 text-[10px] font-black px-2 py-0.5 rounded-full shadow-sm">
                                -{{ $disc }}%
                            </span>
                        @endif

                        <div class="absolute bottom-2 left-3 right-3 flex items-center justify-between text-[11px] font-semibold text-white">
                            <span class="bg-black/60 backdrop-blur-xs px-2 py-0.5 rounded-md">
                                {{ $product->calories ?: 450 }} kcal
                            </span>
                            <span class="bg-black/60 backdrop-blur-xs px-2 py-0.5 rounded-md">
                                {{ $product->prep_time_minutes ?: 8 }} menit
                            </span>
                        </div>
                    </div>

                    <!-- Product Body -->
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600">
                                {{ $product->category->name }}
                            </span>
                            <h3 class="font-bold text-sm text-gray-900 group-hover:text-bites-red transition line-clamp-1 mt-0.5">
                                {{ $product->name }}
                            </h3>
                            <p class="text-xs text-gray-500 line-clamp-2 mt-1 leading-relaxed">
                                {{ $product->description }}
                            </p>
                        </div>

                        <!-- Price & Action -->
                        <div class="pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
                            <div>
                                <div class="text-base font-black text-gray-900">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </div>
                                @if($product->original_price && $product->original_price > $product->price)
                                    <div class="text-[11px] text-gray-400 line-through">
                                        Rp {{ number_format($product->original_price, 0, ',', '.') }}
                                    </div>
                                @endif
                            </div>

                            @if($product->options->isNotEmpty())
                                <button onclick="openProductModal({{ $product->id }})" class="bg-bites-yellow hover:bg-bites-yellow-dark text-bites-dark font-extrabold text-xs px-3.5 py-2 rounded-xl transition shadow-xs flex items-center gap-1 active:scale-95">
                                    <span>Pilih Varian</span>
                                </button>
                            @else
                                <button onclick="quickAddToCart({{ $product->id }})" class="bg-gray-900 hover:bg-bites-red text-white font-extrabold text-xs px-3.5 py-2 rounded-xl transition shadow-xs active:scale-95 flex items-center gap-1">
                                    <span>+ Tambah</span>
                                </button>
                            @endif
                        </div>

                    </div>

                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
