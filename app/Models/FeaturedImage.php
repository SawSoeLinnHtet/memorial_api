<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedImage extends Model
{
    protected $fillable = [
        'memory_text',
        'collection_id',
        'image_url',
        'memorial_date',
    ];

    protected function casts(): array
    {
        return [
            'memorial_date' => 'date:Y-m-d',
        ];
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
