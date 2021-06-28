<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;
    protected $fillable = ['order_id','product_id','quantity','product_weight_id'];

    public function order(){
        $this->belongsTo(Order::class);
    }

    public function product(){
        $this->belongsTo(Product::class);
    }
}
