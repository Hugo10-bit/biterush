<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductOptionValue;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get current cart items from session.
     */
    public function getCart()
    {
        $cart = session()->get('cart', []);
        $coupon = session()->get('applied_coupon');

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['total_price'];
        }

        $discount = 0;
        if ($coupon) {
            $c = Coupon::where('code', $coupon['code'])->where('is_active', true)->first();
            if ($c && $c->isValid()) {
                if ($c->discount_type === 'percentage') {
                    $discount = ($subtotal * $c->discount_value) / 100;
                    if ($c->max_discount && $discount > $c->max_discount) {
                        $discount = $c->max_discount;
                    }
                } else {
                    $discount = min($c->discount_value, $subtotal);
                }
            } else {
                session()->forget('applied_coupon');
                $coupon = null;
            }
        }

        $tax = round(($subtotal - $discount) * 0.10); // 10% PB1 tax
        $total = max(0, $subtotal - $discount + $tax);

        return [
            'items' => array_values($cart),
            'item_count' => array_sum(array_column($cart, 'quantity')),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'coupon' => $coupon,
            'tax' => $tax,
            'total' => $total,
        ];
    }

    /**
     * Return JSON for cart drawer/sidebar.
     */
    public function index()
    {
        return response()->json($this->getCart());
    }

    /**
     * Add an item to cart with options.
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
            'options' => 'nullable|array',
            'notes' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->input('quantity', 1);
        $selectedOptions = $request->input('options', []); // array of value IDs
        $notes = $request->input('notes', '');

        // Fetch selected option values to calculate extra cost
        $optionDetails = [];
        $additionalPrice = 0;

        if (!empty($selectedOptions) && \Illuminate\Support\Facades\Schema::hasTable('product_option_values')) {
            $values = ProductOptionValue::with('option')->whereIn('id', $selectedOptions)->get();
            foreach ($values as $val) {
                $optionDetails[] = [
                    'option_id' => $val->product_option_id,
                    'option_name' => $val->option->name ?? 'Option',
                    'value_id' => $val->id,
                    'value_name' => $val->name,
                    'additional_price' => (float)$val->additional_price,
                ];
                $additionalPrice += (float)$val->additional_price;
            }
        }

        $unitPrice = (float)$product->price + $additionalPrice;
        $totalPrice = $unitPrice * $quantity;

        // Unique item key based on product and selected option IDs
        sort($selectedOptions);
        $cartKey = $product->id . '_' . md5(json_encode($selectedOptions) . $notes);

        $cart = session()->get('cart', []);

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
            $cart[$cartKey]['total_price'] = $cart[$cartKey]['quantity'] * $cart[$cartKey]['unit_price'];
        } else {
            $cart[$cartKey] = [
                'cart_key' => $cartKey,
                'product_id' => $product->id,
                'name' => $product->name,
                'image' => $product->image,
                'base_price' => (float)$product->price,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'total_price' => $totalPrice,
                'options' => $optionDetails,
                'notes' => $notes,
            ];
        }

        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => "{$product->name} berhasil ditambahkan ke keranjang!",
            'cart' => $this->getCart(),
        ]);
    }

    /**
     * Update quantity of a cart item.
     */
    public function update(Request $request)
    {
        $request->validate([
            'cart_key' => 'required|string',
            'quantity' => 'required|integer|min:0',
        ]);

        $cart = session()->get('cart', []);
        $cartKey = $request->cart_key;

        if (isset($cart[$cartKey])) {
            if ($request->quantity <= 0) {
                unset($cart[$cartKey]);
            } else {
                $cart[$cartKey]['quantity'] = $request->quantity;
                $cart[$cartKey]['total_price'] = $cart[$cartKey]['quantity'] * $cart[$cartKey]['unit_price'];
            }
            session()->put('cart', $cart);
        }

        return response()->json([
            'success' => true,
            'cart' => $this->getCart(),
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function remove(Request $request)
    {
        $request->validate(['cart_key' => 'required|string']);

        $cart = session()->get('cart', []);
        if (isset($cart[$request->cart_key])) {
            unset($cart[$request->cart_key]);
            session()->put('cart', $cart);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item dihapus dari keranjang.',
            'cart' => $this->getCart(),
        ]);
    }

    /**
     * Apply coupon code.
     */
    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $code = strtoupper(trim($request->code));

        $coupon = Coupon::where('code', $code)->where('is_active', true)->first();

        if (!$coupon || !$coupon->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Kode voucher tidak valid atau sudah kedaluwarsa.',
            ], 422);
        }

        $cartData = $this->getCart();
        if ($cartData['subtotal'] < $coupon->min_spend) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal belanja untuk voucher ini adalah Rp ' . number_format($coupon->min_spend, 0, ',', '.'),
            ], 422);
        }

        session()->put('applied_coupon', [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->discount_type,
            'value' => (float)$coupon->discount_value,
            'min_spend' => (float)$coupon->min_spend,
            'max_discount' => (float)$coupon->max_discount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Voucher {$coupon->code} berhasil digunakan!",
            'cart' => $this->getCart(),
        ]);
    }

    /**
     * Remove applied coupon.
     */
    public function removeCoupon()
    {
        session()->forget('applied_coupon');
        return response()->json([
            'success' => true,
            'message' => 'Voucher dibatalkan.',
            'cart' => $this->getCart(),
        ]);
    }

    /**
     * Clear the whole cart.
     */
    public function clear()
    {
        session()->forget(['cart', 'applied_coupon']);
        return response()->json([
            'success' => true,
            'message' => 'Keranjang dikosongkan.',
            'cart' => $this->getCart(),
        ]);
    }
}
