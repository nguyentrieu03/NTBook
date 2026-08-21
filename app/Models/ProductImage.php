<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'product_variant_id', 'disk', 'path', 'file_name', 'alt_text', 'is_primary', 'sort_order'];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function productVariant() {
        return $this->belongsTo(ProductVariant::class);
    }
}
