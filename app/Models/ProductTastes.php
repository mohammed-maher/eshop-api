<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTastes extends Model
{
    use HasFactory;
    protected $fillable=['taste','product_id'];
    public function product(){
        return $this->belongsTo(Product::class);
    }
}
