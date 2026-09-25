<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageProducrRequest;
use App\Http\Requests\ProducrRequest;
use App\Http\Requests\UpdateProducrRequest;
use App\Http\Resources\ImageProductResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Productimage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::query();


        if (!auth()->check() || auth()->user()->role !== 'admin') {
            $query->where('status', 'active');
        }

        //search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");

                if (auth()->check() && auth()->user()->role === 'admin') {
                    $q->orWhere('sku', 'like', "%{$search}%");
                }
            });
        }
        //filtering
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('subcategory_id')) {

            $query->where('subcategory_id', $request->subcategory_id);
        }

        if ($request->filled('discount')) {

            $query->where('discount', '>', 0);
        }
        if ($request->filled('min_price')) {

            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {

            $query->where('price', '<=', $request->max_price);
        }
        if (
            auth()->check()
            && auth()->user()->role === 'admin'
            && $request->filled('status')
        ) {

            $query->where('status', $request->status);
        }

        //sort
        $allowedSorts = [
            'price',
            'name',
            'stock',
            'created_at'
        ];
        
        if ($request->filled('sort')) {
            $sort = strtolower($request->sort);
            $direction = strtolower($request->direction ?? 'asc');

            if (!in_array($direction, ['asc', 'desc'])) {

                $direction = 'asc';
            }
            if (in_array($sort, $allowedSorts)) {

                $query->orderBy($sort, $direction);
            }
        } else {

            $query->latest();
        }


        return ProductResource::collection($query->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProducrRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('covers', 'public');
            $data['cover_image'] = $path;
        }
        $product = Product::create($data);
        return response()->json([
            'message' => 'product created successfully',
            'product' => new ProductResource($product)
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json([
            'product' => new ProductResource($product)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProducrRequest $request, Product $product)
    {
        $data = $request->validated();
        if ($request->hasFile('cover_image')) {
            if ($product->cover_image) {
                Storage::disk('public')->delete($product->cover_image);
            }
            $path = $request->file('cover_image')->store('covers', 'public');
            $data['cover_image'] = $path;
        }
        $product->update($data);
        return response()->json([
            'message' => 'product updated successfully',
            'product' => new ProductResource($product)
        ], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->cover_image) {
            Storage::disk('public')->delete($product->cover_image);
        }
        $product->delete();
        return response()->json([
            'message' => 'product deleted successfully'
        ], 200);
    }
    public function addProductImage(ImageProducrRequest $request, Product $product)
    {
        if ($product->productImages()->count() + 1 >= 6) {
            return response()->json(['message' => 'you cannot add more than 6 images'], 403);
        }
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('product_images', 'public');
            $data['image'] = $path;
        }
        $product->productImages()->create($data);
        $images = $product->productImages()->get();
        return response()->json([
            'message' => 'image added successfully',
            'image' => ImageProductResource::collection($images)
        ], 201);
    }
    public function deleteProductImage(Productimage $image)
    {
        if ($image->image) {
            Storage::disk('public')->delete($image->image);
        }
        $image->delete();
        return response()->json([
            'message' => 'image deleted successfully'
        ], 200);
    }
    public function showProductImages(Product $product)
    {
        if ($product->status === 'inactive' && auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'you cannot show this product'
            ], 403);
        }
        $images = $product->productImages()->get();
        return response()->json([
            'product_images' => ImageProductResource::collection($images)
        ], 200);
    }

    public function showProductImage(Productimage $image)
    {
        $product = $image->product;
        if ($product->status === 'inactive' && auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'you cannot show this image'
            ], 403);
        }
        return response()->json([
            'product_image' => new ImageProductResource($image)
        ], 200);
    }


    public function showActiveProduct(Product $product)
    {
        if ($product->status === 'inactive') {
            return response()->json([
                'message' => 'you cannot show this product'
            ], 403);
        }
        $product_image = $product->productImages;
        return response()->json([
            'product' => new ProductResource($product),
            'product_images' => ImageProductResource::collection($product_image),
        ], 200);
    }
    public function showActiveProducts()
    {
        $products = Product::where('status', 'active')->paginate(10);
        return ProductResource::collection($products);
    }
    public function showInactiveProducts()
    {
        $products = Product::where('status', 'inactive')->paginate(10);
        return ProductResource::collection($products);
    }

}
