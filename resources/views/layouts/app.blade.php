<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BiteRush - Fast Food & Burger Ordering')</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bites: {
                            yellow: '#FFC72C',
                            'yellow-dark': '#E8A400',
                            orange: '#F9961F',
                            red: '#D92625',
                            'red-dark': '#A51B12',
                            dark: '#121212',
                            card: '#1E1E1E',
                            cream: '#FFFDF9',
                            bg: '#F8F6F0',
                        }
                    },
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        html { scroll-behavior: smooth; overflow-x: clip; }
        body { 
            font-family: 'Poppins', sans-serif; 
            overflow-x: clip;
            max-width: 100vw;
            animation: pageEnter 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            transition: opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1), transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }
        body.page-exiting {
            opacity: 0 !important;
            transform: translateY(-4px) scale(0.995);
        }
        @keyframes pageEnter {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        #page-loader-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3.5px;
            width: 0%;
            background: linear-gradient(90deg, #FFC72C, #F9961F, #D92625);
            z-index: 99999;
            transition: width 0.35s ease, opacity 0.3s ease;
            pointer-events: none;
            box-shadow: 0 0 10px rgba(249, 150, 31, 0.7);
        }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="min-h-full bg-bites-bg text-gray-900 flex flex-col antialiased">
    <!-- Top Progress Bar for Smooth Navigation -->
    <div id="page-loader-bar"></div>

    <!-- TOP NAVBAR -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-gray-200/80 shadow-xs transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18">
                
                <!-- Logo -->
                <div class="flex items-center gap-6">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                        <img src="{{ asset('images/logo.png') }}" alt="BiteRush Logo" class="h-10 sm:h-12 w-auto object-contain transition group-hover:scale-105">
                    </a>

                    <!-- Nav Links -->
                    <nav class="hidden md:flex items-center gap-1 font-medium text-sm">
                        <a href="{{ route('menu') }}" class="px-3.5 py-2 rounded-xl text-gray-700 hover:text-bites-red hover:bg-red-50/50 transition">
                            Menu Makanan
                        </a>
                        @auth
                            <a href="{{ route('orders.index') }}" class="px-3.5 py-2 rounded-xl text-gray-700 hover:text-bites-red hover:bg-red-50/50 transition">
                                Pesanan Saya
                            </a>
                            @if(Auth::user()->isStaff())
                                <a href="{{ route('admin.pos') }}" class="px-3.5 py-2 rounded-xl text-amber-700 bg-amber-50 hover:bg-amber-100 transition font-semibold">
                                    POS Kasir
                                </a>
                                <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 rounded-xl text-purple-700 bg-purple-50 hover:bg-purple-100 transition font-semibold">
                                    Panel Admin
                                </a>
                            @endif
                        @endauth
                    </nav>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-3">
                    
                    <!-- Search Bar (Desktop) -->
                    <form action="{{ route('menu') }}" method="GET" class="hidden lg:flex items-center relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari burger, chicken..." 
                            class="bg-gray-100/90 text-xs text-gray-800 pl-9 pr-4 py-2.5 rounded-full w-56 focus:w-64 focus:bg-white focus:ring-2 focus:ring-bites-orange focus:outline-none transition-all">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </form>

                    <!-- Cart Trigger Button -->
                    <button onclick="toggleCartDrawer()" id="cart-btn" class="relative bg-bites-yellow hover:bg-bites-yellow-dark text-bites-dark font-bold p-2.5 sm:px-4 sm:py-2.5 rounded-xl flex items-center gap-2 shadow-sm transition active:scale-95">
                        <svg class="w-5 h-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span class="hidden sm:inline text-xs font-semibold">Keranjang</span>
                        <span id="cart-badge" class="bg-bites-red text-white text-[11px] font-extrabold px-2 py-0.5 rounded-full shadow-xs">
                            {{ session('cart') ? array_sum(array_column(session('cart'), 'quantity')) : 0 }}
                        </span>
                    </button>

                    <!-- User / Auth -->
                    <!-- User / Auth -->
                    @auth
                        <div class="relative" id="user-dropdown-container">
                            <button onclick="toggleUserDropdown(event)" id="user-menu-button" type="button" 
                                class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 p-1.5 sm:pr-3 rounded-full text-xs font-semibold transition active:scale-95 focus:outline-none focus:ring-2 focus:ring-bites-orange">
                                <div class="w-8 h-8 rounded-full bg-bites-red text-white flex items-center justify-center font-bold text-xs uppercase shadow-xs">
                                    {{ substr(Auth::user()->name ?: Auth::user()->username, 0, 2) }}
                                </div>
                                <span class="hidden sm:inline max-w-[120px] truncate text-gray-800">{{ Auth::user()->username }}</span>
                                <svg class="w-3.5 h-3.5 text-gray-500 transition-transform duration-200" id="user-menu-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div id="user-dropdown-menu" class="hidden absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 transition-all transform origin-top-right">
                                <div class="px-4 py-2.5 border-b border-gray-100 text-xs">
                                    <div class="font-bold text-gray-900 truncate text-sm">{{ Auth::user()->name }}</div>
                                    <div class="text-gray-500 truncate text-[11px]">{{ Auth::user()->email }}</div>
                                    <span class="inline-block mt-1.5 px-2 py-0.5 bg-yellow-100 text-yellow-800 text-[10px] font-extrabold rounded-md uppercase">Role: {{ Auth::user()->role }}</span>
                                </div>
                                <div class="py-1">
                                    <a href="{{ route('orders.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs text-gray-700 hover:bg-gray-50 hover:text-bites-red transition">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        Pesanan Saya
                                    </a>
                                    @if(Auth::user()->isStaff())
                                        <a href="{{ route('admin.pos') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs text-amber-700 hover:bg-amber-50 font-semibold transition">
                                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            POS Kasir
                                        </a>
                                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs text-purple-700 hover:bg-purple-50 font-semibold transition">
                                            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                            </svg>
                                            Dashboard Admin
                                        </a>
                                    @endif
                                </div>
                                <div class="border-t border-gray-100 pt-1">
                                    <form action="{{ route('logout') }}" method="POST" onsubmit="return confirmLogout(event)">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 font-bold flex items-center gap-2 transition">
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="bg-gray-900 hover:bg-black text-white text-xs font-semibold px-4 py-2.5 rounded-xl transition">
                            Login / Masuk
                        </a>
                    @endauth

                    <!-- Mobile Hamburger Button -->
                    <button onclick="toggleMobileNav()" id="mobile-menu-btn" type="button" 
                        class="md:hidden flex items-center justify-center p-2 rounded-xl text-gray-700 hover:text-bites-red hover:bg-red-50/50 transition active:scale-95 focus:outline-none" 
                        aria-label="Menu Navigasi">
                        <svg id="hamburger-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg id="hamburger-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                </div>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div id="mobile-nav-drawer" class="hidden md:hidden border-t border-gray-100 bg-white/98 backdrop-blur-lg px-4 pt-3 pb-5 shadow-xl transition-all duration-300">
            <!-- Mobile Search Bar -->
            <form action="{{ route('menu') }}" method="GET" class="mb-4">
                <div class="relative">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari burger, chicken, drinks..."
                        class="w-full bg-gray-100 text-xs text-gray-800 pl-10 pr-4 py-2.5 rounded-xl focus:bg-white focus:ring-2 focus:ring-bites-orange focus:outline-none transition">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </form>

            <!-- Navigation Links -->
            <div class="flex flex-col space-y-1 text-sm font-semibold">
                <a href="{{ route('menu') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-gray-700 hover:text-bites-red hover:bg-red-50/50 transition">
                    <span class="text-base">🍔</span>
                    <span>Menu Makanan</span>
                </a>
                @auth
                    <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-gray-700 hover:text-bites-red hover:bg-red-50/50 transition">
                        <span class="text-base">📦</span>
                        <span>Pesanan Saya</span>
                    </a>
                    @if(Auth::user()->isStaff())
                        <a href="{{ route('admin.pos') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-amber-700 bg-amber-50 hover:bg-amber-100 transition">
                            <span class="text-base">💻</span>
                            <span>POS Kasir</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-purple-700 bg-purple-50 hover:bg-purple-100 transition">
                            <span class="text-base">📊</span>
                            <span>Dashboard Admin</span>
                        </a>
                    @endif
                    <div class="pt-3 mt-2 border-t border-gray-100">
                        <div class="px-3.5 py-1 text-xs text-gray-500">
                            Masuk sebagai <strong class="text-gray-900">{{ Auth::user()->name ?: Auth::user()->username }}</strong> ({{ Auth::user()->role }})
                        </div>
                        <form action="{{ route('logout') }}" method="POST" onsubmit="return confirmLogout(event)">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center gap-3 px-3.5 py-2 rounded-xl text-red-600 hover:bg-red-50 font-bold transition">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Keluar (Logout)</span>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="pt-3 mt-2 border-t border-gray-100 flex gap-2">
                        <a href="{{ route('login') }}" class="flex-1 text-center bg-gray-900 hover:bg-black text-white text-xs font-semibold py-2.5 rounded-xl transition">
                            Login / Masuk
                        </a>
                        <a href="{{ route('register') }}" class="flex-1 text-center bg-bites-red hover:bg-red-700 text-white text-xs font-semibold py-2.5 rounded-xl transition">
                            Daftar Akun
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="bg-green-50 border border-green-200 text-green-800 text-xs sm:text-sm font-medium px-4 py-3 rounded-xl flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <span class="font-bold">[Sukses]</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-900 font-bold">&times;</button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm font-medium px-4 py-3 rounded-xl flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <span class="font-bold">[Perhatian]</span>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-900 font-bold">&times;</button>
            </div>
        </div>
    @endif

    <!-- MAIN CONTENT -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-bites-dark text-white border-t border-white/10 mt-20 pt-12 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">
                <div class="space-y-3">
                    <img src="{{ asset('images/logo.png') }}" alt="BiteRush" class="h-10 w-auto">
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Nikmati sensasi burger premium terbaik dengan 100% daging sapi Australia, brioche bun lembut, dan saus lezat khas BiteRush.
                    </p>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-bites-yellow mb-3 uppercase tracking-wider">Jam Buka</h4>
                    <ul class="text-xs text-gray-300 space-y-1.5">
                        <li>Senin - Jumat: 10:00 - 22:00</li>
                        <li>Sabtu - Minggu: 09:00 - 23:00</li>
                        <li>Layanan Dine-In, Takeaway & Delivery</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-bites-yellow mb-3 uppercase tracking-wider">Voucher Promo</h4>
                    <ul class="text-xs text-gray-300 space-y-1.5">
                        <li><span class="bg-white/10 px-2 py-0.5 rounded font-mono text-bites-yellow">WELCOMEBITE</span> Diskon Rp 15.000</li>
                        <li><span class="bg-white/10 px-2 py-0.5 rounded font-mono text-bites-yellow">BITERUSH50</span> Diskon 50%</li>
                        <li><span class="bg-white/10 px-2 py-0.5 rounded font-mono text-bites-yellow">CHEESEFEAST</span> Potongan Rp 20.000</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-bites-yellow mb-3 uppercase tracking-wider">Metode Pembayaran</h4>
                    <div class="flex flex-wrap gap-2 text-xs text-gray-300">
                        <span class="bg-white/10 px-2.5 py-1 rounded">QRIS All Payment</span>
                        <span class="bg-white/10 px-2.5 py-1 rounded">GoPay / OVO</span>
                        <span class="bg-white/10 px-2.5 py-1 rounded">BCA / Mandiri</span>
                        <span class="bg-white/10 px-2.5 py-1 rounded">Tunai di Kasir</span>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/10 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500">
                <p>&copy; {{ date('Y') }} BiteRush Fast Food. All Rights Reserved.</p>
                <p class="mt-2 sm:mt-0 font-medium">BiteRush by Hugo Putra</p>
            </div>
        </div>
    </footer>

    <!-- CART FLOATING CARD -->
    <div id="cart-backdrop" onclick="toggleCartDrawer()" class="fixed inset-0 bg-black/50 z-50 hidden transition-opacity duration-300"></div>
    <div id="cart-drawer" class="fixed top-20 right-4 w-[90vw] max-w-sm bg-white rounded-3xl shadow-2xl border border-gray-200/60 z-50 flex flex-col max-h-[calc(100vh-6rem)] overflow-hidden pointer-events-none opacity-0 invisible" style="transform: scale(0.95) translateY(-8px); transition: transform 0.25s cubic-bezier(0.16,1,0.3,1), opacity 0.2s ease;">
        
        <!-- Cart Header (Fixed Top) -->
        <div class="p-4 sm:p-4.5 bg-gray-50/90 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-bites-yellow flex items-center justify-center font-extrabold text-xs text-bites-dark shadow-xs">BR</div>
                <div>
                    <h3 class="font-extrabold text-gray-900 text-sm leading-tight">Keranjang Pesanan</h3>
                    <p class="text-[11px] text-gray-500" id="cart-items-count-text">0 menu dipilih</p>
                </div>
            </div>
            <button onclick="toggleCartDrawer()" class="text-gray-400 hover:text-gray-700 hover:bg-gray-200 w-7 h-7 rounded-full flex items-center justify-center text-lg font-bold transition">&times;</button>
        </div>

        <!-- Cart Body (Items + Promo + Summary + Button all flowing together) -->
        <div class="overflow-y-auto p-4 space-y-3.5 flex-1 hide-scrollbar">
            
            <!-- Items Container -->
            <div id="cart-items-container" class="space-y-2.5">
                <div class="text-center py-10 text-gray-400">
                    <div class="font-bold text-sm mb-1">[Kosong]</div>
                    <p class="text-xs font-medium">Keranjang Anda masih kosong</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Pilih menu favorit Anda di katalog!</p>
                </div>
            </div>

            <!-- Coupon Box (directly under items) -->
            <div id="cart-coupon-section" class="pt-2 border-t border-gray-100">
                <label class="block text-[11px] font-bold text-gray-700 mb-1 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-bites-orange" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                    </svg>
                    Punya Kupon Promo?
                </label>
                <div class="flex gap-1.5">
                    <input type="text" id="coupon-input" placeholder="KODE PROMO (misal: BITERUSH50)" class="flex-1 bg-gray-50 border border-gray-300 rounded-xl px-3 py-1.5 text-xs uppercase font-bold tracking-wider focus:outline-none focus:border-bites-orange focus:bg-white transition">
                    <button onclick="applyCoupon()" class="bg-gray-900 hover:bg-black text-white text-xs font-extrabold px-3.5 py-1.5 rounded-xl transition shadow-xs active:scale-95">
                        Pakai
                    </button>
                </div>
                <div id="applied-coupon-badge" class="hidden text-xs bg-green-50 text-green-700 border border-green-200 p-2 rounded-xl flex items-center justify-between font-medium mt-1.5">
                    <div class="flex items-center gap-1">
                        <span class="text-green-600 font-bold">✓</span>
                        <span id="coupon-text">Promo Terpasang</span>
                    </div>
                    <button onclick="removeCoupon()" class="text-red-500 hover:text-red-700 font-bold px-1">&times;</button>
                </div>
            </div>

            <!-- Price Breakdown (directly under coupon) -->
            <div id="cart-summary-section" class="bg-gray-50 rounded-2xl p-3.5 border border-gray-200/80 space-y-1.5 text-xs text-gray-600">
                <div class="flex justify-between">
                    <span>Subtotal Menu:</span>
                    <span id="cart-subtotal" class="font-bold text-gray-900">Rp 0</span>
                </div>
                <div class="flex justify-between text-green-600 font-medium">
                    <span>Diskon Promo:</span>
                    <span id="cart-discount" class="font-bold">- Rp 0</span>
                </div>
                <div class="flex justify-between">
                    <span>Pajak PB1 (10%):</span>
                    <span id="cart-tax" class="font-semibold text-gray-900">Rp 0</span>
                </div>
                <div class="flex justify-between text-xs font-black text-gray-900 pt-1.5 border-t border-gray-200">
                    <span>Total Tagihan:</span>
                    <span id="cart-total" class="text-bites-red font-black text-sm">Rp 0</span>
                </div>
            </div>

            <!-- Checkout Action Button (Ends right here!) -->
            <div id="cart-action-section" class="pt-0.5 pb-1">
                <a href="{{ route('orders.checkout') }}" id="checkout-btn" class="w-full bg-bites-yellow hover:bg-bites-yellow-dark text-bites-dark font-black py-3 px-4 rounded-2xl flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition active:scale-98 text-xs sm:text-sm">
                    <span>Lanjut ke Pembayaran</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- PRODUCT DETAIL & CUSTOMIZATION MODAL -->
    <div id="product-modal-backdrop" onclick="closeProductModal()" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden"></div>
    <div id="product-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col shadow-2xl animate-fade-in" onclick="event.stopPropagation()">
            
            <!-- Modal Header / Image -->
            <div class="relative bg-gradient-to-tr from-amber-500 to-orange-400 h-48 overflow-hidden flex items-center justify-center">
                <img id="modal-product-image" src="" alt="Menu" class="w-full h-full object-cover">
                <button onclick="closeProductModal()" class="absolute top-3 right-3 bg-black/50 hover:bg-black/80 text-white rounded-full w-8 h-8 flex items-center justify-center text-lg font-bold transition">
                    &times;
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6 overflow-y-auto flex-1 space-y-4">
                <div>
                    <div class="flex items-start justify-between gap-2">
                        <h3 id="modal-product-name" class="font-extrabold text-xl text-gray-900 leading-tight">Product Name</h3>
                        <span id="modal-product-price" class="text-bites-red font-black text-lg whitespace-nowrap">Rp 0</span>
                    </div>
                    <p id="modal-product-desc" class="text-xs text-gray-500 mt-1 leading-relaxed">Description</p>
                    <div class="flex items-center gap-3 mt-2 text-[11px] text-gray-500 font-medium">
                        <span id="modal-product-calories" class="bg-gray-100 px-2 py-0.5 rounded font-semibold">0 kcal</span>
                        <span id="modal-product-time" class="bg-gray-100 px-2 py-0.5 rounded font-semibold">0 menit</span>
                    </div>
                </div>

                <!-- Dynamic Options Form -->
                <form id="product-options-form" class="space-y-4 pt-2 border-t border-gray-100">
                    <input type="hidden" id="modal-product-id" name="product_id" value="">

                    <div id="modal-options-container" class="space-y-4">
                        <!-- Filled by JS -->
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Koki (Opsional):</label>
                        <input type="text" id="modal-notes" name="notes" placeholder="Contoh: Jangan pakai bawang bombay, extra saus..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-bites-orange focus:outline-none">
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between gap-3">
                <div class="flex items-center border border-gray-300 rounded-xl bg-white overflow-hidden">
                    <button type="button" onclick="adjustModalQty(-1)" class="px-3 py-2 text-gray-600 hover:bg-gray-100 font-bold text-sm">-</button>
                    <input type="number" id="modal-quantity" value="1" min="1" readonly class="w-10 text-center font-bold text-xs text-gray-900 border-none focus:outline-none">
                    <button type="button" onclick="adjustModalQty(1)" class="px-3 py-2 text-gray-600 hover:bg-gray-100 font-bold text-sm">+</button>
                </div>

                <button type="button" onclick="submitAddToCart()" class="flex-1 bg-bites-yellow hover:bg-bites-yellow-dark text-bites-dark font-extrabold py-3 px-4 rounded-xl flex items-center justify-between text-xs sm:text-sm shadow-md transition active:scale-98">
                    <span>+ Tambahkan ke Keranjang</span>
                    <span id="modal-total-btn-price" class="font-black">Rp 0</span>
                </button>
            </div>

        </div>
    </div>

    <!-- GLOBAL JAVASCRIPT FOR CART & MODALS -->
    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let currentModalProduct = null;

        function formatRupiah(num) {
            return 'Rp ' + Number(num).toLocaleString('id-ID');
        }

        function openCartDrawer() {
            const drawer = document.getElementById('cart-drawer');
            const backdrop = document.getElementById('cart-backdrop');
            if (!drawer) return;
            drawer.classList.remove('invisible', 'pointer-events-none', 'opacity-0');
            drawer.classList.add('opacity-100');
            drawer.style.transform = 'scale(1) translateY(0)';
            if (backdrop) backdrop.classList.remove('hidden');
        }

        function closeCartDrawer() {
            const drawer = document.getElementById('cart-drawer');
            const backdrop = document.getElementById('cart-backdrop');
            if (!drawer) return;
            drawer.style.transform = 'scale(0.95) translateY(-8px)';
            drawer.classList.add('pointer-events-none', 'opacity-0');
            drawer.classList.remove('opacity-100');
            if (backdrop) backdrop.classList.add('hidden');
            setTimeout(() => {
                if (drawer.classList.contains('opacity-0')) {
                    drawer.classList.add('invisible');
                }
            }, 250);
        }

        function toggleCartDrawer() {
            const drawer = document.getElementById('cart-drawer');
            const isClosed = !drawer || drawer.classList.contains('invisible') || drawer.classList.contains('opacity-0');

            if (isClosed) {
                // Scroll to top first, then open cart
                window.scrollTo({ top: 0, behavior: 'smooth' });
                const alreadyAtTop = window.scrollY < 5;
                const delay = alreadyAtTop ? 0 : 400;
                setTimeout(() => {
                    openCartDrawer();
                    refreshCart();
                }, delay);
            } else {
                closeCartDrawer();
            }
        }

        async function refreshCart() {
            try {
                const res = await fetch('{{ route("cart.index") }}');
                const data = await res.json();
                renderCart(data);
            } catch (e) {
                console.error(e);
            }
        }

        function renderCart(cart) {
            const container = document.getElementById('cart-items-container');
            const badge = document.getElementById('cart-badge');
            const countText = document.getElementById('cart-items-count-text');
            const checkoutTotal = document.getElementById('checkout-btn-total');
            const couponSection = document.getElementById('cart-coupon-section');
            const summarySection = document.getElementById('cart-summary-section');

            badge.innerText = cart.item_count || 0;
            countText.innerText = `${cart.item_count || 0} menu dipilih`;

            document.getElementById('cart-subtotal').innerText = formatRupiah(cart.subtotal);
            document.getElementById('cart-discount').innerText = '- ' + formatRupiah(cart.discount);
            document.getElementById('cart-tax').innerText = formatRupiah(cart.tax);
            document.getElementById('cart-total').innerText = formatRupiah(cart.total);
            if (checkoutTotal) {
                checkoutTotal.innerText = formatRupiah(cart.total);
            }

            const couponBadge = document.getElementById('applied-coupon-badge');
            const couponText = document.getElementById('coupon-text');
            if (cart.coupon) {
                couponBadge.classList.remove('hidden');
                couponText.innerText = `Promo ${cart.coupon.code} aktif (-${formatRupiah(cart.discount)})`;
            } else {
                couponBadge.classList.add('hidden');
            }

            const actionSection = document.getElementById('cart-action-section');

            if (!cart.items || cart.items.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-16 text-gray-400">
                        <div class="font-bold text-sm mb-1">[Kosong]</div>
                        <p class="text-sm font-medium">Keranjang Anda masih kosong</p>
                        <p class="text-xs text-gray-400 mt-1">Pilih menu favorit Anda di katalog!</p>
                    </div>`;
                if (couponSection) couponSection.classList.add('hidden');
                if (summarySection) summarySection.classList.add('hidden');
                if (actionSection) actionSection.classList.add('hidden');
                return;
            }

            if (couponSection) couponSection.classList.remove('hidden');
            if (summarySection) summarySection.classList.remove('hidden');
            if (actionSection) actionSection.classList.remove('hidden');

            container.innerHTML = cart.items.map(item => `
                <div class="bg-gray-50 border border-gray-200/80 rounded-2xl p-3.5 flex gap-3 items-center">
                    <img src="${item.image ? '/' + item.image : '/images/burger-bg.jpg'}" alt="${item.name}" class="w-16 h-16 object-cover rounded-xl border border-gray-200 flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-1">
                            <h4 class="font-bold text-xs text-gray-900 truncate">${item.name}</h4>
                            <button onclick="removeCartItem('${item.cart_key}')" class="text-gray-400 hover:text-red-500 text-sm font-bold">&times;</button>
                        </div>
                        <div class="text-[11px] text-gray-500 font-semibold mt-0.5">${formatRupiah(item.unit_price)}</div>
                        
                        ${item.options && item.options.length ? `
                            <div class="text-[10px] text-gray-400 mt-1 flex flex-wrap gap-1">
                                ${item.options.map(o => `<span class="bg-white px-1.5 py-0.5 rounded border border-gray-200">+ ${o.value_name}</span>`).join('')}
                            </div>
                        ` : ''}

                        ${item.notes ? `<div class="text-[10px] text-amber-700 italic mt-0.5 truncate">"${item.notes}"</div>` : ''}

                        <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-200/50">
                            <span class="font-black text-xs text-gray-900">${formatRupiah(item.total_price)}</span>
                            <div class="flex items-center border border-gray-300 rounded-lg bg-white overflow-hidden text-xs">
                                <button onclick="updateCartQty('${item.cart_key}', ${item.quantity - 1})" class="px-2 py-0.5 text-gray-600 hover:bg-gray-100 font-bold">-</button>
                                <span class="px-2 font-bold text-gray-900">${item.quantity}</span>
                                <button onclick="updateCartQty('${item.cart_key}', ${item.quantity + 1})" class="px-2 py-0.5 text-gray-600 hover:bg-gray-100 font-bold">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        async function updateCartQty(cartKey, qty) {
            const res = await fetch('{{ route("cart.update") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ cart_key: cartKey, quantity: qty })
            });
            const data = await res.json();
            if (data.cart) renderCart(data.cart);
        }

        async function removeCartItem(cartKey) {
            const res = await fetch('{{ route("cart.remove") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ cart_key: cartKey })
            });
            const data = await res.json();
            if (data.cart) renderCart(data.cart);
        }

        async function applyCoupon() {
            const code = document.getElementById('coupon-input').value;
            if (!code) return;
            const res = await fetch('{{ route("cart.coupon.apply") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ code })
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('coupon-input').value = '';
                renderCart(data.cart);
            } else {
                alert(data.message || 'Kupon tidak dapat digunakan.');
            }
        }

        async function removeCoupon() {
            const res = await fetch('{{ route("cart.coupon.remove") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
            });
            const data = await res.json();
            if (data.cart) renderCart(data.cart);
        }

        async function openProductModal(productId) {
            try {
                const res = await fetch(`/product/${productId}`);
                const data = await res.json();
                if (!data.success) return;

                currentModalProduct = data.product;
                document.getElementById('modal-product-id').value = currentModalProduct.id;
                document.getElementById('modal-product-name').innerText = currentModalProduct.name;
                document.getElementById('modal-product-desc').innerText = currentModalProduct.description || 'Pilihan terbaik dari BiteRush.';
                document.getElementById('modal-product-price').innerText = formatRupiah(currentModalProduct.price);
                document.getElementById('modal-product-image').src = currentModalProduct.image ? '/' + currentModalProduct.image : '/images/burger-bg.jpg';
                document.getElementById('modal-product-calories').innerText = `${currentModalProduct.calories || 450} kcal`;
                document.getElementById('modal-product-time').innerText = `${currentModalProduct.prep_time_minutes || 8} menit`;
                document.getElementById('modal-quantity').value = 1;
                document.getElementById('modal-notes').value = '';

                const optContainer = document.getElementById('modal-options-container');
                if (currentModalProduct.options && currentModalProduct.options.length > 0) {
                    optContainer.innerHTML = currentModalProduct.options.map(opt => `
                        <div class="bg-gray-50 p-3.5 rounded-2xl border border-gray-200/80">
                            <div class="flex items-center justify-between mb-2">
                                <h5 class="text-xs font-bold text-gray-900">${opt.name}</h5>
                                ${opt.is_required ? `<span class="text-[10px] bg-red-100 text-red-700 font-bold px-1.5 py-0.5 rounded">Wajib</span>` : `<span class="text-[10px] text-gray-400">Opsional</span>`}
                            </div>
                            <div class="space-y-1.5">
                                ${opt.values.map(val => `
                                    <label class="flex items-center justify-between p-2 rounded-xl bg-white border border-gray-200 cursor-pointer hover:border-bites-orange transition text-xs">
                                        <div class="flex items-center gap-2">
                                            <input type="${opt.type === 'single' ? 'radio' : 'checkbox'}" 
                                                   name="option_${opt.id}${opt.type === 'single' ? '' : '[]'}" 
                                                   value="${val.id}" 
                                                   data-price="${val.additional_price}"
                                                   ${val.is_default ? 'checked' : ''}
                                                   onchange="recalculateModalPrice()"
                                                   class="text-bites-orange focus:ring-bites-orange">
                                            <span class="font-medium text-gray-800">${val.name}</span>
                                        </div>
                                        <span class="font-bold text-gray-600 text-[11px]">
                                            ${Number(val.additional_price) > 0 ? '+ ' + formatRupiah(val.additional_price) : 'Gratis'}
                                        </span>
                                    </label>
                                `).join('')}
                            </div>
                        </div>
                    `).join('');
                } else {
                    optContainer.innerHTML = `<p class="text-xs text-gray-400 italic">Menu standar siap saji.</p>`;
                }

                recalculateModalPrice();
                document.getElementById('product-modal').classList.remove('hidden');
                document.getElementById('product-modal-backdrop').classList.remove('hidden');
            } catch (e) {
                console.error(e);
            }
        }

        function closeProductModal() {
            document.getElementById('product-modal').classList.add('hidden');
            document.getElementById('product-modal-backdrop').classList.add('hidden');
        }

        function adjustModalQty(delta) {
            const input = document.getElementById('modal-quantity');
            let val = parseInt(input.value) + delta;
            if (val < 1) val = 1;
            input.value = val;
            recalculateModalPrice();
        }

        function recalculateModalPrice() {
            if (!currentModalProduct) return;
            let unit = Number(currentModalProduct.price);

            const checkedInputs = document.querySelectorAll('#modal-options-container input:checked');
            checkedInputs.forEach(inp => {
                unit += Number(inp.getAttribute('data-price') || 0);
            });

            const qty = parseInt(document.getElementById('modal-quantity').value) || 1;
            const total = unit * qty;

            document.getElementById('modal-total-btn-price').innerText = formatRupiah(total);
        }

        async function submitAddToCart() {
            if (!currentModalProduct) return;

            const selectedOptions = [];
            document.querySelectorAll('#modal-options-container input:checked').forEach(inp => {
                selectedOptions.push(inp.value);
            });

            const qty = parseInt(document.getElementById('modal-quantity').value) || 1;
            const notes = document.getElementById('modal-notes').value;

            try {
                const res = await fetch('{{ route("cart.add") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    body: JSON.stringify({
                        product_id: currentModalProduct.id,
                        quantity: qty,
                        options: selectedOptions,
                        notes: notes
                    })
                });

                const data = await res.json();
                if (data.success) {
                    closeProductModal();
                    renderCart(data.cart);
                    animateCartBadge();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    const delay = window.scrollY < 5 ? 0 : 400;
                    setTimeout(() => openCartDrawer(), delay);
                    showCartToast(data.message || 'Menu berhasil ditambahkan ke keranjang!');
                } else {
                    alert(data.message || 'Gagal menambahkan ke keranjang.');
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function quickAddToCart(productId) {
            try {
                const res = await fetch('{{ route("cart.add") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    body: JSON.stringify({ product_id: productId, quantity: 1 })
                });
                const data = await res.json();
                if (data.success) {
                    renderCart(data.cart);
                    animateCartBadge();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    const delay = window.scrollY < 5 ? 0 : 400;
                    setTimeout(() => openCartDrawer(), delay);
                    showCartToast(data.message || 'Menu berhasil ditambahkan ke keranjang!');
                } else {
                    alert(data.message || 'Gagal menambahkan ke keranjang.');
                }
            } catch (e) {
                console.error(e);
            }
        }

        function animateCartBadge() {
            const badge = document.getElementById('cart-badge');
            const cartBtn = document.getElementById('cart-btn');
            if (badge) {
                badge.classList.remove('scale-125');
                badge.classList.add('scale-125');
                setTimeout(() => {
                    badge.classList.remove('scale-125');
                }, 300);
            }
            if (cartBtn) {
                cartBtn.classList.add('scale-105');
                setTimeout(() => {
                    cartBtn.classList.remove('scale-105');
                }, 200);
            }
        }

        function showCartToast(message) {
            let toast = document.getElementById('cart-toast-notification');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'cart-toast-notification';
                toast.className = 'fixed bottom-6 right-6 z-50 bg-gray-900 text-white px-4 py-3 rounded-2xl shadow-2xl flex items-center gap-3 transition-all duration-300 transform translate-y-20 opacity-0 pointer-events-auto border border-gray-800';
                toast.innerHTML = `
                    <div class="w-6 h-6 rounded-full bg-green-500 text-white flex items-center justify-center flex-shrink-0 font-black text-xs shadow-xs">
                        ✓
                    </div>
                    <div class="text-xs font-semibold" id="cart-toast-text"></div>
                    <button onclick="toggleCartDrawer()" class="ml-2 bg-bites-yellow hover:bg-bites-yellow-dark text-gray-900 text-[11px] font-black px-2.5 py-1 rounded-xl transition">
                        Buka
                    </button>
                `;
                document.body.appendChild(toast);
            }

            const textElem = document.getElementById('cart-toast-text');
            if (textElem) textElem.innerText = message;
            toast.classList.remove('translate-y-20', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');

            if (window._cartToastTimeout) clearTimeout(window._cartToastTimeout);
            window._cartToastTimeout = setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }

        function toggleMobileNav() {
            const drawer = document.getElementById('mobile-nav-drawer');
            const iconOpen = document.getElementById('hamburger-icon-open');
            const iconClose = document.getElementById('hamburger-icon-close');
            if (!drawer) return;

            const isHidden = drawer.classList.contains('hidden');
            if (isHidden) {
                drawer.classList.remove('hidden');
                if (iconOpen) iconOpen.classList.add('hidden');
                if (iconClose) iconClose.classList.remove('hidden');
            } else {
                drawer.classList.add('hidden');
                if (iconOpen) iconOpen.classList.remove('hidden');
                if (iconClose) iconClose.classList.add('hidden');
            }
        }

        function toggleUserDropdown(event) {
            if (event) event.stopPropagation();
            const menu = document.getElementById('user-dropdown-menu');
            const arrow = document.getElementById('user-menu-arrow');
            if (!menu) return;

            const isHidden = menu.classList.contains('hidden');
            if (isHidden) {
                menu.classList.remove('hidden');
                if (arrow) arrow.classList.add('rotate-180');
            } else {
                menu.classList.add('hidden');
                if (arrow) arrow.classList.remove('rotate-180');
            }
        }

        // Close user dropdown when clicking outside
        document.addEventListener('click', (event) => {
            const container = document.getElementById('user-dropdown-container');
            const menu = document.getElementById('user-dropdown-menu');
            const arrow = document.getElementById('user-menu-arrow');
            if (container && menu && !container.contains(event.target)) {
                menu.classList.add('hidden');
                if (arrow) arrow.classList.remove('rotate-180');
            }
        });

        function confirmLogout(event) {
            event.preventDefault();
            const form = event.target.closest('form');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Logout',
                    text: 'Apakah Anda yakin ingin keluar dari akun Anda?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#D92625',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Ya, Keluar',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl font-sans',
                        confirmButton: 'rounded-xl px-5 py-2.5 font-bold',
                        cancelButton: 'rounded-xl px-5 py-2.5 font-bold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm('Apakah Anda yakin ingin keluar?')) {
                    form.submit();
                }
            }
            return false;
        }

        // Smooth Page Transition Handler
        document.addEventListener('DOMContentLoaded', () => {
            const loader = document.getElementById('page-loader-bar');
            if (loader) {
                loader.style.width = '100%';
                setTimeout(() => { loader.style.opacity = '0'; }, 200);
            }

            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');
                const target = link.getAttribute('target');

                if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:') || target === '_blank' || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
                    return;
                }

                if (link.hostname === window.location.hostname) {
                    if (loader) {
                        loader.style.opacity = '1';
                        loader.style.width = '70%';
                    }
                    document.body.classList.add('page-exiting');
                }
            });
        });

        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                document.body.classList.remove('page-exiting');
                const loader = document.getElementById('page-loader-bar');
                if (loader) {
                    loader.style.width = '100%';
                    setTimeout(() => { loader.style.opacity = '0'; loader.style.width = '0%'; }, 200);
                }
            }
        });
    </script>
</body>
</html>
