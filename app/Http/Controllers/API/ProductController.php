<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use Illuminate\Support\Facades\Validator;


class ProductController extends Controller
{

    /**
     * Apply auth middleware to all methods in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $products = Product::all();
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'status' => 'sometimes|string|in:in stock,out of stock', // 'sometimes' means it's optional
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400); // Bad Request
        }

        $productData = $validator->validated(); // Get validated data

        // Set default status if not provided
        if (!isset($productData['status'])) {
            $productData['status'] = 'in stock';
        }

        $product = Product::create($productData);

        return response()->json([
            'message' => 'Product successfully created',
            'product' => $product
        ], 201); // 201 Created
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255', 
            'price' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:0',
            'status' => 'sometimes|required|string|in:in stock,out of stock',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400); // Bad Request
        }

        $product->update($validator->validated()); // Update with validated data

        return response()->json([
            'message' => 'Product successfully updated',
            'product' => $product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product successfully deleted'
        ], 200); 
    }
}
