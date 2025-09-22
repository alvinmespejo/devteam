<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['external_id', 'name', 'category', 'description', 'image', 'price', 'rating', 'count'];

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }
}
