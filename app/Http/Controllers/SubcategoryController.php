<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubcategoryRequest;
use App\Http\Requests\UpdatesubRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SubcategoryResource;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubcategoryRequest $request)
    {
        $data=$request->validated();
    $subcategory=Subcategory::Create($data);
    return response()->json([
    'message'=>'sub-category created successfully',
    'sub-category'=>new SubcategoryResource($subcategory)
    ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Subcategory $Subcategory)
    {
        return response()->json([
    'sub-category'=>new SubcategoryResource($Subcategory)
    ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatesubRequest $request, Subcategory $Subcategory)
    {
        $data=$request->validated();
        $Subcategory->update($data);
        return response()->json([
    'message'=>'sub-category updated successfully',
    'sub-category'=>new SubcategoryResource($Subcategory)
    ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subcategory $Subcategory)
    {
        $Subcategory->delete();
        return response()->json([
                            'message'=>'sub-category deleted successfully'], 200);
    }

    public function active_products_of_sub(Subcategory $subcategory)
    {
        $products = $subcategory->products()
                        ->where('status', 'active')
                        ->paginate(10);
        return ProductResource::collection($products);
    }

    public function all_products_of_sub(Subcategory $subcategory)
    {
        $products = $subcategory->products()->paginate(10);
        return ProductResource::collection($products);
    }

    public function inactive_products_of_sub(Subcategory $subcategory)
    {
        $products = $subcategory->products()
                        ->where('status', 'inactive')
                        ->paginate(10);
        return ProductResource::collection($products);
    }
}
