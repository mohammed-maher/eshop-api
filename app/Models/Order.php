<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','total','status','is_preorder','delivery_number','delivery_address'];

    public function products(){
        return $this->belongsToMany(Product::class,'order_products');
    }

    public function order_products(){
        return $this->hasMany(OrderProduct::class,);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
