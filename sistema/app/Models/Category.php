<?php

namespace App\Models;
use Illuminate\database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[fillable(['nombre','descripcion'])]
class Category extends Model
{
    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}
