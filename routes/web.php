<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/home');
});



Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// THIS IS THE ROUTE FOR OUR PRODUCTS PAGE
 Route::get('/products', function () {
     // Check if the user has a token (simple client-side check, not robust security)
     // A more robust way would be to use middleware if this page *required*
     // server-side authentication before rendering. But since we fetch data
     // via API with a token, this client-side check is a basic guard.
     // For now, we just render the view and let JS handle auth for API calls.
     return view('products.index');
 })->name('products.index'); 

