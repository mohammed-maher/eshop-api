<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliverOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductWeights;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        try {
            $orders = Order::query()
                ->when($request->has('status'), function ($query) use ($request) {
                    $query->where('status', OrderStatus::getStatusCode($request->status)); //filter orders by status if status is passed in request
                });
            return new OrderCollection($orders->get());
        } catch (\Exception $e) {
            return response()->json(['error' => 'could not retrieve records'], 422);
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
            // create order
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
            return response()->json(['error' => 'order could not be placed'], 422);
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
        return new OrderResource($order);
    }

    /**
     * Deliver the specified resource.
     *
     * @param \App\Models\Order $order
     * @param \App\Http\Requests\DeliverOrderRequest $request
     * @return OrderResource
     */
    public function deliverOrder(int $orderId, DeliverOrderRequest $request)
    {
        try {
            $order = Order::findOrFail($orderId);
            if ($order->status != OrderStatus::STATUS_PENDING) {
                return response()->json(['error' => 'order is already scheduled for delivery']);
            }
            $order->status = OrderStatus::STATUS_DELIVERING;
            $order->total = $order->total + $this->computeDeliveryCharge($request->delivery_address);
            $order->delivery_address = $request->delivery_address;
            $order->delivery_number = $request->delivery_number;
            $this->updateProductQty($order);
            $order->save();
            return new OrderResource($order);
        } catch (\Exception $e) {
            return response()->json(['error' => 'order could not be processed']);
        }
    }

    /**
     * Calculate the shipping fees.
     *
     * @param string
     * @return int
     */
    private function computeDeliveryCharge(string $address): int
    {
        // if address is inside dhaka 60 otherwise 100
        if (Str::contains(strtolower($address), 'dhaka')) {
            return 60;
        }
        return 100;
    }

    /**
     * Mark order as completed.
     *
     * @param integer orderId
     * @return JsonResponse
     */
    public function completeOrder(int $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            if ($order->status != OrderStatus::STATUS_DELIVERING) {
                throw new \Exception('invalid order status');
            }
            $order->status = OrderStatus::STATUS_COMPLETED;
            $order->save();
            return new OrderResource($order);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'order not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'order could not be marked as completed'], 422);
        }
    }

    /**
     * Mark order as cancelled.
     *
     * @param integer orderId
     * @return JsonResponse
     */
    public function cancelOrder(int $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            // both pending and delivering orders should be cancellable
            if (!($order->status == OrderStatus::STATUS_DELIVERING ||
                $order->status == OrderStatus::STATUS_PENDING)) {
                throw new \Exception('invalid order status');
            }
            $order->status = OrderStatus::STATUS_CANCELLED;
            $this->updateProductQty($order, false);
            $order->save();
            return new OrderResource($order);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'order not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'order could not be marked as canceled'], 422);
        }
    }

    /**
     * Update order product quantities
     * @param Order $order
     * @param bool $isDeliverySuccessful
     */
    private function updateProductQty(Order $order, bool $isDeliverySuccessful = true): void
    {
        foreach ($order->order_products as $product) {
            $newStock = $this->calculateNewStock((int)$product->weight->stock, (int)$product->quantity, $isDeliverySuccessful);
            $product->weight->update([
                'stock' => $newStock
            ]);
        }
    }

    private function calculateNewStock(int $oldStock, int $orderQty, bool $isDeliverySuccessful = true): int
    {
        if ($isDeliverySuccessful) {
            return $oldStock - $orderQty;
        }
        return $oldStock + $orderQty;
    }
}
