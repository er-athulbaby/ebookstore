<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftCardDesign extends Model
{
    protected $fillable = ['name', 'image_path', 'value', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function imageUrl(): string
    {
        return $this->image_path ? asset('storage/'.$this->image_path) : asset('assets/img/gift-card-placeholder.svg');
    }
}
