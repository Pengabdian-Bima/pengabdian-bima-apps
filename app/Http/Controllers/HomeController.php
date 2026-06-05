<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->where('status', true)
            ->latest()
            ->take(6)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->price,
                'stock' => $p->stock,
                'thumbnail_url' => $p->thumbnail_url,
                'category' => $p->category?->name,
            ]);

        $categories = Category::withCount('products')->get();

        return Inertia::render('Home', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
