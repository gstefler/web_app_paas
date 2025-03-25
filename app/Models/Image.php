<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'extension',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function filename(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->id . '.' . $this->extension;
            }
        );
    }

    public function path(): Attribute
    {
        return Attribute::make(
            get: fn() => route('images.show', $this->id)
        );
    }
}
