<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel - BiteRush')</title>

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
                            dark: '#121212',
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
        html { scroll-behavior: smooth; }
        body { 
            font-family: 'Poppins', sans-serif; 
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
    </style>
</head>
<body class="min-h-full bg-gray-50 text-gray-900 flex antialiased">
    <div id="page-loader-bar"></div>

    <!-- SIDEBAR -->
    <aside class="w-64 bg-bites-dark text-white flex flex-col justify-between hidden md:flex flex-shrink-0 min-h-screen">
        <div>
            <!-- Logo -->
            <div class="p-6 border-b border-white/10">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.png') }}" alt="BiteRush Logo" class="h-9 w-auto">
                    <span class="text-[10px] font-bold bg-amber-500 text-black px-2 py-0.5 rounded">ADMIN</span>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5 text-xs font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-bites-yellow text-bites-dark font-extrabold shadow-sm' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                    <span>Overview Dashboard</span>
                </a>
                <a href="{{ route('admin.orders') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.orders') ? 'bg-bites-yellow text-bites-dark font-extrabold shadow-sm' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                    <span>Kitchen & Orders</span>
                </a>
                <a href="{{ route('admin.pos') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.pos') ? 'bg-bites-yellow text-bites-dark font-extrabold shadow-sm' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                    <span>Kasir / POS Screen</span>
                </a>
                <a href="{{ route('admin.products') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.products') ? 'bg-bites-yellow text-bites-dark font-extrabold shadow-sm' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                    <span>Menu & Produk</span>
                </a>
                <a href="{{ route('admin.tables') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.tables') ? 'bg-bites-yellow text-bites-dark font-extrabold shadow-sm' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                    <span>Meja Restoran</span>
                </a>
                <a href="{{ route('admin.coupons') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.coupons') ? 'bg-bites-yellow text-bites-dark font-extrabold shadow-sm' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                    <span>Kupon Promo</span>
                </a>
            </nav>
        </div>

        <!-- Bottom User Box -->
        <div class="p-4 border-t border-white/10 text-xs">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full bg-bites-red text-white flex items-center justify-center font-bold">
                    {{ substr(Auth::user()->name ?: 'A', 0, 2) }}
                </div>
                <div class="truncate">
                    <div class="font-bold text-white truncate">{{ Auth::user()->name }}</div>
                    <div class="text-[10px] text-gray-400 truncate">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('menu') }}" class="flex-1 bg-white/10 hover:bg-white/20 text-center py-2 rounded-lg font-bold transition">
                    Ke Menu
                </a>
                <form action="{{ route('logout') }}" method="POST" class="flex-1" onsubmit="return confirmLogout(event)">
                    @csrf
                    <button type="submit" class="w-full bg-red-900/60 hover:bg-red-800 text-red-200 text-center py-2 rounded-lg font-bold transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0">
        
        <!-- Top Header for Mobile -->
        <header class="bg-white border-b border-gray-200 p-4 flex md:hidden items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/logo.png') }}" alt="BiteRush" class="h-8">
                <span class="text-[10px] font-bold bg-amber-500 text-black px-2 py-0.5 rounded">ADMIN</span>
            </a>
            <div class="flex items-center gap-2 text-xs">
                <a href="{{ route('admin.pos') }}" class="bg-amber-100 text-amber-900 font-bold px-3 py-1.5 rounded-lg">POS</a>
                <a href="{{ route('admin.orders') }}" class="bg-gray-100 font-bold px-3 py-1.5 rounded-lg">Kitchen</a>
                <a href="{{ route('menu') }}" class="bg-gray-900 text-white font-bold px-3 py-1.5 rounded-lg">Menu</a>
            </div>
        </header>

        <!-- Flash alerts -->
        @if(session('success'))
            <div class="p-4 bg-green-50 border-b border-green-200 text-green-800 text-xs font-bold flex justify-between items-center">
                <span>[Sukses] {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-green-600">&times;</button>
            </div>
        @endif

        <main class="p-6 sm:p-8 flex-1 overflow-y-auto">
            @yield('admin_content')
        </main>
    </div>

    <script>
        function confirmLogout(event) {
            event.preventDefault();
            const form = event.target.closest('form');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Logout',
                    text: 'Apakah Anda yakin ingin keluar dari Admin Panel?',
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
                if (confirm('Apakah Anda yakin ingin keluar dari Admin Panel?')) {
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
