<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'brand',
        'status',
        'sku',
        'price',
        'discount',
        'stock',
        'subcategory_id',
        'cover_image'
    ];
    public function productImages()
{
    return $this->hasMany(Productimage::class);
}
public function cartItem()
{
    return $this->hasMany(Cartitem::class);
}
public function subCategory()
{
    return $this->belongsTo(Subcategory::class);
}
}
