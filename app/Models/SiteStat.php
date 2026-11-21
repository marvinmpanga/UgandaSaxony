<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value', 'label', 'sort_order'])]
class SiteStat extends Model
{
    public static function ordered(): Collection
    {
        return static::orderBy('sort_order')->get();
    }
}
