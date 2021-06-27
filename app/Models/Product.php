<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function tastes()
    {
        return $this->hasMany(ProductTastes::class);
    }

    public function weights()
    {
        return $this->hasMany(ProductWeights::class);
    }

    public function getWeightsAttribute()
    {
        return $this->weights();
    }
}
