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
            ->take(8)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->price,
                'final_price' => $p->final_price,
                'is_discount_active' => $p->is_discount_active,
                'discount_percent' => $p->discount_percent,
                'discount_end_at_formatted' => $p->discount_end_at_formatted,
                'discount_remaining_seconds' => $p->discount_remaining_seconds,
                'stock' => $p->stock,
                'thumbnail_url' => $p->thumbnail_url,
                'category' => $p->category?->name,
            ]);

        $categories = Category::withCount('products')->get();

        $reviews = \App\Models\Review::with(['user', 'product'])
            ->latest()
            ->take(6)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'user_name' => $r->user->name,
                'product_name' => $r->product->name,
                'rating' => $r->rating,
                'comment' => $r->comment,
                'created_at' => $r->created_at->format('d M Y'),
            ]);

        return Inertia::render('Home', [
            'products' => $products,
            'categories' => $categories,
            'reviews' => $reviews,
        ]);
    }
}
