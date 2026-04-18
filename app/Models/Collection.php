<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    protected $fillable = [
        'description',
    ];

    public function featuredImages(): HasMany
    {
        return $this->hasMany(FeaturedImage::class);
    }
}
