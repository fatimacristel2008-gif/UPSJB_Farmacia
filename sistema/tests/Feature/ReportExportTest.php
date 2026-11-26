<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReportExportTest extends TestCase
{
    public function test_guest_is_redirected_from_report_exports(): void
    {
        $this->get('/reports/pdf')->assertRedirect('/login');
        $this->get('/reports/excel')->assertRedirect('/login');
    }

    public function test_pwa_files_are_public_and_do_not_contain_dynamic_data(): void
    {
        $this->assertFileExists(public_path('manifest.json'));
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('icons/icon-192.svg'));
        $this->assertFileExists(public_path('icons/icon-512.svg'));

        $this->assertStringNotContainsString('Sale', file_get_contents(public_path('sw.js')));
        $this->assertStringNotContainsString('password', file_get_contents(public_path('sw.js')));
    }
}
