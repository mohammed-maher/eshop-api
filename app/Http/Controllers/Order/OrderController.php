<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductWeights;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try{
            $orders=Order::query()
                ->when($request->has('status'),function ($query) use($request){
                   $query->where('status',OrderStatus::getStatusCode($request->status));
                });
        }catch (\Exception $e){

        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOrderRequest $request)
    {
        try {
            DB::beginTransaction();
            $order = Order::create([
                'user_id' => Auth::user()->id,
                'status' => OrderStatus::STATUS_PENDING,
                'total' => 0,
                'is_preorder' => $request->is_preorder ?? false,
            ]);
            $orderTotal = 0;
            foreach ($request->products as $product) {
                $productInfo = Product::findOrFail($product["product_id"]); // retrieve product info
                $weightInfo = ProductWeights::findOrFail($product["weight_id"]); //retrieve weight info
                if ($weightInfo->product->id != $productInfo->id) { // confirm weight belongs to requested product
                    throw new \Exception('product weight mismatch');
                }
                if (!$request->is_preorder && ($product["quantity"] > $weightInfo->stock)) { //check stock while allowing preorders
                    throw new \Exception('insufficient stock to fulfill order');
                }
                $orderTotal += $weightInfo->price * $product["quantity"]; // calculate product total and add it to order total
                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $productInfo->id,
                    'quantity' => $product['quantity'],
                    'product_weight_id' => $weightInfo->id
                ]);
            }
            $order->total = $orderTotal;
            $order->save();
            DB::commit();
            return new OrderResource($order);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'order could not be placed' . $e->getMessage()], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }

    public function deliver()
    {

    }
}
