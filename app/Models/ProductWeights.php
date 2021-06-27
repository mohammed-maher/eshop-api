<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductWeights extends Model
{
    use HasFactory;

    protected $fillable = ['weight', 'stock', 'price', 'product_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
