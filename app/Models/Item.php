<?php

namespace App\Models;

use App\Models\RecommendedAddOn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'clover_id', 'image', 'short_description', 'long_description', 'price', 'category_id', 'nutrition_html', 'ingredients_html','banner_image','flavor_of_week','drink_of_day','drink_of_month'];

    public function categories()
    {
        return $this->belongsToMany(Category::class,'category_item');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->belongsToMany(Variant::class, 'item_variant')->withTimestamps();
    }

    public function recommendedAddOns()
    {
        return $this->belongsToMany(RecommendedAddOn::class, 'item_recommended_add_on');
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

     public function itemDescriptions()
    {
        return $this->hasMany(ItemDescription::class, 'item_id');
    }
}
