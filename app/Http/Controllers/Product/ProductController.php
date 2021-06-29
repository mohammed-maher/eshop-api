<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductTastes;
use App\Models\ProductWeights;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ProductCollection
     */
    public function index()
    {
        return new ProductCollection(Product::paginate(15));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreProductRequest $request)
    {
        try {
            // init transaction to ensure data integrity
            DB::beginTransaction();
            // create product record
            $product = Product::create($request->only(['name']));
            // attach product weights
            foreach ($request->weights as $weight) {
                ProductWeights::create([
                    'product_id' => $product->id,
                    'weight' => $weight['weight'],
                    'price' => $weight['price'],
                    'stock' => $weight['stock']
                ]);
            }
            // attach product tastes
            foreach ($request->tastes as $taste) {
                ProductTastes::create([
                    'product_id' => $product->id,
                    'taste' => $taste
                ]);
            }
            DB::commit();
            return new ProductResource($product);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'product could not be created' . $e->getMessage()], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return ProductResource
     */
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        try{
            DB::beginTransaction();
            ProductWeights::where('product_id',$product->id)->delete();
            ProductTastes::where('product_id',$product->id)->delete();
            $product->delete();
            DB::commit();
            return response(null, 204);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json(['error'=>'product could not be deleted'],422);
        }

    }
}
