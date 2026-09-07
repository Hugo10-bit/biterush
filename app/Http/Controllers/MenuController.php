<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display the main menu catalog page.
     */
    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $selectedCategory = $request->query('category', 'all');
        $search = $request->query('q');
        $tableNum = $request->query('table'); // If scanned QR: ?table=Meja 01

        if (\Illuminate\Support\Facades\Schema::hasTable('products')) {
            $query = Product::with(['category', 'reviews.user'])
                ->where('is_available', true);

            if ($selectedCategory !== 'all' && !empty($selectedCategory)) {
                $query->whereHas('category', function ($q) use ($selectedCategory) {
                    $q->where('slug', $selectedCategory);
                });
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $products = $query->orderByDesc('is_featured')->orderBy('name')->get();
            $featuredProducts = Product::with('category')->where('is_featured', true)->where('is_available', true)->take(4)->get();
        } else {
            $products = collect([]);
            $featuredProducts = collect([]);
        }

        $tables = RestaurantTable::where('status', 'available')->get();

        return view('menu.index', compact('categories', 'products', 'featuredProducts', 'selectedCategory', 'search', 'tableNum', 'tables'));
    }

    /**
     * Get single product detail with options for AJAX modal.
     */
    public function show($id)
    {
        $product = Product::with(['category', 'reviews.user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'product' => $product,
        ]);
    }
}
