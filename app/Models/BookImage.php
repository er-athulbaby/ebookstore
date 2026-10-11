<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookImage extends Model
{
    use HasFactory;

    protected $fillable = ['book_id', 'path', 'alt_text', 'sort_order'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function url(): string
    {
        return asset('storage/'.$this->path);
    }
}
