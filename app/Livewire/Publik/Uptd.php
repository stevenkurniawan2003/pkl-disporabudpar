<?php

namespace App\Livewire\Publik;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman UPTD (statis): UPTD Sarana dan Prasarana Olahraga dan UPTD Destinasi Wisata Terpadu.
 * Halaman diambil dari route ->defaults('halaman', ...), isinya dari config/uptd.php.
 */
#[Layout('layouts.publik')]
class Uptd extends Component
{
    public const HALAMAN = ['olahraga', 'wisata-terpadu'];

    public string $halaman = 'olahraga';

    public function mount(string $halaman = 'olahraga'): void
    {
        abort_unless(in_array($halaman, self::HALAMAN, true), 404);

        $this->halaman = $halaman;
    }

    public function render()
    {
        $data = config('uptd.'.$this->halaman);

        return view('livewire.publik.uptd.'.$this->halaman, ['d' => $data])
            ->title($data['judul'].' · Disporabudpar Jember');
    }
}
