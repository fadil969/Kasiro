<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * Menu yang memakai kategori ini — dihubungkan lewat nama
     * (menus.category = categories.name, bukan id).
     */
    public function menus()
    {
        return $this->hasMany(Menu::class, 'category', 'name');
    }
}
