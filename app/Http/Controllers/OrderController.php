<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Show checkout page.
     */
    public function checkout(Request $request)
    {
        $cartController = new CartController();
        $cart = $cartController->getCart();

        if (empty($cart['items'])) {
            return redirect()->route('menu')->with('error', 'Keranjang belanja Anda masih kosong.');
        }

        $tables = RestaurantTable::orderBy('table_number')->get();
        $selectedTable = $request->query('table');

        return view('orders.checkout', compact('cart', 'tables', 'selectedTable'));
    }

    /**
     * Store new order from checkout.
     */
    public function store(Request $request)
    {
        $cartController = new CartController();
        $cart = $cartController->getCart();

        if (empty($cart['items'])) {
            return redirect()->route('menu')->with('error', 'Keranjang belanja Anda kosong.');
        }

        $request->validate([
            'order_type' => 'required|in:dine_in,takeaway,delivery',
            'customer_name' => 'required|string|max:60',
            'customer_phone' => 'required|string|max:15',
            'restaurant_table_id' => 'nullable|required_if:order_type,dine_in|exists:restaurant_tables,id',
            'delivery_address' => 'nullable|required_if:order_type,delivery|string|max:500',
            'payment_method' => 'required|in:cash,qris,bank_transfer,ewallet',
            'notes' => 'nullable|string|max:500',
        ], [
            'restaurant_table_id.required_if' => 'Silakan pilih nomor meja untuk pesanan Makan di Tempat (Dine In).',
            'delivery_address.required_if' => 'Alamat pengiriman wajib diisi untuk Pesan Antar (Delivery).',
            'customer_name.required' => 'Nama pemesan wajib diisi.',
            'customer_phone.required' => 'Nomor telepon wajib diisi.',
        ]);

        return DB::transaction(function () use ($request, $cart) {
            // Generate order number BR-YYYYMMDD-XXXX
            $datePrefix = 'BR-' . date('Ymd');
            $countToday = Order::where('order_number', 'like', "{$datePrefix}-%")->count() + 1;
            $orderNumber = $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => Auth::id(),
                'restaurant_table_id' => $request->order_type === 'dine_in' ? $request->restaurant_table_id : null,
                'order_type' => $request->order_type,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $request->payment_method,
                'subtotal' => $cart['subtotal'],
                'tax_amount' => $cart['tax'],
                'discount_amount' => $cart['discount'],
                'total_amount' => $cart['total'],
                'notes' => $request->notes,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'delivery_address' => $request->delivery_address,
            ]);

            // Save order items & options
            foreach ($cart['items'] as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['total_price'],
                    'notes' => $item['notes'] ?? null,
                ]);

                if (!empty($item['options']) && \Illuminate\Support\Facades\Schema::hasTable('order_item_options')) {
                    foreach ($item['options'] as $opt) {
                        OrderItemOption::create([
                            'order_item_id' => $orderItem->id,
                            'option_name' => $opt['option_name'],
                            'option_value_name' => $opt['value_name'],
                            'additional_price' => $opt['additional_price'],
                        ]);
                    }
                }
            }

            // Create initial payment record if table exists
            if (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
                Payment::create([
                    'order_id' => $order->id,
                    'transaction_code' => 'PAY-' . strtoupper(Str::random(10)),
                    'payment_method' => $request->payment_method,
                    'amount' => $order->total_amount,
                    'status' => 'pending',
                    'payment_details' => [
                        'created_by' => Auth::check() ? Auth::user()->name : 'Guest',
                        'order_type' => $request->order_type,
                    ],
                ]);
            }

            // If table occupied, update table status
            if ($order->restaurant_table_id) {
                RestaurantTable::where('id', $order->restaurant_table_id)->update(['status' => 'occupied']);
            }

            // If coupon applied, increment usage count
            if ($cart['coupon']) {
                Coupon::where('id', $cart['coupon']['id'])->increment('usage_count');
            }

            // Clear session cart
            session()->forget(['cart', 'applied_coupon']);

            return redirect()->route('orders.show', $order->order_number)->with('success', 'Pesanan Anda berhasil dibuat!');
        });
    }

    /**
     * Show live order tracker & invoice page.
     */
    public function show($order_number)
    {
        $relations = ['items', 'table', 'reviews'];
        if (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
            $relations[] = 'payment';
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('products')) {
            $relations[] = 'items.product';
        }

        $order = Order::with($relations)
            ->where('order_number', $order_number)
            ->firstOrFail();

        return view('orders.show', compact('order'));
    }

    /**
     * Simulate instant payment confirmation (QRIS / Cash / E-Wallet).
     */
    public function pay(Request $request, $order_number)
    {
        $relations = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
            $relations[] = 'payment';
        }

        $order = Order::with($relations)->where('order_number', $order_number)->firstOrFail();

        if ($order->payment_status === 'paid') {
            return back()->with('info', 'Pesanan ini sudah lunas.');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'payment_status' => 'paid',
                'status' => $order->status === 'pending' ? 'confirmed' : $order->status,
            ]);

            if (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
                if ($order->payment) {
                    $order->payment->update([
                        'status' => 'success',
                        'paid_at' => now(),
                    ]);
                } else {
                    Payment::create([
                        'order_id' => $order->id,
                        'transaction_code' => 'PAY-' . strtoupper(Str::random(10)),
                        'payment_method' => $order->payment_method,
                        'amount' => $order->total_amount,
                        'status' => 'success',
                        'paid_at' => now(),
                    ]);
                }
            }
        });

        return back()->with('success', 'Pembayaran berhasil dikonfirmasi! Pesanan Anda segera diproses.');
    }

    /**
     * Customer past orders history.
     */
    public function myOrders()
    {
        $orders = Order::with(['items', 'table'])
            ->where(function ($q) {
                if (Auth::check()) {
                    $q->where('user_id', Auth::id());
                } else {
                    $q->where('id', 0); // empty for unauthenticated
                }
            })
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Submit review and rating for an ordered product.
     */
    public function storeReview(Request $request, $order_number)
    {
        $order = Order::where('order_number', $order_number)->firstOrFail();

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        Review::updateOrCreate(
            [
                'user_id' => Auth::id() ?? 1,
                'order_id' => $order->id,
                'product_id' => $request->product_id,
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

        return back()->with('success', 'Terima kasih atas ulasan dan penilaian Anda!');
    }
}
