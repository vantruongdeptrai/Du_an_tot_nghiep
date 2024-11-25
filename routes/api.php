<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TagController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\SizeController;
use App\Http\Controllers\API\ColorController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\GalleryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\AttributeController;
use App\Http\Controllers\API\ReplyCommentController;
use App\Http\Controllers\API\PermissionsController;
use App\Http\Controllers\API\DetailProductController;
use App\Http\Controllers\API\OperatingCostController;
use App\Http\Controllers\API\AttributeValueController;
use App\Http\Controllers\API\ProductVariantController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\Api\RevenueController;
use App\Http\Controllers\API\VNPayController;
use App\Http\Controllers\API\vnpayReturn;
use App\Http\Controllers\API\AddressController;
use App\Http\Controllers\API\MomoController;
use App\Http\Controllers\API\ReplyComment;
use App\Http\Controllers\API\StatisticsController;
use App\Http\Controllers\API\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//http://127.0.0.1:8000/api/coupons
Route::get('/coupons', [CouponController::class, 'index']); 

//http://127.0.0.1:8000/api/coupons          
Route::post('/coupons', [CouponController::class, 'store']); 

//http://127.0.0.1:8000/api/coupons/{id}          
Route::get('/coupons/{id}', [CouponController::class, 'show']); 

//http://127.0.0.1:8000/api/coupons/{id}      
Route::put('/coupons/{id}', [CouponController::class, 'update']); 

//http://127.0.0.1:8000/api/coupons/{id}
Route::delete('/coupons/{id}', [CouponController::class, 'destroy']);  

//http://127.0.0.1:8000/api/attributes
Route::get('/attributes', [AttributeController::class, 'index']);  

//http://127.0.0.1:8000/api/attributes  
Route::post('/attributes', [AttributeController::class, 'store']);

//http://127.0.0.1:8000/api/attributes/{id}      
Route::get('/attributes/{id}', [AttributeController::class, 'show']);

//http://127.0.0.1:8000/api/attributes/{id}   
Route::put('/attributes/{id}', [AttributeController::class, 'update']);

//http://127.0.0.1:8000/api/attributes/{id} 
Route::delete('/attributes/{id}', [AttributeController::class, 'destroy']);

Route::get('/tags', [TagController::class, 'index']);
Route::post('/tags', [TagController::class, 'store']);
Route::put('/tags/{id}', [TagController::class, 'update']);
Route::get('/tags/{id}', [TagController::class, 'show']);
Route::delete('/tags/{id}', [TagController::class, 'destroy']);


Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::post('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

Route::get('/operating-costs', [OperatingCostController::class, 'index']);
Route::get('/operating-costs/{id}', [OperatingCostController::class, 'show']);
Route::post('/operating-costs', [OperatingCostController::class, 'store']);
Route::put('/operating-costs/{id}', [OperatingCostController::class, 'update']);
Route::delete('/operating-costs/{id}', [OperatingCostController::class, 'destroy']);

Route::get('/roles', [RoleController::class, 'index']);
//http://127.0.0.1:8000/api/roles

Route::post('/roles', [RoleController::class, 'store']);
//http://127.0.0.1:8000/api/roles

Route::get('/roles/{id}', [RoleController::class, 'show']);
//http://127.0.0.1:8000/api/roles/{id}   

Route::put('/roles/{id}', [RoleController::class, 'update']);
//http://127.0.0.1:8000/api/roles/{id}

Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
//http://127.0.0.1:8000/api/roles/{id}

//http://127.0.0.1:8000/api/colors
Route::get('/colors', [ColorController::class, 'index']); 

//http://127.0.0.1:8000/api/colors/{id}
Route::get('/colors/{id}', [ColorController::class, 'show']);

//http://127.0.0.1:8000/api/colors/{id}
Route::post('/colors', [ColorController::class, 'store']);

//http://127.0.0.1:8000/api/colors
Route::put('/colors/{id}', [ColorController::class, 'update']); 

//http://127.0.0.1:8000/api/colors/{id}
Route::delete('/colors/{id}', [ColorController::class, 'destroy']);

//http://127.0.0.1:8000/api/product-variants
Route::get('/product-variants', [ProductVariantController::class, 'index']);

//http://127.0.0.1:8000/api/product-variants/{id}
Route::get('/product-variants/{id}', [ProductVariantController::class, 'show']); 

//http://127.0.0.1:8000/api/product-variants
Route::post('/product-variants', [ProductVariantController::class, 'store']); 


//http://127.0.0.1:8000/api/product-variants/{id}
Route::put('/product-variants/{id}', [ProductVariantController::class, 'update']);



//http://127.0.0.1:8000/api/product-variants/{id}
Route::delete('/product-variants/{id}', [ProductVariantController::class, 'destroy']);

// Route::get('attribute-values', [AttributeValueController::class, 'index']);

// Route::post('attribute-values', [AttributeValueController::class, 'store']);

// Route::get('attribute-values/{id}', [AttributeValueController::class, 'show']); //lấy theo id của bảng AttributeValues

// Route::get('attribute-values/attribute/{attributeId}', [AttributeValueController::class, 'showByAttributeId']);// lấy theo attribute_id

// Route::put('attribute-values/{id}', [AttributeValueController::class, 'update']);

// Route::delete('attribute-values/{id}', [AttributeValueController::class, 'destroy']);

//size
//http://127.0.0.1:8000/api/sizes
Route::get('sizes',[SizeController::class,'index']);
Route::post('sizes',[SizeController::class,'store']);
Route::get('sizes/{id}',[SizeController::class,'show']);
Route::put('sizes/{id}',[SizeController::class,'update']);
Route::delete('sizes/{id}',[SizeController::class,'destroy']);

//color
//http://127.0.0.1:8000/api/colors
Route::get('colors',[ColorController::class,'index']);
Route::post('colors',[ColorController::class,'store']);
Route::get('colors/{id}',[ColorController::class,'show']);
Route::put('colors/{id}',[ColorController::class,'update']);
Route::delete('colors/{id}',[ColorController::class,'destroy']);


//Products and productVariants

Route::get('products',[ProductController::class,'index']);
//http://127.0.0.1:8000/api/products

Route::post('products',[ProductController::class,'store']);
//http://127.0.0.1:8000/api/products

Route::delete('products/{id}',[ProductController::class,'destroy']);
//http://127.0.0.1:8000/api/products/id

Route::put('/products/{id}', [ProductController::class, 'update']);



Route::get('product/{id}', [ProductController::class, 'show']);
//http://127.0.0.1:8000/api/product/id

Route::put('product/{id}', [ProductController::class, 'update']);
//http://127.0.0.1:8000/api/product/id

Route::get('products/newproduct', [ProductController::class, 'newproduct']);
//http://127.0.0.1:8000/api/products/newproduct

Route::get('products/bestproduct', [ProductController::class, 'bestproduct']);
//http://127.0.0.1:8000/api/products/bestproduct

Route::get('products/featuredproduct', [ProductController::class, 'featuredproduct']);
//http://127.0.0.1:8000/api/products/featuredproduct


Route::get('galleries/', [GalleryController::class, 'index']);
//http://127.0.0.1:8000/api/galleries

Route::post('galleries/', [GalleryController::class, 'store']);
//http://127.0.0.1:8000/api/galleries

Route::delete('galleries/{id}', [GalleryController::class, 'destroy']);
//http://127.0.0.1:8000/api/galleries/{id}

Route::put('galleries/{id}', [GalleryController::class, 'update']);
//http://127.0.0.1:8000/api/galleries/{id}


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
Route::post('/register', [AuthController::class, 'register']);

//http://127.0.0.1:8000/api/comments
Route::get('products/{id}/comments', [CommentController::class, 'index']);

//http://127.0.0.1:8000/api/comments
Route::post('products/{id}/comments', [CommentController::class, 'store']);

//http://127.0.0.1:8000/api/comments/{id}

//http://127.0.0.1:8000/api/comments/{id}
Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

Route::get('comments/{id}/replies', [ReplyCommentController::class, 'index']);
Route::post('comments/{id}/replies', [ReplyCommentController::class, 'store']);
    
    // Cập nhật reply
Route::put('replies/{id}', [ReplyCommentController::class, 'update']);
    
    // Xóa reply
    Route::delete('replies/{id}', [ReplyCommentController::class, 'destroy']);

//http://127.0.0.1:8000/api/blogs
Route::get('blogs', [BlogController::class, 'index']);

// http://127.0.0.1:8000/api/blogs
Route::post('blogs', [BlogController::class, 'store']);

// http://127.0.0.1:8000/api/blogs/{id}
Route::get('blogs/{id}', [BlogController::class, 'show']);

// http://127.0.0.1:8000/api/blogs/{id}
Route::put('blogs/{id}', [BlogController::class, 'update']);

// http://127.0.0.1:8000/api/blogs/{id}
Route::delete('blogs/{id}', [BlogController::class, 'destroy']);


Route::get('products/filter', [ProductController::class, 'filterProducts']);

// Route cho người đã đăng nhập (giỏ hàng lưu trong database)
Route::get('/cart/auth', [CartController::class, 'getCartUser']);
// http://127.0.0.1:8000/api/cart/auth
// // Route cho người chưa đăng nhập (giỏ hàng tạm thời bằng token)
Route::middleware([\Illuminate\Session\Middleware\StartSession::class])->get('/cart/guest', [CartController::class, 'getCart']);
// http://127.0.0.1:8000/api/cart/guest

// Route cho người dùng đã đăng nhập
Route::post('/cart/add', [CartController::class, 'addToCart']);

// Route cho người dùng chưa đăng nhập
Route::middleware([\Illuminate\Session\Middleware\StartSession::class])->post('/cart/add/guest', [CartController::class, 'addToCartGuest']);

Route::delete('/delete/{id}', [CartController::class, 'destroy']); // Route xóa mềm giỏ hàng
Route::middleware([\Illuminate\Session\Middleware\StartSession::class])->delete('/cart/remove/{id}', [CartController::class, 'removeFromCartGuest']);

Route::put('/cart/update', [CartController::class, 'updateCart']);

// http://127.0.0.1:8000/api/oder/login
Route::post('/oder/login', [OrderController::class, 'PaymentLogin']);
// http://127.0.0.1:8000/api/oder/no-login
Route::middleware([\Illuminate\Session\Middleware\StartSession::class])->post('/oder/no-login', [OrderController::class, 'PaymentNoLogin']);
Route::get('/orders', [OrderController::class, 'index']);//http:127.0.0.1:8000/api/orders
Route::get('/orders/{id}', [OrderController::class, 'getOrderById']);//http:127.0.0.1:8000/api/orders/id

Route::put('/orders/{id}', [OrderController::class, 'updateOrder']); //http:127.0.0.1:8000/api/orders/id

Route::delete('/orders/{id}', [OrderController::class, 'deleteOrder']); //http:127.0.0.1:8000/api/orders/id


Route::post('/orders/cancel/{id}', [OrderController::class, 'cancelOrder']);  //http://127.0.0.1:8000/api/orders/cancel/{id}


//http://127.0.0.1:8000/api/revenue/year?year={năm}
Route::get('revenue/year', [RevenueController::class, 'revenueByYear']);//Thong ke doanh thu theo nam
//http://127.0.0.1:8000/api/revenue/months?year=2024
Route::get('revenue/months', [RevenueController::class, 'revenueByMonths']);//Thống kê doanh thu theo Tháng
//http://127.0.0.1:8000/api/revenue/day?date=y-m-d
Route::get('revenue/day', [RevenueController::class, 'revenueByDay']);//Thống kê doanh thu theo ngày
//http://127.0.0.1:8000/api/products/best-sellers
Route::get('/products/best-sellers', [ProductController::class, 'bestSellers']);
//http://127.0.0.1:8000/api/total-revenue
Route::get('/total-revenue', [RevenueController::class, 'getTotalRevenue']);
//http://127.0.0.1:8000/api/products/out-of-stock
Route::get('products/out-of-stock', [ProductController::class, 'getOutOfStockProducts']);
//http://127.0.0.1:8000/api/revenue-by-category
Route::get('/revenue-by-category', [RevenueController::class, 'getRevenueByCategory']);
Route::get('/revenue/sold-products', [RevenueController::class, 'getSoldProductsCount']);



Route::get('/search', [ProductController::class, 'searchProduct']);


Route::get('/filter',[ProductController::class,'filterProducts'] );




Route::post('/create-payment', [VNPayController::class, 'createPayment']); //http://127.0.0.1:8000/api/create-payment
Route::get('/handle-ipn', [VNPayController::class, 'handleIPN']);

//http://127.0.0.1:8000/api/users
Route::get('users', [UserController::class, 'index']); 
//http://127.0.0.1:8000/api/users/{id}
Route::get('users/{id}', [UserController::class, 'show']); 
//http://127.0.0.1:8000/api/users
Route::post('users', [UserController::class, 'store']); 
//http://127.0.0.1:8000/api/users/{id}
Route::put('users/{id}', [UserController::class, 'update']);
//http://127.0.0.1:8000/api/users/{id}
Route::delete('users/{id}', [UserController::class, 'destroy']);


Route::get('/addresses', [AddressController::class, 'getAllData']);

Route::post('/addresses', [AddressController::class, 'addAddresses']);

Route::put('/addresses/{id}', [AddressController::class, 'updateAddress']);

Route::delete('/addresses/{id}', [AddressController::class, 'deleteAddress']);