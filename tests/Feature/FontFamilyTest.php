<?php

namespace Tests\Feature;

use Tests\TestCase;

class FontFamilyTest extends TestCase
{
    public function test_font_family_no_longer_references_georgia(): void
    {
        $css = file_get_contents(base_path('resources/css/app.css'));
        $tailwind = file_get_contents(base_path('tailwind.config.js'));

        $this->assertStringContainsString('Roboto Serif', $css);
        $this->assertStringNotContainsString('Georgia', $css);
        $this->assertStringNotContainsString('Georgia', $tailwind);
    }
}
