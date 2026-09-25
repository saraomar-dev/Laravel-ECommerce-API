<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CatrgoryController;
use App\Http\Controllers\DashboadController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymobWebhookController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\UserController;
use App\Models\Order;
use App\Services\PaymobService;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
/*auth routes*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

/*user mangment routes*/
Route::apiResource('users', UserController::class)->middleware(['auth:sanctum', 'isAdmin']);


/*profile routes*/
Route::apiResource('profiles', ProfileController::class)->middleware('auth:sanctum');
Route::get('/showProfile', [ProfileController::class, 'showProfile'])->middleware('auth:sanctum');
Route::post('addImage', [ProfileController::class, 'addImage'])->middleware('auth:sanctum');
Route::post('addPhone', [ProfileController::class, 'addphone'])->middleware('auth:sanctum');
Route::patch('/editProfile', [ProfileController::class, 'editProfile'])->middleware('auth:sanctum');
Route::post('/addAddress', [ProfileController::class, 'addAdress'])->middleware('auth:sanctum');

/*category routes*/
Route::get('category/subcategories', [CategoryController::class, 'categories_with_sub']);
Route::get('category/{category}/subcategory', [CategoryController::class, 'subcategories_of_category']);
Route::apiResource('categories', CategoryController::class)->only(['store', 'destroy', 'update'])->middleware(['auth:sanctum', 'isAdmin']);
Route::apiResource('categories', CategoryController::class)->only(['show', 'index']);

/*sub-category routes*/
//Route::get('subcategory/{subcategory}/products', [CategoryController::class, 'products_of_sub']);
Route::apiResource('subcategories', SubcategoryController::class)->only(['store', 'destroy', 'update'])->middleware(['auth:sanctum', 'isAdmin']);
Route::apiResource('subcategories', SubcategoryController::class)->only(['show']);
Route::get('subcategory/{subcategory}/products/active', [SubcategoryController::class, 'active_products_of_sub']);
Route::get('subcategory/{subcategory}/products/all', [SubcategoryController::class, 'all_products_of_sub'])->middleware(['auth:sanctum', 'isAdmin']);
Route::get('subcategory/{subcategory}/products/inactive', [SubcategoryController::class, 'inactive_products_of_sub'])->middleware(['auth:sanctum', 'isAdmin']);;

/*products routes*/
Route::apiResource('products', ProductController::class)->only(['store', 'destroy', 'update', 'show'])->middleware(['auth:sanctum', 'isAdmin']);
Route::apiResource('products', ProductController::class)->only(['index']);
Route::post('products/{product}/image', [ProductController::class, 'addProductImage'])->middleware(['auth:sanctum', 'isAdmin']);
Route::delete('products/image/{image}', [ProductController::class, 'deleteProductImage'])->middleware(['auth:sanctum', 'isAdmin']);
Route::get('products/{product}/images', [ProductController::class, 'showProductImages']);
Route::get('products/image/{image}', [ProductController::class, 'showProductImage']);
Route::get('products/{product}/active', [ProductController::class, 'showActiveProduct']);
Route::get('active', [ProductController::class, 'showActiveProducts']);
Route::get('inactive', [ProductController::class, 'showInactiveProducts'])->middleware(['auth:sanctum', 'isAdmin']);
Route::get('index/admin', [ProductController::class, 'index'])->middleware(['auth:sanctum', 'isAdmin']);

/*cart routes*/
Route::apiResource('carts', CartController::class)->only(['store', 'destroy', 'index'])->middleware(['auth:sanctum', 'isUser']);
Route::get('carts/clear', [CartController::class, 'clear'])->middleware(['auth:sanctum', 'isUser']);
Route::post('carts/checkout', [CartController::class, 'checkout'])->middleware(['auth:sanctum', 'isUser']);
Route::post('/paymob/callback', [PaymobWebhookController::class, 'handleCallback']);
Route::get('/paymob/checkout-return', [PaymobWebhookController::class, 'checkoutReturn']);

/*order routes*/
Route::apiResource('orders', OrderController::class)->only(['index', 'update', 'show'])->middleware(['auth:sanctum', 'isAdmin']);
Route::get('order/my-orders', [OrderController::class, 'my_orders'])->middleware(['auth:sanctum', 'isUser']);
Route::get('order/{id}', [OrderController::class, 'my_order'])->middleware(['auth:sanctum', 'isUser']);


/*dashboard routes*/
Route::get('dashboard', [DashboadController::class, 'view'])->middleware(['auth:sanctum', 'isAdmin']);
