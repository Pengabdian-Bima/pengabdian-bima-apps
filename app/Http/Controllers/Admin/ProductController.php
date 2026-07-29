<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->latest()
            ->paginate(10)
            ->through(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category?->name,
                'price' => $p->price,
                'cost_price' => $p->cost_price,
                'discount_percent' => $p->discount_percent,
                'is_discount_active' => $p->is_discount_active,
                'final_price' => $p->final_price,
                'stock' => $p->stock,
                'min_stock' => $p->min_stock,
                'status' => $p->status,
                'thumbnail_url' => $p->thumbnail_url,
                'created_at' => $p->created_at->format('d M Y'),
            ]);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
        ]);
    }

    public function create()
    {
        $categories = Category::all();
        return Inertia::render('Admin/Products/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_start_at' => 'nullable|date',
            'discount_end_at' => 'nullable|date|after_or_equal:discount_start_at',
            'is_discount_active' => 'boolean',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'weight' => 'required|numeric|min:0',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'boolean',
        ]);

        $data = $request->except(['thumbnail', 'gallery']);
        $data['slug'] = Str::slug($request->name);
        $data['status'] = $request->boolean('status', true);
        $data['is_discount_active'] = $request->boolean('is_discount_active', true);
        $data['discount_percent'] = $request->discount_percent ?? 0;

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('products', 'public');
        }

        $product = Product::create($data);

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $image->store('products/gallery', 'public'),
                ]);
            }
        }

        // Record initial stock
        if ($product->stock > 0) {
            StockHistory::create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $product->stock,
                'stock_before' => 0,
                'stock_after' => $product->stock,
                'note' => 'Stok awal',
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $product->load('images');

        return Inertia::render('Admin/Products/Edit', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'description' => $product->description,
                'price' => $product->price,
                'cost_price' => $product->cost_price,
                'discount_percent' => $product->discount_percent,
                'discount_start_at' => $product->discount_start_at ? $product->discount_start_at->format('Y-m-d\TH:i') : null,
                'discount_end_at' => $product->discount_end_at ? $product->discount_end_at->format('Y-m-d\TH:i') : null,
                'is_discount_active' => (bool)($product->getRawOriginal('is_discount_active') ?? true),
                'stock' => $product->stock,
                'min_stock' => $product->min_stock,
                'weight' => $product->weight,
                'status' => $product->status,
                'thumbnail_url' => $product->thumbnail_url,
                'images' => $product->images->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => $img->image_url,
                ]),
            ],
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_start_at' => 'nullable|date',
            'discount_end_at' => 'nullable|date|after_or_equal:discount_start_at',
            'is_discount_active' => 'boolean',
            'weight' => 'required|numeric|min:0',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'boolean',
        ]);

        $data = $request->except(['thumbnail', 'gallery', 'stock']);
        $data['slug'] = Str::slug($request->name);
        $data['status'] = $request->boolean('status', true);
        $data['is_discount_active'] = $request->boolean('is_discount_active', true);
        $data['discount_percent'] = $request->discount_percent ?? 0;

        if ($request->hasFile('thumbnail')) {
            if ($product->thumbnail) {
                Storage::disk('public')->delete($product->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('products', 'public');
        }

        $product->update($data);

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $image->store('products/gallery', 'public'),
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui!');
    }

    public function destroy(Product $product)
    {
        if ($product->thumbnail) {
            Storage::disk('public')->delete($product->thumbnail);
        }

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image);
        }

        $product->delete();

        return back()->with('success', 'Produk berhasil dihapus!');
    }

    public function deleteImage(ProductImage $productImage)
    {
        Storage::disk('public')->delete($productImage->image);
        $productImage->delete();

        return back()->with('success', 'Gambar berhasil dihapus.');
    }
}
