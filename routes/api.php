<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
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


// Publicly accessible auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes requiring authentication
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);

    // Product Management Routes
    Route::apiResource('products', ProductController::class);
    // This single line ^ creates the following routes:
    // GET       /api/products             (index)   - products.index
    // POST      /api/products             (store)   - products.store
    // GET       /api/products/{product}   (show)    - products.show
    // PUT/PATCH /api/products/{product}   (update)  - products.update
    // DELETE    /api/products/{product}   (destroy) - products.destroy
});