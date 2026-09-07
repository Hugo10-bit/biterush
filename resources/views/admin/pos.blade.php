<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS Kasir - BiteRush</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
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
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="h-full bg-gray-100 flex flex-col antialiased overflow-hidden">

    <!-- TOP BAR -->
    <header class="bg-bites-dark text-white px-6 py-3 flex items-center justify-between flex-shrink-0 shadow-md">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/logo.png') }}" alt="BiteRush" class="h-8 w-auto">
            </a>
            <div class="h-5 w-px bg-white/20"></div>
            <span class="bg-amber-400 text-black font-black text-xs px-2.5 py-0.5 rounded uppercase tracking-wider">
                POINT OF SALE
            </span>
            <span class="text-xs text-gray-300">Kasir: <strong>{{ Auth::user()->name }}</strong></span>
        </div>

        <div class="flex items-center gap-3 text-xs">
            <span id="pos-clock" class="font-mono font-bold text-yellow-300">00:00:00</span>
            <a href="{{ route('admin.orders') }}" class="bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg font-bold transition">
                Kitchen Board
            </a>
            <a href="{{ route('admin.dashboard') }}" class="bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg font-bold transition">
                Admin
            </a>
        </div>
    </header>

    <!-- POS WORKSPACE -->
    <div class="flex-1 flex overflow-hidden">
        
        <!-- LEFT: PRODUCTS GRID -->
        <div class="flex-1 flex flex-col bg-gray-100 p-4 overflow-hidden border-r border-gray-300">
            
            <!-- Category Filter Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-3 flex-shrink-0">
                <button onclick="filterPosCategory('all')" class="pos-cat-btn active bg-gray-900 text-white font-extrabold text-xs px-4 py-2 rounded-xl whitespace-nowrap shadow-xs" data-cat="all">
                    Semua ({{ $products->count() }})
                </button>
                @foreach($categories as $cat)
                    <button onclick="filterPosCategory('{{ $cat->id }}')" class="pos-cat-btn bg-white hover:bg-gray-200 text-gray-800 font-extrabold text-xs px-4 py-2 rounded-xl whitespace-nowrap shadow-xs border border-gray-200" data-cat="{{ $cat->id }}">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>

            <!-- Products Touch Grid -->
            <div class="flex-1 overflow-y-auto grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 pr-1 content-start" id="pos-products-grid">
                @forelse($products as $p)
                    <div onclick="addPosItem({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->price }}, '{{ asset($p->image ?: 'images/burger-bg.jpg') }}')" 
                         data-cat-id="{{ $p->category_id }}"
                         class="pos-product-card bg-white rounded-xl border border-gray-200 hover:border-bites-orange shadow-xs hover:shadow-md cursor-pointer transition select-none active:scale-95 overflow-hidden">
                        
                        <img src="{{ asset($p->image ?: 'images/burger-bg.jpg') }}" alt="{{ $p->name }}" class="w-full h-20 object-cover">
                        
                        <div class="p-2">
                            <h4 class="font-bold text-[11px] text-gray-900 line-clamp-2 leading-snug">{{ $p->name }}</h4>
                            <div class="font-black text-xs text-bites-red mt-0.5">Rp {{ number_format($p->price, 0, ',', '.') }}</div>
                        </div>

                    </div>
                @empty
                    <div class="col-span-full py-16 text-center text-gray-400">
                        <div class="font-bold text-sm mb-1">[Belum Ada Menu]</div>
                        <p class="text-xs">Daftar produk/menu belum tersedia di sistem.</p>
                    </div>
                @endforelse
            </div>

        </div>

        <!-- RIGHT: ACTIVE REGISTER & CHECKOUT -->
        <div class="w-96 bg-white flex flex-col justify-between shadow-2xl flex-shrink-0">
            
            <!-- Order Header Settings -->
            <div class="p-4 bg-gray-50 border-b border-gray-200 space-y-3 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <h3 class="font-black text-sm text-gray-900">Pesanan Aktif</h3>
                    <button onclick="clearPosCart()" class="text-red-500 hover:text-red-700 text-xs font-bold">Kosongkan</button>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <label class="border-2 rounded-xl p-2 text-center font-bold cursor-pointer transition border-bites-orange bg-amber-50" id="pos-type-dine_in">
                        <input type="radio" name="pos_order_type" value="dine_in" checked onchange="setPosOrderType('dine_in')" class="sr-only">
                        Dine In
                    </label>
                    <label class="border-2 border-gray-200 rounded-xl p-2 text-center font-bold cursor-pointer transition" id="pos-type-takeaway">
                        <input type="radio" name="pos_order_type" value="takeaway" onchange="setPosOrderType('takeaway')" class="sr-only">
                        Takeaway
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <select id="pos-table-id" class="w-full bg-white border border-gray-300 rounded-xl p-2 text-xs font-bold text-gray-800">
                        <option value="">Pilih Meja</option>
                        @foreach($tables as $tbl)
                            <option value="{{ $tbl->id }}">{{ $tbl->table_number }}</option>
                        @endforeach
                    </select>

                    <input type="text" id="pos-customer-name" placeholder="Nama Pelanggan" class="w-full bg-white border border-gray-300 rounded-xl p-2 text-xs font-semibold focus:outline-none">
                </div>
            </div>

            <!-- Items List -->
            <div id="pos-items-container" class="flex-1 overflow-y-auto p-4 space-y-2 text-xs">
                <div class="text-center py-16 text-gray-400">
                    <div class="font-bold text-sm mb-1">[Kosong]</div>
                    <p class="font-bold">Klik menu untuk menambahkan</p>
                </div>
            </div>

            <!-- Bottom Totals & Payment -->
            <div class="p-4 bg-gray-50 border-t border-gray-200 space-y-3 flex-shrink-0">
                <div class="space-y-1 text-xs text-gray-600">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span id="pos-subtotal" class="font-bold text-gray-900">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Pajak (10%):</span>
                        <span id="pos-tax" class="font-bold text-gray-900">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-base font-black text-gray-900 pt-1 border-t border-gray-200">
                        <span>Total Tagihan:</span>
                        <span id="pos-total" class="text-bites-red text-lg font-black">Rp 0</span>
                    </div>
                </div>

                <!-- Payment Method & Cash Received -->
                <div class="space-y-2 pt-2 border-t border-gray-200 text-xs">
                    <div class="grid grid-cols-2 gap-2 font-bold">
                        <label class="border-2 border-bites-orange bg-amber-50 rounded-xl p-2 text-center cursor-pointer" id="pos-pay-cash">
                            <input type="radio" name="pos_pay_method" value="cash" checked onchange="setPosPayMethod('cash')" class="sr-only">
                            Tunai / Cash
                        </label>
                        <label class="border-2 border-gray-200 rounded-xl p-2 text-center cursor-pointer" id="pos-pay-qris">
                            <input type="radio" name="pos_pay_method" value="qris" onchange="setPosPayMethod('qris')" class="sr-only">
                            QRIS
                        </label>
                    </div>

                    <div id="pos-cash-input-wrap">
                        <label class="block text-[10px] font-bold text-gray-500 mb-0.5">Uang Diterima (Rp):</label>
                        <input type="number" id="pos-cash-received" oninput="calculatePosChange()" placeholder="0" class="w-full bg-white border border-gray-300 rounded-xl p-2 text-xs font-black text-gray-900 focus:outline-none">
                        <div class="flex justify-between text-[11px] font-bold text-gray-700 mt-1">
                            <span>Kembalian:</span>
                            <span id="pos-change" class="text-green-700 font-black">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Process Button -->
                <button onclick="submitPosOrder()" class="w-full bg-bites-yellow hover:bg-bites-yellow-dark text-bites-dark font-black py-3.5 rounded-2xl shadow-lg transition active:scale-98 text-sm flex items-center justify-center gap-2">
                    <span>Bayar &amp; Proses Pesanan</span>
                </button>
            </div>

        </div>

    </div>

    <!-- POS QRIS PAYMENT MODAL -->
    <div id="pos-qris-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-xs hidden">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 text-center shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                <div class="text-left">
                    <span class="font-black text-sm text-gray-900 block">Pembayaran QRIS Kasir</span>
                    <span class="text-[11px] text-gray-500 font-medium">Merchant: Hugo (NMID: ID1026551937497)</span>
                </div>
                <button onclick="document.getElementById('pos-qris-modal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center font-bold text-lg transition">&times;</button>
            </div>
            
            <!-- 5 MINUTE COUNTDOWN TIMER BADGE -->
            <div class="inline-flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 px-4 py-1.5 rounded-full text-xs font-bold shadow-xs">
                <svg class="w-4 h-4 text-red-600 animate-spin" style="animation-duration: 3s;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2" stroke-dasharray="32" stroke-dashoffset="10"></circle>
                </svg>
                <span>Sisa Waktu Pembayaran: <strong id="pos-qris-timer-display" class="font-mono text-red-800 text-sm">05:00</strong></span>
            </div>

            <!-- QRIS IMAGE BOX -->
            <div class="relative bg-white p-2 rounded-2xl border-2 border-gray-200 inline-block shadow-sm w-full max-w-xs mx-auto">
                <img src="{{ asset('images/qris.png') }}" alt="QRIS Hugo" class="w-full h-auto max-h-[360px] mx-auto object-contain rounded-xl">
                <div id="pos-qris-expired-overlay" class="absolute inset-0 bg-white/90 backdrop-blur-xs rounded-2xl flex-col items-center justify-center p-4 hidden">
                    <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-bold text-xl mb-2">!</div>
                    <h4 class="font-bold text-sm text-gray-900 mb-1">Waktu Pembayaran Habis</h4>
                    <p class="text-xs text-gray-500 mb-3">Sesi QRIS 5 menit telah berakhir.</p>
                    <button type="button" onclick="restartPosQrisTimer()" class="bg-gray-900 text-white font-bold text-xs px-4 py-2 rounded-xl">Muat Ulang Timer</button>
                </div>
            </div>

            <div class="bg-amber-50 rounded-2xl p-3 border border-amber-200/70 text-left space-y-1">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-600 font-medium">Total Pembayaran:</span>
                    <span id="pos-qris-amount" class="text-bites-red font-black text-base">Rp 0</span>
                </div>
                <p class="text-[11px] text-gray-500">
                    Arahkan pelanggan untuk scan barcode QRIS Hugo di atas menggunakan GoPay, OVO, Dana, ShopeePay, BCA, atau m-Banking lainnya.
                </p>
            </div>

            <button onclick="executePosCheckout()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold text-xs py-3.5 rounded-xl shadow-md transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Konfirmasi Lunas &amp; Proses Pesanan</span>
            </button>
        </div>
    </div>

    <!-- POS SCRIPT -->
    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let posCart = [];
        let posOrderType = 'dine_in';
        let posPayMethod = 'cash';

        function updateClock() {
            const now = new Date();
            document.getElementById('pos-clock').innerText = now.toLocaleTimeString('id-ID');
        }
        setInterval(updateClock, 1000);
        updateClock();

        function filterPosCategory(catId) {
            document.querySelectorAll('.pos-cat-btn').forEach(btn => {
                btn.classList.remove('bg-gray-900', 'text-white');
                btn.classList.add('bg-white', 'text-gray-800');
            });
            event.target.classList.add('bg-gray-900', 'text-white');
            event.target.classList.remove('bg-white', 'text-gray-800');

            document.querySelectorAll('.pos-product-card').forEach(card => {
                if (catId === 'all' || card.getAttribute('data-cat-id') === catId) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }

        function setPosOrderType(type) {
            posOrderType = type;
            document.getElementById('pos-type-dine_in').classList.toggle('border-bites-orange', type === 'dine_in');
            document.getElementById('pos-type-dine_in').classList.toggle('bg-amber-50', type === 'dine_in');
            document.getElementById('pos-type-takeaway').classList.toggle('border-bites-orange', type === 'takeaway');
            document.getElementById('pos-type-takeaway').classList.toggle('bg-amber-50', type === 'takeaway');
        }

        function setPosPayMethod(method) {
            posPayMethod = method;
            document.getElementById('pos-pay-cash').classList.toggle('border-bites-orange', method === 'cash');
            document.getElementById('pos-pay-cash').classList.toggle('bg-amber-50', method === 'cash');
            document.getElementById('pos-pay-qris').classList.toggle('border-bites-orange', method === 'qris');
            document.getElementById('pos-pay-qris').classList.toggle('bg-amber-50', method === 'qris');

            const cashWrap = document.getElementById('pos-cash-input-wrap');
            if (cashWrap) {
                if (method === 'qris') {
                    cashWrap.classList.add('hidden');
                } else {
                    cashWrap.classList.remove('hidden');
                }
            }
        }

        function addPosItem(id, name, price, img) {
            const existing = posCart.find(i => i.id === id);
            if (existing) {
                existing.qty++;
            } else {
                posCart.push({ id, name, price, img, qty: 1 });
            }
            renderPosCart();
        }

        function updatePosQty(id, delta) {
            const item = posCart.find(i => i.id === id);
            if (!item) return;
            item.qty += delta;
            if (item.qty <= 0) {
                posCart = posCart.filter(i => i.id !== id);
            }
            renderPosCart();
        }

        function clearPosCart() {
            posCart = [];
            renderPosCart();
        }

        function renderPosCart() {
            const container = document.getElementById('pos-items-container');
            if (posCart.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-16 text-gray-400">
                        <div class="font-bold text-sm mb-1">[Kosong]</div>
                        <p class="font-bold">Klik menu untuk menambahkan</p>
                    </div>`;
                document.getElementById('pos-subtotal').innerText = 'Rp 0';
                document.getElementById('pos-tax').innerText = 'Rp 0';
                document.getElementById('pos-total').innerText = 'Rp 0';
                return;
            }

            let subtotal = 0;
            container.innerHTML = posCart.map(item => {
                const total = item.price * item.qty;
                subtotal += total;
                return `
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-2 flex items-center justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <h5 class="font-bold text-gray-900 truncate">${item.name}</h5>
                            <span class="text-gray-500 text-[11px]">Rp ${item.price.toLocaleString('id-ID')}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="flex items-center border border-gray-300 rounded bg-white overflow-hidden text-xs">
                                <button onclick="updatePosQty(${item.id}, -1)" class="px-1.5 py-0.5 text-gray-600 font-bold">-</button>
                                <span class="px-2 font-bold">${item.qty}</span>
                                <button onclick="updatePosQty(${item.id}, 1)" class="px-1.5 py-0.5 text-gray-600 font-bold">+</button>
                            </div>
                            <span class="font-black text-gray-900 w-16 text-right text-xs">
                                Rp ${total.toLocaleString('id-ID')}
                            </span>
                        </div>
                    </div>
                `;
            }).join('');

            const tax = Math.round(subtotal * 0.10);
            const total = subtotal + tax;

            document.getElementById('pos-subtotal').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('pos-tax').innerText = 'Rp ' + tax.toLocaleString('id-ID');
            document.getElementById('pos-total').innerText = 'Rp ' + total.toLocaleString('id-ID');

            calculatePosChange();
        }

        function calculatePosChange() {
            let subtotal = posCart.reduce((sum, i) => sum + (i.price * i.qty), 0);
            let total = subtotal + Math.round(subtotal * 0.10);
            let cash = parseFloat(document.getElementById('pos-cash-received').value) || 0;
            let change = Math.max(0, cash - total);
            document.getElementById('pos-change').innerText = 'Rp ' + change.toLocaleString('id-ID');
        }

        let posQrisTimerSeconds = 5 * 60;
        let posQrisTimerInterval = null;

        function startPosQrisCountdown() {
            if (posQrisTimerInterval) clearInterval(posQrisTimerInterval);
            posQrisTimerSeconds = 5 * 60;
            const overlay = document.getElementById('pos-qris-expired-overlay');
            if (overlay) {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }

            function tick() {
                const min = Math.floor(posQrisTimerSeconds / 60);
                const sec = posQrisTimerSeconds % 60;
                const display = String(min).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
                const el = document.getElementById('pos-qris-timer-display');
                if (el) el.innerText = display;

                if (posQrisTimerSeconds <= 0) {
                    clearInterval(posQrisTimerInterval);
                    if (overlay) {
                        overlay.classList.remove('hidden');
                        overlay.classList.add('flex');
                    }
                } else {
                    posQrisTimerSeconds--;
                }
            }

            tick();
            posQrisTimerInterval = setInterval(tick, 1000);
        }

        function restartPosQrisTimer() {
            startPosQrisCountdown();
        }

        function submitPosOrder() {
            if (posCart.length === 0) {
                alert('Pilih menu terlebih dahulu!');
                return;
            }

            let subtotal = posCart.reduce((sum, i) => sum + (i.price * i.qty), 0);
            let total = subtotal + Math.round(subtotal * 0.10);

            if (posPayMethod === 'qris') {
                document.getElementById('pos-qris-amount').innerText = 'Rp ' + total.toLocaleString('id-ID');
                document.getElementById('pos-qris-modal').classList.remove('hidden');
                startPosQrisCountdown();
                return;
            }

            executePosCheckout();
        }

        async function executePosCheckout() {
            const items = posCart.map(i => ({ product_id: i.id, quantity: i.qty }));
            const tableId = document.getElementById('pos-table-id').value;
            const custName = document.getElementById('pos-customer-name').value;
            const cash = parseFloat(document.getElementById('pos-cash-received').value) || 0;

            try {
                const res = await fetch('{{ route("admin.pos.checkout") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    body: JSON.stringify({
                        items: items,
                        order_type: posOrderType,
                        restaurant_table_id: tableId || null,
                        customer_name: custName,
                        payment_method: posPayMethod,
                        cash_received: cash
                    })
                });

                const data = await res.json();
                if (data.success) {
                    if (posQrisTimerInterval) clearInterval(posQrisTimerInterval);
                    const qrisModal = document.getElementById('pos-qris-modal');
                    if (qrisModal) qrisModal.classList.add('hidden');
                    alert(data.message);
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Gagal memproses pesanan POS.');
                }
            } catch (e) {
                console.error(e);
            }
        }
    </script>

</body>
</html>
