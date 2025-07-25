<?php

namespace Filafly\Icons\Tests\Unit;

use Filafly\Icons\IconStyle;
use PHPUnit\Framework\TestCase;

class IconStyleTest extends TestCase
{
    public function test_enum_cases_exist()
    {
        $expectedCases = [
            'Regular', 'Solid', 'Filled', 'Outline',
            'Light', 'Bold', 'Duotone', 'Sharp',
        ];

        $actualCases = array_map(fn ($case) => $case->name, IconStyle::cases());

        foreach ($expectedCases as $expectedCase) {
            $this->assertContains($expectedCase, $actualCases);
        }
    }

    public function test_get_style_name_returns_lowercase()
    {
        $this->assertEquals('regular', IconStyle::Regular->getStyleName());
        $this->assertEquals('solid', IconStyle::Solid->getStyleName());
        $this->assertEquals('filled', IconStyle::Filled->getStyleName());
        $this->assertEquals('duotone', IconStyle::Duotone->getStyleName());
    }

    public function test_get_enum_suffix_returns_value()
    {
        $this->assertEquals('Regular', IconStyle::Regular->getEnumSuffix());
        $this->assertEquals('Solid', IconStyle::Solid->getEnumSuffix());
        $this->assertEquals('Filled', IconStyle::Filled->getEnumSuffix());
        $this->assertEquals('Duotone', IconStyle::Duotone->getEnumSuffix());
    }

    public function test_get_style_names_returns_all_lowercase_names()
    {
        $styleNames = IconStyle::getStyleNames();

        $this->assertIsArray($styleNames);
        $this->assertContains('regular', $styleNames);
        $this->assertContains('solid', $styleNames);
        $this->assertContains('filled', $styleNames);
        $this->assertContains('outline', $styleNames);
        $this->assertContains('light', $styleNames);
        $this->assertContains('bold', $styleNames);
        $this->assertContains('duotone', $styleNames);
        $this->assertContains('sharp', $styleNames);
    }

    public function test_from_style_name_finds_correct_case()
    {
        $this->assertEquals(IconStyle::Regular, IconStyle::fromStyleName('regular'));
        $this->assertEquals(IconStyle::Solid, IconStyle::fromStyleName('solid'));
        $this->assertEquals(IconStyle::Filled, IconStyle::fromStyleName('filled'));
        $this->assertEquals(IconStyle::Duotone, IconStyle::fromStyleName('duotone'));
    }

    public function test_from_style_name_returns_null_for_invalid_name()
    {
        $this->assertNull(IconStyle::fromStyleName('invalid'));
        $this->assertNull(IconStyle::fromStyleName('Regular')); // Case sensitive
        $this->assertNull(IconStyle::fromStyleName(''));
        $this->assertNull(IconStyle::fromStyleName('nonexistent'));
    }

    public function test_from_style_name_is_case_sensitive()
    {
        $this->assertNull(IconStyle::fromStyleName('REGULAR'));
        $this->assertNull(IconStyle::fromStyleName('Regular'));
        $this->assertNull(IconStyle::fromStyleName('SOLID'));
        $this->assertEquals(IconStyle::Regular, IconStyle::fromStyleName('regular'));
    }
}
