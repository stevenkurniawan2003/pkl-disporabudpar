<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_halaman_utama_diarahkan_ke_beranda_publik(): void
    {
        $this->get('/')->assertRedirect('/publik/beranda');
    }
}
