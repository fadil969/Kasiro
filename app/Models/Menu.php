<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'price',
        'cost',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'cost' => 'integer',
        ];
    }

    /**
     * Slug nama menu → penentu file gambar lokal.
     */
    public function imageSlug(): string
    {
        return strtolower(trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $this->name), '-'));
    }

    /**
     * URL gambar menu dari file LOKAL public/images/menu/<slug>.<ekstensi>.
     * Tidak ada kolom image di database — konsepnya sudah benar: gambar = file lokal.
     * Kembalikan null bila file tidak ada (halaman akan pakai placeholder).
     */
    public function imageUrl(): ?string
    {
        $slug = $this->imageSlug();

        foreach (['svg', 'png', 'jpg', 'jpeg'] as $ext) {
            $relative = "images/menu/{$slug}.{$ext}";
            if (is_file(public_path($relative))) {
                return asset($relative);
            }
        }

        return null;
    }
}
