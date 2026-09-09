<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use Tests\TestCase;

final class StoredXssProtectionTest extends TestCase
{
    public function test_staff_enquiry_message_is_rendered_with_blade_escaping(): void
    {
        $view = file_get_contents(
            resource_path('views/staff/enquiries/index.blade.php'),
        );

        $this->assertIsString($view);

        $this->assertStringContainsString(
            '{{ $enquiry->message }}',
            $view,
        );

        $this->assertStringNotContainsString(
            '{!! $enquiry->message !!}',
            $view,
        );
    }

    public function test_content_security_policy_blocks_inline_javascript(): void
    {
        $response = $this->get('/login');

        $response->assertOk();

        $csp = $response->headers->get(
            'Content-Security-Policy',
        );

        $this->assertNotNull($csp);

        $this->assertStringContainsString(
            "script-src 'self'",
            $csp,
        );

        $this->assertStringNotContainsString(
            "script-src 'self' 'unsafe-inline'",
            $csp,
        );

        $this->assertStringContainsString(
            "object-src 'none'",
            $csp,
        );
    }
}
