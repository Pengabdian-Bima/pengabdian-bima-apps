<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $query = Product::with('category')->where('status', true);

        if (request('search')) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }

        if (request('category')) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', request('category'))->orWhere('id', request('category'));
            });
        }

        $products = $query->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn ($p) => [
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
                'weight' => $p->weight,
                'thumbnail_url' => $p->thumbnail_url,
                'category' => $p->category?->name,
            ]);

        $categories = Category::all();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => request()->only(['search', 'category']),
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'images']);

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', true)
            ->take(4)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->price,
                'final_price' => $p->final_price,
                'is_discount_active' => $p->is_discount_active,
                'discount_percent' => $p->discount_percent,
                'stock' => $p->stock,
                'thumbnail_url' => $p->thumbnail_url,
            ]);

        return Inertia::render('Products/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => $product->price,
                'final_price' => $product->final_price,
                'is_discount_active' => $product->is_discount_active,
                'discount_percent' => $product->discount_percent,
                'discount_start_at' => $product->discount_start_at?->toIso8601String(),
                'discount_end_at' => $product->discount_end_at?->toIso8601String(),
                'discount_end_at_formatted' => $product->discount_end_at_formatted,
                'discount_remaining_seconds' => $product->discount_remaining_seconds,
                'stock' => $product->stock,
                'weight' => $product->weight,
                'thumbnail_url' => $product->thumbnail_url,
                'category' => $product->category?->name,
                'images' => $product->images->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => $img->image_url,
                ]),
            ],
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
