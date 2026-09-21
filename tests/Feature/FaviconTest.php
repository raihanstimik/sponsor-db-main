<?php

declare(strict_types=1);

namespace Tests\Feature;

use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FaviconTest extends TestCase
{
    #[Test]
    public function berkas_favicon_dan_icon_icm_tersedia_di_public(): void
    {
        $icoPath = public_path('favicon.ico');
        $this->assertFileExists($icoPath);
        $this->assertGreaterThan(0, filesize($icoPath));

        // Verifikasi magic bytes file ICO (\x00\x00\x01\x00)
        $handle = fopen($icoPath, 'rb');
        $header = fread($handle, 4);
        fclose($handle);
        $this->assertSame("\x00\x00\x01\x00", $header);

        $pngPath = public_path('images/icon-icm.png');
        $this->assertFileExists($pngPath);
        $this->assertGreaterThan(0, filesize($pngPath));
        $pngSize = getimagesize($pngPath);
        $this->assertSame('image/png', $pngSize['mime']);

        $png32Path = public_path('images/icon-icm-32x32.png');
        $this->assertFileExists($png32Path);
        $png32Size = getimagesize($png32Path);
        $this->assertSame(32, $png32Size[0]);
        $this->assertSame(32, $png32Size[1]);

        $appleTouchPath = public_path('apple-touch-icon.png');
        $this->assertFileExists($appleTouchPath);
        $appleTouchSize = getimagesize($appleTouchPath);
        $this->assertSame(180, $appleTouchSize[0]);
        $this->assertSame(180, $appleTouchSize[1]);
    }

    #[Test]
    public function halaman_login_admin_memuat_link_favicon_icm(): void
    {
        $response = $this->get('/admin/login');

        $response->assertSuccessful();
        $response->assertSee('images/icon-icm.png');
        $response->assertSee('favicon.ico');
        $response->assertSee('apple-touch-icon.png');
        $response->assertSee('images/icon-icm-32x32.png');
    }

    #[Test]
    public function konfigurasi_filament_panel_admin_memiliki_favicon_icm(): void
    {
        $panel = Filament::getPanel('admin');

        $this->assertNotNull($panel->getFavicon());
        $this->assertStringContainsString('images/icon-icm.png', $panel->getFavicon());
    }
}
