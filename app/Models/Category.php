<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name','clover_category_id','item_group_id', 'parent_category_id', 'has_subcategories'];

     public function items()
    {
        return $this->belongsToMany(Item::class,'category_item');
    }

    public function itemGroup()
    {
        return $this->belongsTo(ItemGroup::class);
    }

    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'parent_category_id');
    }

    public function subCategories()
    {
        return $this->hasMany(Category::class, 'parent_category_id');
    }

    // public function items()
    // {
    //     return $this->hasMany(Item::class);
    // }
}
