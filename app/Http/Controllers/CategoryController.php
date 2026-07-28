<?php

namespace App\Http\Controllers;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\SubcategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{   /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories=Category::all();
        return response()->json([
    'categories'=>CategoryResource::collection($categories)
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
    $data=$request->validated();
    $category=Category::Create($data);
    return response()->json([
    'message'=>'category created successfully',
    'category'=>new CategoryResource($category)
    ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
    return response()->json([
    'category'=>new CategoryResource($category)
    ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
    $data=$request->validate(['name'=>'max:25|string|required']);
    $category->update($data);
    return response()->json([
    'message'=>'category updated successfully',
    'category'=>new CategoryResource($category)
    ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json([
                            'message'=>'category deleted successfully'], 200);
    }
    public function categories_with_sub()
    {
    $categories = Category::with('subCategories')->get();
    return response()->json([
        'categories' => $categories
    ], 200);
    }
    public function subcategories_of_category(Category $category)
    {
    $subcategories=$category->subCategories;
    return SubcategoryResource::collection($subcategories);
    }
}
