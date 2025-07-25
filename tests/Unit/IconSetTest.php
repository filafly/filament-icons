<?php

namespace Filafly\Icons\Tests\Unit;

use Filafly\Icons\IconSet;
use Filafly\Icons\IconStyle;
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
        $this->assertEquals(IconStyle::Solid, $this->iconSet->getCurrentStyle());
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
        $this->assertEquals(IconStyle::Regular, $this->iconSet->getCurrentStyle());

        $result = $this->iconSet->solid();

        $this->assertSame($this->iconSet, $result);
        $this->assertEquals(IconStyle::Solid, $this->iconSet->getCurrentStyle());
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

class TestIconSet extends IconSet
{
    protected string $pluginId = 'test-icons';

    protected mixed $iconEnum = TestIconEnum::class;

    protected string $iconPrefix = 'test';

    protected array $availableStyles = [
        IconStyle::Regular,
        IconStyle::Solid,
    ];

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

    public function getCurrentStyle(): ?IconStyle
    {
        return $this->currentStyle;
    }
}
