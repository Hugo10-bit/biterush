<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Admin Analytics Dashboard.
     */
    public function dashboard()
    {
        $todayRevenue = Order::whereDate('created_at', today())->where('payment_status', 'paid')->sum('total_amount');
        $totalOrdersToday = Order::whereDate('created_at', today())->count();
        $pendingOrders = Order::whereIn('status', ['pending', 'confirmed', 'preparing'])->count();
        $occupiedTables = RestaurantTable::where('status', 'occupied')->count();
        $totalTables = RestaurantTable::count();
        $totalCustomers = User::where('role', 'customer')->count();

        $recentOrders = Order::with(['items', 'table', 'user'])->latest()->take(6)->get();

        $topProducts = OrderItem::select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'todayRevenue',
            'totalOrdersToday',
            'pendingOrders',
            'occupiedTables',
            'totalTables',
            'totalCustomers',
            'recentOrders',
            'topProducts'
        ));
    }

    /**
     * Kitchen & Order Management Board.
     */
    public function orders(Request $request)
    {
        $status = $request->query('status', 'all');

        $relations = ['items', 'table', 'user'];
        if (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
            $relations[] = 'payment';
        }
        $query = Order::with($relations)->latest();

        if ($status !== 'all' && in_array($status, ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'])) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(12);

        $counts = [
            'all' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'preparing' => Order::where('status', 'preparing')->count(),
            'ready' => Order::where('status', 'ready')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        return view('admin.orders', compact('orders', 'status', 'counts'));
    }

    /**
     * Update order status (Kitchen Flow).
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,completed,cancelled',
            'payment_status' => 'nullable|in:unpaid,paid,refunded',
        ]);

        $order->status = $request->status;

        if ($request->filled('payment_status')) {
            $order->payment_status = $request->payment_status;
        }

        // If completed or cancelled, free up table
        if (in_array($request->status, ['completed', 'cancelled']) && $order->restaurant_table_id) {
            RestaurantTable::where('id', $order->restaurant_table_id)->update(['status' => 'available']);
        }

        $order->save();

        return back()->with('success', "Status pesanan {$order->order_number} diperbarui menjadi " . ucfirst($order->status) . ".");
    }

    /**
     * Menu & Product Management.
     */
    public function products()
    {
        $products = \Illuminate\Support\Facades\Schema::hasTable('products')
            ? Product::with('category')->latest()->get()
            : collect([]);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.products', compact('products', 'categories'));
    }

    /**
     * Store new product.
     */
    public function storeProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:80',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'calories' => 'nullable|integer',
            'prep_time_minutes' => 'nullable|integer',
            'image' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'is_featured' => 'nullable|boolean',
        ]);

        $imagePath = $request->image ?: 'images/burger-bg.jpg';

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/products'), $filename);
            $imagePath = 'images/products/' . $filename;
        }

        Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . rand(100, 999),
            'category_id' => $request->category_id,
            'price' => $request->price,
            'original_price' => $request->original_price,
            'description' => $request->description,
            'calories' => $request->calories,
            'prep_time_minutes' => $request->prep_time_minutes,
            'image' => $imagePath,
            'is_featured' => $request->boolean('is_featured'),
            'is_available' => true,
        ]);

        return back()->with('success', "Menu '{$request->name}' berhasil ditambahkan!");
    }

    /**
     * Update existing product.
     */
    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:80',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'calories' => 'nullable|integer',
            'prep_time_minutes' => 'nullable|integer',
            'image' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'is_featured' => 'nullable|boolean',
        ]);

        $imagePath = $request->image ?: $product->image;

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/products'), $filename);
            $imagePath = 'images/products/' . $filename;
        }

        $product->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'original_price' => $request->original_price,
            'description' => $request->description,
            'calories' => $request->calories,
            'prep_time_minutes' => $request->prep_time_minutes,
            'image' => $imagePath,
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return back()->with('success', "Menu '{$product->name}' berhasil diperbarui!");
    }

    /**
     * Toggle product availability.
     */
    public function toggleProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->is_available = !$product->is_available;
        $product->save();

        $statusText = $product->is_available ? 'Tersedia' : 'Habis / Nonaktif';
        return back()->with('success', "Status menu {$product->name} diubah menjadi {$statusText}.");
    }

    /**
     * Delete product.
     */
    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return back()->with('success', 'Menu berhasil dihapus.');
    }

    /**
     * Restaurant Table Management.
     */
    public function tables()
    {
        $tables = RestaurantTable::withCount(['orders' => function ($q) {
            $q->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready']);
        }])->orderBy('table_number')->get();

        return view('admin.tables', compact('tables'));
    }

    /**
     * Update table status.
     */
    public function updateTableStatus(Request $request, $id)
    {
        $table = RestaurantTable::findOrFail($id);
        $request->validate(['status' => 'required|in:available,occupied,reserved']);

        $table->status = $request->status;
        $table->save();

        return back()->with('success', "Status {$table->table_number} diperbarui menjadi {$table->status}.");
    }

    /**
     * Coupons Management.
     */
    public function coupons()
    {
        $coupons = Coupon::latest()->get();
        return view('admin.coupons', compact('coupons'));
    }

    /**
     * Store coupon.
     */
    public function storeCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:coupons,code|max:20',
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'required|numeric|min:1',
            'min_spend' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
        ]);

        Coupon::create([
            'code' => strtoupper($request->code),
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'min_spend' => $request->min_spend ?? 0,
            'max_discount' => $request->max_discount,
            'usage_limit' => $request->usage_limit,
            'valid_from' => now(),
            'valid_until' => now()->addMonths(3),
            'is_active' => true,
        ]);

        return back()->with('success', 'Voucher diskon baru berhasil dibuat!');
    }

    /**
     * Toggle coupon active status.
     */
    public function toggleCoupon($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->is_active = !$coupon->is_active;
        $coupon->save();

        return back()->with('success', "Status voucher {$coupon->code} diperbarui.");
    }

    /**
     * Point of Sale (POS) Cashier Screen.
     */
    public function pos()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $products = \Illuminate\Support\Facades\Schema::hasTable('products')
            ? Product::with(['category'])->where('is_available', true)->get()
            : collect([]);
        $tables = RestaurantTable::orderBy('table_number')->get();

        return view('admin.pos', compact('categories', 'products', 'tables'));
    }

    /**
     * POS Instant Checkout.
     */
    public function posCheckout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'order_type' => 'required|in:dine_in,takeaway',
            'restaurant_table_id' => 'nullable|exists:restaurant_tables,id',
            'customer_name' => 'nullable|string',
            'payment_method' => 'required|in:cash,qris,bank_transfer,ewallet',
            'cash_received' => 'nullable|numeric',
        ]);

        return DB::transaction(function () use ($request) {
            $datePrefix = 'BR-' . date('Ymd');
            $countToday = Order::where('order_number', 'like', "{$datePrefix}-%")->count() + 1;
            $orderNumber = $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $orderItemsData = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int)$item['quantity'];
                $itemTotal = (float)$product->price * $qty;
                $subtotal += $itemTotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $qty,
                    'subtotal' => $itemTotal,
                ];
            }

            $tax = round($subtotal * 0.10);
            $total = $subtotal + $tax;

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => Auth::id(),
                'restaurant_table_id' => $request->order_type === 'dine_in' ? $request->restaurant_table_id : null,
                'order_type' => $request->order_type,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_method' => $request->payment_method,
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'discount_amount' => 0,
                'total_amount' => $total,
                'customer_name' => $request->customer_name ?: 'Walk-in Customer',
                'customer_phone' => '-',
            ]);

            foreach ($orderItemsData as $oid) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $oid['product_id'],
                    'product_name' => $oid['product_name'],
                    'price' => $oid['price'],
                    'quantity' => $oid['quantity'],
                    'subtotal' => $oid['subtotal'],
                ]);
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
                Payment::create([
                    'order_id' => $order->id,
                    'transaction_code' => 'POS-' . strtoupper(Str::random(10)),
                    'payment_method' => $request->payment_method,
                    'amount' => $total,
                    'status' => 'success',
                    'payment_details' => [
                        'cashier' => Auth::user()->name ?? 'Kasir',
                        'cash_received' => $request->cash_received ?? $total,
                        'change' => max(0, ($request->cash_received ?? $total) - $total),
                    ],
                    'paid_at' => now(),
                ]);
            }

            if ($order->restaurant_table_id) {
                RestaurantTable::where('id', $order->restaurant_table_id)->update(['status' => 'occupied']);
            }

            return response()->json([
                'success' => true,
                'message' => "Pesanan POS {$order->order_number} berhasil dicatat dan lunas!",
                'order_number' => $order->order_number,
                'redirect' => route('orders.show', $order->order_number),
            ]);
        });
    }
}
