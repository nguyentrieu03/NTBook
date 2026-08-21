<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Product;
use App\Models\AttributeValue;

class Attribute extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'is_active'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attributes', 'attribute_id', 'product_id');
    }

    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class);
    }
}
