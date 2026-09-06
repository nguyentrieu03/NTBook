<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Support\Enums\ProductStatusEnum;
use App\Models\Category;
use App\Models\User;
use App\Models\ProductVariant;
use App\Models\ProductImage;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = ['category_id', 'spu', 'name', 'slug', 'brand', 'team', 'description', 'status', 'created_by', 'updated_by'];

    protected $casts = [
        'status' => ProductStatusEnum::class,
    ];

    public function productVariants() {
        return $this->hasMany(ProductVariant::class);
    }

    public function productImages() {
        return $this->hasMany(ProductImage::class);
    }

    public function attributes() {
        return $this->belongsToMany(Attribute::class, 'product_attributes', 'product_id', 'attribute_id')->withPivot('sort_order');
    }

    public function attributeValues() {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values', 'product_id', 'attribute_value_id');
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
