<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomErrorPagesTest extends TestCase
{
    #[Test]
    public function halaman_404_kustom_merender_desain_anti_ai_slop(): void
    {
        $response = $this->get('/rute-acak-pasti-tidak-ada-' . uniqid());

        $response->assertNotFound();
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('404');
        $response->assertSee('Dashboard Utama');
        $response->assertSee('Kembali');
    }

    #[Test]
    public function halaman_404_pada_jalur_admin_juga_merender_halaman_kustom(): void
    {
        $response = $this->get('/admin/halaman-tidak-terdaftar-' . uniqid());

        $response->assertNotFound();
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('404');
    }

    #[Test]
    public function view_419_kustom_memiliki_teks_dan_kontrol_muat_ulang(): void
    {
        $view = $this->view('errors.419');

        $view->assertSee('Sesi Anda Telah Berakhir');
        $view->assertSee('HTTP 419');
        $view->assertSee('Muat Ulang Halaman');
        $view->assertSee('Kembali ke Login');
    }
}

