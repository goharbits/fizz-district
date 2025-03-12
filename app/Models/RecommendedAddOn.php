<?php

namespace App\Models;

use App\Models\Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecommendedAddOn extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'description'];

    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_recommended_add_on');
    }
}
