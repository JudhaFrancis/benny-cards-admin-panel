<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'photo',
        'stock',
        'size',
        'condition',
        'status',
        'price',
        'discount',
        'is_featured',
        'cat_id',
        'child_cat_id',
        'brand_id',
        'type',
        'added_by',
        'modified_by'
    ];

    /**
     * Relationship: Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id');
    }

    /**
     * Relationship: Child Category
     */
    public function childCategory()
    {
        return $this->belongsTo(Category::class, 'child_cat_id');
    }

    /**
     * Relationship: Brand
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Relationship: Multiple Gallery Images
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Relationship: User who added the product
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Relationship: User who last modified the product
     */
    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    /**
     * Helper to get the image storage directory based on type
     */
    public function getStorageDir()
    {
        return $this->type === 'gift' ? 'gifts' : 'invitation_cards';
    }

    /**
     * Generate a unique slug
     */
    public static function generateSlug($title)
    {
        $slug = Str::slug($title);
        $count = self::where('slug', 'like', "{$slug}%")->count();
        return $count ? "{$slug}-{$count}" : $slug;
    }
}
