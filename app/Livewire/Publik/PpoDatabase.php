<?php

namespace App\Livewire\Publik;

use App\Models\Cabor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Dasar halaman publik PPO Database Atlet/Pelatih/Cabor: grafik, pilihan cabor, pencarian nama/cabor, tabel.
 * Data pribadi (alamat rumah, tanggal lahir, No. HP) tidak ditampilkan ke publik.
 */
#[Layout('layouts.publik')]
abstract class PpoDatabase extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $cari = '';

    #[Url(except: '')]
    public string $cabor = '';

    /** @return array{judul: string, deskripsi: string, placeholder: string, title: string} */
    abstract protected function teks(): array;

    abstract protected function daftarGrafik(): array;

    /** Judul kolom tabel (tanpa No). */
    abstract protected function kolom(): array;

    /** @return array{0: LengthAwarePaginator, 1: list<list<string|int|null>>} paginator dan isi baris tabel */
    abstract protected function data(array $filter): array;

    public function updated(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $teks = $this->teks();
        [$daftar, $baris] = $this->data(['cabor' => isset(Cabor::DAFTAR[$this->cabor]) ? $this->cabor : '', 'cari' => $this->cari]);

        return view('livewire.publik.ppo-database', [
            'teks' => $teks,
            'daftarGrafik' => $this->daftarGrafik(),
            'daftarCabor' => Cabor::pilihan(),
            'kolom' => $this->kolom(),
            'daftar' => $daftar,
            'baris' => $baris,
        ])->title($teks['title'].' · Disporabudpar Jember');
    }
}
