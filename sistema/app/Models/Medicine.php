<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category_id', 'name', 'price', 'stock', 'image'])]
class Medicine extends Model
{
    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'stock' => 'integer'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function detailSales(): HasMany
    {
        return $this->hasMany(DetailSale::class);
    }

    // Diagrama: +actualizarStock(): void
    public function updateStock(int $amount): void
    {
        $this->decrement('stock', $amount);
    }
}
