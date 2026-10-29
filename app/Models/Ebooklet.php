<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** E-booklet wisata Jember untuk diunduh wisatawan (saran dokumen konsep Pariwisata poin 5). */
class Ebooklet extends Model
{
    protected $table = 'ebooklet';

    protected $primaryKey = 'id_ebooklet';

    protected $fillable = ['judul', 'tahun', 'deskripsi', 'sampul', 'file_pdf'];

    protected function casts(): array
    {
        return ['tahun' => 'integer'];
    }

    public function getSampulUrlAttribute(): ?string
    {
        return $this->sampul ? asset('storage/'.$this->sampul) : null;
    }

    public function getPdfUrlAttribute(): string
    {
        return asset('storage/'.$this->file_pdf);
    }
}
