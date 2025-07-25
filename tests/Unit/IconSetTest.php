<?php

namespace Filafly\Icons\Tests\Unit;

use Filafly\Icons\Contracts\StyleEnum;
use Filafly\Icons\IconSet;
use PHPUnit\Framework\TestCase;

class IconSetTest extends TestCase
{
    private TestIconSet $iconSet;

    protected function setUp(): void
    {
        $this->iconSet = new TestIconSet;
    }

    public function test_get_icon_enum_returns_configured_enum()
    {
        $this->assertEquals(TestIconEnum::class, $this->iconSet->getIconEnum());
    }

    public function test_override_alias_adds_to_overrides()
    {
        $result = $this->iconSet->overrideAlias('actions.create', TestIconEnum::PlusRegular);

        $this->assertSame($this->iconSet, $result);
        $this->assertArrayHasKey('actions.create', $this->iconSet->getAliasOverrides());
        $this->assertEquals(TestIconEnum::PlusRegular, $this->iconSet->getAliasOverrides()['actions.create']);
    }

    public function test_override_aliases_adds_multiple_overrides()
    {
        $overrides = [
            'actions.create' => TestIconEnum::PlusRegular,
            'actions.delete' => TestIconEnum::TrashSolid,
        ];

        $result = $this->iconSet->overrideAliases($overrides);

        $this->assertSame($this->iconSet, $result);

        $aliasOverrides = $this->iconSet->getAliasOverrides();
        $this->assertArrayHasKey('actions.create', $aliasOverrides);
        $this->assertArrayHasKey('actions.delete', $aliasOverrides);
        $this->assertEquals(TestIconEnum::PlusRegular, $aliasOverrides['actions.create']);
        $this->assertEquals(TestIconEnum::TrashSolid, $aliasOverrides['actions.delete']);
    }

    public function test_override_icon_adds_to_overrides()
    {
        $result = $this->iconSet->overrideIcon(TestIconEnum::PlusRegular, TestIconEnum::PlusSolid);

        $this->assertSame($this->iconSet, $result);
        $this->assertArrayHasKey('plus-regular', $this->iconSet->getIconOverrides());
        $this->assertEquals(TestIconEnum::PlusSolid, $this->iconSet->getIconOverrides()['plus-regular']);
    }

    public function test_override_icons_adds_multiple_overrides()
    {
        $overrides = [
            'plus-regular' => TestIconEnum::PlusSolid,
            'trash-regular' => TestIconEnum::TrashSolid,
        ];

        $result = $this->iconSet->overrideIcons($overrides);

        $this->assertSame($this->iconSet, $result);

        $iconOverrides = $this->iconSet->getIconOverrides();
        $this->assertArrayHasKey('plus-regular', $iconOverrides);
        $this->assertArrayHasKey('trash-regular', $iconOverrides);
        $this->assertEquals(TestIconEnum::PlusSolid, $iconOverrides['plus-regular']);
        $this->assertEquals(TestIconEnum::TrashSolid, $iconOverrides['trash-regular']);
    }

    public function test_style_sets_current_style()
    {
        $result = $this->iconSet->style('solid');

        $this->assertSame($this->iconSet, $result);
        $this->assertEquals(TestStyleEnum::Solid, $this->iconSet->getCurrentStyle());
    }

    public function test_style_throws_exception_for_unavailable_style()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Style 'filled' is not available for this icon set. Available styles: regular, solid");

        $this->iconSet->style('filled');
    }

    public function test_style_throws_exception_for_invalid_style()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Style 'invalid' is not available for this icon set. Available styles: regular, solid");

        $this->iconSet->style('invalid');
    }

    public function test_dynamic_style_methods_work()
    {
        $result = $this->iconSet->regular();

        $this->assertSame($this->iconSet, $result);
        $this->assertEquals(TestStyleEnum::Regular, $this->iconSet->getCurrentStyle());

        $result = $this->iconSet->solid();

        $this->assertSame($this->iconSet, $result);
        $this->assertEquals(TestStyleEnum::Solid, $this->iconSet->getCurrentStyle());
    }

    public function test_dynamic_method_throws_exception_for_unavailable_style()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage("Method 'filled' does not exist. Available style methods: regular, solid");

        $this->iconSet->filled();
    }

    public function test_get_id_returns_plugin_id()
    {
        $this->assertEquals('test-icons', $this->iconSet->getId());
    }

    public function test_get_available_styles_returns_configured_styles()
    {
        $availableStyles = $this->iconSet->getAvailableStyles();

        $this->assertCount(2, $availableStyles);
        $this->assertContains(TestStyleEnum::Regular, $availableStyles);
        $this->assertContains(TestStyleEnum::Solid, $availableStyles);
    }

    public function test_get_available_style_names_returns_lowercase_names()
    {
        $styleNames = $this->iconSet->getAvailableStyleNames();

        $this->assertCount(2, $styleNames);
        $this->assertContains('regular', $styleNames);
        $this->assertContains('solid', $styleNames);
        $this->assertNotContains('filled', $styleNames);
    }

    public function test_has_style_with_string_parameter()
    {
        $this->assertTrue($this->iconSet->hasStyle('regular'));
        $this->assertTrue($this->iconSet->hasStyle('solid'));
        $this->assertFalse($this->iconSet->hasStyle('filled'));
        $this->assertFalse($this->iconSet->hasStyle('invalid'));
    }

    public function test_has_style_with_enum_parameter()
    {
        $this->assertTrue($this->iconSet->hasStyle(TestStyleEnum::Regular));
        $this->assertTrue($this->iconSet->hasStyle(TestStyleEnum::Solid));
    }
}

// Test implementations
enum TestIconEnum: string
{
    case PlusRegular = 'plus-regular';
    case PlusSolid = 'plus-solid';
    case TrashRegular = 'trash-regular';
    case TrashSolid = 'trash-solid';
    case SearchRegular = 'search-regular';
    case SearchSolid = 'search-solid';
}

enum TestStyleEnum: string implements StyleEnum
{
    case Regular = 'Regular';
    case Solid = 'Solid';

    public function getStyleName(): string
    {
        return strtolower($this->name);
    }

    public function getEnumSuffix(): string
    {
        return $this->value;
    }

    public static function getStyleNames(): array
    {
        return array_map(fn ($case) => $case->getStyleName(), self::cases());
    }

    public static function fromStyleName(string $styleName): mixed
    {
        foreach (self::cases() as $case) {
            if ($case->getStyleName() === $styleName) {
                return $case;
            }
        }

        return null;
    }
}

class TestIconSet extends IconSet
{
    protected string $pluginId = 'test-icons';

    protected mixed $iconEnum = TestIconEnum::class;

    protected mixed $styleEnum = TestStyleEnum::class;

    protected string $iconPrefix = 'test';

    protected array $iconMap = [
        'actions.create' => TestIconEnum::PlusRegular,
        'actions.delete' => TestIconEnum::TrashRegular,
        'actions.search' => TestIconEnum::SearchRegular,
    ];

    // Expose protected properties for testing
    public function getAliasOverrides(): array
    {
        return $this->aliasOverrides;
    }

    public function getIconOverrides(): array
    {
        return $this->iconOverrides;
    }

    public function getCurrentStyle(): mixed
    {
        return $this->currentStyle;
    }
}
