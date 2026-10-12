<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function test_missing_page_uses_the_application_error_experience(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('Page Not Found')
            ->assertSee('Return to Login')
            ->assertDontSee('exception', false);
    }

    public function test_operational_error_pages_render_safe_guidance(): void
    {
        $pages = [
            419 => 'Your Session Expired',
            429 => 'Please Slow Down',
            500 => 'We Could Not Complete That Request',
        ];

        foreach ($pages as $status => $heading) {
            $this->view("errors.{$status}")
                ->assertSee($heading)
                ->assertSee('Return to Login');
        }
    }
}
