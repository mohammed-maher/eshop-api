<?php

namespace App\Http\Resources;

use App\Http\Controllers\Order\OrderStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user' => $this->user,
            'status' => OrderStatus::getStatus($this->status),
            'products' => new OrderProductResourceCollection($this->order_products),
            'delivery_number' => $this->delivery_number,
            'delivery_address'=>$this->delivery_address,
            'total' => $this->total,
            'is_preorder' => $this->is_preorder,
            'updated_at' => (string)$this->updated_at,
            'created_at' => (string)$this->created_at,
        ];
    }
}
