<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Attribute;
use App\Models\ProductVariant;
use App\Models\Product;

class AttributeValue extends Model
{
    use SoftDeletes;

    protected $fillable = ['attribute_id', 'value', 'normalized_value', 'is_active'];

    public function attribute() {
        return $this->belongsTo(Attribute::class);
    }

    public function productVariants() {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_values', 'attribute_value_id', 'product_variant_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attribute_values', 'attribute_value_id', 'product_id');
    }
}
