<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'created_at' => (string)$this->created_at,
            'updated_at' => (string)$this->updated_at,
            'weights' => $request->weights,
            'tastes' => $request->tastes
        ];
    }
}
